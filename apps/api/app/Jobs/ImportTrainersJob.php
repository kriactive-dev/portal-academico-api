<?php

namespace App\Jobs;

use App\Models\Trainer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportTrainersJob implements ShouldQueue
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
                    'email' => $cells[1]?->getValue() ?? '',
                    'phone' => $cells[2]?->getValue() ?? '',
                    'specialty' => $cells[3]?->getValue() ?? '',
                    'status' => 'active',
                ];

                $results['total']++;

                $validator = validator($data, [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|max:255|unique:trainers,email',
                    'phone' => 'required|string|max:50',
                    'specialty' => 'nullable|string|max:255',
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

        Log::info('Trainer import completed', [
            'total' => $results['total'],
            'imported' => $results['imported'],
            'failed' => $results['failed'],
        ]);
    }

    private function processChunk(array $chunk, array &$results): void
    {
        foreach ($chunk as $data) {
            try {
                Trainer::create($data);
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
