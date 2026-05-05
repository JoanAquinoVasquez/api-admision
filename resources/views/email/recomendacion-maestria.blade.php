<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impulsa tu Futuro - Escuela de Posgrado UNPRG</title>
    <!-- Import Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Reset & Base Styles */
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f0f2f5;
            color: #333333;
        }

        /* Container */
        .email-wrapper {
            max-width: 650px;
            margin: 2rem auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid #e1e4e8;
        }

        /* Header Premium */
        .email-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #2873B4 100%);
            padding: 3rem 2rem;
            text-align: center;
            color: #ffffff;
            position: relative;
        }

        .header-logos {
            width: 100%;
            margin-bottom: 2rem;
        }

        .header-logos table {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-img {
            height: 70px;
            width: auto;
            display: block;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }

        .header-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header-title {
            font-size: 2.2rem;
            font-weight: 800;
            margin: 0;
            line-height: 1.2;
            letter-spacing: -1px;
        }

        /* Body Content */
        .email-body {
            padding: 3rem 2.5rem;
        }

        .greeting {
            font-size: 1.5rem;
            color: #1e3a8a;
            margin-bottom: 1rem;
            font-weight: 800;
        }

        .intro-text {
            color: #4b5563;
            font-size: 1.1rem;
            margin-bottom: 2.5rem;
        }

        /* Bachiller Info Box */
        .info-box {
            background-color: #f0fdf4;
            border: 1px solid #dcfce7;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2.5rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .info-icon {
            font-size: 2rem;
            line-height: 1;
        }

        .info-content {
            color: #166534;
            font-size: 1rem;
        }

        .info-content strong {
            display: block;
            font-size: 1.1rem;
            margin-bottom: 4px;
        }

        /* Program Recommendation List */
        .recommendation-section {
            margin-bottom: 3rem;
        }

        .section-title {
            color: #1e293b;
            font-weight: 800;
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .program-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }

        .program-card:hover {
            border-color: #2873B4;
            box-shadow: 0 8px 15px rgba(40, 115, 180, 0.1);
            transform: translateY(-2px);
        }

        .program-info {
            flex: 1;
        }

        .program-name {
            display: block;
            color: #1e3a8a;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 8px;
        }

        .brochure-link {
            color: #2873B4;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .brochure-link:hover {
            color: #1e3a8a;
            text-decoration: underline;
        }

        /* CTA Section */
        .cta-section {
            text-align: center;
            padding: 3rem 2rem;
            background: #f8fafc;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
        }

        .cta-title {
            color: #1e293b;
            font-weight: 800;
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }

        .main-btn {
            display: inline-block;
            background-color: #fbbf24;
            color: #1e3a8a;
            font-weight: 800;
            text-decoration: none;
            padding: 18px 40px;
            border-radius: 12px;
            transition: all 0.2s ease;
            box-shadow: 0 10px 20px rgba(251, 191, 36, 0.2);
            text-transform: uppercase;
            font-size: 1.05rem;
            border: none;
            cursor: pointer;
        }

        .main-btn:hover {
            background-color: #f59e0b;
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(251, 191, 36, 0.3);
        }

        /* Footer */
        .email-footer {
            background-color: #1e293b;
            color: #94a3b8;
            padding: 3rem 2rem;
            text-align: center;
            font-size: 0.85rem;
        }

        .footer-logo {
            height: 40px;
            opacity: 0.5;
            margin-bottom: 1.5rem;
        }

        .contact-info {
            margin: 1.5rem 0;
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

        .contact-link {
            color: #cbd5e1;
            text-decoration: none;
        }

        /* Mobile Responsive */
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                margin: 0;
                border-radius: 0;
                width: 100% !important;
            }

            .email-body {
                padding: 2rem 1.5rem;
            }

            .program-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .header-title {
                font-size: 1.8rem;
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
                        <td width="50%" align="left">
                            <img src="https://drive.usercontent.google.com/download?id=1XdEM7PcBXuRfkkdsBp3MqlDRz4n-GsJf&export=view"
                                alt="EPG Logo" class="logo-img">
                        </td>
                        <td width="50%" align="right">
                            <img src="https://drive.usercontent.google.com/download?id=1ph6WsmccjVnNwkK70-ntCVKKdHGJucS5&export=view"
                                alt="UNPRG Logo" class="logo-img">
                        </td>
                    </tr>
                </table>
            </div>
            <div class="header-badge">Admisión 2026-I</div>
            <h1 class="header-title">Tu próxima meta<br>comienza aquí</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                ¡Hola, {{ $nombre }}!
            </div>

            <p class="intro-text">
                En la <strong>Escuela de Posgrado de la UNPRG</strong>, valoramos tu trayectoria. Es momento de dar el siguiente gran paso en tu carrera profesional y alcanzar el nivel que siempre has deseado.
            </p>

            <!-- Important Info: Bachiller is enough -->
            <div class="info-box">
                <div class="info-icon">🎓</div>
                <div class="info-content">
                    <strong>¿Sabías que ya puedes postular?</strong>
                    Para iniciar tu Maestría, solo necesitas contar con tu <strong>Grado de Bachiller</strong>
                </div>
            </div>

            <div class="recommendation-section">
                <h3 class="section-title">📂 Programas recomendados para tu perfil:</h3>
                
                @foreach($programas as $programa)
                    <div class="program-card">
                        <div class="program-info">
                            <span class="program-name">{{ $programa['nombre'] }}</span>
                            @if($programa['brochure'])
                                <a href="{{ $programa['brochure'] }}" class="brochure-link" target="_blank">
                                    <span>📥</span> Ver Brochure
                                </a>
                            @endif
                        </div>
                        <div style="font-size: 1.5rem; color: #e2e8f0;">🎓</div>
                    </div>
                @endforeach
            </div>

            <div class="cta-section">
                <h3 class="cta-title">¿Listo para transformar tu futuro?</h3>
                <p style="color: #64748b; margin-bottom: 2rem;">Únete a la nueva generación de magísteres de la UNPRG. Las inscripciones ya están abiertas.</p>
                <a href="https://epgunprg.edu.pe/admision-epg/maestrias" target="_blank" class="main-btn">
                    VER REQUISITOS Y CRONOGRAMA
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p style="margin-bottom: 2rem; font-weight: 600; color: #f8fafc;">Escuela de Posgrado - UNPRG</p>
            
            <div class="contact-info">
                <div class="contact-item">
                    <span>📍</span> Lambayeque, Perú
                </div>
                <div class="contact-item">
                    <span>📩</span> <a href="mailto:admision_epg@unprg.edu.pe" class="contact-link">admision_epg@unprg.edu.pe</a>
                </div>
                <div class="contact-item">
                    <span>📱</span> 995901454
                </div>
            </div>

            <img src="https://drive.google.com/uc?export=view&id=1bXqmf32tJmjzpoG90fJJwkGN9N2_fKyQ" width="150px"
                height="3px" alt="Barra" style="margin-top: 2rem; opacity: 0.3;">
            
            <p style="margin-top: 2rem; opacity: 0.5;">
                &copy; {{ date('Y') }} Escuela de Posgrado UNPRG. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>

</html>
