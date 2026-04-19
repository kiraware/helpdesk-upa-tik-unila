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
        Schema::create('ticket_survey_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_survey_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_question_id')->constrained();
            $table->unsignedTinyInteger('satisfaction_score');
            $table->unsignedTinyInteger('importance_score');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_survey_answers');
    }
};
