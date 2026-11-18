<?php
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'کاربر';
?>

<div class="checklist-list-page">
    <div class="container">
        <div class="page-hero">
            <div class="smart-checklist-kicker"><i class="fas fa-wand-magic-sparkles"></i> راهنمای تصمیم‌گیری تعاملی</div>
            <h1>چک‌لیست‌های هوشمند IT4IE</h1>
            <p>به‌جای فرم‌های طولانی، قدم‌به‌قدم پیش بروید؛ پاسخ‌ها را ثبت کنید و در پایان، ارزیابی و پیشنهادهای متناسب با حوزهٔ کاری‌تان را ببینید.</p>
            <div class="smart-checklist-benefits" aria-label="ویژگی‌های چک‌لیست‌ها">
                <span><i class="fas fa-route"></i> مسیر ساده و مرحله‌ای</span>
                <span><i class="fas fa-lightbulb"></i> پیشنهادهای کاربردی</span>
                <span><i class="fas fa-clock"></i> شروع رایگان</span>
            </div>
            
            <?php if ($isLoggedIn): ?>
            <div class="user-checklist-actions">
                <a href="/checklist/history" class="history-link">
                    <span class="history-icon">
                        <i class="fas fa-history"></i>
                    </span>
                    <span class="link-content">
                        <span class="link-label">تاریخچه ارزیابی‌های</span>
                        <span class="link-value"><strong><?= htmlspecialchars($userName) ?></strong></span>
                    </span>
                    <span class="arrow-icon">
                        <i class="fas fa-arrow-left"></i>
                    </span>
                </a>
            </div>
            <?php endif; ?>
        </div>

        <div class="smart-checklist-section-head">
            <div><span class="smart-checklist-eyebrow">کتابخانه ابزارها</span><h2>از کجا شروع می‌کنید؟</h2></div>
            <p>یک موضوع را انتخاب کنید؛ هر ارزیابی چند دقیقه زمان می‌برد.</p>
        </div>
        <div class="checklist-grid">
            <?php if (empty($checklists)): ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <p>هنوز چک‌لیستی منتشر نشده است</p>
                </div>
            <?php else: ?>
                <?php foreach ($checklists as $cl): ?>
                <div class="checklist-card">
                    <div class="card-icon">
                        <i class="fas <?= htmlspecialchars($cl['icon']) ?>"></i>
                    </div>
                    <div class="smart-card-tag"><i class="fas fa-sparkles"></i> ارزیابی هوشمند</div>
                    <h3><?= htmlspecialchars($cl['title']) ?></h3>
                    <p class="card-desc"><?= htmlspecialchars($cl['description'] ?? '') ?></p>
                    <div class="card-meta">
                        <span><i class="fas fa-clock"></i> <?= $cl['estimated_time'] ?> دقیقه</span>
                        <span><i class="fas fa-list-check"></i> حداکثر <?= $cl['questions_count'] ?> پرسش</span>
                    </div>
                    <a href="/checklist/view/<?= htmlspecialchars($cl['slug']) ?>" class="btn-start">
                        شروع ارزیابی
                        <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
