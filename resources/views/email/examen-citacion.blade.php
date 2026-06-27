<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Language" content="es">
    <title>Citación al Examen de Admisión | ESCUELA DE POSGRADO UNPRG</title>
    <!-- Import Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reset & Base Styles */
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            color: #333333;
        }

        /* Container */
        .email-wrapper {
            max-width: 650px;
            margin: 2rem auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #eaeaea;
        }

        /* Header */
        .email-header {
            background-color: #f0f7ff;
            padding: 2.5rem 2rem;
            text-align: center;
            color: #1e3a8a;
            position: relative;
            border-bottom: 4px solid #2873B4;
        }

        .header-logos {
            width: 100%;
            margin-bottom: 1.5rem;
        }

        .header-logos table {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-img {
            height: 90px;
            width: auto;
        }

        .header-title {
            font-size: 1.8rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.5px;
            color: #003366;
        }

        /* Status Banner */
        .status-banner {
            background-color: #dbeafe;
            color: #1e40af;
            padding: 1rem 2rem;
            text-align: center;
            font-weight: 600;
            border-bottom: 2px solid #bfdbfe;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .status-icon {
            font-size: 1.5rem;
        }

        /* Body Content */
        .email-body {
            padding: 2.5rem;
        }

        .greeting {
            font-size: 1.25rem;
            color: #111827;
            margin-bottom: 1.5rem;
        }

        .highlight-text {
            color: #2873B4;
            font-weight: 600;
        }

        /* Schedule and classroom section */
        .info-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.25rem;
            margin: 1.5rem 0;
            border-left: 4px solid #2873B4;
        }

        .info-title {
            font-weight: 700;
            color: #003366;
            margin: 0 0 0.75rem 0;
            font-size: 1.1rem;
            text-transform: uppercase;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 6px 0;
            font-size: 0.95rem;
        }

        .info-label {
            font-weight: bold;
            color: #4b5563;
            width: 35%;
        }

        .info-value {
            color: #111827;
        }

        /* Requirements section */
        .req-box {
            background-color: #fbfdfb;
            border: 1px solid #dcfce7;
            border-radius: 10px;
            padding: 1.25rem;
            margin: 1.5rem 0;
            border-left: 4px solid #10b981;
        }

        .req-title {
            font-weight: 700;
            color: #065f46;
            margin: 0 0 0.75rem 0;
            font-size: 1.1rem;
            text-transform: uppercase;
        }

        .req-list {
            margin: 0;
            padding-left: 1.25rem;
            color: #1e293b;
        }

        .req-list li {
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        /* Footer */
        .email-footer {
            background-color: #2873B4;
            color: #ffffff;
            padding: 0;
            text-align: center;
            font-size: 0.85rem;
        }

        .footer-content {
            padding: 2rem;
        }

        .contact-list {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        @media only screen and (max-width: 600px) {
            .email-wrapper {
                margin: 0;
                border-radius: 0;
                width: 100% !important;
            }

            .email-body {
                padding: 1.5rem;
            }

            .contact-list {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="email-header">
            <div class="header-logos">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td width="50%" align="left" style="padding-right: 10px;">
                            <img src="https://drive.usercontent.google.com/download?id=1XdEM7PcBXuRfkkdsBp3MqlDRz4n-GsJf&export=view"
                                alt="EPG Logo" class="logo-img" style="height: 90px; width: auto; display: block;">
                        </td>
                        <td width="50%" align="right" style="padding-left: 10px;">
                            <img src="https://drive.usercontent.google.com/download?id=1ph6WsmccjVnNwkK70-ntCVKKdHGJucS5&export=view"
                                alt="UNPRG Logo" class="logo-img" style="height: 90px; width: auto; display: block;">
                        </td>
                    </tr>
                </table>
            </div>
            <h1 class="header-title">Examen de Admisión 2026-I</h1>
        </div>

        <!-- Status Banner -->
        <div class="status-banner">
            <span class="status-icon">📅</span>
            <span>Citación Oficial al Examen e Instrucciones</span>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                {{ $sexo == 'M' ? 'Estimado' : 'Estimada' }},
                <strong>{{ $nombres }} {{ $ap_paterno }} {{ $ap_materno }}</strong>
            </div>

            <p style="color: #4b5563; margin-bottom: 1.5rem;">
                Le saludamos cordialmente de la Comisión de Admisión de la Escuela de Posgrado de la Universidad Nacional Pedro Ruiz Gallo. Nos complace informarle que todo se encuentra listo para el examen de admisión presencial correspondiente a su programa: <span class="highlight-text">{{ $nombre_grado }} en {{ $nombre_programa }}</span>.
            </p>

            <div class="info-box">
                <h3 class="info-title">Detalles de la Citación:</h3>
                <table class="info-table">
                    <tr>
                        <td class="info-label">Fecha del Examen:</td>
                        <td class="info-value"><strong>Domingo, 28 de junio de 2026</strong></td>
                    </tr>
                    <tr>
                        <td class="info-label">Apertura de Puertas:</td>
                        <td class="info-value">Desde las <strong>07:00 AM</strong></td>
                    </tr>
                    <tr>
                        <td class="info-label">Horario del Examen:</td>
                        <td class="info-value">De <strong>09:00 AM a 10:00 AM</strong></td>
                    </tr>
                    <tr>
                        <td class="info-label">Entrevista Personal:</td>
                        <td class="info-value">Iniciará inmediatamente después de finalizar el examen de admisión.</td>
                    </tr>
                    <tr>
                        <td class="info-label">Puerta de Ingreso:</td>
                        <td class="info-value">Puerta principal de la <strong>Escuela de Posgrado de la UNPRG</strong> (Av. Huamachuco Nro. 1130).</td>
                    </tr>
                </table>
            </div>

            <div class="req-box">
                <h3 class="req-title">Requisitos Obligatorios para Ingresar:</h3>
                <ul class="req-list">
                    <li>Llevar su Documento Nacional de Identidad (<strong>DNI</strong>).</li>
                    <li>Llevar su <strong>Carnet de postulante</strong>.</li>
                    <li>Llevar su <strong>Perfil de proyecto tentativo de tesis</strong> (en folder manila).</li>
                    <li>Llevar únicamente útiles de escritorio: <strong>lápiz, lapicero, borrador y tajador</strong>.</li>
                </ul>
            </div>

            <p style="color: #4b5563; margin-top: 1.5rem; margin-bottom: 0;">
                Le recomendamos asistir puntualmente respetando la hora de ingreso para evitar inconvenientes en los accesos. Le deseamos el mayor de los éxitos en esta evaluación.
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <img src="https://drive.google.com/uc?export=view&id=1bXqmf32tJmjzpoG90fJJwkGN9N2_fKyQ" width="100%"
                height="6px" alt="Barra Colores" style="display: block; width: 100%;">

            <div class="footer-content">
                <p style="margin: 0 0 1rem 0; font-weight: 600; color: #ffffff;">Comisión de Admisión - Escuela de Posgrado UNPRG</p>

                <ul class="contact-list">
                    <li class="contact-item">
                        <span>📍 Av. Huamachuco Nro. 1130, Lambayeque</span>
                    </li>
                    <li class="contact-item">
                        <span>📩 <a href="mailto:admision_epg@unprg.edu.pe"
                                style="color: #ffffff; text-decoration: none;">admision_epg@unprg.edu.pe</a></span>
                    </li>
                    <li class="contact-item">
                        <span>📱 995901454 / 924 545 013</span>
                    </li>
                </ul>

                <p style="margin-top: 1.5rem; opacity: 0.8; font-size: 0.75rem;">
                    © 2026 Escuela de Posgrado de la Universidad Nacional Pedro Ruiz Gallo. Todos los derechos reservados.
                </p>
            </div>
        </div>
    </div>
</body>

</html>
