<?php

namespace Database\Seeders;

use App\Models\SurveyQuestion;
use Illuminate\Database\Seeder;

class SurveyQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
            [
                'aspect_name' => 'Kemudahan Akses',
                'satisfaction_question' => 'Seberapa mudah Anda mengakses layanan Teknologi Informasi yang disediakan oleh Universitas Lampung?',
                'importance_question' => 'Seberapa penting bagi Anda kemudahan dalam mengakses layanan Teknologi Informasi yang disediakan oleh Universitas Lampung?',
                'sort_order' => 1,
            ],
            [
                'aspect_name' => 'Kecepatan Respons',
                'satisfaction_question' => 'Bagaimana Anda menilai kecepatan respons tim Teknologi Informasi dalam menangani permintaan atau permasalahan Anda?',
                'importance_question' => 'Seberapa penting bagi Anda kecepatan respons tim Teknologi Informasi dalam menangani permintaan atau permasalahan?',
                'sort_order' => 2,
            ],
            [
                'aspect_name' => 'Kualitas Solusi',
                'satisfaction_question' => 'Bagaimana penilaian Anda terhadap kualitas solusi atau penyelesaian yang diberikan oleh tim Teknologi Informasi?',
                'importance_question' => 'Seberapa penting bagi Anda kualitas solusi atau penyelesaian yang diberikan oleh tim Teknologi Informasi?',
                'sort_order' => 3,
            ],
            [
                'aspect_name' => 'Sikap & Profesionalisme',
                'satisfaction_question' => 'Bagaimana Anda menilai sikap petugas Teknologi Informasi dalam hal keramahan dan profesionalisme saat memberikan layanan?',
                'importance_question' => 'Seberapa penting bagi Anda sikap petugas Teknologi Informasi yang ramah dan profesional saat memberikan layanan?',
                'sort_order' => 4,
            ],
            [
                'aspect_name' => 'Kepuasan Keseluruhan',
                'satisfaction_question' => 'Secara keseluruhan, bagaimana tingkat kepuasan Anda terhadap layanan Teknologi Informasi yang Anda terima?',
                'importance_question' => 'Secara keseluruhan, seberapa penting kualitas layanan Teknologi Informasi yang disediakan oleh Universitas Lampung bagi Anda?',
                'sort_order' => 5,
            ],
        ];

        foreach ($questions as $q) {
            SurveyQuestion::create($q);
        }
    }
}
