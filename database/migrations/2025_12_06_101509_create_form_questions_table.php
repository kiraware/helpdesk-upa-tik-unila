<?php

use App\Enums\FormQuestionType;
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
        Schema::create('form_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')
                ->constrained('forms')
                ->cascadeOnDelete(); // Hapus form = hapus semua pertanyaan

            $table->text('question_text');

            // Tipe Pertanyaan (Ambil dari Enum)
            $table->enum('type', array_column(FormQuestionType::cases(), 'value'));

            // Opsi Jawaban (Khusus Radio/Checkbox)
            // Disimpan sebagai JSON. Contoh: ["Pria", "Wanita"]
            $table->json('options')->nullable();

            $table->boolean('is_required')->default(true);
            $table->integer('order')->default(0); // Urutan tampil

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_questions');
    }
};
