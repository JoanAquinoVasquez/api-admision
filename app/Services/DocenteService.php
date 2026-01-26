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
        Programa::whereIn('id', $programaIds)->update(['docente_id' => $docente->id]);
        return $docente;
    }

    /**
     * Get programas assigned to docente
     */
    public function getProgramasAsignados(int $docenteId)
    {
        $programas = Programa::where('docente_id', $docenteId)
            ->with([
                'inscripciones' => function ($query) {
                    $query->select('id', 'programa_id', 'val_fisico');
                },
                'inscripciones.nota'
            ])
            ->get();

        return $programas->map(function ($programa) {
            $inscripcionesValFisico = $programa->inscripciones->filter(fn($i) => $i->val_fisico == 1);

            $conNota = $inscripcionesValFisico->filter(function ($inscripcion) {
                return $inscripcion->nota && is_numeric($inscripcion->nota->cv);
            })->count();

            $sinNota = $inscripcionesValFisico->filter(function ($inscripcion) {
                return !$inscripcion->nota || !is_numeric($inscripcion->nota->cv);
            })->count();

            return [
                'id_programa' => $programa->id,
                'nombre_programa' => $programa->nombre,
                'id_grado' => $programa->grado->id,
                'nombre_grado' => $programa->grado->nombre,
                'con_nota' => $conNota,
                'sin_nota' => $sinNota,
            ];
        });
    }

    /**
     * Get postulantes aptos for a programa
     */
    public function getPostulantesAptos(int $programaId)
    {
        return Inscripcion::where('programa_id', $programaId)
            ->where('val_fisico', 1)
            ->with(['postulante', 'nota'])
            ->get()
            ->map(function ($inscripcion) {
                return [
                    'postulante' => $inscripcion->postulante,
                    'cv' => optional($inscripcion->nota)->cv,
                    'foto' => $inscripcion->postulante->documentos()->where('tipo', 'foto')->first()->url,
                ];
            });
    }

    /**
     * Register CV grade for postulante
     */
    public function registrarNota(int $postulanteId, float $notaCv)
    {
        $inscripcion = Inscripcion::where('postulante_id', $postulanteId)->firstOrFail();

        $nota = Nota::updateOrCreate(
            ['inscripcion_id' => $inscripcion->id],
            ['cv' => $notaCv]
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

    /**
     * Get summary of docente grades
     */
    public function getResumenDocenteNotas()
    {
        $docentes = Docente::with([
            'programas.grado',
            'programas.inscripciones' => function ($query) {
                $query->where('val_fisico', 1)->with('nota');
            }
        ])->get();

        $resumen = [];

        foreach ($docentes as $docente) {
            $detalleProgramas = [];
            $totalGeneral = 0;
            $evaluadosGeneral = 0;

            foreach ($docente->programas as $programa) {
                $totalPostulantes = $programa->inscripciones->count();
                $conNota = $programa->inscripciones->filter(function ($inscripcion) {
                    return $inscripcion->nota !== null && $inscripcion->nota->cv !== null;
                })->count();

                $totalGeneral += $totalPostulantes;
                $evaluadosGeneral += $conNota;

                $detalleProgramas[] = [
                    'programa' => mb_strtoupper($programa->grado->nombre . ' EN ' . $programa->nombre),
                    'total_postulantes' => $totalPostulantes,
                    'con_nota_cv' => $conNota,
                    'sin_nota_cv' => $totalPostulantes - $conNota,
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
