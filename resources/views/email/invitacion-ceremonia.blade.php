<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitación Oficial - Ceremonia de Reconocimiento | ESCUELA DE POSGRADO UNPRG</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            color: #333333;
        }

        .email-wrapper {
            max-width: 650px;
            margin: 2rem auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
            border: 1px solid #e5e7eb;
        }

        .email-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%);
            padding: 2.5rem 2rem;
            text-align: center;
            color: #ffffff;
            position: relative;
            border-bottom: 5px solid #d97706; /* Elegant gold border */
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
            height: 80px;
            width: auto;
        }

        .header-subtitle {
            text-transform: uppercase;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 2px;
            color: #fbbf24; /* Gold accent */
            margin-bottom: 0.5rem;
        }

        .header-title {
            font-size: 1.75rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .honor-badge-container {
            text-align: center;
            margin-top: -20px;
            margin-bottom: 20px;
            position: relative;
            z-index: 10;
        }

        .honor-badge {
            display: inline-block;
            background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
            color: #1e3a8a;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 1rem;
            letter-spacing: 1px;
            padding: 10px 24px;
            border-radius: 9999px;
            box-shadow: 0 4px 15px rgba(217, 119, 6, 0.35);
            border: 2px solid #ffffff;
        }

        .email-body {
            padding: 2.5rem;
        }

        .greeting {
            font-size: 1.3rem;
            color: #111827;
            margin-bottom: 1.5rem;
            font-weight: 700;
        }

        .highlight-text {
            color: #1e3a8a;
            font-weight: 700;
        }

        .program-name {
            color: #d97706;
            font-weight: 700;
        }

        .invitation-text {
            font-size: 1.05rem;
            color: #4b5563;
            margin-bottom: 2rem;
            text-align: justify;
        }

        .event-details-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 2rem;
            margin: 2rem 0;
            border-left: 5px solid #1e3a8a;
        }

        .event-details-card h3 {
            margin-top: 0;
            color: #1e3a8a;
            font-size: 1.2rem;
            font-weight: 700;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-row {
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            font-size: 1rem;
        }

        .detail-row:last-child {
            margin-bottom: 0;
        }

        .detail-icon {
            font-size: 1.25rem;
            margin-right: 12px;
            width: 24px;
            text-align: center;
        }

        .detail-content {
            flex: 1;
        }

        .detail-label {
            font-weight: 700;
            color: #1f2937;
            display: block;
            margin-bottom: 0.15rem;
        }

        .detail-value {
            color: #4b5563;
        }

        .badge-time {
            display: inline-block;
            background-color: #dbeafe;
            color: #1e40af;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .info-box {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 10px;
            padding: 1.25rem;
            margin: 2rem 0;
            border-left: 5px solid #f59e0b;
            font-size: 0.95rem;
            color: #78350f;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .info-box-icon {
            font-size: 1.4rem;
            line-height: 1;
        }

        .info-box-text {
            flex: 1;
            margin: 0;
            line-height: 1.5;
        }

        .email-footer {
            background-color: #2873B4;
            color: #ffffff;
            padding: 0;
            text-align: center;
            font-size: 0.85rem;
        }

        .footer-content {
            padding: 2.5rem 2rem;
        }

        .contact-list {
            list-style: none;
            padding: 0;
            margin: 1.5rem 0;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 24px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .contact-link {
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
        }

        .contact-link:hover {
            text-decoration: underline;
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

            .event-details-card {
                padding: 1.25rem;
            }

            .contact-list {
                flex-direction: column;
                gap: 12px;
                align-items: center;
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
                                 alt="EPG Logo" class="logo-img" style="height: 80px; width: auto; display: block;">
                        </td>
                        <td width="50%" align="right" style="padding-left: 10px;">
                            <img src="https://drive.usercontent.google.com/download?id=1ph6WsmccjVnNwkK70-ntCVKKdHGJucS5&export=view"
                                 alt="UNPRG Logo" class="logo-img" style="height: 80px; width: auto; display: block;">
                        </td>
                    </tr>
                </table>
            </div>
            <div class="header-subtitle">Reconocimiento a la Excelencia Académica</div>
            <h1 class="header-title">Invitación de Honor</h1>
        </div>

        <!-- Honor Badge (Merito) -->
        <div class="honor-badge-container">
            <div class="honor-badge">
                🏆 {{ (int)$merito === 1 ? 'Primer Puesto' : 'Segundo Puesto' }}
            </div>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                {{ $sexo == 'M' ? 'Estimado' : 'Estimada' }}
                <span class="highlight-text">{{ $nombres }} {{ $ap_paterno }} {{ $ap_materno }}</span>,
            </div>

            <p class="invitation-text">
                Reciba un cordial y afectuoso saludo en representación de las autoridades de la 
                <strong>Escuela de Posgrado de la Universidad Nacional Pedro Ruiz Gallo (UNPRG)</strong>.
            </p>

            <p class="invitation-text">
                Nos complace enormemente felicitarle por haber obtenido el 
                <strong>{{ (int)$merito === 1 ? 'Primer Puesto' : 'Segundo Puesto' }}</strong> por estricto orden de mérito en el reciente proceso de admisión para el programa de 
                <span class="program-name">{{ $nombre_grado }} en {{ $nombre_programa }}</span>. Este logro es un reflejo de su dedicación, talento y compromiso con su formación profesional de alto nivel.
            </p>

            <p class="invitation-text">
                En mérito a este destacado desempeño, tenemos el honor de <strong>invitarle de manera especial a la Ceremonia de Reconocimiento de Primeros y Segundos Puestos</strong>, la cual contará con la participación de nuestras principales autoridades académicas.
            </p>

            <!-- Event Details Card -->
            <div class="event-details-card">
                <h3>📅 Detalles de la Ceremonia</h3>
                
                <div class="detail-row">
                    <span class="detail-icon">🗓️</span>
                    <div class="detail-content">
                        <span class="detail-label">Fecha</span>
                        <span class="detail-value" style="font-weight: 600; color: #1e3a8a;">{{ $fecha_ceremonia }}</span>
                    </div>
                </div>

                <div class="detail-row">
                    <span class="detail-icon">⏰</span>
                    <div class="detail-content">
                        <span class="detail-label">Hora</span>
                        <span class="detail-value"><span class="badge-time">{{ $hora_ceremonia }}</span> (Hora exacta)</span>
                    </div>
                </div>

                <div class="detail-row">
                    <span class="detail-icon">📍</span>
                    <div class="detail-content">
                        <span class="detail-label">Lugar</span>
                        <span class="detail-value" style="font-weight: 600; color: #111827;">{{ $lugar_ceremonia }}</span>
                    </div>
                </div>
            </div>

            <!-- Important Note Box -->
            <div class="info-box">
                <span class="info-box-icon">🏫</span>
                <p class="info-box-text">
                    <strong>Información de Clases:</strong> Queremos informarle que, una vez finalizada la ceremonia protocolar, usted podrá incorporarse y retornar con normalidad a sus respectivas aulas para dar inicio a sus clases presenciales del periodo académico.
                </p>
            </div>

            <p class="invitation-text" style="margin-top: 2rem;">
                Agradecemos de antemano su gentil asistencia y puntualidad, las cuales darán mayor realce a este importante acto solemne que premia su excelencia académica.
            </p>

            <p style="color: #4b5563; margin-top: 2.5rem; line-height: 1.4;">
                Atentamente,<br>
                <strong style="color: #1e3a8a;">Dirección de la Escuela de Posgrado</strong><br>
                Universidad Nacional Pedro Ruiz Gallo
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <img src="https://drive.google.com/uc?export=view&id=1bXqmf32tJmjzpoG90fJJwkGN9N2_fKyQ" width="100%"
                 height="6px" alt="Barra Colores" style="display: block; width: 100%;">

            <div class="footer-content">
                <p style="margin: 0 0 1rem 0; font-weight: 600; color: #ffffff;">Escuela de Posgrado - UNPRG</p>

                <ul class="contact-list">
                    <li class="contact-item">
                        <span>📍 Av. Huamachuco Nro. 1130, Lambayeque</span>
                    </li>
                    <li class="contact-item">
                        <span>📩 <a href="mailto:admision_epg@unprg.edu.pe" class="contact-link">admision_epg@unprg.edu.pe</a></span>
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
