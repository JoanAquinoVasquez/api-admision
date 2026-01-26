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
        3 => 'SEGUNDA-ESPECIALIDAD',
    ],

    'autoridades' => [
        1 => 'Rector.', // Doctorado
        2 => 'Rector.', // Maestría
        3 => 'Decano(a).', // Segunda Especialidad
    ],

    'grados_requeridos' => [
        1 => ' grado de MAESTRO.',
        2 => ' grado de BACHILLER.',
        3 => ' TITULO PROFESIONAL.',
    ],

    'url_documentos' => [
        'default' => 'https://drive.google.com/drive/folders/1doGy-G8H-JMG-jHf_j3OuPvJV4hniObX',
        'facultades' => [
            1 => 'https://drive.google.com/drive/folders/1wKQI7J0Ms0ePhy1jXYElfKUwRSX7aNMd', // FIQUIA
            4 => 'https://drive.google.com/drive/folders/1N9tT000Ea8DyQLHg6IU4een08BMcAF7S', // FE
            9 => 'https://drive.google.com/drive/folders/1WDcIDvnrCsBr2ylZtAjT0oEmhY1U3hMJ', // FCCBB
        ]
    ],

    'programa_urls' => [
        1 => "https://drive.google.com/file/d/1LzoXpOslKL7zvqam-8P27v-H1O_2vxDG/view?usp=sharing",
        2 => "https://drive.google.com/file/d/1_hZlj3Dxq4JbQjAdUSYySJfUZdxNP9Ss/view?usp=sharing",
        3 => "https://drive.google.com/file/d/18M66ZSnYSIuKRX2UTOS-AHm1AJSq1XhJ/view?usp=sharing",
        4 => "https://drive.google.com/file/d/1AZ9QDT0kSImzwMeNPBuEzkXa9eX10HMF/view?usp=sharing",
        5 => "https://drive.google.com/file/d/1SoU7u4Er2W_KY6vCoc0JCBKANwmwtAEH/view?usp=sharing",
        6 => "https://drive.google.com/file/d/1vSMShsTPpH8tSwFE4L8Bg5X5c8VBpjom/view?usp=sharing", //Ciencias con mención en Ingeniería Hidráulica
        7 => "https://drive.google.com/file/d/10wQovk3zff1qjrJxiVuamj1_OEfIiC-3/view?usp=sharing", //Ciencias con mención en Ordenamiento Territorial y Desarrollo Urbano
        8 => "https://drive.google.com/file/d/1tltGtANQ55eSQI7ldUlu3jHBDbhm-egg/view?usp=sharing", //Gerencia de Obras y Construcción
        9 => "https://drive.google.com/file/d/1TfAzIGUbpEfpI7DPh4v6bEl33K7NUuUY/view?usp=sharing", //Ingeniería de Sistemas con Mención en Gerencia de Tecnologías de la Información y Gestión del Software
        10 => "https://drive.google.com/file/d/1NaDBJk1a8GmUnSujRKdHNta0HuymyTl4/view?usp=sharing", //Territorio y Urbanismo Sostenible
        11 => "https://drive.google.com/file/d/10wXYe9F_x15i1M8swG4f9dkpb9Doulmv/view?usp=sharing", //Ciencias con mención en Proyectos de Inversión
        12 => "https://drive.google.com/file/d/1f4IynQrZ1K9BKOMAShJ5DNMrSpjqceNu/view?usp=sharing", //Ciencias Veterinarias con Mención en Salud Animal
        13 => "https://drive.google.com/file/d/1ak_-B2slpHWrFlHHbHsoLbDIbOra2kqR/view?usp=sharing", //Administración con mención en Gerencia Empresarial
        14 => "https://drive.google.com/file/d/1eAibk-3ng4ULfbla2gsEHuNM265TiVth/view?usp=sharing", //Administración
        15 => "https://drive.google.com/file/d/1gFgE-0R1Fqt-labTsRJvzw2aMvCfcuEP/view?usp=sharing", //Ciencias de Enfermería
        16 => "https://drive.google.com/file/d/1hxdqWROJg042j4Sm_icIsafnLHU-ofzK/view?usp=sharing", //Ciencias de Enfermería
        17 => "https://drive.google.com/file/d/10ybXs1jyNny7AenUsYIhE9vHjQy1a36V/view?usp=sharing", //Área Organizacional y de Gestión Enfermera Especialista en Administración
        18 => "https://drive.google.com/file/d/14zPKWfMxTRFV6QUK-VgUmwI5K4gO6RI6/view?usp=sharing", //Salud Familiar
        19 => "https://drive.google.com/file/d/1UZFR_-BT0gGQ1lKccXd_xO0qMM_cSGtd/view?usp=sharing", //Crecimiento y Desarrollo
        20 => "https://drive.google.com/file/d/1pC7jUIfrWcvLtMSApcWeHNPBS117hlWe/view?usp=sharing", //Adulto
        21 => "https://drive.google.com/file/d/1x6BY9p61310vicD8qJhZE0zUHBNC9f2V/view?usp=sharing", //Neonatología
        22 => "https://drive.google.com/file/d/1vlKd3RbhT7-X3Qh2J-9OXD2mna9OTyxC/view?usp=sharing", //Cuidados Hospitalarios
        23 => "https://drive.google.com/file/d/1i2_EL0oOD47R9Jyvn0esMR_4fXWWuV3H/view?usp=sharing", //Procedimientos Endoscópicos
        24 => "https://drive.google.com/file/d/1e94RKH0xvKCdoSlTDIc0x0m8402SIzvd/view?usp=sharing", //Dialisis
        25 => "https://drive.google.com/file/d/1eBunzdiDcnfUefU6CTmQQoe44mzXpHbw/view?usp=sharing", //Oncología
        26 => "https://drive.google.com/file/d/1kNuaiLQDaafLukDQlhHkfZFu2Sd4RTOt/view?usp=sharing", //Pediatrica
        27 => "https://drive.google.com/file/d/1crWOuSUUrprSpCb2yKEdKU-tF_zKdMRC/view?usp=sharing", //Salud Ocupacional
        28 => "https://drive.google.com/file/d/1lx40TOExjZD0Sh0I_NjetPFr4l2Y7mE_/view?usp=sharing", //Centro Quirúrgico
        29 => "https://drive.google.com/file/d/1N1PxvogBaEbG5g0nutlhpozIYjN3c4d8/view?usp=sharing", //Ingeniería Mecánica y Eléctrica con mención en Energía
        30 => "https://drive.google.com/file/d/1HshcwKfEBqhSkL10ImvJFBpdcWmRgADu/view?usp=sharing", //Doc. Ingeniería Mecánica
        31 => "https://drive.google.com/file/d/1udmqpVGEPMz6CVrojU89igDWQ3fTDS96/view?usp=sharing", //Civil y Comercial
        32 => "https://drive.google.com/file/d/1N2JYURN-th6wfYZ2gH8cFyFJ_qfYq3Ls/view?usp=sharing", //Constitucional
        33 => "https://drive.google.com/file/d/1ksKTGS52hDISz7gpbH1fCmRAwugDDhS2/view?usp=sharing", //Penal
        34 => "https://drive.google.com/file/d/173lgEfLyIXZDCFBtO5H_9LofeDFmbslT/view?usp=sharing", //Dr. Derecho
        35 => "https://drive.google.com/file/d/1zMavs7pd5Z62EuufRlsgQT7MIgZJHwuU/view?usp=sharing", //Hidricos
        36 => "https://drive.google.com/file/d/1d8BfSCCRyvABYWb5XU5QFmzaohh6n22g/view?usp=sharing", //Ingles
        37 => "https://drive.google.com/file/d/1xdNXe7s61i_X--d1kb0hKhCMhyo0oZCA/view?usp=sharing", //Docencia y Gestión
        38 => "https://drive.google.com/file/d/1fzlxEZL52bq4h4wkV1LDS-xzEwbyMKLW/view?usp=sharing", //Gestión Pública y Gerencia Social
        39 => "https://drive.google.com/file/d/14eoma7JsVKTsY7sD6IqYrdGV3a2ULFN2/view?usp=sharing", //Educación con mención en Tecnologías de la Información
        40 => "https://drive.google.com/file/d/1I8V1yv8bd_kJMyEKu2qBEuQsqcfcBY4V/view?usp=sharing", //Gerencia Educativa Estratégica
        41 => "https://drive.google.com/file/d/17xlmcArjZyvlRUsQZ-753GhcbDm-7ISQ/view?usp=sharing", //Investigación y Docencia
        42 => "https://drive.google.com/file/d/16U3Vo4HBOMIyInicDyYL0bj3kGONw7ZL/view?usp=sharing", //Dr. Sociologia
        43 => "https://drive.google.com/file/d/16U3Vo4HBOMIyInicDyYL0bj3kGONw7ZL/view?usp=sharing", //Dr. Educación
        44 => "https://drive.google.com/file/d/1LRc41qIYa_7WqrRXWfaNp6TtirldbyiB/view?usp=sharing", //Dr. Ambientales
    ],
    'cronograma' => [
        'examen_admision' => 'domingo 27 de Abril',
        'inicio_conceptos' => '17 de febrero',
        'periodo' => '2026-I',
        // Control de Etapas (Automatización)
        // Si 'etapa_manual' tiene valor, se usará ese. Si es null, se calculará por fechas.
        'etapa_manual' => null, // Valores: 'PREINSCRIPCION', 'INSCRIPCION', 'CERRADO', o null

        'fechas_control' => [
            // Preinscripción
            'inicio_preinscripcion' => '2026-01-22',
            'fin_preinscripcion' => '2026-02-25',

            // Inscripción
            'inicio_inscripcion' => '2026-02-26',
            'fin_inscripcion' => '2026-04-22',

            // Evaluación (Examen y Entrevista)
            'inicio_evaluacion' => '2025-04-21',
            'fin_evaluacion' => '2025-04-29',

            // Resultados
            'resultados_publicacion' => '2025-04-30',
        ],
    ],
];
