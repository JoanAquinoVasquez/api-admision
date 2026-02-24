<?php

namespace Database\Seeders;

use App\Models\Programa;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programas = [
            // MAESTRÍAS - FIQIA (2 programas)
            [
                'nombre' => 'Ciencias con mención en Gestión de la Calidad e Inocuidad de Alimentos',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 1, //FIQIA
                'concepto_pago_id' => 1,
                'vacantes' => 45,
                'plan_estudio' => 'https://drive.google.com/file/d/1M2sk3B_hdpjXPwHNhjVjDrDKn0jZiqmk/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1-Zk6wnka23A7sZLSMizTAFcYLrdYf_cU/view?usp=sharing',
            ],
            [
                'nombre' => 'Ciencias con mención en Ingeniería de Procesos Industriales',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 1, //FIQIA
                'concepto_pago_id' => 1,
                'vacantes' => 45,
                'plan_estudio' => 'https://drive.google.com/file/d/1WBGzXwLJOSVwcYylog2FGGqULavpiUse/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1jGZAbDX7mijmCpKs1OG2ixRC7pWxyr-z/view?usp=sharing',
            ],

            // SEGUNDAS ESPECIALIDADES - FIQIA (2 programas)
            [
                'nombre' => 'Gestión Ambiental - PRESENCIAL',
                'grado_id' => 3, // SEGUNDA ESPECIALIDAD
                'facultad_id' => 1, // FIQUIA
                'concepto_pago_id' => 3,
                'vacantes' => 36,
                'plan_estudio' => 'https://drive.google.com/file/d/1VLGbJY-yuulLQbRVID3EHSwBI8c_yFjj/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1BqqKFWs85XGpJTxOeV1wdqzCh9cbBfmf/view?usp=sharing',
            ],
            [
                'nombre' => 'Gestión Ambiental - VIRTUAL',
                'grado_id' => 3, // SEGUNDA ESPECIALIDAD
                'facultad_id' => 1, // FIQUIA
                'concepto_pago_id' => 3,
                'vacantes' => 36,
                'plan_estudio' => 'https://drive.google.com/file/d/1VLGbJY-yuulLQbRVID3EHSwBI8c_yFjj/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1BqqKFWs85XGpJTxOeV1wdqzCh9cbBfmf/view?usp=sharing',
            ],
            [
                'nombre' => 'Educación Ambiental Intercultural - PRESENCIAL',
                'grado_id' => 3, // SEGUNDA ESPECIALIDAD
                'facultad_id' => 1, //FIQUIA
                'concepto_pago_id' => 3,
                'vacantes' => 30,
                'plan_estudio' => 'https://drive.google.com/file/d/1WCY2MqGLuI0eVfJhjnHQXezxUUmtHJnR/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1DMa94BgyzVrRoveLbRf0GSETh2halzv0/view?usp=sharing',
            ],
            [
                'nombre' => 'Educación Ambiental Intercultural - VIRTUAL',
                'grado_id' => 3, // SEGUNDA ESPECIALIDAD
                'facultad_id' => 1, //FIQUIA
                'concepto_pago_id' => 3,
                'vacantes' => 30,
                'plan_estudio' => 'https://drive.google.com/file/d/1WCY2MqGLuI0eVfJhjnHQXezxUUmtHJnR/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1DMa94BgyzVrRoveLbRf0GSETh2halzv0/view?usp=sharing',
            ],

            // MAESTRÍAS - FICSA (4 programas)
            [
                'nombre' => 'Ciencias con mención en Ingeniería Hidráulica',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 2, //FICSA
                'concepto_pago_id' => 1,
                'vacantes' => 35,
                'plan_estudio' => 'https://drive.google.com/file/d/1vqhYeEhcidBLs7xD_g5SUufR0NTMGMjY/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1pj0a3aNyNc9voYvuuHdabdkzv4lSeCPP/view?usp=sharing',
            ],
            [
                'nombre' => 'Ciencias con mención en Ordenamiento Territorial y Desarrollo Urbano',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 2, //FICSA
                'concepto_pago_id' => 1,
                'vacantes' => 42,
                'plan_estudio' => 'https://drive.google.com/file/d/152QrvgJRPSkR8SLIMwxcs8-_qd1wIn4O/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1_XiVdm5zJMl-3Slmfmr2IzfJdPFDEPNw/view?usp=sharing',
            ],
            [
                'nombre' => 'Gerencia de Obras y Construcción',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 2, //FICSA
                'concepto_pago_id' => 1,
                'vacantes' => 64,
                'plan_estudio' => 'https://drive.google.com/file/d/1nkVVxINMIjADWIEg_v63REo35rXUKm4q/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1goGCXl1_ascGklAC-CZgUv32dwt5ScPt/view?usp=sharing',
            ],
            [
                'nombre' => 'Ingeniería de Sistemas con Mención en Gerencia de Tecnologías de la Información y Gestión del Software',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 2, //FICSA
                'concepto_pago_id' => 1,
                'vacantes' => 25,
                'plan_estudio' => 'https://drive.google.com/file/d/1tVC96_jpQVQOHikT0l__RRYfcgxGnoGc/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1MsDDPwLDGjKT_x-Z-Cm72c36JvFUKWUJ/view?usp=sharing',
            ],

            // DOCTORADO - FICSA (1 programa)
            [
                'nombre' => 'Territorio y Urbanismo Sostenible',
                'grado_id' => 1, // DOCTORADO
                'facultad_id' => 2, //FICSA
                'concepto_pago_id' => 1,
                'vacantes' => 43,
                'plan_estudio' => 'https://drive.google.com/file/d/1T7SyNza2NYOpCcn9Yp9hA9AXvpFztyEX/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1LlfIfzGsa8AG_Ma1lnwV8tkPqOqONjQ8/view?usp=sharing',
            ],

            // MAESTRÍAS - FACEAC (2 programas)
            [
                'nombre' => 'Ciencias con mención en Proyectos de Inversión',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 3, //FACEAC
                'concepto_pago_id' => 1,
                'vacantes' => 36,
                'plan_estudio' => 'https://drive.google.com/file/d/1ipt0QgffpxzwGrwrd3OZ7D_3AaLxgIGs/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1_a4DsXJfNMBF6t1Z0DtPUzOhUweWduS4/view?usp=sharing',
            ],
            [
                'nombre' => 'Administración con mención en Gerencia Empresarial',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 3, //FACEAC
                'concepto_pago_id' => 1,
                'vacantes' => rand(35, 50),
                'plan_estudio' => 'https://drive.google.com/file/d/1pY3ZgtrtKyPN1KdSXJFBJQT8qDSwMUu5/view',
                'brochure' => 'https://drive.google.com/file/d/1AMK7evewvD6MeErpr6B8HqQf2F7_172m/view?usp=sharing',
            ],

            // DOCTORADO - FACEAC (1 programa)
            [
                'nombre' => 'Administración',
                'grado_id' => 1, // DOCTORADO
                'facultad_id' => 3, //FACEAC
                'concepto_pago_id' => 1,
                'vacantes' => 36,
                'plan_estudio' => 'https://drive.google.com/file/d/1qmHt8GXNotvXNSAl-OXQ7j4h2ehe_A8S/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1mchSEUkqce31w6fACWrqnoz5VtedjNXK/view?usp=sharing',
            ],

            // MAESTRÍA - FE (1 programa)
            [
                'nombre' => 'Ciencias de Enfermería',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 4, // FE
                'concepto_pago_id' => 1,
                'vacantes' => 36,
                'plan_estudio' => 'https://drive.google.com/file/d/1GAGGgiEjPWP63nbFNTykmudTO83lRNHm/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1IrbsRdHShOF7P0EkC_fVg67gSxC69lAl/view?usp=sharing',
            ],

            // DOCTORADO - FE (1 programa)
            [
                'nombre' => 'Ciencias de Enfermería',
                'grado_id' => 1, // DOCTORADO
                'facultad_id' => 4, // FE
                'concepto_pago_id' => 1,
                'vacantes' => 36,
                'plan_estudio' => 'https://drive.google.com/file/d/1k5xYvxreshkHxBR_8heqiab8kolvfDFW/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/11eFgYi8wXOrpwD8e4bVpEV6v0BWbsOOD/view?usp=sharing',
            ],

            // MAESTRÍA - FIME (1 programa)
            [
                'nombre' => 'Ciencias de la Ingeniería Mecánica y Eléctrica con mención en Energía',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 5, // FIME
                'concepto_pago_id' => 1,
                'vacantes' => 48,
                'plan_estudio' => 'https://drive.google.com/file/d/1MQlqMNOVc0o6XUtRdl5d9-e6G-zdbTcp/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1sjERKOwUIl5i8Skj-Newr11qg0SRTJWZ/view?usp=sharing',
            ],

            // DOCTORADO - FIME (1 programa)
            [
                'nombre' => 'Ciencias de la Ingeniería Mecánica y Eléctrica con mención en Energía',
                'grado_id' => 1, // DOCTORADO
                'facultad_id' => 5,  // FIME
                'concepto_pago_id' => 1,
                'vacantes' => 48,
                'plan_estudio' => 'https://drive.google.com/file/d/199DjP8knqOqZNPW3tHdX_W6L0rMxdlmx/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1Wt2se_g4CiIEzgLFmSZgQ5mubceD2SP-/view?usp=sharing',
            ],

            // MAESTRÍAS - FDCP (3 programas)
            [
                'nombre' => 'Derecho con mención en Civil y Comercial',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 6, // FDCP
                'concepto_pago_id' => 1,
                'vacantes' => 38,
                'plan_estudio' => 'https://drive.google.com/file/d/1ylWlix6QG5YLuHrggyzuz6AymudqRVQD/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1JmoX9_JHt-1RG6koS712rM2wAXDNPvoL/view?usp=sharing',
            ],
            [
                'nombre' => 'Derecho con mención en Derecho Constitucional y Procesal Constitucional',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 6, // FDCP
                'concepto_pago_id' => 1,
                'vacantes' => 38,
                'plan_estudio' => 'https://drive.google.com/file/d/1XO3ER4y7GGOi_UwkAwmunbAeHfA_SIKr/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1OlW088lOyTG0oN24iQB6OPEOjJLMw-Ri/view?usp=sharing',
            ],
            [
                'nombre' => 'Derecho con mención en Derecho Penal y Procesal Penal',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 6, // FDCP
                'concepto_pago_id' => 1,
                'vacantes' => 38,
                'plan_estudio' => 'https://drive.google.com/file/d/1JWF-_tAPuisZvS_WT5EtT-lzbay9Zujf/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1Tx8xb9e25JcK00CrwwJy7haj2YuxqmrF/view?usp=sharing',
            ],

            // DOCTORADO - FDCP (1 programa)
            [
                'nombre' => 'Derecho y Ciencia Política',
                'grado_id' => 1, // DOCTORADO
                'facultad_id' => 6, // FDCP
                'concepto_pago_id' => 1,
                'vacantes' => 31,
                'plan_estudio' => 'https://drive.google.com/file/d/1xz0JZil_nV3husR_-p24nMN0xeqDRn5J/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1gizAhDey_81B1igX4ogd7aAJDWJ_0UZs/view?usp=sharing',
            ],

            // MAESTRÍA - FIA (1 programa)
            [
                'nombre' => 'Gestión Integrada de los Recursos Hídricos',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 7, // FIA
                'concepto_pago_id' => 1,
                'vacantes' => 40,
                'plan_estudio' => 'https://drive.google.com/file/d/1ntyUJGBJnRmEZbSZN7-PyaFzdDVpHRzb/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1zufFqYbBabw_W3RoHXXvaJeN2Xozh_CL/view?usp=sharing',
            ],
            [
                'nombre' => 'Ciencias de la Educación con mención en Docencia y Gestión Universitaria',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 8, // FASCHE
                'concepto_pago_id' => 1,
                'vacantes' => 60,
                'plan_estudio' => 'https://drive.google.com/file/d/15QVBFAAEneoi3YqbRRPKA298Hm1TPtIo/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1giJ7tiTe4h3MxlqX7aUUq9BVB-yt2u2U/view?usp=sharing',
            ],
            [
                'nombre' => 'Ciencias Sociales con mención en Gestión Pública y Gerencia Social',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 8, // FASCHE
                'concepto_pago_id' => 1,
                'vacantes' => 60,
                'plan_estudio' => 'https://drive.google.com/file/d/1on4UnheUKsVVJXlm_YPd-iZnua8DnyZN/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1h9LcHN6GY-v1hbYM9ov7Dz6IhMWtUzK1/view?usp=sharing',
            ],
            [
                'nombre' => 'Ciencias de la Educación con mención en Gerencia Educativa Estratégica',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 8, // FASCHE
                'concepto_pago_id' => 1,
                'vacantes' => 30,
                'plan_estudio' => 'https://drive.google.com/file/d/1lailKNJ6GBpVAG43rbmUEFNo4KWQW3KE/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/14IU_hVTzUHrIXLMGBOyz8i-S0c6EIvgl/view?usp=sharing',
            ],
            [
                'nombre' => 'Ciencias de la Educación con mención en Investigación y Docencia',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 8, // FASCHE
                'concepto_pago_id' => 1,
                'vacantes' => 60,
                'plan_estudio' => 'https://drive.google.com/file/d/1rvND8TleYTsku6IT6PR8x1yJGuSncK44/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1TxjBw4Aq2ZcRxGprVui-j3K2YCBDGkzR/view?usp=sharing',
            ],

            // DOCTORADOS - FACHSE (1 programa)
            [
                'nombre' => 'Ciencias de la Educación',
                'grado_id' => 1, // DOCTORADO
                'facultad_id' => 8, // FASCHE
                'concepto_pago_id' => 1,
                'vacantes' => 60,
                'plan_estudio' => 'https://drive.google.com/file/d/1Wzx3IriRIozl5grMZ9A-CIp7MgLXt9vk/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1iqzF-zemYf_fAbEHoSVs3vL_P_ZHH7y8/view?usp=sharing',
            ],

            // SEGUNDA ESPECIALIDAD - FCCBB (1 programa)
            [
                'nombre' => 'Microbiología Clínica',
                'grado_id' => 3, // SEGUNDA ESPECIALIDAD
                'facultad_id' => 9, //FCCBB
                'concepto_pago_id' => 1,
                'vacantes' => 36,
                'plan_estudio' => 'https://drive.google.com/file/d/1u8jmJCkEUCDXcBQoljqMl0phrijnvWY9/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1tIc1UMBJ-XJFvRto70yPtcjV2Somf070/view?usp=sharing',
            ],

            // DOCTORADO - FCCBB (1 programa)
            [
                'nombre' => 'Ciencias Ambientales',
                'grado_id' => 1, // DOCTORADO
                'facultad_id' => 9, // FCCBB
                'concepto_pago_id' => 1,
                'vacantes' => 36,
                'plan_estudio' => 'https://drive.google.com/file/d/1RIa2RnyjD_j2mdAmd2yGhqPk-cz5Q79w/view?usp=sharing',
                'brochure' => 'https://drive.google.com/file/d/1lmKGW0JRQVrtECeXFO7M3O9DMk6rOT4L/view?usp=sharing',
            ],

            // MAESTRÍA - FMV (1 programa)
            [
                'nombre' => 'Ciencias Veterinarias con Mención en Salud Animal',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 10, //FMV
                'concepto_pago_id' => 1,
                'vacantes' => rand(35, 50),
                'plan_estudio' => 'https://drive.google.com/file/d/1EF9ycTZvjWhSkjCl4N-0jk2CEpr9GNZ4/view',
                'brochure' => 'https://drive.google.com/file/d/1RL0ZQT2hopWpQNjP_PFxX5j_Yw9tIFuH/view?usp=sharing',
            ],

            // MAESTRÍAS - FAG (3 programas)
            [
                'nombre' => 'Ciencias Agrarias con mención en Agroexportación Sostenible',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 11, // FAG
                'concepto_pago_id' => 1,
                'vacantes' => 36,
                'plan_estudio' => null, // Sin plan de estudio aún
                'brochure' => 'https://drive.google.com/file/d/1qWLS_ECdzd5HNC1Jfpicv7fuutjMmMrD/view?usp=sharing',
            ],
            [
                'nombre' => 'Manejo Sostenible de Suelos',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 11, // FAG
                'concepto_pago_id' => 1,
                'vacantes' => 36,
                'plan_estudio' => null, // Sin plan de estudio aún
                'brochure' => 'https://drive.google.com/file/d/1eUdRtQRIvG0XVD6VK3UmnWp46Pz_uMQ4/view?usp=sharing',
            ],
            [
                'nombre' => 'Ciencias con mención en Manejo Integrado de Plagas y Enfermedades',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 11, // FAG
                'concepto_pago_id' => 1,
                'vacantes' => 36,
                'plan_estudio' => null, // Sin plan de estudio aún
                'brochure' => 'https://drive.google.com/file/d/1HmM46rU6oZkdvVYAuKyIfQmpt58k1ncH/view?usp=sharing',
            ],
        ];

        foreach ($programas as $programaData) {
            Programa::create($programaData);
        }
    }
}
