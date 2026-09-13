<?php
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

$level = $result['risk_level'] ?? 'medium';
?>

<div class="checklist-result-page">
    <div class="container result-container">
        
        <!-- کارت نتیجه کلی -->
        <div class="result-card" style="border-top-color: <?= $overallRec['color'] ?>;">
            <div class="result-emoji"><?= $riskIcons[$level] ?? '📊' ?></div>
            <h1 class="result-title"><?= htmlspecialchars($result['checklist_title'] ?? 'نتایج ارزیابی') ?></h1>
            <h2 class="result-subtitle" style="color: <?= $overallRec['color'] ?>;"><?= $overallRec['title'] ?></h2>
            <p class="result-message"><?= $overallRec['message'] ?></p>

            <div class="result-stats">
                <div class="stat">
                    <div class="stat-value" style="color: <?= $overallRec['color'] ?>;"><?= $result['percentage'] ?>٪</div>
                    <div class="stat-label">درصد ریسک</div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?= $result['total_score'] ?> / <?= $result['max_score'] ?></div>
                    <div class="stat-label">امتیاز کل</div>
                </div>
                <div class="stat">
                    <div class="stat-value"><?= $riskLabels[$level] ?? 'نامشخص' ?></div>
                    <div class="stat-label">سطح ریسک</div>
                </div>
            </div>

            <div class="progress-track">
                <div class="progress-fill" style="width: <?= $result['percentage'] ?>%; background: <?= $overallRec['color'] ?>;"></div>
            </div>

            <a href="/contact" class="btn-cta" style="background: <?= $overallRec['color'] ?>;">
                <i class="fas fa-phone-alt"></i> <?= $overallRec['cta'] ?>
            </a>

            <div class="result-notice">
                <i class="fas fa-info-circle"></i>
                پاسخ و بازخورد مدیریت به ارزیابی شما به‌صورت پیام درون‌برنامه‌ای ارسال می‌شود؛ می‌توانید آن را در بخش «پیام‌های قبلی شما» در صفحهٔ <a href="/contact">تماس با ما</a> و همچنین منوی «پیام‌ها و پاسخ‌ها» در پروفایل خود مشاهده کنید.
            </div>
        </div>

        <!-- توصیه‌های تفصیلی -->
        <h2 class="recommendations-title">📊 تحلیل تفصیلی به تفکیک حوزه</h2>

        <?php if (!empty($result['recommendations'])): ?>
            <?php foreach ($result['recommendations'] as $cat => $rec): 
                $catColor = $catColors[$cat] ?? '#667eea';
                $lvlColor = $levelColors[$rec['level'] ?? 'medium'] ?? '#667eea';
            ?>
            <div class="recommendation-card" style="border-right-color: <?= $catColor ?>;">
                <div class="rec-header">
                    <i class="fas <?= htmlspecialchars($rec['icon'] ?? 'fa-circle') ?>" style="color: <?= $catColor ?>;"></i>
                    <h3><?= htmlspecialchars($rec['title']) ?></h3>
                    <span class="rec-badge" style="background: <?= $lvlColor ?>;">
                        ریسک <?= $riskLabels[$rec['level'] ?? 'medium'] ?? 'نامشخص' ?> (<?= $rec['percentage'] ?>٪)
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
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="recommendation-card">
                <p class="result-message">توصیه‌ای برای این ارزیابی ثبت نشده است.</p>
            </div>
        <?php endif; ?>

        <!-- اقدامات پایانی -->
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