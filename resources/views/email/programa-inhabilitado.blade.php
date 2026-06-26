<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunicado Importante | ESCUELA DE POSGRADO UNPRG</title>
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
            background-color: #fef2f2;
            padding: 2.5rem 2rem;
            text-align: center;
            color: #991b1b;
            position: relative;
            border-bottom: 4px solid #ef4444;
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
            color: #991b1b;
        }

        /* Status Banner */
        .status-banner {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 1rem 2rem;
            text-align: center;
            font-weight: 600;
            border-bottom: 2px solid #fca5a5;
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
            color: #ef4444;
            font-weight: 600;
        }

        .option-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.25rem;
            margin: 1rem 0;
            border-left: 4px solid #3b82f6;
        }

        .option-title {
            font-weight: 700;
            color: #1e3a8a;
            margin: 0 0 0.5rem 0;
            font-size: 1.05rem;
        }

        .option-desc {
            margin: 0;
            color: #4b5563;
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
            <h1 class="header-title">Comunicado Oficial</h1>
        </div>

        <!-- Status Banner -->
        <div class="status-banner">
            <span class="status-icon">⚠️</span>
            <span>Aviso sobre la apertura de su programa académico</span>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                {{ $sexo == 'M' ? 'Estimado' : 'Estimada' }},
                <strong>{{ $nombres }} {{ $ap_paterno }} {{ $ap_materno }}</strong>
            </div>

            <p style="color: #4b5563; margin-bottom: 1.5rem;">
                Nos comunicamos con usted de la Dirección de la Escuela de Posgrado de la UNPRG.
            </p>

            <p style="color: #4b5563; margin-bottom: 1.5rem;">
                Lamentamos informarle que en este proceso de admisión, debido al <strong>bajo número de inscritos</strong>, el programa de 
                <span class="highlight-text">{{ $nombre_grado }} en {{ $nombre_programa }}</span> no se aperturará en este proceso.
            </p>

            <p style="color: #1f2937; font-weight: 600; margin-bottom: 1rem;">
                Frente a esta situación, usted cuenta con las siguientes dos (2) opciones:
            </p>

            <!-- Option 1 -->
            <div class="option-card" style="border-left-color: #3b82f6;">
                <h3 class="option-title">Opción 1: Cambio de programa</h3>
                <p class="option-desc">
                    Trasladarse a otro programa de posgrado que sí se aperture en este proceso de admisión (las opciones de programas disponibles se le detallarán posteriormente).
                </p>
            </div>

            <!-- Option 2 -->
            <div class="option-card" style="border-left-color: #10b981;">
                <h3 class="option-title">Opción 2: Reserva de pago</h3>
                <p class="option-desc">
                    Reservar el pago de su derecho de inscripción para el siguiente proceso de admisión de la Escuela de Posgrado (el cual está previsto para el mes de octubre).
                </p>
            </div>


            <p style="color: #4b5563; margin-top: 1.5rem;">
                Nuestro equipo de admisión se comunicará con usted telefónicamente en las próximas horas para confirmar su decisión, así como para indicarle qué programas afines sí se aperturarán en caso de que opte por el cambio de programa.
            </p>

            <p style="color: #4b5563; margin-top: 1rem; margin-bottom: 0;">
                Si tiene alguna consulta adicional, puede responder directamente a este correo o escribirnos a través de los canales de atención oficiales detallados abajo.
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
