<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Felicitaciones, ingresaste! | ESCUELA DE POSGRADO UNPRG</title>
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
            border-bottom: 4px solid #10b981; /* Green border for success */
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
            color: #065f46; /* Green color for success */
        }

        .status-banner {
            background-color: #d1fae5; /* Light green success banner */
            color: #065f46;
            padding: 1rem 2rem;
            text-align: center;
            font-weight: 700;
            border-bottom: 2px solid #a7f3d0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1.1rem;
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
            color: #10b981;
            font-weight: 700;
        }

        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-left: 4px solid #3b82f6;
        }

        .info-card h3 {
            margin-top: 0;
            color: #1e3a8a;
            font-size: 1.1rem;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.5rem;
            font-size: 0.95rem;
        }

        .payment-table th, .payment-table td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
        }

        .payment-table th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 600;
        }

        .payment-table td.monto {
            text-align: right;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
        }

        .payment-table tr.total-row td {
            border-top: 2px solid #cbd5e1;
            font-weight: 700;
            background-color: #f8fafc;
        }

        .btn-container {
            text-align: center;
            margin: 2rem 0;
        }

        .btn-results {
            display: inline-block;
            background-color: #10b981;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 30px;
            font-weight: 700;
            font-size: 1.1rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);
            transition: all 0.3s ease;
        }

        .btn-results:hover {
            background-color: #059669;
            box-shadow: 0 6px 12px rgba(5, 150, 105, 0.3);
        }

        .remember-box {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 1rem;
            margin: 1.5rem 0;
            border-left: 4px solid #f59e0b;
            font-size: 0.95rem;
            color: #78350f;
        }

        .cronograma-card {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-left: 4px solid #10b981;
        }

        .cronograma-card h3 {
            margin-top: 0;
            color: #065f46;
            font-size: 1.15rem;
            border-bottom: 1px solid #bbf7d0;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }

        .cronograma-item {
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }

        .cronograma-label {
            font-weight: 700;
            color: #111827;
        }

        .date-badge {
            display: inline-block;
            background-color: #dcfce7;
            color: #15803d;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 6px;
            font-size: 0.9rem;
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
                                alt="EPG Logo" class="logo-img" style="height: 90px; width: auto; display: block;">
                        </td>
                        <td width="50%" align="right" style="padding-left: 10px;">
                            <img src="https://drive.usercontent.google.com/download?id=1ph6WsmccjVnNwkK70-ntCVKKdHGJucS5&export=view"
                                alt="UNPRG Logo" class="logo-img" style="height: 90px; width: auto; display: block;">
                        </td>
                    </tr>
                </table>
            </div>
            <h1 class="header-title">¡Felicitaciones Ingresante!</h1>
        </div>

        <!-- Status Banner -->
        <div class="status-banner">
            <span class="status-icon">🎉</span>
            <span>¡Alcanzaste vacante en la Escuela de Posgrado UNPRG!</span>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                {{ $sexo == 'M' ? 'Estimado' : 'Estimada' }},
                <strong>{{ $nombres }} {{ $ap_paterno }} {{ $ap_materno }}</strong>
            </div>

            <p style="color: #4b5563; margin-bottom: 1.5rem; font-size: 1.05rem;">
                Nos complace y es un gran orgullo comunicarle que ha <strong>alcanzado una vacante</strong> en el presente proceso de admisión para cursar el programa de 
                <span class="highlight-text">{{ $nombre_grado }} en {{ $nombre_programa }}</span>.
            </p>

            <p style="color: #4b5563; margin-bottom: 1.5rem;">
                La lista de ingresantes oficial y los méritos de admisión se encuentran disponibles en el siguiente documento. Puede hacer clic en el botón de abajo para revisarlo:
            </p>

            <!-- Button to PDF -->
            <div class="btn-container">
                <a href="{{ $pdf_link }}" target="_blank" class="btn-results">Ver Resultados Oficiales (PDF)</a>
            </div>

            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 2rem 0;">

            <!-- Payment Instructions -->
            <div class="info-card">
                <h3>💳 Conceptos y Pagos de Matrícula y Pensión</h3>
                <p style="color: #475569; margin-bottom: 1rem; font-size: 0.95rem;">
                    Para formalizar su matrícula e inicio de clases, debe efectuar los siguientes pagos en el <strong>Banco de la Nación</strong> o mediante la plataforma online <strong>Págalo.pe</strong>:
                </p>

                @if($es_maestria)
                    <table class="payment-table">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Transacción</th>
                                <th>Código</th>
                                <th style="text-align: right;">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Derecho de Matrícula</td>
                                <td>9135</td>
                                <td>001</td>
                                <td class="monto">S/ 250.00</td>
                            </tr>
                            <tr>
                                <td>Pensión de Estudios</td>
                                <td>9135</td>
                                <td>003</td>
                                <td class="monto">S/ 500.00</td>
                            </tr>
                            <tr class="total-row">
                                <td colspan="3">Total a Pagar</td>
                                <td class="monto">S/ 750.00</td>
                            </tr>
                        </tbody>
                    </table>
                @elseif($es_doctorado)
                    <table class="payment-table">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Transacción</th>
                                <th>Código</th>
                                <th style="text-align: right;">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Derecho de Matrícula</td>
                                <td>9135</td>
                                <td>001</td>
                                <td class="monto">S/ 300.00</td>
                            </tr>
                            <tr>
                                <td>Primera Pensión de Estudios</td>
                                <td>9135</td>
                                <td>003</td>
                                <td class="monto">S/ 600.00</td>
                            </tr>
                            <tr class="total-row">
                                <td colspan="3">Total a Pagar</td>
                                <td class="monto">S/ 900.00</td>
                            </tr>
                        </tbody>
                    </table>
                @elseif($es_segunda_especialidad)
                    <table class="payment-table">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Transacción</th>
                                <th>Código</th>
                                <th style="text-align: right;">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Derecho de Matrícula</td>
                                <td>9135</td>
                                <td>594</td>
                                <td class="monto">S/ 200.00</td>
                            </tr>
                            <tr>
                                <td>Pensión de Estudios (Mensualidad)</td>
                                <td>9135</td>
                                <td>595</td>
                                <td class="monto">S/ 350.00</td>
                            </tr>
                            <tr class="total-row">
                                <td colspan="3">Total a Pagar</td>
                                <td class="monto">S/ 550.00</td>
                            </tr>
                        </tbody>
                    </table>
                @else
                    <!-- Fallback: Muestra ambas tablas o información general -->
                    <h4 style="margin-bottom: 0.5rem; color: #334155;">Maestrías:</h4>
                    <table class="payment-table" style="margin-bottom: 1.5rem;">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Transacción</th>
                                <th>Código</th>
                                <th style="text-align: right;">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Derecho de Matrícula</td>
                                <td>9135</td>
                                <td>001</td>
                                <td class="monto">S/ 250.00</td>
                            </tr>
                            <tr>
                                <td>Primera Pensión</td>
                                <td>9135</td>
                                <td>003</td>
                                <td class="monto">S/ 500.00</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4 style="margin-bottom: 0.5rem; color: #334155;">Doctorados:</h4>
                    <table class="payment-table">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Transacción</th>
                                <th>Código</th>
                                <th style="text-align: right;">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Derecho de Matrícula</td>
                                <td>9135</td>
                                <td>001</td>
                                <td class="monto">S/ 300.00</td>
                            </tr>
                            <tr>
                                <td>Primera Pensión</td>
                                <td>9135</td>
                                <td>003</td>
                                <td class="monto">S/ 600.00</td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            </div>

            <!-- Cronograma de Actividades -->
            <div class="cronograma-card">
                <h3>📅 Fechas Importantes</h3>
                <div class="cronograma-item">
                    <span class="cronograma-label">Plazo de Pago de Matrícula y 1ra Pensión:</span>
                    <br>
                    Del <span class="date-badge">lunes 6 al jueves 9 de julio</span>
                </div>
                <div class="cronograma-item" style="margin-bottom: 0;">
                    <span class="cronograma-label">Inicio de Clases:</span>
                    <br>
                    Este <span class="date-badge" style="background-color: #dbeafe; color: #1e40af;">sábado 11 de julio</span>
                </div>
            </div>

            @if(!$es_segunda_especialidad)
            <!-- Inlined Image from User -->
            <div style="text-align: center; margin: 2rem 0;">
                <p style="font-weight: 600; color: #4b5563; margin-bottom: 0.5rem; font-size: 0.9rem;">
                    Guía Oficial de Conceptos de Pago:
                </p>
                <img src="{{ isset($message) ? $message->embed(public_path('img/matricula.jpg')) : asset('img/matricula.jpg') }}" 
                     alt="Guía de Matrícula" 
                     style="width: 100%; max-width: 550px; height: auto; border-radius: 8px; border: 1px solid #e2e8f0; display: block; margin: 0 auto;">
            </div>
            @endif

            <div class="remember-box">
                <strong>📌 RECUERDA:</strong> Es obligatorio realizar el pago tanto de la matrícula como de la primera pensión para poder formalizar su ingreso al sistema de la Escuela de Posgrado.
            </div>

            <p style="color: #4b5563; margin-top: 2rem;">
                Le damos la más cálida bienvenida a la comunidad académica de la <strong>Escuela de Posgrado de la Universidad Nacional Pedro Ruiz Gallo</strong>. Le deseamos el mayor de los éxitos en esta nueva etapa de su desarrollo profesional.
            </p>

            <p style="color: #4b5563; margin-top: 1.5rem; font-size: 0.95rem;">
                Atentamente,<br>
                <strong>Comisión de Admisión 2026-I</strong><br>
                Escuela de Posgrado UNPRG
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
