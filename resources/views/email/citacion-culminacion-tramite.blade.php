<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citación Obligatoria: Culminación de Trámite Documentario | ESCUELA DE POSGRADO UNPRG</title>
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
            background-color: #eff6ff;
            color: #1d4ed8;
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
        }

        .alert-desc {
            margin: 0;
            color: #78350f;
            font-size: 0.98rem;
        }

        /* Details Table */
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
            <h1 class="header-title">Citación Oficial</h1>
        </div>

        <!-- Status Banner -->
        <div class="status-banner">
            <span class="status-icon">📅</span>
            <span>Convocatoria Presencial Obligatoria</span>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                {{ $sexo == 'M' ? 'Estimado' : 'Estimada' }},
                <strong>{{ $nombres }} {{ $ap_paterno }} {{ $ap_materno }}</strong>
            </div>

            <p style="color: #4b5563; margin-bottom: 1.5rem;">
                Le saludamos cordialmente de parte de la Comisión de Admisión de la Escuela de Posgrado de la Universidad Nacional Pedro Ruiz Gallo.
            </p>

            <p style="color: #4b5563; margin-bottom: 1.5rem;">
                Le informamos que, para proceder con la culminación satisfactoria de su postulación e incorporación en el presente proceso de selección, usted debe presentarse a la citación obligatoria presencial para el siguiente programa académico:
            </p>

            <!-- Details Box -->
            <table class="details-table" role="presentation">
                <tr>
                    <td class="label">Programa</td>
                    <td class="value"><strong>{{ $nombre_grado }} en {{ $nombre_programa }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Estado de Evaluación</td>
                    <td class="value">
                        <span style="background-color: #dbeafe; color: #1e40af; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">
                            PENDIENTE DE EVALUACIÓN
                        </span>
                    </td>
                </tr>
            </table>

            <!-- Warning Card (Citación) -->
            <div class="alert-card">
                <h3 class="alert-title">📢 DETALLES DE LA CONVOCATORIA PRESENCIAL</h3>
                <div class="alert-desc">
                    <p style="margin: 0 0 0.8rem 0; font-size: 1.05rem;">
                        Se le comunica que debe acercarse de manera <strong>obligatoria</strong> el:
                    </p>
                    <p style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #92400e; text-align: center;">
                        📅 SÁBADO 04 DE JULIO A LAS 08:30 AM
                    </p>
                    <p style="margin: 0.8rem 0 0 0; text-align: center;">
                        📍 En las instalaciones de la <strong>Escuela de Posgrado (Aula 02)</strong>
                    </p>
                    <p style="margin: 0.5rem 0 0 0; font-size: 0.95rem; text-align: center; font-style: italic;">
                        Para la culminación del trámite documentario de Admisión 2026-I.
                    </p>
                </div>
            </div>

            @if(isset($val_fisico) && $val_fisico == 0)
            <!-- Warning Card (CV Pendiente) -->
            <div style="background-color: #fffbeb; border: 1px solid #fca5a5; border-radius: 10px; padding: 1.5rem; margin: 1.5rem 0; border-left: 4px solid #ef4444;">
                <h4 style="margin: 0 0 0.5rem 0; color: #b91c1c; font-weight: 700; font-size: 1.05rem;">⚠️ RECORDATORIO IMPORTANTE: Expediente Pendiente</h4>
                <p style="margin: 0 0 0.6rem 0; color: #7f1d1d; font-size: 0.95rem;">
                    Detectamos que usted aún tiene pendiente la entrega física de su currículum vitae documentado y expediente. Le recordamos que tiene plazo máximo e improrrogable para entregarlo en la oficina de admisión a más tardar este <strong>Jueves 02 de Julio</strong>.
                </p>
                <p style="margin: 0; color: #991b1b; font-size: 0.88rem; font-style: italic;">
                    *Nota: Si no le es posible acercarse de manera presencial a dejar su expediente, este puede ser entregado por un familiar o tercero autorizado, siempre y cuando la documentación se encuentre debidamente firmada.
                </p>
            </div>
            @endif

            <!-- Requirements Box -->
            <div class="steps-container" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1.5rem; margin: 1.5rem 0;">
                <h3 class="steps-title" style="margin-top: 0; color: #1e3a8a; font-size: 1.1rem; font-weight: 700;">📋 Elementos Obligatorios a Llevar:</h3>
                <ul class="checklist" style="padding-left: 1.25rem; color: #4b5563; margin-bottom: 0; font-size: 0.95rem;">
                    <li style="margin-bottom: 0.5rem;">Documento Nacional de Identidad (DNI) original y vigente.</li>
                    <li style="margin-bottom: 0;">Carnet de Postulante.</li>
                </ul>
            </div>

            <p style="color: #4b5563; margin-top: 1.5rem;">
                Agradecemos su puntual asistencia y compromiso para dar término al proceso regular de su postulación.
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
