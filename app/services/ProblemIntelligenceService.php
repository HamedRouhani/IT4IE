<?php

namespace App\Services;

class ProblemIntelligenceService
{
    public function analyze($problem)
    {
        $problem = trim((string)$problem);

        if ($problem === '') {
            return [
                'success' => false,
                'message' => 'لطفاً مسئله خود را توضیح دهید.'
            ];
        }

        $text = mb_strtolower($problem, 'UTF-8');

        $domains = [];
        $methods = [];
        $modules = [];

        /*
         * انتخاب تأمین‌کننده
         */
        if (
            $this->containsAny($text, [
                'تامین کننده',
                'تأمین کننده',
                'تامین‌کننده',
                'تأمین‌کننده',
                'supplier',
                'فروشنده'
            ])
        ) {
            $domains[] = 'انتخاب تأمین‌کننده';

            $methods[] = 'تحلیل تصمیم‌گیری چندمعیاره (MCDM)';
            $methods[] = 'AHP / TOPSIS';
            $methods[] = 'تحلیل حساسیت';

            $modules[] = [
                'name' => 'MCDM Analyzer',
                'title' => 'تحلیل تصمیم‌گیری چندمعیاره',
                'url' => '/software/mcdm-analyzer/'
            ];
        }

        /*
         * تولید و برنامه‌ریزی
         */
        if (
            $this->containsAny($text, [
                'تولید',
                'برنامه تولید',
                'زمانبندی تولید',
                'زمان‌بندی تولید',
                'ظرفیت تولید',
                'خط تولید',
                'ماشین آلات',
                'ماشین‌آلات'
            ])
        ) {
            $domains[] = 'برنامه‌ریزی و کنترل تولید';

            $methods[] = 'تحقیق در عملیات';
            $methods[] = 'بهینه‌سازی';
            $methods[] = 'برنامه‌ریزی تولید';

            $modules[] = [
                'name' => 'OR Analyzer',
                'title' => 'تحقیق در عملیات و بهینه‌سازی',
                'url' => '/software/or-analyzer/'
            ];
        }

        /*
         * آماری / کیفیت / داده
         */
        if (
            $this->containsAny($text, [
                'آمار',
                'داده',
                'کیفیت',
                'کنترل کیفیت',
                'پیش بینی',
                'پیش‌بینی',
                'رگرسیون',
                'میانگین',
                'واریانس',
                'انحراف معیار'
            ])
        ) {
            $domains[] = 'تحلیل داده و آمار';

            $methods[] = 'تحلیل آماری';
            $methods[] = 'تحلیل داده';
            $methods[] = 'مدل‌سازی آماری';

            $modules[] = [
                'name' => 'StatLab',
                'title' => 'تحلیل آماری و داده',
                'url' => '/software/statlab-analyzer/'
            ];
        }

        /*
         * مدیریت پروژه
         */
        if (
            $this->containsAny($text, [
                'پروژه',
                'تاخیر پروژه',
                'تأخیر پروژه',
                'زمان پروژه',
                'ریسک پروژه',
                'برنامه پروژه'
            ])
        ) {
            $domains[] = 'مدیریت پروژه';

            $methods[] = 'تحلیل پروژه';
            $methods[] = 'مدیریت زمان';
            $methods[] = 'مدیریت ریسک';

            $modules[] = [
                'name' => 'PMBOK Analyzer',
                'title' => 'تحلیل و مدیریت پروژه',
                'url' => '/software/pmbok-analyzer/'
            ];
        }

        /*
         * تحلیل نیازمندی / فرآیند کسب‌وکار
         */
        if (
            $this->containsAny($text, [
                'نیازمندی',
                'نیازمندی ها',
                'نیازمندی‌ها',
                'فرآیند',
                'فرایند',
                'کسب و کار',
                'کسب‌وکار',
                'سیستم'
            ])
        ) {
            $domains[] = 'تحلیل کسب‌وکار و فرآیند';

            $methods[] = 'تحلیل کسب‌وکار';
            $methods[] = 'تحلیل نیازمندی';
            $methods[] = 'مدل‌سازی فرآیند';

            $modules[] = [
                'name' => 'BABOK Analyzer',
                'title' => 'تحلیل کسب‌وکار و نیازمندی',
                'url' => '/software/babok-analyzer/'
            ];
        }

        $domains = array_values(array_unique($domains));
        $methods = array_values(array_unique($methods));

        if (empty($domains)) {
            return [
                'success' => true,
                'confidence' => 'low',
                'domains' => [],
                'methods' => [],
                'modules' => [],
                'message' =>
                    'برای تشخیص دقیق‌تر، مسئله شما نیاز به اطلاعات بیشتری دارد.'
            ];
        }

        return [
            'success' => true,
            'confidence' => count($domains) >= 2 ? 'medium' : 'initial',
            'domains' => $domains,
            'methods' => $methods,
            'modules' => $modules,
            'message' =>
                'بر اساس توضیح اولیه شما، مسیرهای زیر برای بررسی مناسب به نظر می‌رسند.'
        ];
    }

    private function containsAny($text, array $keywords)
    {
        foreach ($keywords as $keyword) {
            if (
                mb_strpos(
                    $text,
                    mb_strtolower($keyword, 'UTF-8')
                ) !== false
            ) {
                return true;
            }
        }

        return false;
    }
}