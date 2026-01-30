<?php

namespace App\Http\Controllers;

use App\Exports\BitacoraExport;
use App\Models\Distrito;
use App\Models\Inscripcion;
use App\Models\Programa;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;

class BitacoraController extends BaseController
{
    /**
     * Get all activity logs with formatted properties
     */
    public function index()
    {
        return $this->handleRequest(function () {
            $bitacora = Activity::latest()->get()->map(function ($log) {
                $properties = $log->properties->toArray();

                // Process distrito_id in data_old and data_new
                foreach (['data_old', 'data_new'] as $key) {
                    if (isset($properties[$key]['distrito_id'])) {
                        $distrito = Distrito::with('provincia.departamento')->find($properties[$key]['distrito_id']);
                        if ($distrito) {
                            $properties[$key]['distrito_info'] = [
                                'nombre_distrito' => $distrito->nombre,
                                'nombre_provincia' => $distrito->provincia->nombre ?? null,
                                'nombre_departamento' => $distrito->provincia->departamento->nombre ?? null,
                            ];
                        }
                    }
                }

                // Process programa_id in programa_old and programa_new
                foreach (['programa_old', 'programa_new'] as $key) {
                    if (isset($properties[$key]['programa_id'])) {
                        $programa = Programa::with('grado')->find($properties[$key]['programa_id']);
                        if ($programa) {
                            $properties[$key] = [
                                'nombre_programa' => $programa->nombre,
                                'nombre_grado' => $programa->grado->nombre ?? null,
                            ];
                        }
                    }
                }

                return [
                    'id' => $log->id,
                    'description' => $log->description,
                    'causer' => $log->causer ? $log->causer->only(['id', 'name', 'email', 'profile_picture']) : ['name' => 'Usuario eliminado'],
                    'properties' => $properties,
                    'created_at' => $log->created_at->toDateTimeString(),
                ];
            });

            return $this->successResponse($bitacora);
        }, 'Error al obtener la bitácora');
    }

    /**
     * Get programa update logs
     */
    public function programaUpdate()
    {
        return $this->handleRequest(function () {
            $bitacoras = Activity::where('description', 'like', '%programa%')->latest()->get();

            $responseData = [];

            foreach ($bitacoras as $bitacora) {
                $oldProgramaId = data_get($bitacora, 'properties.old.programa_id');
                $newProgramaId = data_get($bitacora, 'properties.new.programa_id');
                $otrosCambios = data_get($bitacora, 'properties.otros_cambios');

                $oldPrograma = Programa::with('grado')->find($oldProgramaId);
                $newPrograma = Programa::with('grado')->find($newProgramaId);
                $inscripcion = Inscripcion::find($bitacora->subject_id);

                $responseData[] = [
                    'inscripcion' => $inscripcion,
                    'programa_anterior' => $oldPrograma,
                    'programa_nuevo' => $newPrograma,
                    'otros_cambios' => $otrosCambios,
                    'fecha' => $bitacora->created_at,
                ];
            }

            return $this->successResponse($responseData);
        }, 'Error al obtener actualizaciones de programa');
    }

    /**
     * Export bitacora to Excel
     */
    public function export(Request $request)
    {
        return $this->handleRequest(function () use ($request) {
            $request->validate([
                'fecha_inicio' => 'nullable|date',
                'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            ]);

            $fechaInicio = $request->query('fecha_inicio');
            $fechaFin = $request->query('fecha_fin');

            $export = new BitacoraExport($fechaInicio, $fechaFin);
            $fileName = 'bitacora_admision_' . now()->format('d-m-Y_His') . '.xlsx';

            $this->logActivity('Bitácora exportada', null, [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
            ]);

            return Excel::download($export, $fileName);
        }, 'Error al exportar la bitácora');
    }
}
