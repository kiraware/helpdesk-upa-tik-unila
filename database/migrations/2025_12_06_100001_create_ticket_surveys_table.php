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
            $table->foreignId('ticket_id')
                ->unique()
                ->constrained('tickets')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('overall_rating'); // Bintang 1-5
            $table->text('feedback')->nullable(); // Saran/Masukan
            $table->decimal('csi_score', 5, 2)->nullable(); // Nilai kalkulasi (0-100)
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
