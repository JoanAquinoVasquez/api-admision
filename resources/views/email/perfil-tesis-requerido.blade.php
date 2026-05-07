<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Proyecto de Tesis - Escuela de Posgrado UNPRG</title>
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
            margin-bottom: 2rem;
        }

        /* Important Info Box */
        .info-box {
            background-color: #fefce8;
            border: 1px solid #fef08a;
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
            color: #854d0e;
            font-size: 1rem;
        }

        .info-content strong {
            display: block;
            font-size: 1.1rem;
            margin-bottom: 4px;
        }

        .content-paragraph {
            margin-bottom: 2rem;
            color: #4b5563;
        }

        /* CTA Section */
        .cta-section {
            text-align: center;
            padding: 2.5rem 2rem;
            background: #f8fafc;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            margin-top: 2rem;
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
            font-size: 1rem;
            border: none;
        }

        /* Footer */
        .email-footer {
            background-color: #1e293b;
            color: #94a3b8;
            padding: 3rem 2rem;
            text-align: center;
            font-size: 0.85rem;
        }

        .contact-info {
            margin: 1.5rem 0;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
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
            <div class="header-badge">Proceso de Admisión 2026-I</div>
            <h1 class="header-title">Información Importante<br>para tu Postulación</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                Estimado(a) {{ $nombre }}:
            </div>

            <p class="intro-text">
                Reciba un cordial saludo de la <strong>Escuela de Posgrado de la UNPRG</strong>.
            </p>

            <p class="content-paragraph">
                Por medio del presente, se le informa que, como parte del proceso de admisión a los programas de maestría, actualmente se está solicitando la presentación de un <strong>perfil de proyecto de tesis tentativo</strong>.
            </p>

            <div class="info-box">
                <div class="info-icon">📋</div>
                <div class="info-content">
                    <strong>Disposición SUNEDU</strong>
                    Esta disposición responde a las exigencias establecidas por la SUNEDU, orientadas a fortalecer la calidad de la formación académica e investigativa.
                </div>
            </div>

            <p class="content-paragraph">
                En ese sentido, es importante precisar que, si bien anteriormente se indicó que la entrevista personal se basaría en preguntas relacionadas a su programa de estudio, en esta etapa el proceso considerará <strong>aspectos vinculados al perfil de investigación propuesto por el postulante</strong>.
            </p>

            <div style="background-color: #f0f9ff; border-left: 4px solid #0369a1; padding: 1.5rem; margin: 2rem 0; border-radius: 8px;">
                <p style="margin: 0; color: #0c4a6e; font-size: 0.95rem; line-height: 1.6;">
                    <strong>Especificaciones del Perfil:</strong><br>
                    Debe ser un <strong>perfil tentativo</strong> (el cual puede estar sujeto a cambios durante el desarrollo del posgrado) con una extensión promedio de <strong>cinco páginas</strong>, letra <strong>Arial 12</strong>, interlineado de <strong>1.5</strong> y en formato <strong>A4</strong>.
                </p>
            </div>

            <!-- Download Section Simplified -->
            <div style="margin-top: 2rem; text-align: center;">
                <a href="https://drive.google.com/file/d/18tZFUHQyehMIgxn-9o25h_JHG-jv-Il3/view?usp=sharing" 
                   target="_blank"
                   style="background-color: #1e3a8a; color: #ffffff; padding: 15px 35px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 1rem; display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 10px 20px rgba(30, 58, 138, 0.2);">
                    👁️ Ver Rúbrica Proyecto Tentativo de Tesis
                </a>
            </div>

            <p class="content-paragraph" style="margin-top: 2.5rem;">
                Agradecemos su comprensión y compromiso con este proceso, el cual busca garantizar una formación de excelencia.
            </p>

            <div class="cta-section">
                <p style="color: #64748b; margin-bottom: 1.5rem;">Para más detalles sobre los requisitos, puede visitar nuestro portal:</p>
                <a href="https://epgunprg.edu.pe/admision-epg/maestrias" target="_blank" class="main-btn">
                    PORTAL DE ADMISIÓN
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p style="margin-bottom: 2rem; font-weight: 600; color: #f8fafc;">Escuela de Posgrado - UNPRG</p>
            
            <div class="contact-info">
                <div class="contact-item"> Lambayeque, Perú </div>
                <div class="contact-item">
                    <a href="mailto:admision_epg@unprg.edu.pe" class="contact-link">admision_epg@unprg.edu.pe</a>
                </div>
                <div class="contact-item"> 995901454 </div>
            </div>

            <p style="margin-top: 2rem; opacity: 0.5;">
                &copy; {{ date('Y') }} Escuela de Posgrado UNPRG. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>

</html>
