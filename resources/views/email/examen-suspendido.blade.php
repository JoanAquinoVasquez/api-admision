<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            background-color: #fffbeb;
            /* Light Amber */
            padding: 2.5rem 2rem;
            text-align: center;
            color: #92400e;
            position: relative;
            border-bottom: 4px solid #f59e0b;
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
            color: #b45309;
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

        .alert-box {
            background-color: #fff7ed;
            border-left: 5px solid #f97316;
            padding: 1.5rem;
            border-radius: 6px;
            margin-bottom: 2rem;
        }

        .alert-text {
            color: #9a3412;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .content-section {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 2rem;
            margin: 2rem 0;
            color: #334155;
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

        @media only screen and (max-width: 600px) {
            .email-wrapper {
                margin: 0;
                border-radius: 0;
                width: 100% !important;
            }
            .email-body {
                padding: 1.5rem;
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
            <h1 class="header-title">Aviso de Reprogramación</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                Estimado(a) Postulante,
            </div>

            <div class="alert-box">
                <div class="alert-text">
                    ⚠️ El examen de admisión programado para este domingo 17 de mayo queda SUSPENDIDO.
                </div>
            </div>

            <div class="content-section">
                <p style="margin-top: 0;">
                    Atendiendo las solicitudes y comentarios de muchos postulantes, especialmente de quienes viajarían desde distintas ciudades, se ha tomado la decisión de <strong>reprogramar el examen de admisión</strong>.
                </p>
                <p>
                    Por ello, el examen programado para este 17 de mayo queda suspendido, por lo que <strong>no será necesario acudir a la universidad en dicha fecha</strong>.
                </p>
                <p style="margin-bottom: 0;">
                    La nueva fecha oficial será comunicada durante el transcurso de la semana a través de nuestras redes sociales oficiales y canales institucionales.
                </p>
            </div>

            <p style="color: #64748b; font-size: 0.95rem; text-align: center;">
                Agradecemos su comprensión y les recomendamos estar atentos a nuestras comunicaciones.
            </p>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <img src="https://drive.google.com/uc?export=view&id=1bXqmf32tJmjzpoG90fJJwkGN9N2_fKyQ" width="100%"
                height="6px" alt="Barra Colores" style="display: block; width: 100%;">

            <div class="footer-content">
                <p style="margin: 0 0 1rem 0; font-weight: 600; color: #ffffff;">Comisión de Admisión - Escuela de Posgrado UNPRG</p>
                <ul class="contact-list">
                    <li>📍 Av. Huamachuco Nro. 1130, Lambayeque</li>
                    <li>📩 admision_epg@unprg.edu.pe</li>
                    <li>📱 995901454</li>
                </ul>
            </div>
        </div>
    </div>
</body>

</html>
