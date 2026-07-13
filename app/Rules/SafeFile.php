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
        //    ImageSanitizer akan membersihkan EXIF dan data non-pixel setelah upload.
        $imageExtensions = ['jpg', 'jpeg', 'png'];
        if (in_array($extension, $imageExtensions)) {
            $imageInfo = @getimagesize($value->getRealPath());

            if ($imageInfo === false) {
                $fail('File gambar tidak valid atau rusak.');
            }

            return;
        }

        // 3. Untuk file non-gambar (PDF, dll.), baca isi file untuk
        //    mendeteksi script berbahaya (XSS / RCE)
        $content = file_get_contents($value->getRealPath());

        $maliciousPatterns = [
            '/<\?php/i',            // Tag pembuka PHP
            '/<\?=/i',              // Tag echo PHP
            '/<script.*?>/i',       // Tag script HTML
            '/eval\s*\(/i',         // Fungsi eval()
            '/exec\s*\(/i',         // Fungsi exec()
            '/shell_exec\s*\(/i',   // Fungsi shell_exec()
            '/system\s*\(/i',       // Fungsi system()
            '/passthru\s*\(/i',     // Fungsi passthru()
            '/base64_decode\s*\(/i', // Fungsi base64_decode() sering digunakan obfuscation
            '/javascript:/i',       // Protokol javascript
            '/vbscript:/i',         // Protokol vbscript
            '/onload=/i',           // Event handler onload
            '/onerror=/i',          // Event handler onerror
        ];

        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $fail('Terdeteksi konten berbahaya (skrip) di dalam file yang diunggah.');

                return;
            }
        }
    }
}
