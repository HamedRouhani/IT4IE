<?php
namespace App\Software\TimeStudyAnalyzer\Controllers;

use App\Core\Controller;

abstract class StudyControllerBase extends Controller
{
    protected static function clip(string $value, int $length): string
    {
        return function_exists('mb_substr') ? mb_substr($value, 0, $length, 'UTF-8') : substr($value, 0, $length);
    }

    protected function userId(): int
    {
        $this->requireAuth();
        return (int) ($_SESSION['user_id'] ?? 0);
    }

    protected function postOnly(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            $this->redirect(ts_url());
        }
        $this->verifyCsrf('درخواست معتبر نیست یا نشست منقضی شده است؛ صفحه را تازه کنید.');
    }

    protected function renderPage(string $view, array $data = []): void
    {
        $data = array_merge([
            'pageTitle' => 'Time Study Analyzer',
            'softwareName' => 'Time Study Analyzer',
            'csrfHtml' => $this->csrfField(),
            'flash' => ts_flash_get(),
        ], $data);
        $data['title'] = $data['pageTitle'] . ' - Time Study Analyzer';
        $this->renderSoftware($view, $data, 'time-study-analyzer');
    }

    protected function inputString(string $key, string $label, int $maxLength, bool $required = false): ?string
    {
        $value = $_POST[$key] ?? '';
        if (!is_string($value)) throw new \InvalidArgumentException("فیلد «{$label}» معتبر نیست.");
        $value = trim($value);
        $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
        if ($required && $value === '') throw new \InvalidArgumentException("فیلد «{$label}» الزامی است.");
        if ($length > $maxLength) throw new \InvalidArgumentException("طول فیلد «{$label}» بیش از حد مجاز است.");
        return $value === '' ? null : $value;
    }

    protected function decimal(string $raw, string $label, float $min, float $max, int $scale = 2): float
    {
        $raw = self::latinDigits(trim($raw));
        $raw = str_replace(['٬', ','], '', $raw);
        $raw = str_replace('٫', '.', $raw);
        if (!preg_match('/^\d+(?:\.\d+)?$/D', $raw)) throw new \InvalidArgumentException("«{$label}» باید عدد معتبر باشد.");
        $parts = explode('.', $raw, 2);
        if (isset($parts[1]) && strlen($parts[1]) > $scale) throw new \InvalidArgumentException("«{$label}» حداکثر {$scale} رقم اعشار دارد.");
        $number = (float) $raw;
        if (!is_finite($number) || $number < $min || $number > $max) throw new \InvalidArgumentException("«{$label}» باید بین {$min} و {$max} باشد.");
        return $number;
    }

    protected static function latinDigits(string $value): string
    {
        return strtr($value, [
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
        ]);
    }

    protected function jalaliDate(string $raw, string $label): string
    {
        $raw = str_replace(['-', '٫'], ['/', '/'], self::latinDigits(trim($raw)));
        if (!preg_match('/^(13\d{2}|14\d{2}|1500)\/(\d{1,2})\/(\d{1,2})$/D', $raw, $match)) {
            throw new \InvalidArgumentException("«{$label}» را به‌شکل شمسی سال/ماه/روز وارد کنید.");
        }
        $normalized = (int) $match[1] . '/' . (int) $match[2] . '/' . (int) $match[3];
        if (!class_exists('App\\Helpers\\DateHelper')) throw new \RuntimeException('سرویس تبدیل تاریخ سایت در دسترس نیست.');
        $gregorian = \App\Helpers\DateHelper::toGregorian($raw);
        if (!$gregorian || \App\Helpers\DateHelper::toJalali($gregorian, 'Y/n/j') !== $normalized) {
            throw new \InvalidArgumentException("«{$label}» معتبر نیست.");
        }
        return $gregorian;
    }

    protected function studyPayload(string $method): array
    {
        $confidence = self::latinDigits((string) ($_POST['confidence_level'] ?? '95'));
        if (!in_array($confidence, ['90', '95', '99'], true)) throw new \InvalidArgumentException('سطح اطمینان باید ۹۰، ۹۵ یا ۹۹ درصد باشد.');
        $basis = (string) ($_POST['allowance_basis'] ?? 'standard');
        if (!in_array($basis, ['standard', 'normal'], true)) throw new \InvalidArgumentException('مبنای allowance معتبر نیست.');
        $allowances = [];
        foreach (['personal', 'fatigue', 'delay', 'other'] as $key) {
            $allowances[$key] = $this->decimal((string) ($_POST[$key . '_allowance_pct'] ?? '0'), 'درصد allowance', 0, 79.99, 2);
        }
        if (array_sum($allowances) >= 80) throw new \InvalidArgumentException('مجموع allowance باید کمتر از ۸۰٪ باشد.');
        $precisionMax = $method === 'stopwatch' ? 50 : 30;
        $precision = $this->decimal((string) ($_POST['precision_pct'] ?? '5'), 'دقت هدف', 0.1, $precisionMax, 2);
        $date = $this->jalaliDate((string) ($_POST['study_date'] ?? ''), 'تاریخ مطالعه');
        return [
            'method' => $method,
            'title' => $this->inputString('title', 'عنوان مطالعه', 180, true),
            'process_name' => $this->inputString('process_name', 'نام عملیات', 180, true),
            'department' => $this->inputString('department', 'واحد سازمانی', 120),
            'product_name' => $this->inputString('product_name', 'محصول/خدمت', 180),
            'work_center' => $this->inputString('work_center', 'ایستگاه کاری', 120),
            'operator_ref' => $this->inputString('operator_ref', 'شناسه مشاهده‌شونده', 120),
            'study_date' => $date,
            'shift_label' => $this->inputString('shift_label', 'شیفت', 80),
            'unit_name' => $this->inputString('unit_name', 'واحد خروجی', 60, true),
            'method_description' => $this->inputString('method_description', 'شرح روش فعلی', 5000, true),
            'work_conditions' => $this->inputString('work_conditions', 'شرایط کار', 5000),
            'confidence_level' => $confidence,
            'precision_pct' => $precision,
            'allowance_basis' => $basis,
            'personal_allowance_pct' => $allowances['personal'],
            'fatigue_allowance_pct' => $allowances['fatigue'],
            'delay_allowance_pct' => $allowances['delay'],
            'other_allowance_pct' => $allowances['other'],
            'allowance_rationale' => $this->inputString('allowance_rationale', 'مبنای allowance', 1000),
        ];
    }

    protected function childrenPayload(string $method): array
    {
        $children = [];
        if ($method === 'stopwatch') {
            $names = $_POST['element_name'] ?? [];
            $codes = $_POST['element_code'] ?? [];
            $types = $_POST['element_type'] ?? [];
            $frequencies = $_POST['element_frequency'] ?? [];
            $notes = $_POST['element_note'] ?? [];
            if (!is_array($names) || !is_array($codes) || !is_array($types) || !is_array($frequencies) || !is_array($notes)) throw new \InvalidArgumentException('فهرست عناصر معتبر نیست.');
            foreach ($names as $index => $name) {
                if (!is_string($name)) throw new \InvalidArgumentException('نام عنصر کار معتبر نیست.');
                $name = trim($name);
                if ($name === '') continue;
                if (count($children) >= 100) throw new \InvalidArgumentException('حداکثر ۱۰۰ عنصر برای هر مطالعه مجاز است.');
                $type = (string) ($types[$index] ?? 'manual');
                if (!in_array($type, ['manual', 'machine'], true)) throw new \InvalidArgumentException('نوع عنصر کار معتبر نیست.');
                $children[] = [
                    'name' => self::clip($name, 180), 'code' => isset($codes[$index]) ? self::clip(trim((string) $codes[$index]), 32) : null,
                    'type' => $type, 'frequency' => $this->decimal((string) ($frequencies[$index] ?? '1'), 'تکرار در هر چرخه', 0.0001, 10000, 4),
                    'notes' => isset($notes[$index]) ? self::clip(trim((string) $notes[$index]), 500) : null,
                ];
            }
            if (!$children) throw new \InvalidArgumentException('برای زمان‌سنجی مستقیم، حداقل یک عنصر کار تعریف کنید.');
        } elseif ($method === 'work_sampling') {
            $names = $_POST['category_name'] ?? [];
            $types = $_POST['category_type'] ?? [];
            if (!is_array($names) || !is_array($types)) throw new \InvalidArgumentException('فهرست دسته‌های نمونه‌برداری معتبر نیست.');
            $seen = [];
            foreach ($names as $index => $name) {
                if (!is_string($name)) throw new \InvalidArgumentException('نام دسته معتبر نیست.');
                $name = trim($name);
                if ($name === '') continue;
                if (count($children) >= 30) throw new \InvalidArgumentException('حداکثر ۳۰ دستهٔ مشاهده مجاز است.');
                $type = (string) ($types[$index] ?? 'other');
                if (!in_array($type, ['productive','machine','personal','delay','other'], true)) throw new \InvalidArgumentException('طبقهٔ دستهٔ مشاهده معتبر نیست.');
                $folded = function_exists('mb_strtolower') ? mb_strtolower($name, 'UTF-8') : strtolower($name);
                if (isset($seen[$folded])) throw new \InvalidArgumentException('نام دسته‌های مشاهده باید یکتا باشد.');
                $seen[$folded] = true;
                $children[] = ['name' => self::clip($name, 120), 'type' => $type];
            }
            if (count($children) < 2) throw new \InvalidArgumentException('برای نمونه‌برداری، حداقل دو دسته تعریف کنید.');
        } else {
            throw new \InvalidArgumentException('روش مطالعه معتبر نیست.');
        }
        return $children;
    }
}
