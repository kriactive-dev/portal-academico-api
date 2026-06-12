<?php

namespace App\Imports;

use App\Models\Student\StudentAcademicRecord;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Carbon\Carbon;
use Exception;

class StudentAcademicRecordImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use Importable, SkipsErrors;

    private $importedCount = 0;
    private $errors = [];

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            // Converter data para formato correto
            $date = null;
            if (!empty($row['date'])) {
                $date = $this->convertDate($row['date']);
            }

            $record = new StudentAcademicRecord([
                'subject_code' => $row['subject_code'] ?? null,
                'subject_name' => $row['subject_name'] ?? null,
                'academic_year' => $row['academic_year'] ?? null,
                'semester' => $row['semester'] ?? null,
                'credits' => $row['credits'] ?? null,
                'grade' => $row['grade'] ?? null,
                'teacher_name' => $row['teacher_name'] ?? null,
                'description' => $row['description'] ?? null,
                'date' => $date,
                'student_id' => $row['student_id'] ?? null,
                'student_code' => $row['student_code'] ?? null,
                'created_by_user_id' => auth()->user()?->id ?? null,
            ]);

            $this->importedCount++;
            return $record;
        } catch (Exception $e) {
            $this->errors[] = "Linha com erro: {$e->getMessage()}";
            return null;
        }
    }

    /**
     * Define validation rules
     */
    public function rules(): array
    {
        return [
            'subject_code' => 'nullable|string|max:50',
            'subject_name' => 'nullable|string|max:255',
            'academic_year' => 'nullable|string|max:20',
            'semester' => 'nullable|string|max:20',
            'credits' => 'nullable|string|max:10',
            'grade' => 'nullable|string|max:20',
            'teacher_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'student_code' => 'nullable|string|max:50',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages(): array
    {
        return [
            'subject_code.max' => 'O código da disciplina não pode ter mais de 50 caracteres.',
            'subject_name.max' => 'O nome da disciplina não pode ter mais de 255 caracteres.',
            'academic_year.max' => 'O ano acadêmico não pode ter mais de 20 caracteres.',
        ];
    }

    /**
     * Convert date from various formats
     */
    private function convertDate($dateValue): ?string
    {
        if (empty($dateValue)) {
            return null;
        }

        if (is_numeric($dateValue)) {
            // Excel date serial number
            try {
                return Carbon::createFromFormat('Y-m-d', '1900-01-01')
                    ->addDays($dateValue - 2)->format('Y-m-d');
            } catch (Exception $e) {
                return null;
            }
        }

        try {
            return Carbon::parse($dateValue)->format('Y-m-d');
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get imported count
     */
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    /**
     * Get errors
     */
    public function getErrors(): array
    {
        return array_merge($this->errors, $this->getFailures());
    }

    /**
     * Get failures as array
     */
    private function getFailures(): array
    {
        return [];
    }

    /**
     * Handle import errors
     */
    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
    }
}
