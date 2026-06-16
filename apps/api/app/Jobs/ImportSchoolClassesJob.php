<?php

namespace App\Jobs;

use App\Models\SchoolClass;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Reader\Common\Creator\ReaderEntityFactory;

class ImportSchoolClassesJob implements ShouldQueue
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
                    'course_id' => $cells[1]?->getValue() ?? 0,
                    'shift' => $cells[2]?->getValue() ?? '',
                    'status' => $cells[3]?->getValue() ?? 'active',
                    'start_date' => $cells[4]?->getValue() ?? '',
                    'end_date' => $cells[5]?->getValue() ?? '',
                ];

                $results['total']++;

                $validator = validator($data, [
                    'name' => 'required|string|max:255',
                    'course_id' => 'required|integer|exists:courses,id',
                    'shift' => 'required|string|in:morning,afternoon,evening',
                    'status' => 'required|string|in:active,inactive,completed',
                    'start_date' => 'required|date',
                    'end_date' => 'nullable|date|after_or_equal:start_date',
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

        Log::info('School class import completed', [
            'total' => $results['total'],
            'imported' => $results['imported'],
            'failed' => $results['failed'],
        ]);
    }

    private function processChunk(array $chunk, array &$results): void
    {
        foreach ($chunk as $data) {
            try {
                SchoolClass::create($data);
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
