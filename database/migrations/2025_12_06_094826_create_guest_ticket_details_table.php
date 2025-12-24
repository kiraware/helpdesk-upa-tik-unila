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
            $table->id();
            $table->foreignId('ticket_id')
                ->unique()
                ->constrained('tickets')
                ->cascadeOnDelete();
            $table->string('full_name', 50);
            $table->string('email');
            $table->string('identity_number', 32);
            $table->enum('entity_type', array_column(IdentityType::cases(), 'value'));
            $table->string('photo_identity_path');
            $table->string('photo_selfie_path');
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
