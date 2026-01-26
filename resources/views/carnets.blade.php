<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exportar Carnets</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .page {
            width: 21cm;
            height: 30.6cm;
            page-break-after: always;
            position: relative;
            margin: 0;
            padding: 0;
        }

        .card-container {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            border: 20cm;
            /* Espacio entre filas de tarjetas */
        }

        .card {
            width: 10cm;
            height: 15cm;
            border: 1px solid #ccc;
            border-radius: 5px;
            position: relative;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .photo {
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            position: absolute;
            top: 0;
            left: 0;
        }

        .details {
            position: absolute;
            bottom: 90px;
            left: -3px;
            color: black;
            width: 100%;
        }

        .postulante-dni {
            position: absolute;
            bottom: 117.5px;
            /* Ajusta según sea necesario */
            left: 29px;
            width: 100%;
            text-align: center;
            color: #062E47;
            font-size: 11.7px;
        }

        .postulante-apellidos {
            position: absolute;
            bottom: 100px;
            /* Ajusta según sea necesario */
            left: 15px;
            /* Ajusta según sea necesario */
            width: 100%;
            text-align: center;
            color: #062E47;
            font-size: 11px;
        }

        .postulante-nombres {
            position: absolute;
            bottom: 86px;
            /* Ajusta según sea necesario */
            left: 15px;
            /* Ajusta según sea necesario */
            width: 100%;
            text-align: center;
            color: #062E47;
            font-size: 11px;
        }

        .postulante-grado {
            position: absolute;
            bottom: 65px;
            /* Ajusta según sea necesario */
            left: 86px;
            /* Ajusta según sea necesario */
            width: 230px;
            text-align: center;
            color: #FF3131;
            font-size: 10px;
        }

        .postulante-programa {
            position: absolute;
            left: 86px;
            top: -65px;
            /* Ajusta según sea necesario */
            width: 230px;
            text-align: center;
            color: #FF3131;
            font-size: 8px;
            /* Alineación vertical */
            display: flex;
            align-items: center;
            justify-content: center;
            /* Centra verticalmente */
        }

        /* Asegura que la imagen del postulante sea posicionada correctamente */
        .details img {
            position: absolute;
            bottom: 153px;
            /* Ajusta según sea necesario */
            left: 137.5px;
            /* Ajusta según sea necesario */
            color: black;

        }
    </style>
</head>

<body>
    @php
        $postulantesCount = count($postulantes);
        $pageCount = ceil($postulantesCount / 4);
        $currentIndex = 0;
    @endphp

    @for ($i = 0; $i < $pageCount; $i++)
        <div class="page">
            @php
                $postulantesOnPage = min(4, $postulantesCount - $currentIndex);
            @endphp
            <div class="card-container">
                @for ($j = 0; $j < $postulantesOnPage; $j++)
                    @php
                        $postulante = $postulantes[$currentIndex];
                    @endphp

                    <div class="card">
                        <div class="photo">
                            <img src="{{ asset('/img/carnet-2025.png')}}" width="100%" height="100%">
                            {{-- La imagen del postulante se establece como fondo --}}
                        </div>
                        <div class="details">
                            <img src="{{ $postulante->documentos->first()->url }}" alt="Foto del Postulante" width="128.04cm" height="169.28 cm">
                            <div class="postulante-dni"><strong>{{ $postulante->num_iden }} </strong></div>
                            <div class="postulante-apellidos"><strong>{{ $postulante->ap_paterno }}
                                    {{ $postulante->ap_materno }},</strong>
                            </div>
                            <div class="postulante-nombres"><strong>{{ $postulante->nombres }} </strong></div>
                            <div class="postulante-grado">
                                <strong>{{ $postulante->inscripcion->programa->grado->nombre }}
                                </strong></div>
                            <div class="postulante-programa"><strong>{{ $postulante->inscripcion->programa->nombre }}
                                </strong>
                            </div>
                            {{-- Puedes agregar más detalles del postulante aquí --}}
                        </div>
                    </div>

                    @php
                        $currentIndex++;
                    @endphp
                @endfor
            </div>
        </div>
    @endfor

    <!-- Importar scripts de Bootstrap --->
</body>

</html>
