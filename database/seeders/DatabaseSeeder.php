<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // --- MASTER DATA ---
            DivisionSeeder::class,
            ServiceSeeder::class,

            // --- USERS ---
            UserSeeder::class,

            // --- TICKETING SYSTEM ---
            TicketSeeder::class,
            GuestTicketDetailSeeder::class,
            TicketCommentSeeder::class,
            TicketSurveySeeder::class,

            // --- DYNAMIC FORM SYSTEM ---
            FormSeeder::class,
            FormQuestionSeeder::class,
            FormSubmissionSeeder::class,
            FormAnswerSeeder::class,
        ]);
    }
}
