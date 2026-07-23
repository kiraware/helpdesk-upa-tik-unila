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
            $table->increments('id');
            $table->unsignedInteger('ticket_survey_id');
            $table->foreign('ticket_survey_id')->references('id')->on('ticket_surveys')->cascadeOnDelete();
            $table->unsignedTinyInteger('survey_question_id');
            $table->foreign('survey_question_id')->references('id')->on('survey_questions');
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
