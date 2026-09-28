<?php
$stats = $stats ?? [];
$recentProjects = $recentProjects ?? [];

$quickLinks = [
    ['url' => 'controller=descriptive', 'icon' => 'fa-chart-simple', 'color' => '#2563eb', 'label' => 'آمار توصیفی', 'desc' => 'میانگین، میانه، پراکندگی و بررسی داده‌های پرت'],
    ['url' => 'controller=distribution', 'icon' => 'fa-dice', 'color' => '#7c3aed', 'label' => 'توزیع‌های احتمال', 'desc' => 'توزیع‌های پرکاربرد و محاسبهٔ احتمال'],
    ['url' => 'controller=hypothesis', 'icon' => 'fa-scale-balanced', 'color' => '#ea580c', 'label' => 'آزمون فرض', 'desc' => 'آزمون‌های آماری برای مقایسه و تصمیم‌گیری'],
    ['url' => 'controller=regression', 'icon' => 'fa-chart-line', 'color' => '#059669', 'label' => 'رگرسیون و همبستگی', 'desc' => 'بررسی رابطهٔ متغیرها و برازش مدل'],
    ['url' => 'controller=smart_statistician', 'icon' => 'fa-wand-magic-sparkles', 'color' => '#db2777', 'label' => 'دستیار هوشمند', 'desc' => 'پیشنهاد روش تحلیل بر اساس مسئله'],
    ['url' => 'controller=project', 'icon' => 'fa-folder-open', 'color' => '#475569', 'label' => 'پروژه‌های آماری', 'desc' => 'مدیریت پروژه‌ها، متغیرها و مجموعه‌داده‌ها'],
    ['url' => 'controller=report', 'icon' => 'fa-file-lines', 'color' => '#0891b2', 'label' => 'گزارش‌ها', 'desc' => 'مرور و چاپ نتایج تحلیل‌ها'],
];
$catLabels = ['descriptive' => 'توصیفی', 'distribution' => 'توزیع', 'hypothesis' => 'آزمون فرض', 'regression' => 'رگرسیون'];
?>
<div class="statlab-dashboard">
    <header class="statlab-dashboard__header">
        <div>
            <span class="statlab-dashboard__eyebrow">مرکز تحلیل داده</span>
            <h1><i class="fas fa-chart-pie" aria-hidden="true"></i> داشبورد StatLab</h1>
            <p>ابزارهای آمار و تحلیل داده برای مهندسی صنایع</p>
        </div>
        <a href="<?= stat_url('controller=smart_statistician') ?>" class="statlab-dashboard__primary-action">
            <i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i>
            <span>شروع تحلیل هوشمند</span>
            <i class="fas fa-arrow-left" aria-hidden="true"></i>
        </a>
    </header>

    <section class="statlab-dashboard__stats" aria-label="خلاصه فعالیت‌ها">
        <article class="statlab-dashboard__stat statlab-dashboard__stat--blue">
            <span class="statlab-dashboard__stat-icon"><i class="fas fa-folder" aria-hidden="true"></i></span>
            <div><span>پروژه‌های من</span><strong><?= (int) ($stats['total_projects'] ?? 0) ?></strong></div>
        </article>
        <article class="statlab-dashboard__stat statlab-dashboard__stat--green">
            <span class="statlab-dashboard__stat-icon"><i class="fas fa-circle-check" aria-hidden="true"></i></span>
            <div><span>پروژه‌های تکمیل‌شده</span><strong><?= (int) ($stats['completed'] ?? 0) ?></strong></div>
        </article>
        <article class="statlab-dashboard__stat statlab-dashboard__stat--cyan">
            <span class="statlab-dashboard__stat-icon"><i class="fas fa-table" aria-hidden="true"></i></span>
            <div><span>مجموعه‌داده‌ها</span><strong><?= (int) ($stats['total_datasets'] ?? 0) ?></strong></div>
        </article>
        <article class="statlab-dashboard__stat statlab-dashboard__stat--amber">
            <span class="statlab-dashboard__stat-icon"><i class="fas fa-chart-column" aria-hidden="true"></i></span>
            <div><span>تحلیل‌های انجام‌شده</span><strong><?= (int) ($stats['total_results'] ?? 0) ?></strong></div>
        </article>
    </section>

    <section class="statlab-dashboard__section" aria-labelledby="statlab-tools-title">
        <div class="statlab-dashboard__section-heading">
            <div><span class="statlab-dashboard__eyebrow">ابزارهای تحلیل</span><h2 id="statlab-tools-title">از کجا شروع کنیم؟</h2></div>
            <p>یک ابزار را انتخاب کنید تا تحلیل خود را آغاز کنید.</p>
        </div>
        <div class="statlab-dashboard__tools">
            <?php foreach ($quickLinks as $link): ?>
                <a href="<?= stat_url($link['url']) ?>" class="statlab-dashboard__tool">
                    <span class="statlab-dashboard__tool-icon" style="--tool-color: <?= htmlspecialchars($link['color'], ENT_QUOTES, 'UTF-8') ?>"><i class="fas <?= htmlspecialchars($link['icon'], ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i></span>
                    <span class="statlab-dashboard__tool-copy"><strong><?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($link['desc'], ENT_QUOTES, 'UTF-8') ?></small></span>
                    <i class="fas fa-arrow-left statlab-dashboard__tool-arrow" aria-hidden="true"></i>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="statlab-dashboard__section statlab-dashboard__recent" aria-labelledby="statlab-recent-title">
        <div class="statlab-dashboard__section-heading">
            <div><span class="statlab-dashboard__eyebrow">ادامهٔ کار</span><h2 id="statlab-recent-title">آخرین پروژه‌ها</h2></div>
            <a href="<?= stat_url('controller=project') ?>" class="statlab-dashboard__text-link">مشاهدهٔ همهٔ پروژه‌ها <i class="fas fa-arrow-left" aria-hidden="true"></i></a>
        </div>
        <?php if (empty($recentProjects)): ?>
            <div class="statlab-dashboard__empty">
                <span><i class="fas fa-folder-open" aria-hidden="true"></i></span>
                <strong>هنوز پروژه‌ای نساخته‌اید</strong>
                <p>برای شروع، یک ابزار تحلیل را انتخاب کنید یا داده‌های خود را در آمار توصیفی بررسی کنید.</p>
                <a href="<?= stat_url('controller=descriptive') ?>">رفتن به آمار توصیفی <i class="fas fa-arrow-left" aria-hidden="true"></i></a>
            </div>
        <?php else: ?>
            <div class="statlab-dashboard__table-wrap">
                <table class="statlab-dashboard__table">
                    <thead><tr><th>نام پروژه</th><th>دسته</th><th>وضعیت</th><th>آخرین به‌روزرسانی</th><th><span class="visually-hidden">عملیات</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($recentProjects as $project): ?>
                        <tr>
                            <td data-label="پروژه"><strong><?= stat_e($project['name']) ?></strong></td>
                            <td data-label="دسته"><span class="statlab-dashboard__category"><?= htmlspecialchars($catLabels[$project['category_code']] ?? $project['category_code'], ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td data-label="وضعیت"><span class="statlab-dashboard__status <?= $project['status'] === 'completed' ? 'is-complete' : '' ?>"><?= htmlspecialchars(stat_getStatusLabel($project['status']), ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td data-label="به‌روزرسانی" class="statlab-dashboard__date"><?= stat_e($project['updated_at']) ?></td>
                            <td data-label="عملیات"><a class="statlab-dashboard__open" href="<?= stat_url('controller=project&action=show&id=' . (int) $project['id']) ?>"><span>بازکردن</span><i class="fas fa-arrow-left" aria-hidden="true"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
