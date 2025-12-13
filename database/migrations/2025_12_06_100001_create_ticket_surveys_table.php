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
        Schema::create('ticket_surveys', function (Blueprint $table) {
            $table->id();

            // Relasi ke Tiket (One-to-One)
            $table->foreignId('ticket_id')
                ->unique() // Penting: 1 Tiket = 1 Review
                ->constrained('tickets')
                ->cascadeOnDelete();

            // Dimensi Penilaian (Skala 1-5)
            // Sesuai PDF Kuesioner
            $table->unsignedTinyInteger('score_access');   // Kemudahan Akses
            $table->unsignedTinyInteger('score_speed');    // Kecepatan Respons
            $table->unsignedTinyInteger('score_solution'); // Kualitas Solusi
            $table->unsignedTinyInteger('score_attitude'); // Sikap Petugas
            $table->unsignedTinyInteger('score_overall');  // Kepuasan Keseluruhan

            // Kritik dan Saran
            $table->text('feedback_comment')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_surveys');
    }
};
