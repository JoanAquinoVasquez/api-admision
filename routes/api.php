<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\BitacoraController;
use App\Http\Controllers\ConceptoPagoController;
use App\Http\Controllers\CorreoController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\DistritoController;
use App\Http\Controllers\DniController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\FacultadController;
use App\Http\Controllers\GradoController;
use App\Http\Controllers\InscripcionController;
use App\Http\Controllers\InscripcionUpdateController;
use App\Http\Controllers\NotaController;
use App\Http\Controllers\PostulanteController;
use App\Http\Controllers\PreInscripcionController;
use App\Http\Controllers\ProgramaController;
use App\Http\Controllers\ProvinciaController;
use App\Http\Controllers\ReservaDevolucionController;
use App\Http\Controllers\ResultadosController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\Auth\AuthDocenteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'active'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->middleware(['role:super-admin']);
    Route::get('users/{id}', [UserController::class, 'show'])->middleware(['role:super-admin']);
    Route::post('users', [UserController::class, 'store'])->middleware(['role:super-admin']);
    Route::post('users/{id}', [UserController::class, 'update'])->middleware(['role:super-admin']);

    Route::get('/programas-aperturados-pdf', [InscripcionController::class, 'reportProgramasAperturadosPDF']);
    Route::get('/programas-no-aperturados-pdf', [InscripcionController::class, 'reportProgramasNoAperturadosPDF']);
    Route::get('/preinscritos-totales', [ProgramaController::class, 'preInscritosTotales']);
    Route::get('/programas-inscritos', [ProgramaController::class, 'listInscritos']);
    Route::get('/resumen-inscripcion', [InscripcionController::class, 'resumenInscripcion']);
    Route::get('/estado-inscripcion', [InscripcionController::class, 'estadoInscripcion']);
    Route::apiResource('/concepto-pago', ConceptoPagoController::class);


    /**
     * Modulo de Pre-Inscripciones
     */
    // CRUD
    Route::get('/pre-inscripcion', [PreInscripcionController::class, 'index'])->middleware(['role:super-admin|admin|comision']);
    Route::get('/pre-inscripcion/{preInscripcion}', [PreInscripcionController::class, 'show'])->middleware(['role:super-admin|admin']);
    Route::put('/pre-inscripcion/{preInscripcion}', [PreInscripcionController::class, 'update'])->middleware(['role:super-admin|admin']);
    Route::delete('/pre-inscripcion/{preInscripcion}', [PreInscripcionController::class, 'destroy'])->middleware(['role:super-admin|admin']);
    // Reportes
    Route::get('/reporte-preinscripcion', [PreInscripcionController::class, 'report'])->middleware(['role:super-admin|admin|comision']);
    Route::get('/reporte-preinscripcion-diario', [PreInscripcionController::class, 'reportDiario'])->middleware(['role:super-admin|admin|comision']);
    Route::get('/reporte-preinscripcion-facultad-diario', [PreInscripcionController::class, 'reportDiarioFacultad'])->middleware(['role:super-admin|admin|comision']);
    //Dashboard Inscripcion
    Route::get('resumen-preinscripcion', [PreInscripcionController::class, 'resumenPreinscripcion'])->middleware(['role:super-admin|admin|comision']);
    Route::get('/resumen-tabla-preinscripcion', [PreInscripcionController::class, 'resumenTablaPreInscripcion'])->middleware(['role:super-admin|admin|comision']);
    //Resumen para UiPath
    Route::get('/pre-inscripcion/resumen/total', [PreInscripcionController::class, 'resumenGeneralPreinscripcion']);
    /**
     * Fin Modulo de Pre-Inscripciones
     */


    /**
     * Modulo de Inscripciones
     */
    // CRUD
    Route::get('/inscripcion', [InscripcionController::class, 'index'])->middleware(['role:super-admin|admin|comision']);
    Route::get('/inscripcion/{inscripcion}', [InscripcionController::class, 'show'])->middleware(['role:super-admin|admin']);
    Route::post('/inscripcion-update/{id}', [InscripcionUpdateController::class, 'update'])->middleware(['role:super-admin|admin']);
    Route::post('/inscripcion/val-digital', [InscripcionController::class, 'valDigital'])->middleware(['role:super-admin|admin']);
    Route::get('/inscripcion/val-fisica/{id}', [InscripcionController::class, 'valFisica'])->middleware(['role:super-admin|admin']);
    // Grafico dashboard inscripcion
    Route::get('/resumen-inscripcion-grafico', [InscripcionController::class, 'resumenInscripcionGrafico'])->middleware(['role:super-admin|admin|comision']);
    Route::get('/programas-posibles/{id}', [InscripcionController::class, 'programasPosibles'])->middleware(['role:super-admin|admin']);
    //Resumen para UiPath
    Route::get('/inscripcion/resumen/total', [InscripcionController::class, 'resumenGeneralInscripcion']);

    //Enviar correo de validación
    Route::get('/inscripcion/validad-correo/{id}', [InscripcionController::class, 'enviarCorreo']);

    //Generar cosntancia de inscripcion del postulante
    Route::get('/postulante/constancia/{id}', [PostulanteController::class, 'generateConstancia'])->middleware(['role:super-admin|admin']);

    //Generar los carnets de los postulantes
    Route::post('/postulante-carnet', [PostulanteController::class, 'generateCarnet'])->middleware(['role:super-admin|admin']);

    //Evaluacion postulantes
    Route::get('/inscripcion-nota', [InscripcionController::class, 'inscripcionNota'])->middleware(['role:super-admin|admin']);
    // Reportes
    Route::get('/reporte-inscripcion', [InscripcionController::class, 'report'])->middleware(['role:super-admin|admin']);
    Route::get('/reporte-inscripcion-diario', [InscripcionController::class, 'reportDiario'])->middleware(['role:super-admin|admin']);
    Route::get('/reporte-inscripcion-facultad', [InscripcionController::class, 'reportDiarioFacultad'])->middleware(['role:super-admin|admin']);
    Route::get('/reporte-programas-top', [InscripcionController::class, 'reportProgramasTop'])->middleware(['role:super-admin|admin']);
    // Reporte por facultad en pdf
    Route::get('/reporte-inscripcion-facultad-pdf', [InscripcionController::class, 'reportFacultadPDF'])->middleware(['role:super-admin|admin']);
    Route::get('/reporte-preinscriptos-sin-pagar', [InscripcionController::class, 'reportPreinscritosSinPagar'])->middleware(['role:super-admin|admin']);

    //// Vouchers
    // Cargar Vouchers del BN para la Inscripcion
    Route::apiResource('/vouchers', VoucherController::class)->except(['destroy'])->middleware(['role:super-admin']);
    Route::get('/resumen-vouchers', [VoucherController::class, 'resumenVouchers'])->middleware(['role:super-admin|admin|comision']);
    // Route::post('/vouchers', [VoucherController::class, 'store']);
    Route::get('/voucher/exportar', [VoucherController::class, 'export'])->middleware(['role:super-admin']);
    /**
     * Fin Modulo de Inscripciones
     */



    /**
     * Modulo de Evaluaciones: Notas y Resultados
     */
    Route::apiResource('docentes', DocenteController::class)->except('show')->middleware(['role:super-admin']);
    // Rutas para el docente y asignación de programas
    Route::post('docente-programa/{docente}', [DocenteController::class, 'asignarPrograma'])->middleware(['role:super-admin']);

    ///////// Reportes los Postulantes Aptos para evaluacion en PDF /////////
    // Reporte de postulantes y notas de cv evaluadas por un docente
    Route::get('/postulantes-notasCV-admin/{idPrograma}', [DocenteController::class, 'reportNotasCV'])->middleware(['role:super-admin|admin']);
    Route::post('/postulantes-notasCV-multiple-admin', [DocenteController::class, 'reportNotasCVMultiple'])->middleware(['role:super-admin|admin']);
    // Reporte de postulantes aptos para entrevista
    Route::get('/postulantes-aptos/{idPrograma}', [NotaController::class, 'postulantesAptos'])->middleware(['role:super-admin|admin']);
    Route::get('/postulantes-aptos-multiple', [NotaController::class, 'postulantesAptosMultiple'])->middleware(['role:super-admin|admin']);
    // Extraer las notas de xlsx de los postulantes del Examen de Admisión
    Route::post('/extraer-notas-examen', [NotaController::class, 'storeExamenAdmision'])->middleware(['role:super-admin|admin']);
    // Reporte final de postulantes con sus 3 notas y nota final

    Route::post('/guardar-nota-entrevista', [NotaController::class, 'guardarNotaEntrevista'])->middleware(['role:super-admin|admin']);

    /**
     * Fin Modulo de Evaluaciones: Notas y Resultados
     */



    /**
     * Modulo de Reserva y Devoluciones
     */
    Route::post('/inhabilitar-inscripciones', [ReservaDevolucionController::class, 'inhabilitarInscripciones'])->middleware(['role:super-admin|admin']);
    Route::get('/programas-inhabilitados', [ReservaDevolucionController::class, 'programasInhabilitadosAll'])->middleware(['role:super-admin|admin']);
    Route::get('/programas-inhabilitados/{idGrado}', [ReservaDevolucionController::class, 'programasInhabilitados'])->middleware(['role:super-admin|admin']);
    Route::get('/inscripciones-inhabilitadas', [ReservaDevolucionController::class, 'inscripcionesInhabilitadas'])->middleware(['role:super-admin|admin']);
    Route::get('/inscripciones-inhabilitadas/{idPrograma}', [ReservaDevolucionController::class, 'inscripcionesInhabilitadasPrograma'])->middleware(['role:super-admin|admin']);
    // Acciones para la inscripciones que no abriran
    // RESERVA:
    Route::get('/reservas/inscripcion/{id}', [ReservaDevolucionController::class, 'reservarInscripcion'])->middleware(['role:super-admin|admin']);
    Route::get('/reservas/programa/{idPrograma}', [ReservaDevolucionController::class, 'listarInscripcionesReserva'])->middleware(['role:super-admin|admin']);
    Route::get('/reservas/inscripcion/{id}/cancelar', [ReservaDevolucionController::class, 'cancelarReserva'])->middleware(['role:super-admin|admin']);
    Route::get('/reservas/reporte', [ReservaDevolucionController::class, 'reportReserva'])->middleware(['role:super-admin|admin']);
    Route::get('/reservas/vouchers', [ReservaDevolucionController::class, 'reportReservaVouchers'])->middleware(['role:super-admin|admin']);
    // DEVOLUCION:
    Route::get('/devolucion/inscripcion/{id}', [ReservaDevolucionController::class, 'devolverInscripcion'])->middleware(['role:super-admin|admin']);
    Route::get('/devolucion/programa/{idPrograma}', [ReservaDevolucionController::class, 'listarInscripcionesDevolver'])->middleware(['role:super-admin|admin']);
    Route::get('/devolucion/inscripcion/{id}/cancelar', [ReservaDevolucionController::class, 'cancelarDevolucion'])->middleware(['role:super-admin|admin']);
    Route::get('/devolucion/reporte', [ReservaDevolucionController::class, 'reportDevolucion'])->middleware(['role:super-admin|admin']);
    // Cambio de programa
    Route::get('/programas-posibles/{id}', [ReservaDevolucionController::class, 'showProgramasPosibles'])->middleware(['role:super-admin|admin']);
    Route::post('/programa-cambio/{id}', [ReservaDevolucionController::class, 'updateProgramasPosibles'])->middleware(['role:super-admin|admin']);

    /**
     * Fin de Reserva y Devoluciones
     */

    // REPORTES EXTRAS FINALES
    Route::get('/reporte-final-notas', [NotaController::class, 'reportFinalNotas'])->middleware(['role:super-admin|admin']);
    Route::get('/reporte-inscripcion-final/excel', [InscripcionController::class, 'reportFinalExcel'])->middleware(['role:super-admin|admin']);
    Route::get('/reporte-notas-final-excel', [InscripcionController::class, 'reportNotasFinalExcel'])->middleware(['role:super-admin|admin']);
    Route::get('/reporte-inscripcion-final/pdf', [InscripcionController::class, 'reportFinalPdf'])->middleware(['role:super-admin|admin']);
    Route::get('/reporte-inscripcion-final/aulas/pdf', [InscripcionController::class, 'reportFinalAulasPdf'])->middleware(['role:super-admin|admin']);
    Route::get('/reporte-inscripcion-final/firmas/pdf', [InscripcionController::class, 'reportFinalFirmasPdf'])->middleware(['role:super-admin|admin']);
    Route::get('/reporte-ingresantes-programa', [ResultadosController::class, 'reportIngresantesPrograma'])->middleware(['role:super-admin|admin|comision']);

    // Resultados Ingresantes
    Route::get('/resultados-ingresantes', [ResultadosController::class, 'index'])->middleware(['role:super-admin|admin|comision']);
    // Data de la Bitacora
    Route::get('/bitacora', [BitacoraController::class, 'index'])->middleware(['role:super-admin']);
    Route::get('/bitacora-programa', [BitacoraController::class, 'programaUpdate'])->middleware(['role:super-admin']);
    Route::get('/bitacora-export', [BitacoraController::class, 'export'])->middleware(['role:super-admin']);
    // Dashboard Evaluacion
    Route::get('/resumen-evaluacion', [NotaController::class, 'resumenEvaluacion'])->middleware(['role:super-admin|admin|comision']);
    Route::get('/notas-cv-diarias', [NotaController::class, 'resumenNotasDiarias'])->middleware(['role:super-admin|admin|comision']);
    // Dashboard Resultados
    Route::get('/ingresantes-programa', [ResultadosController::class, 'ingresantesPorPrograma'])->middleware(['role:super-admin|admin|comision']);
    Route::get('/computo-ingresantes', [ResultadosController::class, 'computoIngresantes'])->middleware(['role:super-admin|admin|comision']);
    Route::get('/resumen-edad', [ResultadosController::class, 'resumenPorEdad'])->middleware(['role:super-admin|admin|comision']);
    Route::get('/resumen-general', [ResultadosController::class, 'resumenGeneral'])->middleware(['role:super-admin|admin|comision']);
    Route::get('/histograma-notas', [ResultadosController::class, 'histogramaNotas'])->middleware(['role:super-admin|admin|comision']);
    // Ruta de RPA UiPath y Dashboard Evaluacion
    Route::get('/resumen-docente-notas', [DocenteController::class, 'resumenDocenteNotas'])->middleware(['role:super-admin|admin|comision']);
});

