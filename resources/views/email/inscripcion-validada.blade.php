<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Inscripción</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }

        .header-content {
            flex: 1;
            text-align: center;
        }

        .logo-placeholder {
            width: 120px;
            height: auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-placeholder img {
            max-width: 100%;
            max-height: 170px;
            height: auto;
            object-fit: contain;
        }

        h1 {
            color: #2c3e50;
            font-size: 2rem;
            margin: 0;
            padding: 0 1rem;
        }

        .consultation-banner {
            background-color: #478ac5;
            color: white;
            padding: 1rem;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 0.3rem;
            padding-bottom: 0.4rem;
        }

        .consultation-banner-content {
            flex-grow: 1;
        }

        .consultation-link {
            background-color: #ffffff;
            color: #000000;
            padding: 7px 10px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-left: 1rem;
            transition: background-color 0.3s;
        }

        .consultation-link:hover {
            background-color: #FFD633;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 0.8rem;
            padding-top: 0.1rem;
            padding-bottom: 0.1rem;
        }

        /* Estilo mejorado para los datos de la inscripción */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0.8rem;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 1rem;
        }

        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        .footer-container {
            background-color: #2873B4;
            color: white;
            font-size: 0.9rem;
            text-align: center;
            border-radius: 4px;
        }

        .footer-section h2 {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid #FFC300;
            display: inline-block;
            padding-bottom: 0.3rem;
        }

        .footer-section ul {
            margin: 0;
            padding: 0;
            list-style: none;
            display: flex;
            /* Distribuye los elementos en una fila */
            justify-content: center;
            /* Centra los elementos */
            gap: 30px;
            /* Espacio entre los elementos */
        }

        .footer-section ul li {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .footer-section ul li i {
            font-size: 1.2rem;
            color: #FFC300;
        }

        .footer-bottom {
            text-align: center;
            padding: 1rem 0;
            font-size: 0.8rem;
        }

        .footer-bottom strong {
            font-size: 0.9rem;
        }

        /* Evita que el correo se convierta en un enlace */
        .footer-section ul li a {
            text-decoration: none;
            color: white;
        }

        @media (max-width: 768px) {
            .container {
                margin: 1rem;
                padding: 1rem;
            }

            .header {
                flex-direction: column;
                gap: 1rem;
            }

            .logo-placeholder {
                width: 120px;
                height: 60px;
            }

            table {
                width: 100%;
                font-size: 0.9rem;
            }

            .footer-container {
                text-align: center;
            }

            .footer-section {
                text-align: center;
            }

            .consultation-banner {
                flex-direction: column;
                text-align: center;
            }

            .consultation-link {
                margin-left: 0;
                margin-top: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="logo-placeholder">
                {{-- Cuando se suba producción sera asi: --}}
                {{-- <img src="{{ asset('img/logo-epg.webp') }}" alt="EPG"> --}}
                {{-- Por ahora en local se usara img subidas a drive --}}
                <img src="https://drive.usercontent.google.com/download?id=1XdEM7PcBXuRfkkdsBp3MqlDRz4n-GsJf&export=view&authuser=0"
                    alt="EPG">
            </div>
            <div class="header-content">
                <h1>Inscripción Validada Exitosamente</h1>
            </div>
            <div class="logo-placeholder">
                {{-- <img src="{{ asset('img/escudo_act_ofic.webp') }}" alt="UNPRG"> --}}
                <img src="https://drive.usercontent.google.com/download?id=1ph6WsmccjVnNwkK70-ntCVKKdHGJucS5&export=view&authuser=0"
                    alt="UNPRG">
            </div>
        </div>

        <div class="success-message">
            <h4>Felicidades,
                <strong> {{ $inscripcion->postulante->nombres }} {{ $inscripcion->postulante->ap_paterno }}
                    {{ $inscripcion->postulante->ap_materno }} </strong>
            </h4>
            <p>Usted se ha inscrito en {{ $inscripcion->programa->grado_id == 1 ? 'el' : 'la' }}
                <strong>{{ $inscripcion->programa->grado->nombre }}</strong> del Programa de
                <strong>{{ $inscripcion->programa->nombre }}</strong>. Nos complace informarle que sus documentos
                digitales han sido
                validados y recepcionados exitosamente. Ya puede acercarse a dejar los siguientes documentos en fisico
                en
                la oficina de Admisión de la Escuela de Posgrado de la UNPRG:

            <ul>
                <li>Constancia de Inscripción (Adjunta en el correo).</li>
                <li>Voucher original de pago en el BANCO DE LA NACIÓN o Constancia de Pago de Págalo.pe.</li>
                <li>Solicitud dirigida al {{ $autoridad ?? '' }} (Enlace a un DRIVE con los documentos disponibles al
                    final
                    del mensaje).</li>
                <li>Copia simple de DNI/Carnet de Extranjería.</li>
                <li>Una (1) fotografía a color tamaño carné.</li>
                <li>Copia simple del {{ $gradoRequerido }}</li>
                <li>Impresión del Registro en SUNEDU.</li>
                <li>Currículum vitae documentado. (El Currículum
                    Vitae debe presentarse anillado con tapa
                    {{ $inscripcion->programa->facultad_id == 4 && !in_array($inscripcion->programa->grado_id, [1, 2])
                        ? 'turquesa'
                        : 'verde' }}
                    posterior y tapa transparente inicial, en hoja A4, además debe ser foliado en la parte superior
                    derecha).</li>
            </ul>

            Recuerde que debe firmar y poner su huella digital solo en los documentos que lo solicitan, además que debe
            <strong>presentar su expediente en un tiempo máximo dentro de las 48 horas
                hábiles</strong> de haber recibido este correo electrónico
            </p>
        </div>
        <div class="consultation-banner">
            <div class="consultation-banner-content">
                <p>Trae estos documentos:
                    <span style="font-size: 1.6rem; display: inline;">👉</span>
                    <strong>
                        <a href="{{ $urlDocumentos }}" target="_blank"
                            style="font-size: 1.6rem; color: #ffffff; text-decoration: underline;">
                            DOCUMENTOS (Dale click Aquí)
                        </a>
                    </strong>
                </p>
            </div>
        </div>


        <!--         {{-- <img src="{{ asset('img/barra_colores_ofic.webp') }}" width="800px" height="10px" alt=""> --}}
 --> <img src="https://drive.google.com/uc?export=view&id=1bXqmf32tJmjzpoG90fJJwkGN9N2_fKyQ" width="800px"
            height="10px" alt="">
        <div class="footer-container">
            <div class="footer-section">
                <h2>Contacto</h2>
                <ul>
                    <li><i class="bx bx-map"></i> <strong>Dirección: </strong> Av. Huamachuco Nro. 1130 Lambayeque</li>
                    <li><i class="bx bx-envelope"></i> <strong>Email: </strong> tele-educacion_epg@unprg.edu.pe</li>
                </ul>
                <ul>
                    <li><i class="bx bx-phone"></i> <strong>Teléfono: </strong>+51 996 235 308</li>
                </ul>
                <div class="footer-bottom">
                    <strong>Comisión de Admisión de la Escuela de Posgrado - UNPRG</strong>
                    <p>© 2025 Universidad Nacional Pedro Ruiz Gallo | Todos los derechos reservados</p>
                </div>
            </div>
        </div>

    </div>
</body>

</html>
