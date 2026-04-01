<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Manual de Usuario - Comisión de Admisión 2026-I</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; line-height: 1.5; color: #333; margin: 0; padding: 0; }
        .page { padding: 40px; }
        .header { text-align: center; border-bottom: 3px solid #1e3a8a; padding-bottom: 20px; margin-bottom: 30px; }
        .title { color: #1e40af; font-size: 28px; font-weight: bold; margin: 0; }
        .subtitle { color: #475569; font-size: 16px; margin-top: 5px; }
        .salutation { background-color: #f8fafc; border-left: 4px solid #3b82f6; padding: 15px; margin-bottom: 25px; border-radius: 4px; }
        .section-title { color: #1e3a8a; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px; margin-top: 30px; font-size: 20px; font-weight: bold;}
        .module { margin-bottom: 20px; }
        .image-container { text-align: center; margin: 20px 0; background-color: #f1f5f9; border: 1px solid #cbd5e1; padding: 10px; border-radius: 8px; }
        .image-container img { max-width: 100%; height: auto; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .caption { font-size: 12px; color: #64748b; font-style: italic; margin-top: 8px; }
        ul { padding-left: 20px; }
        li { margin-bottom: 8px; }
        .footer { text-align: center; font-size: 12px; color: #94a3b8; margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 20px; }
        .page-break { page-break-after: always; }
        .notice { font-size: 14px; padding: 12px; border-radius: 5px; color: #0369a1; background-color: #f0f9ff; border: 1px solid #bae6fd; margin-bottom: 20px; }
        .highlight { font-weight: bold; color: #1e3a8a; }
    </style>
</head>
<body>
    <div class="page">
        <!-- Portada / Cabecera -->
        <div class="header">
            <h1 class="title">Sistema de Admisión 2026-I</h1>
            <h2 class="subtitle">Escuela de Posgrado - UNPRG</h2>
            <br>
            <p style="text-transform: uppercase; color: #475569;"><strong>Guía Práctica para la Comisión de Admisión</strong></p>
        </div>

        <div class="salutation">
            <strong>Hola, {{ $user->name }}:</strong><br>
            Te damos la bienvenida al panel principal. Esta guía rápida te ayudará a entender cómo monitorear todo el proceso de los postulantes de forma sencilla y directa.
        </div>

        <div class="notice">
            <strong>💡 Un tip antes de empezar (Botón Exportar):</strong> En todas las pestañas que veremos a continuación, encontrarás un botón llamado <strong>"Exportar"</strong> en la parte superior derecha de las tablas. Este botón es tu gran aliado: te permite descargar cualquier lista que estés viendo directamente a Excel para que la revises con comodidad.
        </div>

        <!-- 1. Inicio de Sesión -->
        <div class="section-title">1. ¿Cómo ingresar al Sistema?</div>
        <p>Para entrar al sistema, ve a la página principal de admisión. Nuestro sistema está conectado directamente con Google para darte la mayor seguridad.</p>
        <ul>
            <li>Solo tienes que hacer clic en el botón blanco que dice <strong>"Iniciar Sesión con Google"</strong>.</li>
            <li>Asegúrate de ingresar usando tu correo institucional (terminado en <strong>@unprg.edu.pe</strong>). Si usas un correo distinto (como un gmail personal), el sistema no te dejará entrar.</li>
        </ul>
        
        <div class="image-container">
            <img src="{{ public_path('img/manual/01_login.png') }}" alt="Pantalla de Iniciar Sesión">
            <div class="caption">Figura 1: Pantalla principal. Haz clic en el botón de Google para entrar.</div>
        </div>

        <!-- 2. Dashboard Preinscripción -->
        <div class="page-break"></div>
        <div class="section-title">2. Pestaña: Preinscripción (Prospectos e Interesados)</div>
        <div class="module">
            <p>La preinscripción agrupa a todas aquellas personas que piden o buscan más información acerca de un determinado programa para iniciar su proceso. Abarca todos los prospectos de nivel <strong>Maestría, Doctorado y Segunda Especialidad</strong>.</p>
            <ul>
                <li><strong>Conteo Rápido:</strong> Arriba verás el número grande de <span class="highlight">Preinscritos</span>. Así sabrás si hay más personas interesadas en Maestrías o en Doctorados.</li>
                <li><strong>Tabla Resumen:</strong> Te muestra exactamente cuántas personas van en cada programa. Es muy importante revisar la columna de <span class="highlight">Vacantes</span>; recuerda que si un programa no llega a 30 inscritos, no se abrirá para este ciclo.</li>
                <li><strong>Gráfico Diario:</strong> Una curva que te muestra qué días se registra más gente.</li>
            </ul>
            
            <div class="image-container">
                <img src="{{ public_path('img/manual/02_preinscripcion.png') }}" alt="Dashboard de Preinscripción">
                <div class="caption">Figura 2: Resumen rápido de cuántas personas han iniciado su trámite.</div>
            </div>
        </div>

        <!-- 3. Dashboard Inscripción -->
        <div class="section-title">3. Pestaña: Inscripción (Control de Pagos y Expedientes)</div>
        <div class="module">
            <p>A esta pestaña pasan los postulantes que <strong>ya pagaron</strong> y enviaron sus documentos. Es la zona donde se confirma definitivamente quiénes van a postular.</p>
            <ul>
                <li><strong>¿Están completos los expedientes?:</strong> La tarjeta central te muestra cómo va el progreso de la validación física en oficina. Sabrás al instante a cuántos postulantes les falta entregar todavía sus papeles y vouchers.</li>
                <li><strong>Control de Dinero:</strong> La tarjeta "Vouchers" suma automáticamente el dinero recaudado de los postulantes.</li>
                <li><strong>Ritmo de Inscripciones:</strong> El pequeño gráfico te muestra la velocidad en la que los candidatos van completando sus trámites día a día.</li>
            </ul>
            
            <div class="image-container">
                <img src="{{ public_path('img/manual/03_inscripcion.png') }}" alt="Dashboard de Inscripción">
                <div class="caption">Figura 3: Panel para verificar que los postulantes estén completando sus requisitos formales.</div>
            </div>
        </div>

        <!-- 3.1. Ver Inscritos -->
        <div class="page-break"></div>
        <div class="section-title">3.1. Submenú: Ver Inscritos (El Detalle de cada Postulante)</div>
        <div class="module">
            <p>Si la Comisión necesita revisar el expediente de alguien en específico (por ejemplo, para resolver un reclamo o hacer una auditoría), esta es la pantalla a la que deben ir:</p>
            <ul>
                <li><strong>Abre cualquier documento:</strong> En la tabla de inscritos, notarás enlaces azules que dicen <span class="highlight">"Abrir"</span>. Con un solo clic puedes ver el Currículum Vitae, la Foto, el DNI y el comprobante de voucher que subió el postulante. Todo aparece en pantalla, sin necesidad de descargarlo.</li>
                <li><strong>Buscador:</strong> Escribe el apellido o nombre de un postulante en la barra superior para encontrarlo en segundos.</li>
                <li><strong>Filtros por Especialidad:</strong> Selecciona un Programa en específico (ej. "Maestría en Sistemas") para listar únicamente a esos alumnos.</li>
            </ul>
            
            <div class="image-container">
                <img src="{{ public_path('img/manual/03_ver_inscritos.png') }}" alt="Panel de Ver Inscritos">
                <div class="caption">Figura 4: El directorio completo de alumnos. Aquí puedes hacer clic en "Abrir" para ver el expediente (CV, voucher y foto) individual.</div>
            </div>
        </div>

        <!-- 4. Evaluaciones -->
        <div class="section-title">4. Pestaña: Evaluación (El Avance de los Docentes)</div>
        <div class="module">
            <p>Esta zona te permite vigilar que los docentes evaluadores estén terminando de calificar los Currículos a tiempo (antes de presentar las actas finales).</p>
            <ul>
                <li><strong>El Progreso de cada Profesor:</strong> Verás el nombre del docente que califica un programa y un número en verde. Por ejemplo: <code>162/163</code> significa que al profesor ya revisó casi todos y solo le falta calificar a 1 postulante.</li>
                <li><strong>Porcentaje Global:</strong> El anillo grande azul central (por ejemplo 99%) te indica si a nivel general toda la universidad ya está terminando de evaluar o si hay demoras críticas.</li>
            </ul>

            <div class="image-container">
                <img src="{{ public_path('img/manual/04_evaluacion.png') }}" alt="Dashboard de Evaluación">
                <div class="caption">Figura 5: Pizarra de vigilancia para asegurar que todos los profesores terminen de entregar notas a tiempo.</div>
            </div>
        </div>

        <!-- 5. Resultados -->
        <div class="page-break"></div>
        <div class="section-title">5. Pestaña: Resultados (Ingresantes Finales)</div>
        <div class="module">
            <p>El punto culminante del proceso (alrededor del 20 de mayo). Este panel suma automáticamente las notas del Curriculum, Examen y Entrevista para dar el veredicto final.</p>
            <ul>
                <li><strong>El Anillo de Resultados:</strong> El círculo de colores te dice claramente cuántas personas lograron convertirse oficialmente en <span class="highlight">Ingresantes</span> y cuántos quedaron fuera, retirados o con devoluciones.</li>
                <li><strong>Estadísticas Adicionales:</strong> Los dos gráficos inferiores te ayudan a ver qué edades tienen los nuevos ingresantes y qué tan altas (o bajas) fueron las notas en promedio.</li>
                <li><strong>Exportación de Actas:</strong> Recuerda usar el botón "Exportar Reportes" para imprimir la lista definitiva y oficial de ingresantes por cada Maestría y Doctorado.</li>
            </ul>

            <div class="image-container">
                <img src="{{ public_path('img/manual/05_resultados.png') }}" alt="Dashboard de Resultados">
                <div class="caption">Figura 6: Pantalla analítica de ingresantes aprobados.</div>
            </div>
        </div>

        <div class="footer">
            Sistema de Admisión 2026 - Escuela de Posgrado UNPRG<br>
            Manual generado el {{ date('d/m/Y') }} 
        </div>
    </div>
</body>
</html>
