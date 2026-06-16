<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Reader\Common\Creator\ReaderEntityFactory;

class ImportPaymentsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

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

        $reader = ReaderEntityFactory::createReaderFromFile($fullPath);
        $reader->open($fullPath);

        $results = ['total' => 0, 'imported' => 0, 'failed' => 0, 'errors' => []];
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
                    'student_id' => $cells[0]?->getValue() ?? 0,
                    'course_id' => $cells[1]?->getValue() ?? 0,
                    'reference_month' => $cells[2]?->getValue() ?? '',
                    'amount' => $cells[3]?->getValue() ?? 0,
                    'status' => $cells[4]?->getValue() ?? 'pending',
                    'method' => $cells[5]?->getValue() ?? '',
                    'payment_date' => $cells[6]?->getValue() ?? '',
                    'due_date' => $cells[7]?->getValue() ?? '',
                ];

                $results['total']++;

                $validator = validator($data, [
                    'student_id' => 'required|integer|exists:students,id',
                    'course_id' => 'required|integer|exists:courses,id',
                    'reference_month' => 'required|string|max:7',
                    'amount' => 'required|numeric|min:0',
                    'status' => 'required|string|in:pending,paid,overdue,cancelled',
                    'method' => 'required|string|in:cash,card,transfer,mbway,other',
                    'payment_date' => 'nullable|date',
                    'due_date' => 'nullable|date',
                ]);

                if ($validator->fails()) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $rowNumber,
                        'student_id' => $data['student_id'],
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

        Log::info('Payment import completed', [
            'total' => $results['total'],
            'imported' => $results['imported'],
            'failed' => $results['failed'],
        ]);
    }

    private function processChunk(array $chunk, array &$results): void
    {
        foreach ($chunk as $data) {
            try {
                Payment::create($data);
                $results['imported']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => 0,
                    'student_id' => $data['student_id'],
                    'errors' => [$e->getMessage()],
                ];
            }
        }
    }
}
