<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Manual del Docente Evaluador - Comisión de Admisión 2026-I</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .page {
            padding: 40px;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #1e3a8a;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .title {
            color: #1e40af;
            font-size: 28px;
            font-weight: bold;
            margin: 0;
        }

        .subtitle {
            color: #475569;
            font-size: 16px;
            margin-top: 5px;
        }

        .salutation {
            background-color: #f8fafc;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
        }

        .section-title {
            color: #1e3a8a;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 5px;
            margin-top: 30px;
            font-size: 20px;
            font-weight: bold;
        }

        .module {
            margin-bottom: 20px;
        }

        .image-placeholder {
            text-align: center;
            margin: 20px 0;
            background-color: #f1f5f9;
            border: 1px dashed #cbd5e1;
            padding: 40px 10px;
            border-radius: 8px;
            color: #64748b;
            font-style: italic;
        }

        .image-container {
            text-align: center;
            margin: 20px 0;
        }

        .image-container img {
            max-width: 100%;
            height: auto;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        ul {
            padding-left: 20px;
        }

        li {
            margin-bottom: 8px;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            margin-top: 40px;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }

        .page-break {
            page-break-after: always;
        }

        .notice {
            font-size: 14px;
            padding: 12px;
            border-radius: 5px;
            color: #0369a1;
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            margin-bottom: 20px;
        }

        .highlight {
            font-weight: bold;
            color: #1e3a8a;
        }
    </style>
</head>

<body>
    <div class="page">
        <!-- Portada / Cabecera -->
        <div class="header">
            <h1 class="title">Sistema de Admisión 2026-I</h1>
            <h2 class="subtitle">Escuela de Posgrado - UNPRG</h2>
            <br>
            <p style="text-transform: uppercase; color: #475569;"><strong>Guía Práctica para Docentes Evaluadores de
                    CV</strong></p>
        </div>

        <div class="salutation">
            <strong>Estimado(a) Docente:</strong><br>
            Le damos la bienvenida al módulo de evaluación. Esta guía rápida le ayudará a entender cómo acceder al
            sistema, visualizar los postulantes asignados a su cargo y registrar las calificaciones correspondientes a
            la evaluación de expedientes (Curriculum Vitae).
        </div>

        <!-- 1. Inicio de Sesión -->
        <div class="section-title">1. ¿Cómo ingresar al Sistema?</div>
        <p>Para entrar al sistema de evaluación, debe acceder a la URL oficial proporcionada por la Escuela de Posgrado.
        </p>
        <ul>
            <li>Ingrese a la ruta correspondiente a docentes: <span
                    class="highlight">/admision-epg/iniciar-sesion</span>.</li>
            <li>En el formulario, ingrese el <strong>Correo Electrónico Institucional</strong> que le fue asignado para
                la evaluación.</li>
            <li>Ingrese la <strong>Contraseña</strong> proporcionada por coordinación.</li>
            <li>Haga clic en el botón azul <strong>"Ingresar"</strong>.</li>
        </ul>
        <div class="image-container">
            <img src="{{ public_path('img/manual/06_login_docente.png') }}" alt="Login Docente">
        </div>

        <!-- 2. Dashboard -->
        <div class="section-title">2. Panel Principal (Dashboard del Evaluador)</div>
        <p>Una vez dentro, el sistema le redirigirá automáticamente a la vista de "Evaluación de Expediente". El panel
            está diseñado para facilitar su trabajo:</p>
        <ul>
            <li><strong>Panel Lateral (Programas):</strong> A la izquierda, verá la lista de todas las maestrías o
                doctorados que le han sido asignados. La vista es adaptativa y mostrará los programas de forma comprimida, ajustándose dinámicamente al tamaño de su pantalla (hasta 8 programas por página en monitores grandes).</li>
            <li><strong>Métricas de Avance:</strong> En la parte superior derecha, podrá ver rápidamente tarjetas de resumen con la cantidad de postulantes que ya han sido evaluados y los que faltan por calificar.</li>
            <li><strong>Lista de Postulantes:</strong> En el centro, una tabla mostrará a todos los postulantes del
                programa seleccionado, ordenados alfabéticamente.</li>
        </ul>
        <div class="image-container">
            <img src="{{ public_path('img/manual/07_dashboard_docente_cv.png') }}" alt="Dashboard Docente">
        </div>

        <div class="page-break"></div>

        <!-- 3. Ingreso de Notas -->
        <div class="section-title">3. Registro de Evaluaciones de CV</div>
        <div class="module">
            <p>Para asentar una nota a un postulante, tiene dos opciones dentro de la tabla principal:</p>

            <h4>Opción A: Ingreso Directo</h4>
            <p>Si ya calculó la nota previamente:</p>
            <ul>
                <li>Ubique la fila del postulante y vaya a la columna <strong>"NOTA CV"</strong>.</li>
                <li>Haga clic en la casilla de texto e ingrese el valor numérico (de 0 a 20 puntos).</li>
                <li>Haga clic en el botón azul con el ícono de <strong>Guardar (Disquete)</strong> en la columna de
                    Acciones.</li>
            </ul>

            <h4>Opción B: Uso de la Calculadora Automática</h4>
            <p>El sistema cuenta con una herramienta para facilitar la suma de los rubros requeridos en el prospecto:
            </p>
            <ul>
                <li>Haga clic en el ícono de <strong>Calculadora</strong> ubicado al costado de la casilla de nota del
                    postulante.</li>
                <li>Se abrirá una ventana emergente. Rellene los puntajes correspondientes a cada rubro (Grados,
                    Experiencia, Publicaciones, etc.).</li>
                <li>El sistema sumará automáticamente el puntaje total en la parte inferior.</li>
                <li>Haga clic en <strong>"Aplicar y Guardar"</strong>. La nota se registrará en la base de datos
                    automáticamente.</li>
            </ul>
            <div class="image-container">
                <img src="{{ public_path('img/manual/08_calculadora.png') }}" alt="Calculadora de CV">
            </div>

            <div class="notice">
                <strong>💡 Tip de Seguridad (Autoguardado):</strong> Cada vez que hace clic en el botón Guardar, el
                sistema registra la fecha y hora exacta de la modificación. Si comete un error, simplemente cambie el
                valor y vuelva a guardar; el sistema mantendrá la nota más reciente.
            </div>
        </div>

        <!-- 4. Reportes -->
        <div class="section-title">4. Generación y Descarga de Reportes</div>
        <p>Al finalizar de calificar a todos los postulantes de un programa, debe generar el acta de evaluación
            correspondiente:</p>
        <ul>
            <li>En la esquina superior derecha de la tabla de postulantes, encontrará el botón <strong>"Reporte
                    General"</strong> o el ícono de descarga.</li>
            <li>Al hacer clic, el sistema descargará de inmediato un archivo PDF consolidado con todas las
                calificaciones que usted ha ingresado para ese programa en específico.</li>
            <li>Dicho reporte debe ser remitido a la coordinación general como evidencia del proceso concluido.</li>
        </ul>
        <div class="image-container">
            <img src="{{ public_path('img/manual/09_nota_guardada.png') }}" alt="Nota Guardada">
        </div>

        <div class="footer">
            Generado automáticamente por el Sistema de Admisión EPG-UNPRG<br>
            Universidad Nacional Pedro Ruiz Gallo &copy; {{ date('Y') }}
        </div>
    </div>
</body>

</html>