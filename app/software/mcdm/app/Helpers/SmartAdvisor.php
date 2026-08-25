<?php

namespace App\Software\Mcdm\Helpers;

class SmartAdvisor
{
    /**
     * پیشنهاد معیارهای پیش‌فرض بر اساس صنعت
     */
    public function suggestCriteria(string $industry): array
    {
        $criteria = match ($industry) {
            'MFG' => [
                ['name' => 'هزینه', 'type' => 'cost'],
                ['name' => 'کیفیت', 'type' => 'benefit'],
                ['name' => 'زمان تحویل', 'type' => 'cost'],
                ['name' => 'انعطاف‌پذیری', 'type' => 'benefit'],
                ['name' => 'خدمات پس از فروش', 'type' => 'benefit'],
            ],
            'OG' => [
                ['name' => 'ایمنی HSE', 'type' => 'benefit'],
                ['name' => 'هزینه', 'type' => 'cost'],
                ['name' => 'تجربه پیمانکار', 'type' => 'benefit'],
                ['name' => 'زمان اجرا', 'type' => 'cost'],
                ['name' => 'انطباق با استانداردها', 'type' => 'benefit'],
            ],
            'IT' => [
                ['name' => 'هزینه مالکیت', 'type' => 'cost'],
                ['name' => 'قابلیت فنی', 'type' => 'benefit'],
                ['name' => 'پشتیبانی', 'type' => 'benefit'],
                ['name' => 'مقیاس‌پذیری', 'type' => 'benefit'],
                ['name' => 'امنیت', 'type' => 'benefit'],
            ],
            'SVC' => [
                ['name' => 'رضایت مشتری', 'type' => 'benefit'],
                ['name' => 'هزینه', 'type' => 'cost'],
                ['name' => 'کیفیت خدمت', 'type' => 'benefit'],
                ['name' => 'دسترسی', 'type' => 'benefit'],
                ['name' => 'برند', 'type' => 'benefit'],
            ],
            default => [
                ['name' => 'هزینه', 'type' => 'cost'],
                ['name' => 'کیفیت', 'type' => 'benefit'],
                ['name' => 'زمان', 'type' => 'cost'],
                ['name' => 'ریسک', 'type' => 'cost'],
            ],
        };

        return $criteria;
    }

    /**
     * توصیه روش بر اساس ویژگی‌های مسئله
     */
    public function recommendMethod(array $ctx): array
    {
        $scores = ['AHP' => 0, 'TOPSIS' => 0, 'VIKOR' => 0, 'SAW' => 0];

        if (($ctx['needs_weights'] ?? false)) {
            $scores['AHP'] += 3;
            $scores['SAW'] += 1;
        }
        if (($ctx['expert_driven'] ?? false)) {
            $scores['AHP'] += 2;
        }
        if (($ctx['alt_count'] ?? 0) >= 5) {
            $scores['TOPSIS'] += 2;
            $scores['VIKOR'] += 2;
            $scores['AHP'] -= 1;
        }
        if (($ctx['conflict'] ?? false)) {
            $scores['VIKOR'] += 3;
        }
        if (($ctx['quantitative'] ?? false)) {
            $scores['TOPSIS'] += 2;
            $scores['SAW'] += 1;
        }
        if (($ctx['transparency'] ?? false)) {
            $scores['SAW'] += 3;
        }

        arsort($scores);
        $reasons = [
            'AHP' => 'نیاز به وزن‌دهی خبره‌محور با بررسی سازگاری قضاوت‌ها',
            'TOPSIS' => 'داده کمی و تعداد گزینه‌های زیاد؛ فاصله‌سنجی از ایده‌آل',
            'VIKOR' => 'معیارهای متعارض؛ نیاز به راه‌حل توافق قابل دفاع',
            'SAW' => 'شفافیت و سادگی برای ارائه به مدیریت',
        ];
        
        $out = [];
        foreach ($scores as $m => $s) {
            $out[] = [
                'method' => $m,
                'score' => $s,
                'reason' => $reasons[$m]
            ];
        }
        
        return $out;
    }
}