// Rutas protegidas para docentes
Route::middleware(['auth.docente.cookie', 'active:docente'])->group(function () {
    Route::get('/docentes/{id}', [DocenteController::class, 'show']);
    Route::get('/docente-programas', [DocenteController::class, 'programasAsignados']);
    Route::get('/postulantes-programa/{id}', [DocenteController::class, 'postulantesAptos']);
    Route::post('/registrar-nota', [DocenteController::class, 'registrarNota']);
    Route::get('/postulantes-notasCV/{idPrograma}', [DocenteController::class, 'reportNotasCV']);
    Route::post('/postulantes-notasCV-multiple', [DocenteController::class, 'reportNotasCVMultiple']);
});

// Login y Logout de Docente
Route::post('docente-login', [AuthDocenteController::class, 'login']);
Route::post('/refresh-user', [AuthController::class, 'refresh']);
Route::post('/refresh-docente', [AuthDocenteController::class, 'refreshDocente']);
Route::post('docente-logout', [AuthDocenteController::class, 'logout']);
Route::get('/check-auth-docente', [AuthDocenteController::class, 'checkAuthDocente']); // Nueva ruta para verificar autenticación


//Route::post('/vouchers', [VoucherController::class, 'store']);
/////////////////////////////////////////////////////////////


// Rutas de Inscripción para formularios Externos
Route::post('/validar-voucher', [VoucherController::class, 'validarVoucher']);
Route::post('/inscripcion', [InscripcionController::class, 'store']);


