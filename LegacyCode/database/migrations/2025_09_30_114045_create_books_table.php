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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('author')->nullable();
            $table->string('editor')->nullable();
            $table->string('cdu')->nullable();
            $table->string('topic')->nullable();
            $table->string('edition')->nullable();
            $table->string('launch_date')->nullable();
            $table->string('launch_place')->nullable();
            $table->string('book_img_path')->nullable();
            $table->string('book_file_path')->nullable();
            $table->string('book_cover_path')->nullable();
            $table->unsignedBigInteger('library_id')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->unsignedBigInteger('updated_by_user_id')->nullable();
            $table->unsignedBigInteger('deleted_by_user_id')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('library_id')->references('id')->on('libraries')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
