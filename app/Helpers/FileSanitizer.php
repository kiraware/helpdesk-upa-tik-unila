<?php

namespace App\Helpers;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\PdfParserException;

class FileSanitizer
{
    /**
     * Sanitize uploaded file by stripping metadata and embedded malicious content.
     *
     * For images: Re-encodes to strip EXIF data and embedded scripts (Polyglot).
     * For PDFs: Re-creates the document page-by-page, stripping metadata, JavaScript,
     *           embedded files, and form actions.
     *
     * @param  string  $fullPath  Absolute path to the saved file
     * @param  string  $extension  The file extension
     */
    public static function sanitize(string $fullPath, string $extension): void
    {
        $extension = strtolower($extension);

        if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
            static::sanitizeImage($fullPath);
        } elseif ($extension === 'pdf') {
            static::sanitizePdf($fullPath);
        }
    }

    /**
     * Re-encode image to strip EXIF and non-pixel data.
     */
    protected static function sanitizeImage(string $fullPath): void
    {
        try {
            $manager = new ImageManager(Driver::class);
            $image = $manager->decodePath($fullPath);

            // Saving it back re-encodes the image and strips out non-image data
            $image->save($fullPath);
        } catch (\Exception $e) {
            // If the image cannot be read, it might be a corrupted or completely fake image.
            // We could delete it, but SafeFile already validates the basic structure.
            @unlink($fullPath);
            throw new \Exception('File gambar tidak valid atau rusak.');
        }
    }

    /**
     * Re-create PDF page-by-page using FPDI to strip metadata, JavaScript,
     * embedded files, and other non-page content.
     *
     * FPDI imports only the visual page content (the page's content stream).
     * Document-level objects like /Info (Author, Creator, Producer), /JavaScript,
     * /OpenAction, /EmbeddedFiles, /AcroForm, and /XMP metadata are NOT carried over.
     */
    protected static function sanitizePdf(string $fullPath): void
    {
        try {
            $pdf = new Fpdi;

            $pageCount = $pdf->setSourceFile($fullPath);

            for ($i = 1; $i <= $pageCount; $i++) {
                $templateId = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($templateId);

                // Use the same orientation and dimensions as the original page
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($templateId);
            }

            // Write sanitized PDF to a temp file first, then replace original
            $tempPath = $fullPath.'.tmp';
            $pdf->Output('F', $tempPath);

            // Replace original with sanitized version
            if (file_exists($tempPath) && filesize($tempPath) > 0) {
                rename($tempPath, $fullPath);
            } else {
                @unlink($tempPath);
                throw new \Exception('Gagal membuat PDF yang telah disanitasi.');
            }
        } catch (PdfParserException $e) {
            @unlink($fullPath);
            throw new \Exception('File PDF tidak valid atau rusak.');
        } catch (\Exception $e) {
            @unlink($fullPath);
            throw new \Exception('File PDF tidak valid atau rusak.');
        }
    }
}
