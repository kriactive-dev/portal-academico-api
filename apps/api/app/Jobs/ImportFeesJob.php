<?php

namespace App\Jobs;

use App\Models\Fee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Reader\Common\Creator\ReaderEntityFactory;

class ImportFeesJob implements ShouldQueue
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
                    'name' => $cells[0]?->getValue() ?? '',
                    'type' => $cells[1]?->getValue() ?? '',
                    'amount' => $cells[2]?->getValue() ?? 0,
                    'course_id' => $cells[3]?->getValue() ?? 0,
                    'is_active' => filter_var($cells[4]?->getValue() ?? true, FILTER_VALIDATE_BOOLEAN),
                ];

                $results['total']++;

                $validator = validator($data, [
                    'name' => 'required|string|max:255',
                    'type' => 'required|string|in:monthly,enrollment,other',
                    'amount' => 'required|numeric|min:0',
                    'course_id' => 'required|integer|exists:courses,id',
                    'is_active' => 'boolean',
                ]);

                if ($validator->fails()) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $rowNumber,
                        'name' => $data['name'],
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

        Log::info('Fee import completed', [
            'total' => $results['total'],
            'imported' => $results['imported'],
            'failed' => $results['failed'],
        ]);
    }

    private function processChunk(array $chunk, array &$results): void
    {
        foreach ($chunk as $data) {
            try {
                Fee::create($data);
                $results['imported']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => 0,
                    'name' => $data['name'],
                    'errors' => [$e->getMessage()],
                ];
            }
        }
    }
}
