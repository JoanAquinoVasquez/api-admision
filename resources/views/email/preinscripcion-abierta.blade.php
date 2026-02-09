<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Las inscripciones para el proceso de Admisión 2026-I ya están abiertas! | EPG UNPRG</title>
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
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            padding: 2.5rem 2rem;
            text-align: center;
            color: #ffffff;
            position: relative;
        }

        .logo-img {
            height: 90px;
            width: auto;
            margin-bottom: 20px;
        }

        .header-title {
            font-size: 1.8rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.5px;
            color: #ffffff;
        }

        .status-banner {
            background-color: #fbbf24;
            color: #1e3a8a;
            padding: 1rem 2rem;
            text-align: center;
            font-weight: 700;
            border-bottom: 2px solid #f59e0b;
            text-transform: uppercase;
            letter-spacing: 1px;
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

        .cta-box {
            background: #eff6ff;
            border-left: 5px solid #2563eb;
            padding: 1.5rem;
            margin: 2rem 0;
        }

        .cta-button {
            display: inline-block;
            background-color: #1e3a8a; /* Darker blue for better contrast */
            color: #ffffff !important;
            font-weight: 700;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-transform: uppercase;
            font-size: 0.95rem;
            margin-top: 1rem;
        }

        .brochure-button {
            display: inline-block;
            background-color: #065f46; /* Darker green for white text contrast */
            color: #ffffff !important;
            font-weight: 700;
            text-decoration: none;
            padding: 10px 24px;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 0.85rem;
            margin-top: 5px;
        }

        .email-footer {
            background-color: #1e293b;
            color: #ffffff;
            padding: 2rem;
            text-align: center;
            font-size: 0.85rem;
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
        <div class="email-header">
            <img src="https://drive.usercontent.google.com/download?id=1XdEM7PcBXuRfkkdsBp3MqlDRz4n-GsJf&export=view"
                alt="EPG Logo" class="logo-img">
            <h1 class="header-title">Admisión 2026-I</h1>
        </div>

        <div class="status-banner">
            🎯 ¡INSCRIPCIONES ABIERTAS!
        </div>

        <div class="email-body">
            <div class="greeting">
                Hola, <strong>{{ $preInscripcion->nombres }} {{ $preInscripcion->ap_paterno }}</strong>
            </div>

            <p style="color: #4b5563; margin-bottom: 1.5rem;">
                ¡Tenemos excelentes noticias para ti! El proceso de <span class="highlight-text">Inscripciones para el
                    Ciclo 2026-I</span> ha comenzado oficialmente.
            </p>

            <div class="info-card">
                <p style="margin: 0; color: #1e293b; font-weight: 600;">
                    Programa de tu interés:
                </p>
                <p style="color: #2563eb; font-size: 1.1rem; margin: 5px 0;">
                    {{ $preInscripcion->programa->grado->nombre ?? '' }} en
                    {{ $preInscripcion->programa->nombre ?? '' }}
                </p>

                @if($preInscripcion->programa->brochure)
                    <p style="margin-top: 15px; font-size: 0.9rem; color: #64748b;">
                        Hemos adjuntado el folleto informativo para que conozcas todos los detalles del programa:
                    </p>
                    <a href="{{ $preInscripcion->programa->brochure }}" target="_blank" class="brochure-button">
                        📄 Descargar Folleto Informativo
                    </a>
                @endif
            </div>

            <div class="cta-box">
                <p style="margin: 0 0 10px 0; color: #1e3a8a; font-weight: 600;">
                    ¿Listo para dar el siguiente paso en tu carrera profesional?
                </p>
                <p style="margin: 0; color: #4b5563;">
                    Puedes iniciar tu proceso de inscripción totalmente en línea a través de nuestro portal:
                </p>
                <a href="https://epgunprg.edu.pe/admision-epg/inscripcion" target="_blank" class="cta-button">
                    Iniciar Inscripción Ahora
                </a>
            </div>

            <p style="color: #4b5563; margin-bottom: 1.5rem;">
                Para más información sobre requisitos, costos y cronograma, visita nuestra página web oficial:
                <br>
                <a href="https://epgunprg.edu.pe/admision-epg/"
                    style="color: #2563eb; font-weight: 600;">https://epgunprg.edu.pe/admision-epg/</a>
            </p>
        </div>

        <div class="email-footer">
            <p style="margin: 0 0 1rem 0; font-weight: 600;">Escuela de Posgrado - UNPRG</p>
            <ul class="contact-list" style="color: #cbd5e1;">
                <li>📍 Av. Huamachuco Nro. 1130, Lambayeque</li>
                <li>📩 admision_epg@unprg.edu.pe</li>
                <li>📱 995901454</li>
            </ul>
            <p style="margin-top: 1.5rem; opacity: 0.6; font-size: 0.75rem;">
                Recibes este correo porque te registraste previamente en nuestro sistema de preinscripción.
            </p>
        </div>
    </div>
</body>

</html>