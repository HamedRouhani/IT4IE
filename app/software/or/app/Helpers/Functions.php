<?php
function or_url($path = '')
{
    $b = defined('CURRENT_MODULE_URL') ? CURRENT_MODULE_URL : '/software/or-analyzer/';
    if (strpos($path, '?') === 0) return $b . 'index.php' . $path;
    if (empty($path)) return $b;
    return $b . '?' . ltrim($path, '?');
}

function or_isActiveMenu($menu)
{
    $current = $_GET['controller'] ?? 'dashboard';
    return strtolower($current) === strtolower($menu) ? 'active' : '';
}

function or_e($string)
{
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

function or_getStatusLabel($status)
{
    return ['draft'=>'پیش‌نویس','solving'=>'در حال حل','solved'=>'حل شده','infeasible'=>'غیرممکن'][$status] ?? $status;
}

function or_getStatusColor($status)
{
    return ['draft'=>'secondary','solving'=>'warning','solved'=>'success','infeasible'=>'danger'][$status] ?? 'secondary';
}

function or_getProblemTypeLabel($code)
{
    return ['TRANS'=>'حمل و نقل','ASSIGN'=>'تخصیص','TRANSSHIP'=>'ترانشیپمنت','SHORTEST'=>'کوتاه‌ترین مسیر'][$code] ?? $code;
}

function or_getMethodCategoryLabel($category)
{
    return ['initial'=>'روش اولیه','optimization'=>'بهینه‌سازی','exact'=>'دقیق','heuristic'=>'ابتکاری'][$category] ?? $category;
}

function or_getNodeLabel($type)
{
    return ['source'=>'مبدأ (عرضه)','destination'=>'مقصد (تقاضا)','dummy'=>'مجازی'][$type] ?? $type;
}

function or_showDate($date, $format = 'Y/m/d')
{
    if (empty($date)) return '-';
    return date($format, strtotime($date));
}

function or_formatNumber($number, $decimals = 0)
{
    return number_format((float)$number, $decimals, '.', ',');
}

function or_checkBalance($totalSupply, $totalDemand)
{
    if ($totalSupply === $totalDemand)
        return ['balanced'=>true, 'message'=>'✅ مسئله متوازن است.'];
    if ($totalSupply > $totalDemand) {
        $d = $totalSupply - $totalDemand;
        return ['balanced'=>false, 'message'=>"⚠️ عرضه بیشتر از تقاضا است. یک مقصد مجازی با تقاضای {$d} اضافه خواهد شد.",
                'dummy_type'=>'destination', 'dummy_capacity'=>$d];
    }
    $d = $totalDemand - $totalSupply;
    return ['balanced'=>false, 'message'=>"⚠️ تقاضا بیشتر از عرضه است. یک مبدأ مجازی با عرضه {$d} اضافه خواهد شد.",
            'dummy_type'=>'source', 'dummy_capacity'=>$d];
}