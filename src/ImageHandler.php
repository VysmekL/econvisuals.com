<?php
/**
 * Image Upload and Processing Handler
 */

namespace App;

class ImageHandler
{
    private const UPLOAD_DIR = __DIR__ . '/../uploads/infographics/';
    private const MAX_WIDTH = 1200;
    private const WEBP_QUALITY = 85;
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    // Velikosti pro srcset
    private const SIZES = [600, 800, 1200];

    /**
     * Zpracuje nahraný obrázek
     */
    public function processUpload(array $file): array
    {
        // Kontrola chyb při uploadu
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->error($this->getUploadError($file['error']));
        }

        // Kontrola velikosti (max 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            return $this->error('Soubor je příliš velký. Maximum je 10MB.');
        }

        // KRITICKÉ: Validace MIME typu pomocí finfo (ne hlavičky z browseru!)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES)) {
            return $this->error('Nepovolený typ souboru. Pouze JPG, PNG nebo WebP.');
        }

        // Validace že je to skutečně obrázek
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return $this->error('Soubor není platný obrázek.');
        }

        // Generovat unikátní, bezpečný název
        $filename = $this->generateSafeFilename();

        // Zpracovat obrázek
        try {
            $this->processImage($file['tmp_name'], $filename, $mimeType);
            return ['success' => true, 'filename' => $filename . '.webp', 'error' => null];
        } catch (\Exception $e) {
            error_log('Image processing error: ' . $e->getMessage());
            return $this->error('Chyba při zpracování obrázku.');
        }
    }

    /**
     * Zpracuje obrázek - resize, konverze do WebP, generování velikostí
     */
    private function processImage(string $sourcePath, string $baseFilename, string $mimeType): void
    {
        // Načíst obrázek podle typu
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($sourcePath);
                break;
            default:
                throw new \Exception('Unsupported image type');
        }

        if ($image === false) {
            throw new \Exception('Failed to create image resource');
        }

        // Odstranit EXIF metadata (soukromí)
        $image = $this->removeExif($image);

        // Získat rozměry
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        // Generovat různé velikosti
        foreach (self::SIZES as $targetWidth) {
            // Pokud je originál menší, přeskočit
            if ($originalWidth <= $targetWidth && $targetWidth < self::MAX_WIDTH) {
                continue;
            }

            $width = min($targetWidth, $originalWidth);
            $height = (int)($originalHeight * ($width / $originalWidth));

            // Resize
            $resized = imagescale($image, $width, $height);

            if ($resized === false) {
                continue;
            }

            // Uložit jako WebP
            $outputPath = self::UPLOAD_DIR . $baseFilename . '-' . $width . 'w.webp';
            imagewebp($resized, $outputPath, self::WEBP_QUALITY);

            imagedestroy($resized);
        }

        // Uložit hlavní verzi (bez suffixu)
        $mainWidth = min(self::MAX_WIDTH, $originalWidth);
        $mainHeight = (int)($originalHeight * ($mainWidth / $originalWidth));
        $mainResized = imagescale($image, $mainWidth, $mainHeight);

        $mainPath = self::UPLOAD_DIR . $baseFilename . '.webp';
        imagewebp($mainResized, $mainPath, self::WEBP_QUALITY);

        imagedestroy($mainResized);
        imagedestroy($image);
    }

    /**
     * Odstraní EXIF metadata z obrázku
     */
    private function removeExif($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $clean = imagecreatetruecolor($width, $height);

        // Zachovat průhlednost pro PNG
        imagealphablending($clean, false);
        imagesavealpha($clean, true);

        imagecopy($clean, $image, 0, 0, 0, 0, $width, $height);

        return $clean;
    }

    /**
     * Vygeneruje kryptograficky bezpečný, unikátní název souboru
     */
    private function generateSafeFilename(): string
    {
        return bin2hex(random_bytes(16)) . '_' . time();
    }

    /**
     * Smaže obrázek a všechny jeho velikosti
     */
    public function deleteImage(string $filename): bool
    {
        $baseFilename = pathinfo($filename, PATHINFO_FILENAME);

        // Smazat hlavní soubor
        $mainPath = self::UPLOAD_DIR . $filename;
        if (file_exists($mainPath)) {
            unlink($mainPath);
        }

        // Smazat všechny velikosti
        foreach (self::SIZES as $size) {
            $sizePath = self::UPLOAD_DIR . $baseFilename . '-' . $size . 'w.webp';
            if (file_exists($sizePath)) {
                unlink($sizePath);
            }
        }

        return true;
    }

    /**
     * Vrátí cestu k obrázku pro srcset
     */
    public static function getSrcset(string $filename): string
    {
        $baseFilename = pathinfo($filename, PATHINFO_FILENAME);
        $srcset = [];

        foreach (self::SIZES as $size) {
            $srcset[] = "/uploads/infographics/{$baseFilename}-{$size}w.webp {$size}w";
        }

        return implode(', ', $srcset);
    }

    /**
     * Pomocná funkce pro chybové zprávy
     */
    private function error(string $message): array
    {
        return ['success' => false, 'filename' => null, 'error' => $message];
    }

    /**
     * Převede PHP upload error kódy na lidsky čitelné zprávy
     */
    private function getUploadError(int $errorCode): string
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Soubor přesahuje maximální velikost.',
            UPLOAD_ERR_FORM_SIZE => 'Soubor přesahuje maximální velikost.',
            UPLOAD_ERR_PARTIAL => 'Soubor byl nahrán pouze částečně.',
            UPLOAD_ERR_NO_FILE => 'Nebyl nahrán žádný soubor.',
            UPLOAD_ERR_NO_TMP_DIR => 'Chybí dočasná složka.',
            UPLOAD_ERR_CANT_WRITE => 'Nepodařilo se zapsat soubor na disk.',
            UPLOAD_ERR_EXTENSION => 'Upload byl zastaven rozšířením PHP.',
        ];

        return $errors[$errorCode] ?? 'Neznámá chyba při uploadu.';
    }

    /**
     * Vytvoří upload složku pokud neexistuje
     */
    public static function ensureUploadDir(): void
    {
        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);

            // Vytvořit .htaccess pro zabránění spouštění PHP
            $htaccess = self::UPLOAD_DIR . '.htaccess';
            file_put_contents($htaccess, "# Disable PHP execution\nphp_flag engine off\n<FilesMatch \"\\.ph(p[3-7]?|tml|ar)$\">\n    deny from all\n</FilesMatch>");
        }
    }
}
