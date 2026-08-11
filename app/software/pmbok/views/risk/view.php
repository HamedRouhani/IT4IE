<?php
$pageTitle = ($risk['title'] ?? 'ریسک') . ' - PMBOK';
$activePage = 'risk';
$riskColor = $risk['risk_score'] >= 15 ? '#EF4444' : ($risk['risk_score'] >= 8 ? '#F59E0B' : '#10B981');
?>

<div class="page-header">
    <nav class="breadcrumb">
        <a href="?controller=risk">ریسک‌ها</a> /
        <span><?= htmlspecialchars($risk['title']) ?></span>
    </nav>
    <h2><i class="fas fa-exclamation-triangle" style="color: <?= $riskColor ?>;"></i> <?= htmlspecialchars($risk['title']) ?></h2>
    <div style="margin-top: 10px;">
        <a href="?controller=risk&action=edit&id=<?= $risk['id'] ?>" class="btn btn-warning">
            <i class="fas fa-edit"></i> ویرایش
        </a>
    </div>
</div>

<div class="card">
    <h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات ریسک</h3>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">پروژه:</span>
            <a href="?controller=project&action=show&id=<?= $risk['project_id'] ?>">
                <?= htmlspecialchars($risk['project_name']) ?>
            </a>
        </div>
        <div class="info-item">
            <span class="info-label">امتیاز ریسک:</span>
            <strong style="color: <?= $riskColor ?>; font-size: 1.3rem;"><?= $risk['risk_score'] ?></strong>
        </div>
        <div class="info-item">
            <span class="info-label">احتمال:</span>
            <span class="badge"><?= pmbok_getProbabilityLabel($risk['probability']) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">تاثیر:</span>
            <span class="badge"><?= pmbok_getImpactLabel($risk['impact']) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">وضعیت:</span>
            <span class="badge badge-info"><?= pmbok_getRiskStatusLabel($risk['status']) ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">مسئول:</span>
            <span><?= htmlspecialchars($risk['owner'] ?? '-') ?></span>
        </div>
    </div>
    
    <div style="margin-top: 20px;">
        <strong>توضیحات:</strong>
        <p style="margin-top: 5px; line-height: 1.8;"><?= nl2br(htmlspecialchars($risk['description'] ?? 'توضیحاتی ثبت نشده است.')) ?></p>
    </div>
    
    <?php if (!empty($risk['response_strategy'])): ?>
    <div style="margin-top: 15px;">
        <strong>استراتژی پاسخ:</strong>
        <p style="margin-top: 5px;"><?= nl2br(htmlspecialchars($risk['response_strategy'])) ?></p>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($risk['response_plan'])): ?>
    <div style="margin-top: 15px;">
        <strong>برنامه پاسخ:</strong>
        <p style="margin-top: 5px;"><?= nl2br(htmlspecialchars($risk['response_plan'])) ?></p>
    </div>
    <?php endif; ?>
</div>

<!-- ماتریس ریسک -->
<div class="card">
    <h3 class="card-title"><i class="fas fa-th"></i> موقعیت در ماتریس ریسک</h3>
    <div style="display: grid; grid-template-columns: auto 1fr; gap: 15px; align-items: center;">
        <div style="text-align: center; font-weight: 600; color: var(--gray-dark);">
            احتمال: <strong><?= pmbok_getProbabilityLabel($risk['probability']) ?></strong><br>
            ×<br>
            تاثیر: <strong><?= pmbok_getImpactLabel($risk['impact']) ?></strong>
        </div>
        <div style="background: <?= $riskColor ?>20; border: 2px solid <?= $riskColor ?>; border-radius: 12px; padding: 30px; text-align: center;">
            <div style="font-size: 3rem; color: <?= $riskColor ?>;">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div style="font-size: 1.5rem; font-weight: 800; color: <?= $riskColor ?>;">
                امتیاز: <?= $risk['risk_score'] ?>
            </div>
            <div style="color: var(--gray-dark); margin-top: 5px;">
                <?= $risk['risk_score'] >= 15 ? 'بحرانی - نیاز به اقدام فوری' : ($risk['risk_score'] >= 8 ? 'متوسط - نیاز به نظارت' : 'کم - قابل کنترل') ?>
            </div>
        </div>
    </div>
</div>