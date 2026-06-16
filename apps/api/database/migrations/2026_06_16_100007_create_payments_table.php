<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('course_id')->constrained('courses');
            $table->string('reference_month', 7);
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['paid', 'pending', 'overdue'])->default('pending');
            $table->enum('method', ['transfer', 'cash', 'mpesa', 'emola', 'deposit'])->nullable();
            $table->date('payment_date')->nullable();
            $table->date('due_date');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
