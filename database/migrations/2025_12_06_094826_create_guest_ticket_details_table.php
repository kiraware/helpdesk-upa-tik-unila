<?php

use App\Enums\IdentityType;
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
        Schema::create('guest_ticket_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('ticket_id')->unique();
            $table->foreign('ticket_id')->references('id')->on('tickets')->cascadeOnDelete();
            $table->string('full_name', 50);
            $table->string('email', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('identity_number', 32);
            $table->unsignedTinyInteger('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->string('other_department', 150)->nullable();
            $table->enum('entity_type', array_column(IdentityType::cases(), 'value'));
            $table->string('photo_identity_path')->nullable();
            $table->string('photo_selfie_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guest_ticket_details');
    }
};
