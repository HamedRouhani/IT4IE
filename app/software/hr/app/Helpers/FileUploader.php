<?php
namespace App\Software\Hr\Helpers;

/**
 * ============================================================
 * FileUploader - بارگذاری امن فایل
 * ============================================================
 * مسیر: app/software/hr/app/Helpers/FileUploader.php
 * 
 * فایل‌ها در پوشه‌ای خارج از public_html ذخیره می‌شوند
 * و فقط از طریق کنترلر امن قابل دسترسی هستند.
 * ============================================================
 */
class FileUploader
{
    /**
     * مسیر پایه ذخیره‌سازی (خارج از public_html)
     */
    private static function getBasePath(): string
    {
        // مسیر: /home/itieir/hr_secure_uploads/
        // از __DIR__ ماژول استفاده می‌کنیم و ۴ سطح بالا می‌رویم
        // __DIR__ = /home/itieir/public_html/app/software/hr/app/Helpers
        // ۶ سطح بالا = /home/itieir/
        $rootPath = dirname(__DIR__, 6);
        
        // اگر خواستید مسیر را تغییر دهید، فقط این خط را ویرایش کنید
        $uploadPath = $rootPath . '/hr_secure_uploads/documents/';
        
        // اگر پوشه وجود ندارد، ایجاد کن
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        return $uploadPath;
    }

