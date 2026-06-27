<?php

namespace App\Services;

use App\Models\Docente;
use App\Models\Inscripcion;
use App\Models\Nota;
use App\Models\Programa;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Hash;

class DocenteService
{
    /**
     * Get all docentes
     */
    public function getAll()
    {
        return Docente::all();
    }

    /**
     * Get docente by ID
     */
    public function getById(int $id)
    {
        return Docente::find($id);
    }

    /**
     * Create new docente
     */
    public function create(array $data)
    {
        return Docente::create([
            'nombres' => $data['nombres'],
            'ap_paterno' => $data['ap_paterno'],
            'ap_materno' => $data['ap_materno'],
            'dni' => $data['dni'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'tipo' => $data['tipo'] ?? 'cv',
            'estado' => true,
        ]);
    }

    /**
     * Update docente
     */
    public function update(int $id, array $data)
    {
        $docente = Docente::find($id);

        if (!$docente) {
            return null;
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $docente->update($data);
        return $docente;
    }

    /**
     * Deactivate docente
     */
    public function deactivate(int $id)
    {
        $docente = Docente::find($id);

        if (!$docente) {
            return false;
        }

        $docente->update(['estado' => false]);
        return true;
    }

    /**
     * Assign programas to docente
     */
    public function assignProgramas(int $docenteId, array $programaIds)
    {
        $docente = Docente::findOrFail($docenteId);
        
        $column = $docente->tipo === 'entrevista' ? 'docente_entrevista_id' : 'docente_id';
        
        // Limpiamos asignaciones previas de este docente en otros programas para mantener consistencia si es necesario, 
        // o simplemente actualizamos los seleccionados.
        // En este caso, el comportamiento actual es actualizar los programas seleccionados para que este docente sea su evaluador.
        Programa::whereIn('id', $programaIds)->update([$column => $docente->id]);
        
        return $docente;
    }

    /**
     * Get programas assigned to docente
     */
    public function getProgramasAsignados(int $docenteId)
    {
        $docente = Docente::findOrFail($docenteId);

        $query = Programa::query();
        if ($docente->tipo === 'entrevista') {
            $query->where('docente_entrevista_id', $docenteId);
        } else {
            $query->where('docente_id', $docenteId);
        }

        $programas = $query->with([
                'inscripciones' => function ($query) {
                    $query->select('id', 'programa_id', 'val_fisico');
                },
                'inscripciones.nota',
                'grado'
            ])
            ->get();

        return $programas->map(function ($programa) use ($docente) {
            $inscripcionesValFisico = $programa->inscripciones->filter(fn($i) => $i->val_fisico == 1);

            $column = $docente->tipo === 'entrevista' ? 'entrevista' : 'cv';

            $conNota = $inscripcionesValFisico->filter(function ($inscripcion) use ($column) {
                return $inscripcion->nota && is_numeric($inscripcion->nota->$column);
            })->count();

            $sinNota = $inscripcionesValFisico->filter(function ($inscripcion) use ($column) {
                return !$inscripcion->nota || !is_numeric($inscripcion->nota->$column);
            })->count();

            return [
                'id_programa' => $programa->id,
                'nombre_programa' => $programa->nombre,
                'id_grado' => $programa->grado->id,
                'nombre_grado' => $programa->grado->nombre,
                'con_nota' => $conNota,
                'sin_nota' => $sinNota,
                'tipo_docente' => $docente->tipo
            ];
        });
    }

    /**
     * Get postulantes aptos for a programa
     */
    public function getPostulantesAptos(int $programaId)
    {
        $docenteType = auth()->guard('docente')->user()->tipo ?? 'cv';
        $column = $docenteType === 'entrevista' ? 'entrevista' : 'cv';

        return Inscripcion::where('programa_id', $programaId)
            ->where('val_fisico', 1)
            ->with(['postulante', 'nota'])
            ->get()
            ->map(function ($inscripcion) use ($column) {
                return [
                    'postulante' => $inscripcion->postulante,
                    'notaValue' => optional($inscripcion->nota)->$column,
                    'foto' => $inscripcion->postulante->documentos()->where('tipo', 'foto')->first()->url ?? null,
                ];
            });
    }

    /**
     * Register CV grade for postulante
     */
    public function registrarNota(int $postulanteId, float $valorNota, string $tipo = 'cv')
    {
        $inscripcion = Inscripcion::where('postulante_id', $postulanteId)->firstOrFail();

        $column = $tipo === 'entrevista' ? 'entrevista' : 'cv';

        $nota = Nota::updateOrCreate(
            ['inscripcion_id' => $inscripcion->id],
            [$column => $valorNota]
        );

        return $nota;
    }

    /**
     * Generate CV grades report PDF
     */
    public function generateReportNotasCV(int $programaId)
    {
        $inscripciones = Inscripcion::with([
            'postulante',
            'programa.grado',
            'programa.docente',
            'nota'
        ])
            ->where('programa_id', $programaId)
            ->where('val_fisico', 1)
            ->get();

        if ($inscripciones->isEmpty()) {
            return null;
        }

        $inscripciones = $inscripciones->sortBy(function ($inscripcion) {
            return strtolower($inscripcion->postulante->ap_paterno) . ' ' .
                strtolower($inscripcion->postulante->ap_materno) . ' ' .
                strtolower($inscripcion->postulante->nombres);
        })->values();

        $programaData = [
            'programa' => $inscripciones->first()->programa->nombre ?? 'Desconocido',
            'grado' => $inscripciones->first()->programa->grado->nombre ?? 'Desconocido',
            'inscripciones' => $inscripciones,
            'docente' => $inscripciones->first()->programa->docente,
        ];

        $pdf = Pdf::loadView('notas.postulantes-expediente', ['programaData' => $programaData]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    /**
     * Generate multiple CV grades report PDF
     */
    public function generateReportNotasCVMultiple(array $programaIds)
    {
        $programasData = [];

        foreach ($programaIds as $idPrograma) {
            $inscripciones = Inscripcion::with([
                'postulante',
                'programa.grado',
                'programa.docente',
                'nota'
            ])
                ->where('programa_id', $idPrograma)
                ->where('val_fisico', 1)
                ->get();

            $inscripciones = $inscripciones->sortBy(function ($inscripcion) {
                return strtolower($inscripcion->postulante->ap_paterno) . ' ' .
                    strtolower($inscripcion->postulante->ap_materno) . ' ' .
                    strtolower($inscripcion->postulante->nombres);
            })->values();

            if ($inscripciones->isNotEmpty()) {
                $programasData[] = [
                    'programa' => $inscripciones->first()->programa->nombre ?? 'Desconocido',
                    'grado' => $inscripciones->first()->programa->grado->nombre ?? 'Desconocido',
                    'inscripciones' => $inscripciones,
                    'docente' => $inscripciones->first()->programa->docente,
                ];
            }
        }

        if (empty($programasData)) {
            return null;
        }

        $pdf = Pdf::loadView('notas.postulantes-expediente-multiple', ['programasData' => $programasData]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    private function getDocenteEntrevistaForReport(int $programaId, $docenteEntrevista)
    {
        $docenteObj = new \stdClass();
        $docenteObj->nombres = 'POR ASIGNAR';
        $docenteObj->ap_paterno = '';
        $docenteObj->ap_materno = '';
        $docenteObj->dni = '';

        if ($docenteEntrevista) {
            $docenteObj->nombres = trim("{$docenteEntrevista->nombres} {$docenteEntrevista->ap_paterno} {$docenteEntrevista->ap_materno}");
            $docenteObj->dni = $docenteEntrevista->dni ?? '';
        } else {
            $evaluadoresEspeciales = [
                9  => 'DR. CARLOS ADOLFO LOAYZA RIVAS / DR. JUAN FARIAS FEIJOO',
                21 => 'DRA. JESUS ALICIA FERNANDEZ PALOMINO / DR. FREDDY HERNANDEZ RENGIFO',
                10 => 'DR. LUIS ALBERTO OTAKE OYAMA',
                32 => 'DR. VICTOR GUSTAVO HERNANDEZ JIMENEZ', // Aula 13
                34 => 'DRA. MARIA JULIA JARAMILLO CARRION',
                33 => 'DR. MARIANO AGUSTIN RAMOS GARCIA / DR. ELEAZAR RUFASTO CAMPOS',
                8  => 'DRA. MARIANELLA LAURA GARCIA AURICH',
                7  => 'DR. HAMILTON CUEVA CAMPOS',
                22 => 'DR. LEOPOLDO YZQUIERDO HERNANDEZ',
                29 => 'DR. JOSE REUPO PERICHE',
                31 => 'M.SC. JOSE CARLOS LEIVA PIEDRA',
                25 => 'DRA. MILAGROS DEL PILAR CABEZAS MARTINEZ',
                28 => 'DR. PERCY MORANTE GAMARRA',
                27 => 'DR. JUAN CARLOS GRANADOS BARRETO',
                24 => 'DRA. GLORIA PUICON CRUZALEGUI',
            ];

            if (isset($evaluadoresEspeciales[$programaId])) {
                $docenteObj->nombres = $evaluadoresEspeciales[$programaId];
            }
        }

        return $docenteObj;
    }

    /**
     * Generate Entrevista grades report PDF
     */
    public function generateReportNotasEntrevista(int $programaId)
    {
        $inscripciones = Inscripcion::with([
            'postulante',
            'programa.grado',
            'programa.docenteEntrevista',
            'nota'
        ])
            ->where('programa_id', $programaId)
            ->where('val_fisico', 1)
            ->get();

        if ($inscripciones->isEmpty()) {
            return null;
        }

        $inscripciones = $inscripciones->sortBy(function ($inscripcion) {
            return strtolower($inscripcion->postulante->ap_paterno) . ' ' .
                strtolower($inscripcion->postulante->ap_materno) . ' ' .
                strtolower($inscripcion->postulante->nombres);
        })->values();

        $docenteObj = $this->getDocenteEntrevistaForReport($programaId, $inscripciones->first()->programa->docenteEntrevista);

        $programaData = [
            'programa' => $inscripciones->first()->programa->nombre ?? 'Desconocido',
            'grado' => $inscripciones->first()->programa->grado->nombre ?? 'Desconocido',
            'inscripciones' => $inscripciones,
            'docente' => $docenteObj,
        ];

        $pdf = Pdf::loadView('notas.postulantes-entrevista', ['programaData' => $programaData]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    /**
     * Generate multiple Entrevista grades report PDF
     */
    public function generateReportNotasEntrevistaMultiple(array $programaIds)
    {
        $programasData = [];

        foreach ($programaIds as $idPrograma) {
            $inscripciones = Inscripcion::with([
                'postulante',
                'programa.grado',
                'programa.docenteEntrevista',
                'nota'
            ])
                ->where('programa_id', $idPrograma)
                ->where('val_fisico', 1)
                ->get();

            $inscripciones = $inscripciones->sortBy(function ($inscripcion) {
                return strtolower($inscripcion->postulante->ap_paterno) . ' ' .
                    strtolower($inscripcion->postulante->ap_materno) . ' ' .
                    strtolower($inscripcion->postulante->nombres);
            })->values();

            if ($inscripciones->isNotEmpty()) {
                $docenteObj = $this->getDocenteEntrevistaForReport($idPrograma, $inscripciones->first()->programa->docenteEntrevista);

                $programasData[] = [
                    'programa' => $inscripciones->first()->programa->nombre ?? 'Desconocido',
                    'grado' => $inscripciones->first()->programa->grado->nombre ?? 'Desconocido',
                    'inscripciones' => $inscripciones,
                    'docente' => $docenteObj,
                ];
            }
        }

        if (empty($programasData)) {
            return null;
        }

        $pdf = Pdf::loadView('notas.postulantes-entrevista-multiple', ['programasData' => $programasData]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    /**
     * Get summary of docente grades
     */
    public function getResumenDocenteNotas()

    {
        $docentes = Docente::all();

        $resumen = [];

        foreach ($docentes as $docente) {
            $isEntrevista = $docente->tipo === 'entrevista';
            $relationship = $isEntrevista ? 'programasEntrevista' : 'programas';
            $noteColumn = $isEntrevista ? 'entrevista' : 'cv';

            $docente->load([
                $relationship => function ($query) use ($noteColumn) {
                    $query->with('grado')
                          ->withCount([
                              'inscripciones as total_postulantes' => function($query) {
                                  $query->where('val_fisico', 1);
                              },
                              'inscripciones as con_nota' => function($query) use ($noteColumn) {
                                  $query->where('val_fisico', 1)
                                        ->whereHas('nota', function($q) use ($noteColumn){
                                            $q->whereNotNull($noteColumn);
                                        });
                              }
                          ]);
                }
            ]);

            $detalleProgramas = [];
            $totalGeneral = 0;
            $evaluadosGeneral = 0;

            foreach ($docente->$relationship as $programa) {
                $totalPostulantes = $programa->total_postulantes ?? 0;
                $conNota = $programa->con_nota ?? 0;

                $totalGeneral += $totalPostulantes;
                $evaluadosGeneral += $conNota;

                $detalleProgramas[] = [
                    'programa' => mb_strtoupper($programa->grado->nombre . ' EN ' . $programa->nombre),
                    'total_postulantes' => $totalPostulantes,
                    'con_nota' => $conNota,
                    'sin_nota' => $totalPostulantes - $conNota,
                    'avance' => $totalPostulantes > 0
                        ? round(($conNota / $totalPostulantes) * 100, 2) . '%'
                        : '0%',
                ];
            }

            if (count($detalleProgramas)) {
                $resumen[] = [
                    'docente' => [
                        'nombre' => $docente->ap_paterno . ' ' . $docente->ap_materno . ', ' . $docente->nombres,
                        'email' => $docente->email,
                        'tipo' => $docente->tipo,
                    ],
                    'resumen_general' => [
                        'total_postulantes' => $totalGeneral,
                        'evaluados' => $evaluadosGeneral,
                        'pendientes' => $totalGeneral - $evaluadosGeneral,
                        'avance_general' => $totalGeneral > 0
                            ? round(($evaluadosGeneral / $totalGeneral) * 100, 2) . '%'
                            : '0%',
                    ],
                    'programas' => $detalleProgramas,
                ];
            }
        }

        return $resumen;
    }
}
