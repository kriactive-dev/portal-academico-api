<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_trainer', function (Blueprint $table) {
            $table->foreignId('trainer_id')->constrained('trainers')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('school_classes')->cascadeOnDelete();
            $table->primary(['trainer_id', 'school_class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_trainer');
    }
};
