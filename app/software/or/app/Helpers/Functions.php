<?php
/**
 * توابع کمکی ماژول OR Analyzer
 * مسیر: app/software/or/app/Helpers/Functions.php
 */

if (!function_exists('or_url')) {
    function or_url($path = '') {
        $b = defined('CURRENT_MODULE_URL') ? CURRENT_MODULE_URL : '/software/or-analyzer/';
        if (strpos($path, '?') === 0) return $b . 'index.php' . $path;
        if (empty($path)) return $b;
        return $b . '?' . ltrim($path, '?');
    }
}

if (!function_exists('or_isActiveMenu')) {
    function or_isActiveMenu($menu) {
        $current = $_GET['controller'] ?? 'dashboard';
        return strtolower($current) === strtolower($menu) ? 'active' : '';
    }
}

if (!function_exists('or_e')) {
    function or_e($string) {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }
}

// ✅ این تابع حیاتی است و باید وجود داشته باشد
if (!function_exists('or_truncateText')) {
    function or_truncateText($text, $length = 100, $suffix = '...') {
        $text = (string)$text;
        if (mb_strlen($text, 'UTF-8') > $length) {
            return mb_substr($text, 0, $length, 'UTF-8') . $suffix;
        }
        return $text;
    }
}

if (!function_exists('or_getStatusLabel')) {
    function or_getStatusLabel($status) {
        return ['draft'=>'پیش‌نویس','solving'=>'در حال حل','solved'=>'حل شده','infeasible'=>'غیرممکن'][$status] ?? $status;
    }
}

if (!function_exists('or_getStatusColor')) {
    function or_getStatusColor($status) {
        return ['draft'=>'secondary','solving'=>'warning','solved'=>'success','infeasible'=>'danger'][$status] ?? 'secondary';
    }
}

if (!function_exists('or_getProblemTypeLabel')) {
    function or_getProblemTypeLabel($code) {
        return [
            'TRANS'=>'حمل و نقل', 'ASSIGN'=>'تخصیص', 'TRANSSHIP'=>'ترانشیپمنت', 
            'SHORTEST'=>'کوتاه‌ترین مسیر', 'LP'=>'برنامه‌ریزی خطی'
        ][$code] ?? $code;
    }
}

if (!function_exists('or_getMethodCategoryLabel')) {
    function or_getMethodCategoryLabel($category) {
        return ['initial'=>'روش اولیه','optimization'=>'بهینه‌سازی','exact'=>'دقیق','heuristic'=>'ابتکاری'][$category] ?? $category;
    }
}

if (!function_exists('or_getNodeLabel')) {
    function or_getNodeLabel($type) {
        return ['source'=>'مبدأ (عرضه)','destination'=>'مقصد (تقاضا)','dummy'=>'مجازی'][$type] ?? $type;
    }
}

if (!function_exists('or_showDate')) {
    function or_showDate($date, $format = 'Y/m/d') {
        if (empty($date)) return '-';
        return date($format, strtotime($date));
    }
}

if (!function_exists('or_formatNumber')) {
    function or_formatNumber($number, $decimals = 0) {
        return number_format((float)$number, $decimals, '.', ',');
    }
}

if (!function_exists('or_checkBalance')) {
    function or_checkBalance($totalSupply, $totalDemand) {
        if ($totalSupply === $totalDemand)
            return ['balanced'=>true, 'message'=>'✅ مسئله متوازن است.'];
        if ($totalSupply > $totalDemand) {
            $d = $totalSupply - $totalDemand;
            return ['balanced'=>false, 'message'=>"⚠️ عرضه بیشتر از تقاضا است. یک مقصد مجازی با تقاضای {$d} اضافه خواهد شد.", 'dummy_type'=>'destination', 'dummy_capacity'=>$d];
        }
        $d = $totalDemand - $totalSupply;
        return ['balanced'=>false, 'message'=>"⚠️ تقاضا بیشتر از عرضه است. یک مبدأ مجازی با عرضه {$d} اضافه خواهد شد.", 'dummy_type'=>'source', 'dummy_capacity'=>$d];
    }

    /**
     * فرمت‌بندی هوشمند اعداد
     * - اعداد صحیح (مثل 220.0000) را به صورت "220" نمایش می‌دهد
     * - اعداد اعشاری (مثل 215.0012) را با حفظ ارقام معنادار نمایش می‌دهد
     * 
     * @param mixed $value مقدار عددی
     * @param int $maxDecimals حداکثر تعداد ارقام اعشار (پیش‌فرض 4)
     * @return string
     */
    function orFormatNumber($value, $maxDecimals = 4) {
        if ($value === null || $value === '' || $value === '-') return '-';
        
        $float = (float)$value;
        
        // اگر عدد صحیح است، بدون اعشار نمایش بده
        if ($float == floor($float)) {
            return number_format($float, 0, '.', ',');
        }
        
        // در غیر این صورت، با حذف صفرهای اضافی در انتها نمایش بده
        $formatted = number_format($float, $maxDecimals, '.', '');
        $formatted = rtrim($formatted, '0');
        $formatted = rtrim($formatted, '.');
        
        return $formatted;
    }
}