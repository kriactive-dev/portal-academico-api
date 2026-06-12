<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            //
            $table->string('student_code')->nullable();
            $table->string('full_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('province')->nullable();
            $table->string('province_code')->nullable();
            $table->string('status')->nullable();
            $table->string('enrollment_plan_status')->nullable();
            $table->string('faculdade')->nullable();
            $table->string('unidade_organica')->nullable();
            $table->string('course')->nullable();
            $table->string('course_code')->nullable();
            $table->string('locality')->nullable();
            $table->string('nationality')->nullable();
            $table->string('academic_year')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            //
        });
    }
};
