<?php
/** جزئیات ریسک - PMBOK (بازطراحی‌شده) */
$pageTitle  = ($risk['title'] ?? 'ریسک') . ' - PMBOK';
$activePage = 'risk';

$e     = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$score = (int)$risk['risk_score'];
$tone  = $score >= 15 ? 'high' : ($score >= 8 ? 'mid' : 'low');
$verdict = ['high' => 'بحرانی - نیاز به اقدام فوری', 'mid' => 'متوسط - نیاز به نظارت', 'low' => 'کم - قابل کنترل'][$tone];
?>

<div class="page-header">
    <div>
        <nav class="breadcrumb" aria-label="مسیر">
            <a href="?controller=risk">ریسک‌ها</a> / <span><?= $e($risk['title']) ?></span>
        </nav>
        <h2><i class="fas fa-exclamation-triangle score--<?= $tone ?>"></i> <?= $e($risk['title']) ?></h2>
    </div>
    <div class="header-actions">
        <a href="?controller=risk&action=edit&id=<?= (int)$risk['id'] ?>" class="btn btn-warning"><i class="fas fa-edit"></i> ویرایش</a>
    </div>
</div>

<section class="card">
    <h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات ریسک</h3>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">پروژه</span>
            <a href="?controller=project&action=show&id=<?= (int)$risk['project_id'] ?>"><?= $e($risk['project_name']) ?></a>
        </div>
        <div class="info-item">
            <span class="info-label">امتیاز ریسک</span>
            <strong class="score score--<?= $tone ?> score--lg"><?= $score ?></strong>
        </div>
        <div class="info-item">
            <span class="info-label">احتمال</span>
            <span class="badge"><?= pmbok_getProbabilityLabel($risk['probability']) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">تاثیر</span>
            <span class="badge"><?= pmbok_getImpactLabel($risk['impact']) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">وضعیت</span>
            <span class="badge badge-info"><?= pmbok_getRiskStatusLabel($risk['status']) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">مسئول</span>
            <span><?= $e($risk['owner'] ?? '-') ?></span>
        </div>
    </div>

    <div class="text-block">
        <strong>توضیحات</strong>
        <p><?= nl2br($e($risk['description'] ?? 'توضیحاتی ثبت نشده است.')) ?></p>
    </div>
    <?php if (!empty($risk['response_strategy'])): ?>
    <div class="text-block">
        <strong>استراتژی پاسخ</strong>
        <p><?= nl2br($e(pmbok_getResponseStrategyLabel($risk['response_strategy']))) ?></p>
    </div>
    <?php endif; ?>
    <?php if (!empty($risk['response_plan'])): ?>
    <div class="text-block">
        <strong>برنامه پاسخ</strong>
        <p><?= nl2br($e($risk['response_plan'])) ?></p>
    </div>
    <?php endif; ?>
</section>

<!-- ماتریس ریسک -->
<section class="card">
    <h3 class="card-title"><i class="fas fa-th"></i> موقعیت در ماتریس ریسک</h3>
    <div class="risk-matrix">
        <div class="risk-matrix-axes">
            احتمال: <strong><?= pmbok_getProbabilityLabel($risk['probability']) ?></strong><br>
            ×<br>
            تاثیر: <strong><?= pmbok_getImpactLabel($risk['impact']) ?></strong>
        </div>
        <div class="risk-matrix-result risk-matrix-result--<?= $tone ?>">
            <div class="risk-matrix-icon" aria-hidden="true"><i class="fas fa-exclamation-circle"></i></div>
            <div class="risk-matrix-score">امتیاز: <?= $score ?></div>
            <div class="text-muted"><?= $verdict ?></div>
        </div>
    </div>
</section>
