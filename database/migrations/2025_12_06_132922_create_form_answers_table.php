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
        Schema::create('form_answers', function (Blueprint $table) {
            $table->id();
            // Jawaban ini milik submisi siapa?
            $table->foreignId('submission_id')
                ->constrained('form_submissions')
                ->cascadeOnDelete();
            // Jawaban ini untuk pertanyaan yang mana?
            $table->foreignId('question_id')
                ->constrained('form_questions')
                ->cascadeOnDelete();
            // Isi jawaban.
            // Bisa berupa teks, angka ("5"), atau JSON string (["A", "B"] untuk checkbox)
            $table->text('answer_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_answers');
    }
};
