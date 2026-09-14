<?php
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'کاربر';
?>

<div class="checklist-list-page">
    <div class="container">
        <div class="page-hero">
            <h1>📋 چک‌لیست‌های تخصصی IT4IE</h1>
            <p>ابزارهای تعاملی برای ارزیابی و بهبود پروژه‌ها و فرآیندهای کسب‌وکار شما</p>
            
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
                    <h3><?= htmlspecialchars($cl['title']) ?></h3>
                    <p class="card-desc"><?= htmlspecialchars($cl['description'] ?? '') ?></p>
                    <div class="card-meta">
                        <span><i class="fas fa-clock"></i> <?= $cl['estimated_time'] ?> دقیقه</span>
                        <span><i class="fas fa-question-circle"></i> <?= $cl['questions_count'] ?> سوال</span>
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