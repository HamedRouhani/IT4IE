<?php
// ============================================
// نگاشت‌های پایه
// ============================================
$riskLabels = [
    'critical' => 'بحرانی',
    'high' => 'بالا',
    'medium' => 'متوسط',
    'low' => 'پایین'
];
$riskIcons = [
    'critical' => '🚨',
    'high' => '⚠️',
    'medium' => '⚡',
    'low' => '✅'
];
$levelColors = [
    'critical' => '#dc3545',
    'high' => '#fd7e14',
    'medium' => '#ffc107',
    'low' => '#28a745'
];
$catColors = [
    'sign1' => '#e53e3e',
    'sign2' => '#dd6b20',
    'sign3' => '#d69e2e',
    'sign4' => '#38a169',
    'sign5' => '#3182ce'
];
$mcdmLevelLabels = [
    'critical' => 'تأثیرگذار',
    'high' => 'قابل توجه',
    'medium' => 'متوسط',
    'low' => 'کم‌اثر'
];

// ============================================
// نگاشت تحلیل‌گرها (اتصال چک‌لیست به نرم‌افزارها)
// ============================================
$analyzerMap = [
    'mcdm-method-selector' => [
        'emoji' => '🎯',
        'subtitle' => 'روش بهینه تصمیم‌گیری شما شناسایی شد',
        'color' => '#667eea',
        'message' => 'بر اساس پاسخ‌های شما، ابعاد مسئله تحلیل شد و مناسب‌ترین روش‌های تصمیم‌گیری چندمعیاره به‌ترتیب اولویت در بخش «روش‌های پیشنهادی» ارائه شده‌اند.',
        'stat1Label' => 'شاخص تطبیق',
        'stat3Label' => 'سطح قطعیت تحلیل',
        'ctaHref' => '/software/mcdm-analyzer/',
        'ctaIcon' => 'fa-calculator',
        'ctaLabel' => 'شروع تحلیل با MCDM Analyzer',
    ],
    'statistical-method-selector' => [
        'emoji' => '📊',
        'subtitle' => 'روش تحلیل آماری مناسب داده‌های شما شناسایی شد',
        'color' => '#0e7490',
        'message' => 'بر اساس پاسخ‌های شما، نوع داده، هدف تحلیل و فرض‌های آماری بررسی شد و مناسب‌ترین روش‌های آماری به‌ترتیب اولویت در بخش «روش‌های پیشنهادی» ارائه شده‌اند.',
        'stat1Label' => 'شاخص انطباق داده',
        'stat3Label' => 'سطح قطعیت تحلیل',
        'ctaHref' => '/software/statlab-analyzer/',
        'ctaIcon' => 'fa-chart-bar',
        'ctaLabel' => 'شروع تحلیل با StatLab Analyzer',
    ],
];

// ============================================
// تشخیص نوع چک‌لیست و استخراج خلاصه
// ============================================
$level = $result['risk_level'] ?? 'medium';
$analyzer = $analyzerMap[$result['checklist_slug'] ?? ''] ?? null;
$isAnalyzer = ($analyzer !== null);

$summary = null;
$recommendations = $result['recommendations'] ?? [];
if (isset($recommendations['_summary'])) {
    $summary = $recommendations['_summary'];
    unset($recommendations['_summary']);
}

// ============================================
// متن‌ها و رنگ‌های انطباقی
// ============================================
if ($isAnalyzer) {
    $headerEmoji    = $analyzer['emoji'];
    $headerSubtitle = $analyzer['subtitle'];
    $headerColor    = $analyzer['color'];
    $headerMessage  = $analyzer['message'];
    $stat1Label     = $analyzer['stat1Label'];
    $stat3Value     = $mcdmLevelLabels[$level] ?? 'متوسط';
    $stat3Label     = $analyzer['stat3Label'];
    $ctaHref        = $analyzer['ctaHref'];
    $ctaIcon        = $analyzer['ctaIcon'];
    $ctaLabel       = $analyzer['ctaLabel'];
} else {
    $headerEmoji    = $riskIcons[$level] ?? '📊';
    $headerSubtitle = $overallRec['title'];
    $headerColor    = $overallRec['color'];
    $headerMessage  = $overallRec['message'];
    $stat1Label     = 'درصد ریسک';
    $stat3Value     = $riskLabels[$level] ?? 'نامشخص';
    $stat3Label     = 'سطح ریسک';
    $ctaHref        = '/contact';
    $ctaIcon        = 'fa-phone-alt';
    $ctaLabel       = $overallRec['cta'];
}
?>

