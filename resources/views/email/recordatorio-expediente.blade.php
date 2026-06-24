<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Urgente: Entrega de Expediente Físico | ESCUELA DE POSGRADO UNPRG</title>
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
            color: #1e3a8a;
        }

        /* Status Banner */
        .status-banner {
            background-color: #fffbeb;
            color: #b45309;
            padding: 1rem 2rem;
            text-align: center;
            font-weight: 600;
            border-bottom: 2px solid #fde68a;
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
            color: #b45309;
            font-weight: 700;
        }

        .alert-card {
            background-color: #fffbeb;
            border: 1px solid #fef3c7;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-left: 4px solid #d97706;
        }

        .alert-title {
            font-weight: 700;
            color: #92400e;
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .alert-desc {
            margin: 0;
            color: #78350f;
            font-size: 0.98rem;
        }

        /* Buttons or Details Table */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            background-color: #f8fafc;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .details-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.95rem;
        }

        .details-table tr:last-child td {
            border-bottom: none;
        }

        .label {
            font-weight: 600;
            color: #4b5563;
            width: 35%;
        }

        .value {
            color: #1f2937;
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
            <h1 class="header-title">Recordatorio Urgente</h1>
        </div>

        <!-- Status Banner -->
        <div class="status-banner">
            <span class="status-icon">⚠️</span>
            <span>Presentación de Expediente Físico Pendiente</span>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                {{ $sexo == 'M' ? 'Estimado' : 'Estimada' }},
                <strong>{{ $nombres }} {{ $ap_paterno }} {{ $ap_materno }}</strong>
            </div>

            <p style="color: #4b5563; margin-bottom: 1.5rem;">
                Le saludamos cordialmente de parte de la Comisión de Admisión de la Escuela de Posgrado de la UNPRG.
            </p>

            <p style="color: #4b5563; margin-bottom: 1.5rem;">
                Le recordamos que, para culminar su postulación en el presente proceso de admisión y rendir el examen de admisión, aún tiene pendiente realizar la <strong>entrega física de su expediente (Validación Física)</strong> para el siguiente programa académico:
            </p>

            <!-- Details Box -->
            <table class="details-table" role="presentation">
                <tr>
                    <td class="label">Programa</td>
                    <td class="value"><strong>{{ $nombre_grado }} en {{ $nombre_programa }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Estado de Validación</td>
                    <td class="value"><span style="background-color: #fed7aa; color: #9a3412; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">PENDIENTE DE EXPEDIENTE</span></td>
                </tr>
            </table>

            <!-- Document Checklist -->
            <div class="steps-container" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.5rem; margin: 1.5rem 0;">
                <h3 class="steps-title" style="margin-top: 0; color: #1e3a8a; font-size: 1.1rem; font-weight: 700;">📋 Documentos a Presentar en Físico:</h3>
                <ul class="checklist" style="padding-left: 1.25rem; color: #4b5563; margin-bottom: 0; font-size: 0.95rem;">
                    <li style="margin-bottom: 0.5rem;"><strong>Constancia de Inscripción</strong> (la puede descargar ingresando al portal del postulante).</li>
                    <li style="margin-bottom: 0.5rem;"><strong>Comprobante de Pago Original</strong> (Banco de la Nación o Págalo.pe).</li>
                    <li style="margin-bottom: 0.5rem;"><strong>Solicitud dirigida al {{ $autoridad ?? 'Director' }}</strong> (Descargar del enlace de formatos de abajo).</li>
                    <li style="margin-bottom: 0.5rem;">Copia simple de DNI o Carnet de Extranjería.</li>
                    <li style="margin-bottom: 0.5rem;">Una (1) fotografía a color tamaño carné.</li>
                    <li style="margin-bottom: 0.5rem;">Copia simple del <strong>{{ $gradoRequerido }}</strong>.</li>
                    <li style="margin-bottom: 0.5rem;">Impresión del Registro en SUNEDU.</li>
                    <li style="margin-bottom: 0;">
                        <strong>Currículum Vitae Documentado</strong>
                        <br>
                        <span style="font-size: 0.9rem; color: #64748b;">
                            (Anillado con tapa transparente al inicio y tapa trasera color
                            <strong style="color: {{ $inscripcion->programa->facultad_id == 4 && !in_array($inscripcion->programa->grado_id, [1, 2]) ? '#0ea5e9' : '#22c55e' }}">
                                {{ $inscripcion->programa->facultad_id == 4 && !in_array($inscripcion->programa->grado_id, [1, 2]) ? 'TURQUESA' : 'VERDE' }}
                            </strong>. Foliado en la parte superior derecha).
                        </span>
                    </li>
                </ul>
            </div>

            <!-- Warning Card -->
            <div class="alert-card">
                <h3 class="alert-title">📢 FECHA LÍMITE IMPRORROGABLE</h3>
                <p class="alert-desc">
                    La fecha máxima para la recepción física de expedientes es este <span class="highlight-text">viernes 26 de junio de 2026</span>.
                    <br>Horario de atención: <strong>Lunes a Viernes de 08:00 a.m. a 02:00 p.m.</strong>
                </p>
            </div>

            <!-- Download Formats CTA -->
            <div class="cta-section" style="background-color: #2873B4; border-radius: 10px; padding: 1.5rem; text-align: center; color: #ffffff; margin: 1.5rem 0;">
                <h3 style="margin-top: 0; color: #fbbf24; font-size: 1.2rem; font-weight: 700;">📂 Descarga los Formatos</h3>
                <p style="opacity: 0.9; margin-bottom: 1.2rem; font-size: 0.95rem;">Accede a los formatos de solicitud y declaraciones juradas aquí:</p>
                <a href="{{ $urlDocumentos }}" target="_blank" class="download-btn" style="background-color: #fbbf24; color: #1e3a8a; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 700; display: inline-block;">
                    DESCARGAR FORMATOS
                </a>
            </div>

            <p style="color: #4b5563; margin-bottom: 1.5rem;">
                Le instamos a tomar todas las precauciones del caso y acercarse a la brevedad posible a regularizar su expediente físico en las oficinas de admisión de la Escuela de Posgrado.
            </p>

            <p style="color: #4b5563; margin-bottom: 0;">
                Si ya realizó la entrega de su expediente en las últimas horas, por favor omita este mensaje.
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
