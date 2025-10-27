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
        Schema::table('universities', function (Blueprint $table) {
            $table->string('code', 20)->unique()->nullable()->after('name');
            $table->text('description')->nullable()->after('code');
            $table->text('address')->nullable()->after('description');
            $table->string('phone', 50)->nullable()->after('address');
            $table->string('email')->nullable()->after('phone');
            $table->string('website')->nullable()->after('email');
            $table->string('logo_url')->nullable()->after('website');
            $table->boolean('is_active')->default(true)->after('logo_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            $table->dropColumn([
                'code',
                'description',
                'address',
                'phone',
                'email',
                'website',
                'logo_url',
                'is_active',
            ]);
        });
    }
};
