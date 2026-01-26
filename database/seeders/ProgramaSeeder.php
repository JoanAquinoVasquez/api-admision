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
            [
                'nombre' => 'Ciencias con mención en Gestión de la Calidad e Inocuidad de Alimentos',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 1, //FIQUIA
                'concepto_pago_id' => 1,
                'vacantes' => 45,
            ],
            [
                'nombre' => 'Ciencias con mención en Ingeniería de Procesos Industriales',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 1, //FIQUIA
                'concepto_pago_id' => 1,
                'vacantes' => 45,
            ],
            [
                'nombre' => 'Microbiología Clínica',
                'grado_id' => 3, // SEGUNDA ESPECIALIDAD
                'facultad_id' => 9, //FCCBB
                'concepto_pago_id' => 1,
                'vacantes' => 36,
            ],
            [
                'nombre' => 'Gestión Ambiental - PRESENCIAL',
                'grado_id' => 3, // SEGUNDA ESPECIALIDAD
                'facultad_id' => 1, // FIQUIA
                'concepto_pago_id' => 3,
                'vacantes' => 36,
            ],
             [
                'nombre' => 'Gestión Ambiental - VIRTUAL',
                'grado_id' => 3, // SEGUNDA ESPECIALIDAD
                'facultad_id' => 1, // FIQUIA
                'concepto_pago_id' => 3,
                'vacantes' => 36,
            ],
            [
                'nombre' => 'Educación Ambiental Intercultural - PRESENCIAL',
                'grado_id' => 3, // SEGUNDA ESPECIALIDAD
                'facultad_id' => 1, //FIQUIA
                'concepto_pago_id' => 3,
                'vacantes' => 30,
            ],
            [
                'nombre' => 'Educación Ambiental Intercultural - VIRTUAL',
                'grado_id' => 3, // SEGUNDA ESPECIALIDAD
                'facultad_id' => 1, //FIQUIA
                'concepto_pago_id' => 3,
                'vacantes' => 30,
            ],
            [
                'nombre' => 'Ciencias con mención en Ingeniería Hidráulica',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 2, //FICSA
                'concepto_pago_id' => 1,
                'vacantes' => 35,
            ],
            [
                'nombre' => 'Ciencias con mención en Ordenamiento Territorial y Desarrollo Urbano',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 2, //FICSA
                'concepto_pago_id' => 1,
                'vacantes' => 42,
            ],
            [
                'nombre' => 'Gerencia de Obras y Construcción',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 2, //FICSA
                'concepto_pago_id' => 1,
                'vacantes' => 64,
            ],
            [
                'nombre' => 'Ingeniería de Sistemas con Mención en Gerencia de Tecnologías de la Información y Gestión del Software',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 2, //FICSA
                'concepto_pago_id' => 1,
                'vacantes' =>25,
            ],
            [
                'nombre' => 'Territorio y Urbanismo Sostenible',
                'grado_id' => 1, // DOCTORADO
                'facultad_id' => 2, //FICSA
                'concepto_pago_id' => 1,
                'vacantes' => 43,
            ],
            [
                'nombre' => 'Ciencias con mención en Proyectos de Inversión',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 3, //FACEAC
                'concepto_pago_id' => 1,
                'vacantes' => 36,
            ],
            [
                'nombre' => 'Ciencias Veterinarias con Mención en Salud Animal',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 10, //FMV
                'concepto_pago_id' => 1,
                'vacantes' => rand(35, 50),
            ],
            [
                'nombre' => 'Administración con mención en Gerencia Empresarial',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 3, //FACEAC
                'concepto_pago_id' => 1,
                'vacantes' => rand(35, 50),
            ],
            [
                'nombre' => 'Administración',
                'grado_id' => 1, // DOCTORADO
                'facultad_id' => 3, //FACEAC
                'concepto_pago_id' => 1,
                'vacantes' => 36,
            ],
            [
                'nombre' => 'Ciencias de Enfermería',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 4, // FE
                'concepto_pago_id' => 1,
                'vacantes' => 36,
            ],
            [
                'nombre' => 'Ciencias de Enfermería',
                'grado_id' => 1, // DOCTORADO
                'facultad_id' => 4, // FE
                'concepto_pago_id' => 1,
                'vacantes' => 36,
            ],
            // [
            //     'nombre' => 'Área Organizacional y de Gestión Enfermera Especialista en Administración y Gerencia en Salud con mención en Gestión de la Calidad',
            //     'grado_id' => 3, // SEGUNDA ESPECIALIDAD
            //     'facultad_id' => 4, // FE
            //     'concepto_pago_id' => 2,
            //     'vacantes' => 25,
            // ],
            // [
            //     'nombre' => 'Área de Salud Pública y Comunitaria Enfermera Especialista en Salud Pública con mención en Salud Familiar',
            //     'grado_id' => 3, // SEGUNDA ESPECIALIDAD
            //     'facultad_id' => 4, // FE
            //     'concepto_pago_id' => 2,
            //     'vacantes' => 25,
            // ],
            // [
            //     'nombre' => 'Área del cuidado a la Persona Enfermera Especialista en Cuidado Integral Infantil con Mención en Crecimiento y Desarrollo',
            //     'grado_id' => 3, // SEGUNDA ESPECIALIDAD
            //     'facultad_id' => 4, // FE
            //     'concepto_pago_id' => 2,
            //     'vacantes' => 25,
            // ],
            // [
            //     'nombre' => 'Área del cuidado a la Persona Enfermera Especialista en Cuidados Críticos con mención en Adulto',
            //     'grado_id' => 3, // SEGUNDA ESPECIALIDAD
            //     'facultad_id' => 4, // FE
            //     'concepto_pago_id' => 2,
            //     'vacantes' => 25,
            // ],
            // [
            //     'nombre' => 'Área del cuidado a la Persona Enfermera Especialista en Cuidados Críticos con mención en Neonatología',
            //     'grado_id' => 3, // SEGUNDA ESPECIALIDAD
            //     'facultad_id' => 4, // FE
            //     'concepto_pago_id' => 2,
            //     'vacantes' => 25,
            // ],
            // [
            //     'nombre' => 'Área del cuidado a la Persona Enfermera Especialista en Emergencia y Desastres con mención en Cuidados Hospitalarios',
            //     'grado_id' => 3, // SEGUNDA ESPECIALIDAD
            //     'facultad_id' => 4, // FE
            //     'concepto_pago_id' => 2,
            //     'vacantes' => 25,
            // ],
            // [
            //     'nombre' => 'Área del cuidado a la Persona Enfermera Especialista en Gastroenterología y Procedimientos Endoscópicos con mención En Procedimientos Endoscópicos',
            //     'grado_id' => 3, // SEGUNDA ESPECIALIDAD
            //     'facultad_id' => 4, // FE
            //     'concepto_pago_id' => 2,
            //     'vacantes' => 25,
            // ],
            // [
            //     'nombre' => 'Área del cuidado a la Persona Especialista en Enfermería Nefrológica y Urológica con mención en Diálisis',
            //     'grado_id' => 3, // SEGUNDA ESPECIALIDAD
            //     'facultad_id' => 4, // FE
            //     'concepto_pago_id' => 2,
            //     'vacantes' => 25,
            // ],
            // [
            //     'nombre' => 'Área del cuidado a la Persona Especialista en Enfermería Oncológica con mención en Oncología',
            //     'grado_id' => 3, // SEGUNDA ESPECIALIDAD
            //     'facultad_id' => 4, // FE
            //     'concepto_pago_id' => 2,
            //     'vacantes' => 25,
            // ],
            // [
            //     'nombre' => 'Área del Cuidado a la Persona Especialista en Enfermería Pediátrica Y Neonatología con mención en Pediatría',
            //     'grado_id' => 3, // SEGUNDA ESPECIALIDAD
            //     'facultad_id' => 4, // FE
            //     'concepto_pago_id' => 2,
            //     'vacantes' => 25,
            // ],
            // [
            //     'nombre' => 'Área de Salud Pública y Comunitaria Enfermera Especialista en Salud Ocupacional con mención en Salud Ocupacional',
            //     'grado_id' => 3, // SEGUNDA ESPECIALIDAD
            //     'facultad_id' => 4, // FE
            //     'concepto_pago_id' => 2,
            //     'vacantes' => 25,
            // ],
            // [
            //     'nombre' => 'Área del Cuidado a la Persona Enfermera Especialista en Centro Quirúrgico Especializado con mención en Centro Quirúrgico',
            //     'grado_id' => 3, // SEGUNDA ESPECIALIDAD
            //     'facultad_id' => 4, // FE
            //     'concepto_pago_id' => 2,
            //     'vacantes' => 25,
            // ],
            [
                'nombre' => 'Ciencias de la Ingeniería Mecánica y Eléctrica con mención en Energía',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 5, // FIME
                'concepto_pago_id' => 1,
                'vacantes' => 48,
            ],
            [
                'nombre' => 'Ciencias de la Ingeniería Mecánica y Eléctrica con mención en Energía',
                'grado_id' => 1, // DOCTORADO
                'facultad_id' => 5,  // FIME
                'concepto_pago_id' => 1,
                'vacantes' => 48,
            ],
            [
                'nombre' => 'Derecho con mención en Civil y Comercial',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 6, // FDCP
                'concepto_pago_id' => 1,
                'vacantes' => 38,
            ],
            [
                'nombre' => 'Derecho con mención en Derecho Constitucional y Procesal Constitucional',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 6, // FDCP
                'concepto_pago_id' => 1,
                'vacantes' => 38,
            ],
            [
                'nombre' => 'Derecho con mención en Derecho Penal y Procesal Penal',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 6, // FDCP
                'concepto_pago_id' => 1,
                'vacantes' => 38,
            ],
            [
                'nombre' => 'Derecho y Ciencia Política',
                'grado_id' => 1, // DOCTORADO
                'facultad_id' => 6, // FDCP
                'concepto_pago_id' => 1,
                'vacantes' => 31,
            ],
            [
                'nombre' => 'Gestión Integrada de los Recursos Hídricos',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 7, // FIA
                'concepto_pago_id' => 1,
                'vacantes' => 40,
            ],
            [
                'nombre' => 'Ciencias de la Educación con mención en Didáctica del Idioma Inglés',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 8, // FASCHE
                'concepto_pago_id' => 1,
                'vacantes' => 30,
            ],
            [
                'nombre' => 'Ciencias de la Educación con mención en Docencia y Gestión Universitaria',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 8, // FASCHE
                'concepto_pago_id' => 1,
                'vacantes' => 60,
            ],
            [
                'nombre' => 'Ciencias Sociales con mención en Gestión Pública y Gerencia Social',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 8, // FASCHE
                'concepto_pago_id' => 1,
                'vacantes' => 60,
            ],
            [
                'nombre' => 'Ciencias de la Educación con mención en Tecnologías de la Información e Informática Educativa',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 8, // FASCHE
                'concepto_pago_id' => 1,
                'vacantes' => 30,
            ],
            [
                'nombre' => 'Ciencias de la Educación con mención en Gerencia Educativa Estratégica',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 8, // FASCHE
                'concepto_pago_id' => 1,
                'vacantes' => 30,
            ],
            [
                'nombre' => 'Ciencias de la Educación con mención en Investigación y Docencia',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 8, // FASCHE
                'concepto_pago_id' => 1,
                'vacantes' => 60,
            ],
            [
                'nombre' => 'Sociología',
                'grado_id' => 1, // DOCTORADO
                'facultad_id' => 8, // FASCHE
                'concepto_pago_id' => 1,
                'vacantes' => 30,
            ],
            [
                'nombre' => 'Ciencias de la Educación',
                'grado_id' => 1, // DOCTORADO
                'facultad_id' => 8, // FASCHE
                'concepto_pago_id' => 1,
                'vacantes' => 60,
            ],
            [
                'nombre' => 'Ciencias Ambientales',
                'grado_id' => 1, // DOCTORADO
                'facultad_id' => 9, // FCCBB
                'concepto_pago_id' => 1,
                'vacantes' => 36,
            ],
            [
                'nombre' => 'Ciencias Agrarias con mención en Agroexportación Sostenible',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 11, // FAG
                'concepto_pago_id' => 1,
                'vacantes' => 36,
            ],
            [
                'nombre' => 'Manejo Sostenible de Suelos',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 11, // FAG
                'concepto_pago_id' => 1,
                'vacantes' => 36,
            ],
            [
                'nombre' => 'Ciencias con mención en Manejo Integrado de Plagas y Enfermedades',
                'grado_id' => 2, // MAESTRÍA
                'facultad_id' => 11, // FAG
                'concepto_pago_id' => 1,
                'vacantes' => 36,
            ],
        ];

        foreach ($programas as $programaData) {
            Programa::create($programaData);
        }
    }
}
