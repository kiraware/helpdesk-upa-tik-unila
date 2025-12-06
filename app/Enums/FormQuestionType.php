<?php

namespace App\Enums;

enum FormQuestionType: string
{
    case TEXT = 'text';         // Jawaban singkat
    case TEXTAREA = 'textarea'; // Jawaban panjang
    case RADIO = 'radio';       // Pilihan Ganda (Satu)
    case CHECKBOX = 'checkbox'; // Pilihan Ganda (Banyak)
    case SCALE = 'scale';       // Skala 1-5
    case DATE = 'date';         // Tanggal
}
