<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Docente Evaluador | ESCUELA DE POSGRADO UNPRG</title>
    <!-- Import Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
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
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #eaeaea;
        }

        .email-header {
            background-color: #eff6ff;
            padding: 2.5rem 2rem;
            text-align: center;
            color: #1e3a8a;
            position: relative;
            border-bottom: 4px solid #3b82f6;
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
            color: #1e40af;
        }

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

        .email-body {
            padding: 2.5rem;
        }

        .greeting {
            font-size: 1.25rem;
            color: #111827;
            margin-bottom: 1.5rem;
        }

        .highlight-text {
            color: #2563eb;
            font-weight: 600;
        }

        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }

        .credentials-box {
            background-color: #e0f2fe;
            border-left: 4px solid #0ea5e9;
            padding: 1rem 1.5rem;
            margin-top: 1rem;
            border-radius: 4px;
        }

        .cta-box {
            background: linear-gradient(to right, #2563eb, #1d4ed8);
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            color: white;
            margin-top: 2rem;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }

        .cta-text {
            margin-bottom: 1rem;
            font-size: 1rem;
            opacity: 0.9;
        }

        .cta-button {
            display: inline-block;
            background-color: #fbbf24;
            color: #1e3a8a;
            font-weight: 700;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 50px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .cta-button:hover {
            background-color: #f59e0b;
            transform: translateY(-2px);
        }

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
                                alt="EPG Logo" class="logo-img">
                        </td>
                        <td width="50%" align="right" style="padding-left: 10px;">
                            <img src="https://drive.usercontent.google.com/download?id=1ph6WsmccjVnNwkK70-ntCVKKdHGJucS5&export=view"
                                alt="UNPRG Logo" class="logo-img">
                        </td>
                    </tr>
                </table>
            </div>
            <h1 class="header-title">Sistema de Admisión 2026-I</h1>
        </div>

        <!-- Status Banner -->
        <div class="status-banner">
            <span class="status-icon">✅</span>
            <span>¡Bienvenido(a) como Evaluador(a)!</span>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                Estimado(a) docente: <strong>{{ $docente->nombres }} {{ $docente->ap_paterno }} {{ $docente->ap_materno }}</strong>
            </div>

            <p style="color: #4b5563; margin-bottom: 1.5rem;">
                Le informamos que ha sido registrado(a) en el <span class="highlight-text">Sistema de Admisión
                    2026-I</span> de la Escuela de Posgrado como Docente Evaluador de Expedientes (CV).
            </p>

            <div class="info-card">
                <p style="margin: 0; color: #1e293b; font-weight: 500;">
                    A continuación, le brindamos sus credenciales de acceso:
                </p>
                <div class="credentials-box">
                    <strong>Usuario:</strong> {{ $docente->email }}<br>
                    <strong>Contraseña:</strong> {{ $plainPassword }}
                </div>
            </div>

            <p style="color: #4b5563; margin-bottom: 1.5rem;">
                Adjunto a este correo, encontrará el <span class="highlight-text">Manual del Docente Evaluador</span>, 
                donde se explica paso a paso cómo ingresar al sistema, revisar la lista de postulantes asignados y registrar sus calificaciones usando nuestra herramienta automatizada.
            </p>

            <div class="cta-box">
                <div class="cta-text">Haga clic en el siguiente enlace para acceder:</div>
                <a href="https://epgunprg.edu.pe/admision-epg/iniciar-sesion" target="_blank" class="cta-button">Ingresar al Sistema</a>
            </div>

            <p
                style="color: #6b7280; font-size: 0.9rem; margin-top: 2rem; border-top: 1px solid #e5e7eb; padding-top: 1rem;">
                Si tiene problemas para acceder, por favor contacte con la coordinación general.
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <img src="https://drive.google.com/uc?export=view&id=1bXqmf32tJmjzpoG90fJJwkGN9N2_fKyQ" width="100%"
                height="6px" alt="Barra Colores" style="display: block; width: 100%;">

            <div class="footer-content">
                <p style="margin: 0 0 1rem 0; font-weight: 600; color: #ffffff;">Comisión de Admisión - Escuela de
                    Posgrado UNPRG</p>

                <ul class="contact-list">
                    <li class="contact-item">
                        <span>📍 Av. Huamachuco Nro. 1130, Lambayeque</span>
                    </li>
                    <li class="contact-item">
                        <span>📩 <a href="mailto:admision_epg@unprg.edu.pe"
                                style="color: #ffffff; text-decoration: none;">admision_epg@unprg.edu.pe</a></span>
                    </li>
                </ul>

                <p style="margin-top: 1.5rem; opacity: 0.8; font-size: 0.75rem;">
                    © 2026 Escuela de Posgrado de la Universidad Nacional Pedro Ruiz Gallo.
                </p>
            </div>
        </div>
    </div>
</body>

</html>