// Test de prueba para subir a Drive
Route::post('/test', [CorreoController::class, 'uploadFile']);
Route::get('/test', [CorreoController::class, 'testing']);
Route::get('/list-files', [CorreoController::class, 'listFiles']);


// Rutas de Pre-Inscripción para formularios Externos
Route::post('/pre-inscripcion', [PreInscripcionController::class, 'store']);
Route::post('/pre-inscripcion/registrado', [PreInscripcionController::class, 'preInscrito']);
Route::post('/consulta-dni', [DniController::class, 'consultarDni']);


//Rutas para obtener datos de la BD
Route::get('/departamentos', [DepartamentoController::class, 'index']);
Route::get('/provincias/{id}', [ProvinciaController::class, 'showDepartamento']);
Route::get('/distritos/{id}', [DistritoController::class, 'showProvincia']);
Route::get('/grados', [GradoController::class, 'index']);
Route::get('/facultades', [FacultadController::class, 'index']);
Route::apiResource('/programas', ProgramaController::class);
Route::get('/programas-habilitados', [ProgramaController::class, 'programasHabilitados']);
Route::get('/programa-grado/{id}', [ProgramaController::class, 'showGrado']);


// Rutas de Autenticación de Google con correo Institucional
Route::get('/check-auth', [AuthController::class, 'checkAuth']); // Nueva ruta para verificar autenticación
Route::post('/google-login', [AuthController::class, 'googleLogin']);
Route::post('/logout', [AuthController::class, 'logout']);

//Login Robot RPA UiPath
Route::post('/login-cypress', [AuthController::class, 'loginCypress']);
Route::post('/login-rpa', [AuthController::class, 'loginRPA']);


// Ruta de fallback para rutas no encontradas
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Ruta no encontrada. Por favor verifica la URL.',
    ], 404);
});
