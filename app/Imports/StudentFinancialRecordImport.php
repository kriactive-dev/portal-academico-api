<?php

namespace App\Imports;

use App\Models\Student\StudentFinancialRecord;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Carbon\Carbon;
use Exception;

class StudentFinancialRecordImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
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

            $record = new StudentFinancialRecord([
                'date' => $date ?? Carbon::now()->format('Y-m-d'),
                'amount' => $this->convertAmount($row['amount'] ?? 0),
                'description' => $row['description'] ?? null,
                'status' => $row['status'] ?? 'pending',
                'student_id' => $row['student_id'] ?? null,
                'student_code' => $row['student_code'] ?? null,
                'payment_method' => $row['payment_method'] ?? null,
                'notes' => $row['notes'] ?? null,
                'created_by_user_id' => auth()->id(),
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
            'amount' => 'required|numeric|min:0',
            'student_code' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'payment_method' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages(): array
    {
        return [
            'amount.required' => 'O valor é obrigatório.',
            'amount.numeric' => 'O valor deve ser numérico.',
            'amount.min' => 'O valor deve ser maior ou igual a zero.',
        ];
    }

    /**
     * Convert date from various formats
     */
    private function convertDate($dateValue): string
    {
        if (is_numeric($dateValue)) {
            // Excel date serial number
            return Carbon::createFromFormat('Y-m-d', '1900-01-01')
                ->addDays($dateValue - 2)->format('Y-m-d');
        }

        try {
            return Carbon::parse($dateValue)->format('Y-m-d');
        } catch (Exception $e) {
            return Carbon::now()->format('Y-m-d');
        }
    }

    /**
     * Convert amount to decimal
     */
    private function convertAmount($amount): float
    {
        if (is_string($amount)) {
            // Remove currency symbols and spaces
            $amount = preg_replace('/[^0-9.,]/', '', $amount);
            $amount = str_replace(',', '.', $amount);
        }

        return (float) $amount;
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
        return array_merge($this->errors, $this->failures()->toArray());
    }

    /**
     * Handle import errors
     */
    public function onError(\Throwable $e)
    {
        $this->errors[] = $e->getMessage();
    }
}
