<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva de Pago | ESCUELA DE POSGRADO UNPRG</title>
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
            background-color: #f0fdf4;
            padding: 2.5rem 2rem;
            text-align: center;
            color: #166534;
            position: relative;
            border-bottom: 4px solid #22c55e;
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
            color: #166534;
        }

        /* Status Banner */
        .status-banner {
            background-color: #dcfce7;
            color: #166534;
            padding: 1rem 2rem;
            text-align: center;
            font-weight: 600;
            border-bottom: 2px solid #86efac;
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
            color: #166534;
            font-weight: 600;
        }

        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-left: 4px solid #22c55e;
        }

        .info-title {
            font-weight: 700;
            color: #1e3a8a;
            margin: 0 0 0.75rem 0;
            font-size: 1.05rem;
        }

        .info-list {
            margin: 0 0 1.25rem 0;
            padding-left: 1.25rem;
            color: #4b5563;
            font-size: 0.95rem;
        }

        .info-list li {
            margin-bottom: 0.5rem;
        }

        /* Button */
        .btn-container {
            text-align: center;
            margin: 1.5rem 0 0.5rem 0;
        }

        .btn-action {
            display: inline-block;
            background-color: #22c55e;
            color: #ffffff !important;
            text-decoration: none;
            padding: 0.8rem 2rem;
            font-weight: 600;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(34, 197, 94, 0.2);
            transition: background-color 0.2s;
        }

        .btn-action:hover {
            background-color: #166534;
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
            <h1 class="header-title">Comunicado de Admisión</h1>
        </div>

        <!-- Status Banner -->
        <div class="status-banner">
            <span class="status-icon">📥</span>
            <span>Reserva de Pago - Programa No Aperturado</span>
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
                Le informamos que lamentablemente el programa de <span class="highlight-text">{{ $nombre_grado }} en {{ $nombre_programa }}</span> al que se inscribió no alcanzó el número mínimo de postulantes requeridos para su apertura en este proceso de admisión, por lo que no se aperturará en esta edición.
            </p>

            <p style="color: #4b5563; margin-bottom: 1.5rem;">
                Debido a esta situación, queremos comunicarle que <strong>su pago por derecho de inscripción queda automáticamente reservado</strong> para el próximo proceso de admisión de la Escuela de Posgrado, el cual está previsto tentativamente para iniciarse en el mes de <strong>noviembre</strong>.
            </p>

            <!-- Voucher info card -->
            <div class="info-card">
                <h3 class="info-title">Detalles del Pago Reservado</h3>
                <ul class="info-list">
                    <li><strong>Código de Voucher:</strong> {{ $codigo_voucher }}</li>
                    <li><strong>Monto Reservado:</strong> S/. {{ $monto_voucher }}</li>
                    <li><strong>Estado del Pago:</strong> Reservado para el próximo Proceso de Admisión</li>
                </ul>
                
                @if($voucher_url && $voucher_url !== '#')
                    <p style="color: #4b5563; font-size: 0.9rem; margin-bottom: 1rem;">
                        Como constancia de su trámite y para su total tranquilidad, puede visualizar la copia digitalizada de su voucher haciendo clic en el siguiente enlace:
                    </p>
                    <div class="btn-container">
                        <a href="{{ $voucher_url }}" target="_blank" rel="noopener noreferrer" class="btn-action">
                            Ver mi Voucher en Google Drive
                        </a>
                    </div>
                @endif
            </div>

            <p style="color: #4b5563; margin-top: 1.5rem;">
                El área de admisión se mantendrá en comunicación oportuna con usted para guiarle y facilitarle todo el proceso de revalidación de su inscripción cuando se aperture la convocatoria de noviembre.
            </p>

            <p style="color: #4b5563; margin-top: 1rem; margin-bottom: 0;">
                Si tiene alguna consulta adicional o prefiere solicitar información complementaria, puede responder directamente a este correo electrónico o comunicarse con nosotros a través de los canales oficiales detallados abajo.
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