    /**
     * بارگذاری فایل
     * 
     * @param array $file آرایه $_FILES['field_name']
     * @param int $systemId شناسه سیستم
     * @param int $employeeId شناسه کارمند
     * @param array $options گزینه‌های اضافی (allowed_extensions, max_size)
     * @return array نتیجه ['success' => bool, 'path' => string, 'file_name' => string, 'error' => string]
     */
    public static function upload(array $file, int $systemId, int $employeeId, array $options = []): array
    {
        // تنظیمات پیش‌فرض
        $allowedExtensions = $options['allowed_extensions'] ?? [
            'pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp',
            'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip'
        ];
        $maxSize = $options['max_size'] ?? (10 * 1024 * 1024); // 10 MB

        // بررسی خطای آپلود
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'error' => 'فایل نامعتبر است.'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => self::uploadErrorMessage($file['error'])];
        }

        // بررسی حجم
        if ($file['size'] > $maxSize) {
            return [
                'success' => false,
                'error'   => 'حجم فایل بیشتر از حد مجاز (' . self::formatBytes($maxSize) . ') است.'
            ];
        }

        // بررسی پسوند
        $originalName = $file['name'] ?? '';
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            return [
                'success' => false,
                'error'   => 'پسوند فایل مجاز نیست. پسوندهای مجاز: ' . implode(', ', $allowedExtensions)
            ];
        }

        // بررسی MIME Type واقعی
        $mimeType = self::detectMimeType($file['tmp_name']);
        $allowedMimes = [
            'application/pdf',
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'application/zip',
            'application/x-zip-compressed',
        ];

        if (!in_array($mimeType, $allowedMimes)) {
            return ['success' => false, 'error' => 'نوع فایل مجاز نیست.'];
        }

        // ایجاد پوشه اختصاصی برای این کارمند
        $basePath = self::getBasePath();
        $relativePath = "{$systemId}/{$employeeId}/";
        $targetDir = $basePath . $relativePath;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // تولید نام فایل امن
        $safeName = self::generateSafeFileName($originalName);
        $targetPath = $targetDir . $safeName;

        // جلوگیری از بازنویسی
        $counter = 1;
        while (file_exists($targetPath)) {
            $safeName = self::generateSafeFileName($originalName, $counter);
            $targetPath = $targetDir . $safeName;
            $counter++;
        }

        // انتقال فایل
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => false, 'error' => 'خطا در ذخیره فایل.'];
        }

        // تنظیم دسترسی (فقط owner)
        @chmod($targetPath, 0644);

        return [
            'success'        => true,
            'file_name'      => $originalName,
            'stored_name'    => $safeName,
            'relative_path'  => $relativePath . $safeName,
            'absolute_path'  => $targetPath,
            'file_size'      => $file['size'],
            'file_type'      => $mimeType,
            'extension'      => $extension,
        ];
    }

    /**
     * حذف فایل
     */
    public static function delete(?string $relativePath): bool
    {
        if (empty($relativePath)) {
            return false;
        }

        // جلوگیری از path traversal
        if (strpos($relativePath, '..') !== false) {
            return false;
        }

        $basePath = self::getBasePath();
        $fullPath = $basePath . $relativePath;

        // اطمینان از اینکه فایل داخل پوشه امن است
        $realBase = realpath($basePath);
        $realFile = realpath($fullPath);

        if ($realBase === false || $realFile === false) {
            return false;
        }

        if (strpos($realFile, $realBase) !== 0) {
            return false;
        }

        if (is_file($fullPath)) {
            return @unlink($fullPath);
        }

        return false;
    }

    /**
     * دریافت مسیر کامل فایل
     */
    public static function getFullPath(?string $relativePath): ?string
    {
        if (empty($relativePath) || strpos($relativePath, '..') !== false) {
            return null;
        }

        $basePath = self::getBasePath();
        $fullPath = $basePath . $relativePath;

        // بررسی امنیتی
        $realBase = realpath($basePath);
        $realFile = realpath($fullPath);

        if ($realBase === false || $realFile === false) {
            return null;
        }

        if (strpos($realFile, $realBase) !== 0) {
            return null;
        }

        return is_file($fullPath) ? $fullPath : null;
    }

    /**
     * بررسی وجود فایل
     */
    public static function exists(?string $relativePath): bool
    {
        return self::getFullPath($relativePath) !== null;
    }

    // ============================================
    // متدهای کمکی
    // ============================================

    /**
     * تولید نام فایل امن
     */
    private static function generateSafeFileName(string $originalName, int $counter = 0): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $date = date('Y-m-d');
        $random = bin2hex(random_bytes(8));
        
        $suffix = $counter > 0 ? "_{$counter}" : '';
        
        return "{$date}_{$random}{$suffix}.{$extension}";
    }

    /**
     * تشخیص MIME Type واقعی فایل
     */
    private static function detectMimeType(string $filePath): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            return $mime ?: 'application/octet-stream';
        }

        if (function_exists('mime_content_type')) {
            return mime_content_type($filePath) ?: 'application/octet-stream';
        }

        return 'application/octet-stream';
    }

    /**
     * پیام خطای آپلود
     */
    private static function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE   => 'حجم فایل بیشتر از حد مجاز سرور است.',
            UPLOAD_ERR_FORM_SIZE  => 'حجم فایل بیشتر از حد مجاز فرم است.',
            UPLOAD_ERR_PARTIAL    => 'فایل به‌طور کامل آپلود نشد.',
            UPLOAD_ERR_NO_FILE    => 'فایلی انتخاب نشده است.',
            UPLOAD_ERR_NO_TMP_DIR => 'پوشه موقت یافت نشد.',
            UPLOAD_ERR_CANT_WRITE => 'خطا در نوشتن فایل.',
            UPLOAD_ERR_EXTENSION  => 'آپلود فایل توسط افزونه متوقف شد.',
            default               => 'خطای ناشناخته در آپلود فایل.',
        };
    }

    /**
     * فرمت حجم
     */
    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * آیکون بر اساس پسوند
     */
    public static function getIcon(string $extension): string
    {
        $icons = [
            'pdf'  => 'fas fa-file-pdf',
            'doc'  => 'fas fa-file-word',
            'docx' => 'fas fa-file-word',
            'xls'  => 'fas fa-file-excel',
            'xlsx' => 'fas fa-file-excel',
            'jpg'  => 'fas fa-file-image',
            'jpeg' => 'fas fa-file-image',
            'png'  => 'fas fa-file-image',
            'gif'  => 'fas fa-file-image',
            'webp' => 'fas fa-file-image',
            'zip'  => 'fas fa-file-archive',
            'txt'  => 'fas fa-file-alt',
        ];
        return $icons[$extension] ?? 'fas fa-file';
    }

    /**
     * رنگ آیکون بر اساس پسوند
     */
    public static function getIconColor(string $extension): string
    {
        $colors = [
            'pdf'  => '#dc2626',
            'doc'  => '#2563eb',
            'docx' => '#2563eb',
            'xls'  => '#16a34a',
            'xlsx' => '#16a34a',
            'jpg'  => '#9333ea',
            'jpeg' => '#9333ea',
            'png'  => '#9333ea',
            'gif'  => '#9333ea',
            'webp' => '#9333ea',
            'zip'  => '#f59e0b',
        ];
        return $colors[$extension] ?? '#6c757d';
    }
}