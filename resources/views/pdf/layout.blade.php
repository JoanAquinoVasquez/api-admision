<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('document_title', 'Reporte Institucional')</title>
    <style>
        /* Configuración base de la hoja para Layout Fijo en DomPDF */
        @page {
            margin: 140px 40px 60px 40px; /* Top, Right, Bottom, Left. Amplio Top para el Header Fijo */
        }
        
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #2c3e50;
            margin: 0;
            padding: 0;
        }

        /* --- CABECERA INSTITUCIONAL FIJA --- */
        header {
            position: fixed;
            top: -120px;
            left: 0;
            right: 0;
            height: 90px;
            border-bottom: 2px solid #003366;
            text-align: center;
        }

        table.header-table {
            width: 100%;
            border: none;
            margin: 0;
            padding: 0;
        }
        
        table.header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        
        .logo-unprg { height: 75px; } /* Escala proporcional basada en la altura */
        .logo-epg { height: 75px; }

        .header-text {
            text-align: center;
        }
        
        .header-text h1 {
            font-size: 16px;
            margin: 0;
            color: #003366;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        
        .header-text h2 {
            font-size: 14px;
            margin: 3px 0 0 0;
            color: #333333;
            font-weight: bold;
        }

        /* --- PIE DE PÁGINA FIJO --- */
        footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            height: 30px;
            border-top: 1px solid #bdc3c7;
            text-align: center;
            font-size: 9px;
            color: #7f8c8d;
        }

        /* --- ESTILOS DEL CONTENIDO (TABLAS Y TÍTULOS) --- */
        .report-title {
            text-align: center;
            margin-top: 0px;
            margin-bottom: 15px;
        }
        
        .report-title h3 {
            font-size: 15px;
            margin: 0;
            font-weight: bold;
            text-transform: uppercase;
            color: #000000;
        }

        .report-title h4 {
            font-size: 12px;
            margin: 5px 0 0 0;
            font-weight: normal;
            color: #34495e;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
            font-size: 10px;
        }
        
        table.data-table th, table.data-table td {
            border: 1px solid #7f8c8d;
            padding: 6px 8px;
        }
        
        table.data-table th {
            background-color: #ecf0f1;
            color: #003366;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }

        table.data-table tbody tr:nth-child(even) {
            background-color: #fbfcfc;
        }
        
        /* Contenedores de Información Adicional (Ej. Fechas, Director) */
        .info-panel {
            margin-bottom: 15px;
            font-size: 11px;
            color: #2c3e50;
        }
        .info-panel strong {
            color: #000;
        }
        .info-panel p {
            margin: 3px 0;
        }

        /* Firmas */
        .firma-container {
            margin-top: 40px;
            text-align: center;
            page-break-inside: avoid; /* Previene que la firma se divida en dos hojas */
        }
        
        .firma-linea {
            display: inline-block;
            border-top: 1px solid #000;
            width: 250px;
            margin-top: 50px;
            padding-top: 5px;
        }
        
        .firma-text {
            margin: 2px 0;
            font-size: 11px;
        }

        /* --- CLASES UTILITARIAS --- */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .fw-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .page-break { page-break-after: always; }
        .mt-1 { margin-top: 10px; }
        .mt-2 { margin-top: 20px; }
        .mb-1 { margin-bottom: 10px; }
        .mb-2 { margin-bottom: 20px; }
        .w-100 { width: 100%; }
        
        /* Paginación automática: requiere un SPAN con clase page-number */
        .page-number:before {
            content: "Página " counter(page);
        }
    </style>
    @yield('custom_css')
</head>
<body>

    <!-- CABECERA FIJA SUPERIOR -->
    <header>
        <table class="header-table">
            <tr>
                <td style="width: 20%; text-align: left;">
                    <img src="{{ public_path('img/isotipo_color_unprg.webp') }}" class="logo-unprg" alt="UNPRG">
                </td>
                <td style="width: 60%;" class="header-text">
                    <h1>UNIVERSIDAD NACIONAL PEDRO RUIZ GALLO</h1>
                    <h2>ESCUELA DE POSGRADO</h2>
                    <h2 style="font-weight: normal; font-size: 13px; margin-top: 5px; color: #555555;">ADMISIÓN {{ config('admission.cronograma.periodo') }}</h2>
                </td>
                <td style="width: 20%; text-align: right;">
                    <img src="{{ public_path('img/isotipo_color_epg.webp') }}" class="logo-epg" alt="EPG">
                </td>
            </tr>
        </table>
    </header>

    <!-- PIE DE PÁGINA FIJO -->
    <footer>
        <table style="width: 100%; border: none; padding: 0; color: #7f8c8d; font-size: 9px;">
            <tr>
                <td style="text-align: left; width: 33%; border: none;">Reporte automatizado</td>
                <td style="text-align: center; width: 34%; border: none;">Sistema de Admisión EPG</td>
                <td style="text-align: right; width: 33%; border: none;"><span class="page-number"></span></td>
            </tr>
        </table>
    </footer>

    <!-- CONTENIDO PRINCIPAL -->
    <main>
        @hasSection('report_title')
        <div class="report-title">
            <h3>@yield('report_title')</h3>
            @hasSection('report_subtitle')
                <h4>@yield('report_subtitle')</h4>
            @endif
        </div>
        @endif

        @yield('content')
    </main>

</body>
</html>
