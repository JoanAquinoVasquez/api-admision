<?php

return [
    'grados' => [
        'doctorado' => 1,
        'maestria' => 2,
        'segunda_especialidad' => 3,
    ],

    'carpetas_drive' => [
        1 => 'DOCTORADO',
        2 => 'MAESTRIA',
        3 => 'SEGUNDA-ESPECIALIDAD-PROFESIONAL',
    ],

    'autoridades' => [
        1 => 'Rector.', // Doctorado
        2 => 'Rector.', // Maestría
        3 => 'Decano(a).', // Segunda Especialidad
    ],

    'grados_requeridos' => [
        1 => ' grado de MAESTRO',
        2 => ' grado de BACHILLER',
        3 => ' TITULO PROFESIONAL',
    ],

    'url_documentos' => [
        'default' => 'https://drive.google.com/drive/folders/1KL5JUaprIHlCo6MNxVPwtk1VVq3FKvFZ?usp=sharing',
        'facultades' => [
            1 => 'https://drive.google.com/drive/folders/1_XarGM36EErGcNeTUOqtry7uuYi-rUW0?usp=sharing', // FIQUIA
            4 => 'https://drive.google.com/drive/folders/1N9tT000Ea8DyQLHg6IU4een08BMcAF7S', // FE
            9 => 'https://drive.google.com/drive/folders/1FqRzE0vyrgHH1j5wQxyr5K0eK_gONe12?usp=sharing', // FCCBB
        ]
    ],
    'email_images' => [
        'logo_epg' => 'https://drive.usercontent.google.com/download?id=1XdEM7PcBXuRfkkdsBp3MqlDRz4n-GsJf&export=view&authuser=0',
        'logo_unprg' => 'https://drive.usercontent.google.com/download?id=1ph6WsmccjVnNwkK70-ntCVKKdHGJucS5&export=view&authuser=0',
        'barra_colores' => 'https://drive.google.com/uc?export=view&id=1bXqmf32tJmjzpoG90fJJwkGN9N2_fKyQ',
    ],
    'cronograma' => [
        'examen_admision' => 'domingo 19 de Abril',
        'inicio_conceptos' => '17 de febrero',
        'periodo' => '2026-I',
        // Control de Etapas (Automatización)
        // Si 'etapa_manual' tiene valor, se usará ese. Si es null, se calculará por fechas.
        'etapa_manual' => null, // Valores: 'PREINSCRIPCION', 'INSCRIPCION', 'CERRADO', o null

        'fechas_control' => [
            // Preinscripción
            'inicio_preinscripcion' => '2026-01-22',
            'fin_preinscripcion' => '2026-02-01',

            // Inscripción
            'inicio_inscripcion' => '2026-02-02',
            'fin_inscripcion' => '2026-04-15',

            // Evaluación (Examen y Entrevista)
            'inicio_evaluacion' => '2026-04-16',
            'fin_evaluacion' => '2026-04-18',

            // Resultados
            'resultados_publicacion' => '2026-04-19',
        ],
    ],
];
