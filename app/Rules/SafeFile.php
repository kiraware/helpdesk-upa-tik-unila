<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Translation\PotentiallyTranslatedString;

class SafeFile implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return;
        }

        // 1. Cek ekstensi file yang dilarang
        $extension = strtolower($value->getClientOriginalExtension());
        $dangerousExtensions = [
            'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
            'exe', 'bat', 'cmd', 'sh', 'js', 'html', 'htm', 'jar', 'vbs', 'ps1',
        ];

        if (in_array($extension, $dangerousExtensions)) {
            $fail('File memiliki ekstensi yang tidak diizinkan untuk alasan keamanan.');

            return;
        }

        // 2. Untuk file gambar (jpg/jpeg/png), validasi bahwa file benar-benar
        //    gambar yang valid menggunakan GD. Skip pattern scanning karena data
        //    binary EXIF pada foto asli sering memicu false positive regex.
        //    FileSanitizer akan membersihkan EXIF dan data non-pixel setelah upload.
        $imageExtensions = ['jpg', 'jpeg', 'png'];
        if (in_array($extension, $imageExtensions)) {
            $imageInfo = @getimagesize($value->getRealPath());

            if ($imageInfo === false) {
                $fail('File gambar tidak valid atau rusak.');
            }

            return;
        }

        // 3. Untuk file PDF, validasi struktur dan scan pola berbahaya PDF-specific
        if ($extension === 'pdf') {
            $content = file_get_contents($value->getRealPath());

            // Validasi magic bytes — file PDF harus dimulai dengan %PDF-
            if (substr($content, 0, 5) !== '%PDF-') {
                $fail('File PDF tidak valid (header tidak sesuai).');

                return;
            }

            // Pattern berbahaya yang spesifik untuk konteks PDF.
            // CATATAN: Pattern seperti /OpenAction, /AA, /Launch TIDAK di-scan di sini
            // karena sering muncul di PDF legitimate (contoh: /OpenAction untuk set tampilan awal).
            // FileSanitizer akan menghapus semua action/JS/metadata via FPDI flat-copy setelah upload.
            // Di sini kita hanya mendeteksi polyglot attacks (PHP/HTML disisipkan dalam PDF).
            $pdfMaliciousPatterns = [
                '/<\?php/i',             // Polyglot attack: PHP tag di dalam PDF
                '/<\?=/i',              // Polyglot attack: PHP short echo tag
                '/<script.*?>/i',       // HTML script tag di dalam PDF
            ];

            foreach ($pdfMaliciousPatterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    $fail('Terdeteksi konten berbahaya di dalam file PDF yang diunggah.');

                    return;
                }
            }

            return;
        }

        // 4. Untuk file non-gambar dan non-PDF (fallback untuk tipe lain di masa depan)
        $content = file_get_contents($value->getRealPath());

        $genericMaliciousPatterns = [
            '/<\?php/i',             // Tag pembuka PHP
            '/<\?=/i',              // Tag echo PHP
            '/<script.*?>/i',       // Tag script HTML
            '/javascript:/i',       // Protokol javascript
            '/vbscript:/i',         // Protokol vbscript
            '/onload\s*=/i',        // Event handler onload
            '/onerror\s*=/i',       // Event handler onerror
        ];

        foreach ($genericMaliciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $fail('Terdeteksi konten berbahaya (skrip) di dalam file yang diunggah.');

                return;
            }
        }
    }
}
