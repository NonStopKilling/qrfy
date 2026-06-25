<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('qr_code', 191)->unique();
            $table->string('name');
            $table->string('serial_number', 191)->unique();
            $table->string('model');
            $table->string('status')->default('Operativo');
            $table->string('public_token', 191)->nullable()->unique();
            $table->string('manual_pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
