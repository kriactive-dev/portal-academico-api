<?php

namespace Database\Seeders;

use App\Models\Documents\DocumentStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentStatusSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => DocumentStatus::STATUS_DRAFT],
            ['name' => DocumentStatus::STATUS_PENDING],
            ['name' => DocumentStatus::STATUS_APPROVED],
            ['name' => DocumentStatus::STATUS_REJECTED],
            ['name' => DocumentStatus::STATUS_ARCHIVED],
        ];

        foreach ($statuses as $status) {
            DocumentStatus::firstOrCreate(['name' => $status['name']], $status);
        }

        $this->command->info('Document statuses seeded successfully!');
    }
}
