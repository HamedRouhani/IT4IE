<?php
namespace App\Software\Mcdm\Controllers;

use App\Software\Mcdm\Core\Controller;

class AssistantController extends Controller
{
    public function index()
    {
        $recommendation = null;
        $suggestedCriteria = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // منطق ساده پیشنهاد روش
            $needsWeights = isset($_POST['needs_weights']);
            $expertDriven = isset($_POST['expert_driven']);
            $conflictCriteria = isset($_POST['conflict']);
            $quantitative = isset($_POST['quantitative']);
            $transparency = isset($_POST['transparency']);
            $altCount = (int)($_POST['alt_count'] ?? 4);
            $industry = $_POST['industry'] ?? 'MFG';
            
            // امتیازدهی ساده به روش‌ها
            $scores = ['AHP' => 0, 'TOPSIS' => 0, 'VIKOR' => 0, 'SAW' => 0];
            
            if ($needsWeights)    { $scores['AHP'] += 3; $scores['SAW'] += 1; }
            if ($expertDriven)    { $scores['AHP'] += 2; }
            if ($altCount >= 5)   { $scores['TOPSIS'] += 2; $scores['VIKOR'] += 2; $scores['AHP'] -= 1; }
            if ($conflictCriteria){ $scores['VIKOR'] += 3; }
            if ($quantitative)    { $scores['TOPSIS'] += 2; $scores['SAW'] += 1; }
            if ($transparency)    { $scores['SAW'] += 3; }
            
            arsort($scores);
            
            $reasons = [
                'AHP'    => 'نیاز به وزن‌دهی خبره‌محور با بررسی سازگاری قضاوت‌ها',
                'TOPSIS' => 'داده کمی و تعداد گزینه‌های زیاد؛ فاصله‌سنجی از ایده‌آل',
                'VIKOR'  => 'معیارهای متعارض؛ نیاز به راه‌حل توافق قابل دفاع',
                'SAW'    => 'شفافیت و سادگی برای ارائه به مدیریت',
            ];
            
            $recommendation = [];
            foreach ($scores as $m => $s) {
                $recommendation[] = ['method' => $m, 'score' => $s, 'reason' => $reasons[$m]];
            }
            
            // معیارهای پیشنهادی بر اساس صنعت
            $criteriaMap = [
                'MFG' => ['هزینه', 'کیفیت', 'زمان تحویل', 'انعطاف‌پذیری', 'خدمات پس از فروش'],
                'IT'  => ['هزینه مالکیت', 'قابلیت فنی', 'پشتیبانی', 'مقیاس‌پذیری', 'امنیت'],
                'OG'  => ['ایمنی HSE', 'هزینه', 'تجربه پیمانکار', 'زمان اجرا', 'انطباق با استانداردها'],
                'SVC' => ['رضایت مشتری', 'هزینه', 'کیفیت خدمت', 'دسترسی', 'برند'],
            ];
            $suggestedCriteria = $criteriaMap[$industry] ?? $criteriaMap['MFG'];
        }
        
        $this->view('assistant/index', [
            'pageTitle' => 'دستیار هوشمند',
            'currentPage' => 'assistant',
            'recommendation' => $recommendation,
            'suggestedCriteria' => $suggestedCriteria,
        ]);
    }
}