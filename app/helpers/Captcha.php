<?php
namespace App\Helpers;

class Captcha
{
    private $width = 380;
    private $height = 90;
    private $length = 4;
    private $sessionKey = 'captcha_code';
    private $fontFile;
    
    public function __construct()
    {
        // پیدا کردن فونت مناسب
        $this->fontFile = __DIR__ . '/../../public/assets/fonts/arial.ttf';
        
        if (!file_exists($this->fontFile)) {
            // فونت‌های سیستمی
            $systemFonts = [
                '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
                '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
                '/usr/share/fonts/truetype/msttcorefonts/Arial_Bold.ttf',
                '/usr/share/fonts/truetype/ubuntu/Ubuntu-B.ttf',
                '/System/Library/Fonts/Helvetica.ttf',
                'C:/Windows/Fonts/arialbd.ttf',
                'C:/Windows/Fonts/arial.ttf',
            ];
            
            foreach ($systemFonts as $font) {
                if (file_exists($font)) {
                    $this->fontFile = $font;
                    break;
                }
            }
        }
    }
    
    public function generate()
    {
        // تولید ۴ رقم تصادفی
        $code = '';
        for ($i = 0; $i < $this->length; $i++) {
            $code .= rand(0, 9);
        }
        
        $_SESSION[$this->sessionKey] = $code;
        
        // ایجاد تصویر
        $image = imagecreatetruecolor($this->width, $this->height);
        
        // رنگ‌ها
        $bgColor = imagecolorallocate($image, 245, 248, 252);
        $lineColor = imagecolorallocate($image, 150, 180, 220);
        $noiseColor = imagecolorallocate($image, 200, 215, 235);
        
        // پس‌زمینه
        imagefill($image, 0, 0, $bgColor);
        
        // خطوط
        for ($i = 0; $i < 8; $i++) {
            imageline(
                $image,
                rand(0, $this->width),
                rand(0, $this->height),
                rand(0, $this->width),
                rand(0, $this->height),
                $lineColor
            );
        }
        
        // نویز
        for ($i = 0; $i < 150; $i++) {
            imagesetpixel(
                $image,
                rand(0, $this->width),
                rand(0, $this->height),
                $noiseColor
            );
        }
        
        // ============================================
        // نمایش اعداد با فونت بزرگ
        // ============================================
        if (file_exists($this->fontFile)) {
            // استفاده از فونت TTF با سایز بزرگ
            $fontSize = 42; // سایز بزرگ
            $spacing = 50;
            $totalWidth = ($this->length - 1) * $spacing;
            $startX = ($this->width - $totalWidth) / 2 - 10;
            $startY = 62;
            
            for ($i = 0; $i < $this->length; $i++) {
                $angle = rand(-5, 5);
                $x = $startX + ($i * $spacing);
                $y = $startY + rand(-3, 3);
                
                // رنگ‌های متفاوت
                $colors = [
                    imagecolorallocate($image, 20, 70, 200),
                    imagecolorallocate($image, 180, 40, 40),
                    imagecolorallocate($image, 0, 150, 100),
                    imagecolorallocate($image, 150, 100, 0),
                ];
                $color = $colors[$i % count($colors)];
                
                imagettftext(
                    $image,
                    $fontSize,
                    $angle,
                    $x,
                    $y,
                    $color,
                    $this->fontFile,
                    $code[$i]
                );
            }
        } else {
            // Fallback: فونت داخلی
            $fontSize = 5; // بزرگترین فونت داخلی
            $spacing = 35;
            $totalWidth = ($this->length - 1) * $spacing;
            $startX = ($this->width - $totalWidth) / 2;
            $startY = 35;
            
            for ($i = 0; $i < $this->length; $i++) {
                $x = $startX + ($i * $spacing) + 5;
                $y = $startY + rand(-5, 5);
                
                $colors = [
                    imagecolorallocate($image, 20, 70, 200),
                    imagecolorallocate($image, 180, 40, 40),
                    imagecolorallocate($image, 0, 150, 100),
                    imagecolorallocate($image, 150, 100, 0),
                ];
                $color = $colors[$i % count($colors)];
                
                imagechar($image, $fontSize, $x, $y, $code[$i], $color);
            }
        }
        
        // پاک کردن خروجی قبلی
        if (ob_get_length()) {
            ob_clean();
        }
        
        // هدرها
        header('Content-Type: image/png');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        imagepng($image);
        imagedestroy($image);
        exit;
    }
    
    public function verify($code)
    {
        if (empty($code) || empty($_SESSION[$this->sessionKey])) {
            return false;
        }
        
        $isValid = $code === $_SESSION[$this->sessionKey];
        unset($_SESSION[$this->sessionKey]);
        return $isValid;
    }
}