<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->index('status');
            $table->index('priority');
            $table->index('created_at');
            $table->index('closed_at');
            $table->index(['status', 'priority']);
            $table->index(['status', 'created_at']);
            $table->index(['assigned_to', 'status']);
            $table->index(['user_id', 'status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('entity');
        });

        Schema::table('guest_ticket_details', function (Blueprint $table) {
            $table->index('entity_type');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->index('is_active');
        });

        Schema::table('ticket_attachments', function (Blueprint $table) {
            $table->index('ticket_id');
        });

        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->index('ticket_id');
            $table->index('user_id');
            $table->index('created_at');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index('read_at');
        });

        Schema::table('survey_questions', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->index(['is_active', 'show_to_guest']);
            $table->index(['is_active', 'show_to_user']);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['closed_at']);
            $table->dropIndex(['status', 'priority']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['assigned_to', 'status']);
            $table->dropIndex(['user_id', 'status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['entity']);
        });

        Schema::table('guest_ticket_details', function (Blueprint $table) {
            $table->dropIndex(['entity_type']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
        });

        Schema::table('ticket_attachments', function (Blueprint $table) {
            $table->dropIndex(['ticket_id']);
        });

        Schema::table('ticket_comments', function (Blueprint $table) {
            $table->dropIndex(['ticket_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['read_at']);
        });

        Schema::table('survey_questions', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'sort_order']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'show_to_guest']);
            $table->dropIndex(['is_active', 'show_to_user']);
        });
    }
};
