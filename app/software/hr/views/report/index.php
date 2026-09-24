<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-chart-pie"></i> گزارش‌های تحلیلی
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                گزارش‌های جامع منابع انسانی بر اساس استانداردهای ISO 30414
            </p>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="hr-report-grid">
        <?php
        $reports = [
            [
                'action' => 'summary',
                'icon'   => 'fas fa-chart-pie',
                'color'  => '#1E40AF',
                'title'  => 'خلاصه سازمانی',
                'desc'   => 'نمای کلی از آمار کارکنان، دپارتمان‌ها، توزیع جنسیت، نوع قرارداد و استخدام‌های سال',
            ],
            [
                'action' => 'employees',
                'icon'   => 'fas fa-users',
                'color'  => '#0F766E',
                'title'  => 'گزارش کارکنان',
                'desc'   => 'فهرست کامل کارکنان با فیلتر دپارتمان، وضعیت اشتغال و نوع قرارداد',
            ],
            [
                'action' => 'recruitment',
                'icon'   => 'fas fa-user-plus',
                'color'  => '#2563EB',
                'title'  => 'جذب و استخدام',
                'desc'   => 'قیف متقاضیان، منابع جذب، مصاحبه‌ها و میانگین زمان جذب',
            ],
            [
                'action' => 'performance',
                'icon'   => 'fas fa-chart-line',
                'color'  => '#f59e0b',
                'title'  => 'عملکرد و اهداف',
                'desc'   => 'وضعیت اهداف OKR/MBO، ارزیابی‌های عملکرد و توزیع رتبه‌ها',
            ],
            [
                'action' => 'training',
                'icon'   => 'fas fa-graduation-cap',
                'color'  => '#059669',
                'title'  => 'آموزش',
                'desc'   => 'دوره‌های آموزشی، ثبت‌نام‌ها، هزینه آموزش و وضعیت شرکت‌کنندگان',
            ],
            [
                'action' => 'compensation',
                'icon'   => 'fas fa-money-bill-wave',
                'color'  => '#dc2626',
                'title'  => 'جبران خدمات',
                'desc'   => 'حقوق و دستمزد، هزینه payroll و تحلیل حقوق بر اساس دپارتمان',
            ],
            [
                'action' => 'kpi',
                'icon'   => 'fas fa-chart-bar',
                'color'  => '#7C3AED',
                'title'  => 'شاخص‌های KPI',
                'desc'   => 'مقادیر شاخص‌ها، درصد دستیابی به اهداف و روند شاخص‌ها',
            ],
            [
                'action' => 'competency',
                'icon'   => 'fas fa-cubes',
                'color'  => '#9333EA',
                'title'  => 'شایستگی‌ها',
                'desc'   => 'فهرست شایستگی‌ها، شایستگی‌های هسته‌ای و فاصله مهارتی (Skill Gap)',
            ],
        ];
        ?>

        <?php foreach ($reports as $r): ?>
            <a href="<?= hr_url('report', $r['action']) ?>" class="hr-report-card">
                <div class="hr-report-icon" style="background: <?= hr_e($r['color']) ?>;">
                    <i class="<?= hr_e($r['icon']) ?>"></i>
                </div>
                <div class="hr-report-body">
                    <h3><?= hr_e($r['title']) ?></h3>
                    <p><?= hr_e($r['desc']) ?></p>
                </div>
                <div class="hr-report-arrow">
                    <i class="fas fa-arrow-left"></i>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>