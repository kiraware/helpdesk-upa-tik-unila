<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // --- MASTER DATA ---
            DivisionSeeder::class,
            DepartmentSeeder::class,
            ServiceSeeder::class,

            // --- USERS ---
            UserSeeder::class,

            // --- TICKETING SYSTEM ---
            TicketSeeder::class,
            GuestTicketDetailSeeder::class,

            // Attachment untuk Tiket (harus setelah TicketSeeder)
            TicketAttachmentSeeder::class,

            TicketCommentSeeder::class,

            // Attachment untuk Komentar (harus setelah TicketCommentSeeder)
            CommentAttachmentSeeder::class,

            SurveyQuestionSeeder::class,
            TicketSurveySeeder::class,

            // --- DYNAMIC FORM SYSTEM ---
            FormSeeder::class,
            FormQuestionSeeder::class,
            FormSubmissionSeeder::class,
            FormAnswerSeeder::class,

            // --- CONFIGURATION ---
            ConfigurationSeeder::class,
        ]);
    }
}
