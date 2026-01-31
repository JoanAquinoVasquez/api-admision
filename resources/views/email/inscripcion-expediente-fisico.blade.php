<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expediente Físico Validado - Escuela de Posgrado UNPRG | Proceso de Admisión 2026-I</title>
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
            /* Light Green */
            padding: 2.5rem 2rem;
            text-align: center;
            color: #065f46;
            position: relative;
            border-bottom: 4px solid #10b981;
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
            /* Increased size */
            width: auto;
        }

        .header-title {
            font-size: 1.8rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: -0.5px;
            color: #047857;
            /* Darker green for text */
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

        .congrats-box {
            background-color: #ecfdf5;
            border-left: 5px solid #10b981;
            padding: 1.5rem;
            border-radius: 6px;
            margin-bottom: 2rem;
        }

        .congrats-text {
            color: #065f46;
            font-size: 1.05rem;
            font-weight: 500;
        }

        /* Steps / Checklist */
        .steps-container {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 2rem;
            margin: 2rem 0;
        }

        .steps-title {
            color: #1e293b;
            font-weight: 700;
            font-size: 1.2rem;
            margin-top: 0;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checklist {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .checklist-item {
            position: relative;
            padding-left: 35px;
            margin-bottom: 1rem;
            color: #475569;
        }

        .checklist-item::before {
            content: "✅";
            position: absolute;
            left: 0;
            top: 0;
            font-size: 1.1rem;
        }

        .checklist-item.next-step::before {
            content: "✅";
        }

        .checklist-item.important {
            color: #b91c1c;
            font-weight: 600;
        }

        /* Alert Box */
        .alert-box {
            background-color: #fff7ed;
            border: 1px solid #ffedd5;
            color: #9a3412;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1.5rem;
            display: flex;
            gap: 12px;
            align-items: start;
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

        /* Mobile Responsive */
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                margin: 0;
                border-radius: 0;
                width: 100% !important;
            }

            .email-body {
                padding: 1.5rem;
            }

            .header-logos {
                justify-content: center;
                gap: 20px;
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
            <h1 class="header-title">Expediente Físico Validado</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                {{ $inscripcion->postulante->sexo == 'M' ? 'Estimado' : 'Estimada' }}
                <strong>{{ $inscripcion->postulante->nombres }} {{ $inscripcion->postulante->ap_paterno }}
                    {{ $inscripcion->postulante->ap_materno }}</strong>,
            </div>

            <div class="congrats-box">
                <div class="congrats-text">
                    Su <strong>Expediente Físico</strong> ha sido validado y recepcionado con éxito. Ahora usted ya se
                    encuentra
                    {{ $inscripcion->postulante->sexo == 'Masculino' ? 'apto' : 'apta' }} para dar el <strong>Examen
                        de Admisión</strong>!
                </div>
            </div>

            <p style="color: #4b5563;">
                A continuación, detallamos las etapas finales de evaluación en las que deberás participar:
            </p>

            <div class="steps-container">
                <h3 class="steps-title">📅 Cronograma de Evaluación</h3>
                <ul class="checklist">
                    <li class="checklist-item next-step">
                        <strong>Examen de Admisión:</strong> Se realizará el día
                        <strong>{{ $examen_admision }}</strong>.
                    </li>


                    <li class="checklist-item">
                        <strong>Entrevista Personal:</strong> Se llevará a cabo <strong>inmediatamente después</strong>
                        del Examen de Admisión.
                        @if(isset($inscripcion->programa->grado_id) && ($inscripcion->programa->grado_id === 1 || $inscripcion->programa->grado_id === 2))
                            Es indispensable tener listo tu
                        perfil de proyecto tentativo de investigación. @endif
                    </li>


                    <li class="checklist-item next-step">
                        <strong>Requisitos de ingreso:</strong> Portar obligatoriamente DNI original, Carnet de
                        Postulante, Lápiz 2B y borrador.
                    </li>

                    <li class="checklist-item next-step">
                        <strong>Resultados:</strong> La lista de ingresantes se publicará el
                        <strong>{{ $resultados_publicacion }}</strong> en nuestra pagina web y redes sociales.
                    </li>
                </ul>
            </div>

            <div class="alert-box">
                <span style="font-size: 1.5rem;">⚠️</span>
                <div>
                    <strong>Nota:</strong>
                    <p style="margin: 0;">La ausencia en cualquiera de las etapas (examen o entrevista) descalificará
                        automáticamente tu postulación.</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <img src="https://drive.google.com/uc?export=view&id=1bXqmf32tJmjzpoG90fJJwkGN9N2_fKyQ" width="100%"
                height="6px" alt="Barra Colores" style="display: block; width: 100%;">

            <div class="footer-content">
                <p style="margin: 0 0 1rem 0; font-weight: 600; color: #ffffff;">Comisión de Admisión - Escuela de
                    Posgrado
                    UNPRG</p>

                <ul class="contact-list">
                    <li>📍 Av. Huamachuco Nro. 1130, Lambayeque</li>
                    <li>📩 <a href="mailto:admision_epg@unprg.edu.pe"
                            style="color: #ffffff; text-decoration: none;">admision_epg@unprg.edu.pe</a></li>
                    <li>📱 995901454</li>
                </ul>
            </div>
        </div>
    </div>
</body>

</html>