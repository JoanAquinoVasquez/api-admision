<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Language" content="es">
    <title>Reprogramación de Examen de Admisión - Escuela de Posgrado UNPRG</title>
    <!-- Import Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reset & Base Styles */
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            color: #334155;
        }

        /* Container */
        .email-wrapper {
            max-width: 650px;
            margin: 2rem auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
        }

        /* Header */
        .email-header {
            background-color: #ffffff;
            padding: 2.5rem 2rem;
            text-align: center;
            border-bottom: 1px solid #f1f5f9;
        }

        .header-logos {
            width: 100%;
            margin-bottom: 1.5rem;
        }

        .logo-img {
            height: 70px;
            width: auto;
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.025em;
            color: #1e293b;
            text-transform: uppercase;
        }

        /* Body Content */
        .email-body {
            padding: 3rem 2.5rem;
        }

        .greeting {
            font-size: 1.125rem;
            color: #1e293b;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .alert-box {
            background-color: #f0fdf4;
            border-left: 4px solid #22c55e;
            padding: 1.25rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .alert-text {
            color: #166534;
            font-size: 1.05rem;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .content-text {
            margin-bottom: 2rem;
            color: #475569;
            font-size: 1rem;
        }

        /* Schedule Grid */
        .schedule-container {
            background-color: #f1f5f9;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .schedule-title {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: block;
        }

        .schedule-grid {
            display: table;
            width: 100%;
        }

        .schedule-row {
            display: table-row;
        }

        .schedule-item {
            display: table-cell;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .schedule-label {
            font-weight: 600;
            color: #64748b;
            width: 40%;
        }

        .schedule-value {
            color: #1e293b;
            font-weight: 700;
        }

        .last-item .schedule-item {
            border-bottom: none;
        }

        /* Button */
        .btn-container {
            text-align: center;
            margin: 2.5rem 0;
        }

        .btn {
            background-color: #2873B4;
            color: #ffffff !important;
            padding: 12px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            display: inline-block;
            transition: background-color 0.2s;
        }

        /* Footer */
        .email-footer {
            background-color: #1e293b;
            color: #94a3b8;
            padding: 2.5rem 2rem;
            text-align: center;
            font-size: 0.875rem;
        }

        .contact-info {
            margin-top: 1.5rem;
            border-top: 1px solid #334155;
            padding-top: 1.5rem;
        }

        .contact-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .contact-list li {
            display: inline-block;
            margin: 0 10px;
        }

        @media only screen and (max-width: 600px) {
            .email-wrapper {
                margin: 0;
                border-radius: 0;
            }
            .email-body {
                padding: 2rem 1.5rem;
            }
            .schedule-label {
                width: 50%;
            }
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="email-header">
            <div class="header-logos">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td align="center">
                            <img src="https://drive.usercontent.google.com/download?id=1XdEM7PcBXuRfkkdsBp3MqlDRz4n-GsJf&export=view"
                                alt="EPG Logo" class="logo-img">
                            <img src="https://drive.usercontent.google.com/download?id=1ph6WsmccjVnNwkK70-ntCVKKdHGJucS5&export=view"
                                alt="UNPRG Logo" class="logo-img" style="margin-left: 20px;">
                        </td>
                    </tr>
                </table>
            </div>
            <h1 class="header-title">Reprogramación del Examen de Admisión Posgrado 2026-I</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                Hola, {{ $nombre }}
            </div>

            <div class="content-text">
                Queremos informarte que la Comisión de Admisión ha establecido oficialmente la nueva fecha para el proceso de admisión 2026-I. Por favor, toma nota de los siguientes detalles actualizados:
            </div>

            <div class="alert-box">
                <div class="alert-text">
                    📅 El examen se realizará el domingo 28 de Junio.
                </div>
            </div>

            <div class="schedule-container">
                <span class="schedule-title">Detalles del Proceso</span>
                <div class="schedule-grid">
                    <div class="schedule-row">
                        <div class="schedule-item schedule-label">Nueva Fecha</div>
                        <div class="schedule-item schedule-value">28 de Junio, 2026</div>
                    </div>
                    <div class="schedule-row">
                        <div class="schedule-item schedule-label">Ingreso a la Universidad</div>
                        <div class="schedule-item schedule-value">07:00 AM - 09:00 AM</div>
                    </div>
                    <div class="schedule-row">
                        <div class="schedule-item schedule-label">Inicio de Examen de Admisión</div>
                        <div class="schedule-item schedule-value">09:00 AM</div>
                    </div>
                    <div class="schedule-row">
                        <div class="schedule-item schedule-label">Inicio de Entrevista Personal</div>
                        <div class="schedule-item schedule-value">10:00 AM</div>
                    </div>
                    <div class="schedule-row last-item">
                        <div class="schedule-item schedule-label">Lugar</div>
                        <div class="schedule-item schedule-value">Escuela de Posgrado - UNPRG</div>
                    </div>
                </div>
            </div>

            <div class="content-text">
                Hemos adjuntado el <strong>nuevo cronograma</strong> y el <strong>comunicado oficial</strong> para que los tengas a mano. Te recomendamos revisarlos detalladamente.
            </div>

            <div class="btn-container">
                <a href="https://epgunprg.edu.pe/admision-epg/" class="btn">Ir al Portal de Admisión</a>
            </div>

            <p style="text-align: center; font-size: 0.9rem; color: #64748b;">
                Atentamente,<br>
                <strong>Comisión de Admisión 2026-I</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <div class="footer-text">
                Este es un correo institucional de la Escuela de Posgrado de la Universidad Nacional Pedro Ruiz Gallo.
            </div>
            <div class="contact-info">
                <ul class="contact-list">
                    <li>📍 Lambayeque, Perú</li>
                    <li>📩 admision_epg@unprg.edu.pe</li>
                    <li>📱 995901454</li>
                </ul>
            </div>
        </div>
    </div>
</body>

</html>