<div class="checklist-result-page">
    <div class="container result-container">

        <!-- ============================================
             کارت نتیجه کلی
             ============================================ -->
        <div class="result-card" style="border-top-color: <?= $headerColor ?>;">
            <div class="result-emoji"><?= $headerEmoji ?></div>
            <h1 class="result-title"><?= htmlspecialchars($result['checklist_title'] ?? 'نتایج ارزیابی') ?></h1>
            <h2 class="result-subtitle" style="color: <?= $headerColor ?>;"><?= htmlspecialchars($headerSubtitle) ?></h2>
            <p class="result-message"><?= htmlspecialchars($headerMessage) ?></p>

            <div class="result-stats">
                <div class="stat">
                    <div class="stat-value" style="color: <?= $headerColor ?>;"><?= $result['percentage'] ?>٪</div>
                    <div class="stat-label"><?= $stat1Label ?></div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?= $result['total_score'] ?> / <?= $result['max_score'] ?></div>
                    <div class="stat-label">امتیاز کل</div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?= htmlspecialchars($stat3Value) ?></div>
                    <div class="stat-label"><?= $stat3Label ?></div>
                </div>
            </div>

            <div class="progress-track">
                <div class="progress-fill" style="width: <?= $result['percentage'] ?>%; background: <?= $headerColor ?>;"></div>
            </div>

            <a href="<?= $ctaHref ?>" class="btn-cta" style="background: <?= $headerColor ?>;">
                <i class="fas <?= $ctaIcon ?>"></i> <?= htmlspecialchars($ctaLabel) ?>
            </a>

            <div class="result-notice">
                <i class="fas fa-info-circle"></i>
                پاسخ و بازخورد مدیریت به ارزیابی شما به‌صورت پیام درون‌برنامه‌ای ارسال می‌شود؛ می‌توانید آن را در بخش «پیام‌های قبلی شما» در صفحهٔ <a href="/contact">تماس با ما</a> و همچنین منوی «پیام‌ها و پاسخ‌ها» در پروفایل خود مشاهده کنید.
            </div>
        </div>

        <!-- ============================================
             کارت ویژه MCDM: روش‌های پیشنهادی
             ============================================ -->
        <?php if ($summary): ?>
        <div class="recommendation-card summary-card">
            <div class="rec-header">
                <i class="fas <?= htmlspecialchars($summary['icon'] ?? 'fa-trophy') ?>" style="color: #667eea;"></i>
                <h3><?= htmlspecialchars($summary['title']) ?></h3>
            </div>

            <ul class="rec-items">
                <?php foreach (($summary['items'] ?? []) as $item): ?>
                <li>
                    <i class="fas fa-medal"></i>
                    <span><?= $item /* خروجی مدل امن و شامل تگ‌های قالب‌بندی است */ ?></span>
                </li>
                <?php endforeach; ?>
            </ul>

            <?php if (!empty($summary['topMethods'])): ?>
            <div class="method-chips">
                <?php foreach ($summary['topMethods'] as $methodName => $methodScore): ?>
                <span class="method-chip"><?= htmlspecialchars(is_string($methodName) ? $methodName : $methodScore) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="summary-cta">
                <a href="<?= htmlspecialchars($summary['analyzer_url'] ?? '/software/') ?>" class="btn-cta" style="background: <?= $headerColor ?>;">
                    <i class="fas <?= htmlspecialchars($summary['analyzer_icon'] ?? 'fa-cubes') ?>"></i>
                    <?= htmlspecialchars($summary['analyzer_label'] ?? 'شروع تحلیل در نرم‌افزار') ?>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- ============================================
             تحلیل تفصیلی به تفکیک حوزه
             ============================================ -->
        <h2 class="recommendations-title">📊 تحلیل تفصیلی به تفکیک حوزه</h2>

        <?php if (!empty($recommendations)): ?>
            <?php foreach ($recommendations as $cat => $rec):
                $catColor = $catColors[$cat] ?? '#667eea';
                $recLevel = $rec['level'] ?? 'medium';
                $lvlColor = $levelColors[$recLevel] ?? '#667eea';
                $badgeLabel = $isAnalyzer
                    ? 'اهمیت: ' . ($mcdmLevelLabels[$recLevel] ?? 'متوسط')
                    : 'ریسک ' . ($riskLabels[$recLevel] ?? 'نامشخص');
            ?>
            <div class="recommendation-card" style="border-right-color: <?= $catColor ?>;">
                <div class="rec-header">
                    <i class="fas <?= htmlspecialchars($rec['icon'] ?? 'fa-circle') ?>" style="color: <?= $catColor ?>;"></i>
                    <h3><?= htmlspecialchars($rec['title']) ?></h3>
                    <span class="rec-badge" style="background: <?= $lvlColor ?>;">
                        <?= htmlspecialchars($badgeLabel) ?> (<?= $rec['percentage'] ?>٪)
                    </span>
                </div>

                <div class="progress-track small">
                    <div class="progress-fill" style="width: <?= $rec['percentage'] ?>%; background: <?= $catColor ?>;"></div>
                </div>

                <ul class="rec-items">
                    <?php foreach (($rec['items'] ?? []) as $item): ?>
                    <li>
                        <i class="fas fa-check-circle" style="color: <?= $catColor ?>;"></i>
                        <span><?= htmlspecialchars($item) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <?php if (!empty($rec['topMethods'])): ?>
                <div class="cat-methods">
                    <i class="fas fa-link"></i>
                    <span>روش‌های مرتبط:</span>
                    <?php foreach ($rec['topMethods'] as $m): ?>
                    <span class="method-chip"><?= htmlspecialchars($m) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="recommendation-card">
                <p class="result-message">توصیه‌ای برای این ارزیابی ثبت نشده است.</p>
            </div>
        <?php endif; ?>

        <!-- ============================================
             اقدامات پایانی
             ============================================ -->
        <div class="result-actions">
            <a href="/checklist/history" class="btn-secondary">
                <i class="fas fa-history"></i> تاریخچه ارزیابی‌های من
            </a>
            <a href="/checklist" class="btn-secondary">
                <i class="fas fa-list"></i> چک‌لیست‌های دیگر
            </a>
            <a href="/" class="btn-secondary">
                <i class="fas fa-home"></i> بازگشت به خانه
            </a>
        </div>
    </div>
</div>