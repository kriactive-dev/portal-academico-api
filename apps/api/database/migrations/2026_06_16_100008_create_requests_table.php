<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->enum('type', ['certificate', 'internship_approval']);
            $table->enum('status', ['pending', 'approved', 'denied'])->default('pending');
            $table->dateTime('submission_date');
            $table->dateTime('response_date')->nullable();
            $table->text('denial_reason')->nullable();
            $table->json('details');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
