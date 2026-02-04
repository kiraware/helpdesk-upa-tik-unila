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
            ['question' => 'Seberapa mudah Anda mengakses layanan Teknologi Informasi yang disediakan oleh Universitas Lampung?', 'sort_order' => 1],
            ['question' => 'Bagaimana Anda menilai kecepatan respons tim Teknologi Informasi dalam menangani permintaan atau permasalahan Anda?', 'sort_order' => 2],
            ['question' => 'Bagaimana penilaian Anda terhadap kualitas solusi atau penyelesaian yang diberikan oleh tim Teknologi Informasi? ', 'sort_order' => 3],
            ['question' => 'Bagaimana Anda menilai sikap petugas Teknologi Informasi dalam hal keramahan dan profesionalisme saat memberikan layanan?', 'sort_order' => 4],
            ['question' => 'Secara keseluruhan, bagaimana tingkat kepuasan Anda terhadap layanan Teknologi Informasi yang Anda terima?  ', 'sort_order' => 5],
        ];

        foreach ($questions as $q) {
            SurveyQuestion::create($q);
        }
    }
}
