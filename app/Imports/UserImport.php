<?php

namespace App\Imports;

use App\Models\Apprentice;
use App\Models\Course;
use App\Models\DocumentType;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserImport implements ToCollection, WithValidation
{
    use Importable, SkipsFailures;

    private $errores = [];

    public function collection(Collection $rows)
    {
        $documentTypes = DocumentType::pluck('id', 'abbreviation'); // Pre-cargar tipos de documento
        $courses = Course::pluck('id', 'code'); // Pre-cargar cursos

        $usersToInsert = [];
        $apprenticesToInsert = [];

        foreach ($rows as $index => $row) {
            if ($index === 0) continue; // Saltar encabezados

            try {
                // Verificar si hay datos vacíos
                if (!$this->validateRow($row)) {
                    $this->errores[] = ["Fila " . ($index + 1) => "Datos incompletos"];
                    continue; // Saltar esta fila
                }

                $identityDocument = (string) $row['numero_documento'];

                // Obtener el ID del tipo de documento (o crearlo si no existe)
                $documentTypeId = $documentTypes[$row['tipo_documento']] ?? DocumentType::create([
                    'abbreviation' => $row['tipo_documento']
                ])->id;

                // Preparar datos del usuario
                $usersToInsert[] = [
                    'identity_document' => $identityDocument,
                    'name' => $row['nombre'],
                    'last_name' => $row['primer_apellido'] . ' ' . $row['segundo_apellido'],
                    'email' => $row['correo_electronico'],
                    'document_type_id' => $documentTypeId,
                    'password' => Hash::make('password123'),
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                // Verificar si el curso existe antes de asociar aprendiz
                if (isset($courses[$row['ficha']])) {
                    $apprenticesToInsert[] = [
                        'user_id' => $identityDocument, // Temporalmente usamos el documento
                        'course_id' => $courses[$row['ficha']],
                        'state' => $this->formatState($row['estado_aprendiz']),
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            } catch (\Exception $e) {
                $this->errores[] = ["Fila " . ($index + 1) => $e->getMessage()];
            }
        }

        // Insertar usuarios en bloque
        if (!empty($usersToInsert)) {
            User::insert($usersToInsert);
        }

        // Asociar aprendices con los usuarios insertados
        if (!empty($apprenticesToInsert)) {
            foreach ($apprenticesToInsert as &$apprentice) {
                $user = User::where('identity_document', $apprentice['user_id'])->first();
                if ($user) {
                    $apprentice['user_id'] = $user->id;
                }
            }
            Apprentice::insert($apprenticesToInsert);
        }
    }

    private function validateRow($row)
    {
        return !empty($row['nombre']) &&
               !empty($row['primer_apellido']) &&
               !empty($row['numero_documento']) &&
               !empty($row['correo_electronico']) &&
               !empty($row['tipo_documento']) &&
               !empty($row['ficha']) &&
               !empty($row['estado_aprendiz']);
    }

    private function formatState($state)
    {
        return str_replace(' ', '_', $state);
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string',
            'primer_apellido' => 'required|string',
            'segundo_apellido' => 'nullable|string',
            'numero_documento' => 'required|string|unique:users,identity_document',
            'correo_electronico' => 'required|email|unique:users,email',
            'tipo_documento' => 'required|string',
            'ficha' => 'required|exists:courses,code',
            'estado_aprendiz' => ['required', Rule::in(['Formacion', 'Desertado', 'Etapa productiva', 'Retiro voluntario'])]
        ];
    }

    public function getErrores()
    {
        return $this->errores;
    }
}
