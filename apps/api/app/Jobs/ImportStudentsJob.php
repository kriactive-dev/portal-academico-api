<?php

namespace App\Jobs;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportStudentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    /**
     * @param string $filePath
     * @param int $userId
     */
    public function __construct(
        public string $filePath,
        public int $userId
    ) {}

    public function handle(): void
    {
        $fullPath = Storage::path($this->filePath);

        if (!file_exists($fullPath)) {
            Log::error("Import file not found: {$fullPath}");
            return;
        }

        $reader = new \OpenSpout\Reader\XLSX\Reader();
        $reader->open($fullPath);

        $results = [
            'total' => 0,
            'imported' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        $chunk = [];
        $chunkSize = 50;
        $rowNumber = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rowNumber++;

                if ($rowNumber === 1) {
                    continue;
                }

                $cells = $row->getCells();
                $data = [
                    'student_number' => $cells[0]?->getValue() ?? '',
                    'name' => $cells[1]?->getValue() ?? '',
                    'email' => $cells[2]?->getValue() ?? '',
                    'phone' => $cells[3]?->getValue() ?? '',
                    'birth_date' => $cells[4]?->getValue() ?? '',
                    'enrollment_date' => $cells[5]?->getValue() ?? '',
                    'status' => 'active',
                ];

                $results['total']++;

                $validator = validator($data, [
                    'student_number' => 'required|string|max:50',
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|max:255|unique:students,email',
                    'phone' => 'required|string|max:50',
                    'birth_date' => 'required|date',
                    'enrollment_date' => 'required|date',
                ]);

                if ($validator->fails()) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $rowNumber,
                        'student_number' => $data['student_number'],
                        'errors' => $validator->errors()->all(),
                    ];
                    continue;
                }

                $chunk[] = $data;

                if (count($chunk) >= $chunkSize) {
                    $this->processChunk($chunk, $results);
                    $chunk = [];
                }
            }
        }

        if (!empty($chunk)) {
            $this->processChunk($chunk, $results);
        }

        $reader->close();

        Storage::delete($this->filePath);

        Log::info('Student import completed', [
            'total' => $results['total'],
            'imported' => $results['imported'],
            'failed' => $results['failed'],
        ]);
    }

    private function processChunk(array $chunk, array &$results): void
    {
        foreach ($chunk as $data) {
            try {
                Student::create($data);
                $results['imported']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => 0,
                    'student_number' => $data['student_number'],
                    'errors' => [$e->getMessage()],
                ];
            }
        }
    }
}
