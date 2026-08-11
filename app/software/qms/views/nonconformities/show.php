<?php
$pageTitle = 'عدم انطباق ' . $nc['nc_number'];
$currentPage = 'nonconformities';

$severityColors = [
    'minor' => '#F59E0B',
    'major' => '#F97316',
    'critical' => '#EF4444'
];

$statusColors = [
    'open' => '#EF4444',
    'under_review' => '#F59E0B',
    'car_issued' => '#3B82F6',
    'in_progress' => '#8B5CF6',
    'closed' => '#10B981',
    'rejected' => '#6B7280'
];
?>

<div class="container-fluid" style="padding: 20px;">
    
    <!-- هدر -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
                <i class="fas fa-exclamation-triangle" style="color: #EF4444;"></i>
                عدم انطباق <?= qms_e($nc['nc_number']) ?>
            </h1>
            <p style="color: #718096; margin-top: 5px;"><?= qms_e($nc['title']) ?></p>
        </div>
        <a href="?controller=nonconformities" 
           style="background: #718096; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div style="background: #D1FAE5; border: 1px solid #86EFAC; color: #065F46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?= qms_e($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        
        <!-- ستون چپ -->
        <div>
            <!-- اطلاعات اصلی -->
            <div style="background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h3 style="margin: 0 0 20px 0; color: #2D3748; border-bottom: 2px solid #E2E8F0; padding-bottom: 10px;">
                    <i class="fas fa-info-circle" style="color: #6C3CE1;"></i>
                    اطلاعات عدم انطباق
                </h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                    <div>
                        <strong style="color: #718096; font-size: 0.85rem;">ثبت‌کننده:</strong>
                        <div style="color: #2D3748;"><?= qms_e($nc['reporter_name'] ?? '-') ?></div>
                    </div>
                    <div>
                        <strong style="color: #718096; font-size: 0.85rem;">تاریخ ثبت:</strong>
                        <div style="color: #2D3748;"><?= qms_e($nc['detected_date'] ?? '-') ?></div>
                    </div>
                    <div>
                        <strong style="color: #718096; font-size: 0.85rem;">واحد متأثر:</strong>
                        <div style="color: #2D3748;"><?= qms_e($nc['dept_name'] ?? '-') ?></div>
                    </div>
                    <div>
                        <strong style="color: #718096; font-size: 0.85rem;">فرآیند:</strong>
                        <div style="color: #2D3748;"><?= qms_e($nc['affected_process'] ?? '-') ?></div>
                    </div>
                </div>

                <div style="margin-bottom: 15px;">
                    <strong style="color: #718096; font-size: 0.85rem; display: block; margin-bottom: 5px;">شرح عدم انطباق:</strong>
                    <div style="background: #FEF3C7; padding: 15px; border-radius: 8px; border-right: 4px solid #F59E0B;">
                        <?= nl2br(qms_e($nc['description'])) ?>
                    </div>
                </div>

                <?php if (!empty($nc['current_situation'])): ?>
                    <div style="margin-bottom: 15px;">
                        <strong style="color: #718096; font-size: 0.85rem; display: block; margin-bottom: 5px;">وضعیت موجود مشاهده شده:</strong>
                        <div style="background: #F3F4F6; padding: 15px; border-radius: 8px;">
                            <?= nl2br(qms_e($nc['current_situation'])) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($nc['requirement_text'])): ?>
                    <div>
                        <strong style="color: #718096; font-size: 0.85rem; display: block; margin-bottom: 5px;">الزام استاندارد:</strong>
                        <div style="background: #DBEAFE; padding: 15px; border-radius: 8px; border-right: 4px solid #3B82F6;">
                            <?= nl2br(qms_e($nc['requirement_text'])) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- بندهای مرتبط -->
            <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h3 style="margin: 0 0 20px 0; color: #2D3748; border-bottom: 2px solid #E2E8F0; padding-bottom: 10px;">
                    <i class="fas fa-book" style="color: #6C3CE1;"></i>
                    بندهای مرتبط با عدم انطباق
                </h3>

                <?php foreach ($relatedClauses as $rc): ?>
                    <div style="padding: 15px; border: 1px solid #E2E8F0; border-radius: 10px; margin-bottom: 10px; border-right: 4px solid <?= $rc['is_primary'] ? '#6C3CE1' : '#3B82F6' ?>;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                            <div>
                                <strong style="color: #2D3748; font-size: 1.05rem;">
                                    <?= qms_e($rc['clause_number']) ?> - <?= qms_e($rc['title_fa']) ?>
                                </strong>
                                <?php if ($rc['is_primary']): ?>
                                    <span style="background: #6C3CE1; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem; margin-right: 8px;">
                                        بند اصلی
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 1.5rem; font-weight: 800; color: #6C3CE1;">
                                    <?= $rc['match_percentage'] ?>%
                                </div>
                                <div style="font-size: 0.75rem; color: #718096;">تطابق</div>
                            </div>
                        </div>
                        <?php if (!empty($rc['description'])): ?>
                            <p style="color: #4A5568; font-size: 0.9rem; margin: 0; line-height: 1.6;">
                                <?= qms_e(mb_substr($rc['description'], 0, 250)) ?>...
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ستون راست: وضعیت و اقدامات -->
        <div>
            <!-- وضعیت -->
            <div style="background: white; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h4 style="margin: 0 0 15px 0; color: #2D3748;">
                    <i class="fas fa-flag" style="color: #6C3CE1;"></i>
                    وضعیت
                </h4>
                <?php $sColor = $statusColors[$nc['status']] ?? '#6B7280'; ?>
                <div style="background: <?= $sColor ?>20; color: <?= $sColor ?>; padding: 15px; border-radius: 10px; text-align: center; margin-bottom: 15px;">
                    <div style="font-size: 1.3rem; font-weight: 700;">
                        <?= qms_status_label($nc['status']) ?>
                    </div>
                </div>

                <div style="margin-bottom: 10px;">
                    <strong style="color: #718096; font-size: 0.85rem;">شدت:</strong>
                    <?php $sevColor = $severityColors[$nc['severity']] ?? '#6B7280'; ?>
                    <span style="background: <?= $sevColor ?>20; color: <?= $sevColor ?>; padding: 3px 10px; border-radius: 10px; font-size: 0.85rem; font-weight: 600;">
                        <?= qms_status_label($nc['severity']) ?>
                    </span>
                </div>

                <div style="margin-bottom: 10px;">
                    <strong style="color: #718096; font-size: 0.85rem;">بند اصلی:</strong>
                    <div style="color: #2D3748;">
                        <?= qms_e($nc['clause_number'] ?? '-') ?> - <?= qms_e($nc['clause_title'] ?? '-') ?>
                    </div>
                </div>
            </div>

            <!-- اقدامات -->
            <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h4 style="margin: 0 0 15px 0; color: #2D3748;">
                    <i class="fas fa-tasks" style="color: #6C3CE1;"></i>
                    اقدامات
                </h4>

                <?php if (!empty($nc['car_form_id'])): ?>
                    <div style="background: #D1FAE5; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        <strong style="color: #065F46;">
                            <i class="fas fa-check-circle"></i> CAR صادر شده
                        </strong>
                        <div style="margin-top: 5px;">
                            <a href="?controller=car&action=show&id=<?= $nc['car_form_id'] ?>" 
                               style="color: #065F46; text-decoration: underline;">
                                مشاهده CAR: <?= qms_e($nc['car_number']) ?>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <form method="POST" action="?controller=nonconformities&action=generateCar" style="margin-bottom: 15px;">
                        <input type="hidden" name="nc_id" value="<?= $nc['id'] ?>">
                        <button type="submit" 
                                style="width: 100%; background: linear-gradient(135deg, #10B981, #34D399); color: white; padding: 12px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                            <i class="fas fa-magic"></i> صدور CAR خودکار
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($nc['status'] !== 'closed'): ?>
                    <hr style="margin: 20px 0; border: none; border-top: 1px solid #E2E8F0;">
                    
                    <form method="POST" action="?controller=nonconformities&action=close" style="margin-bottom: 10px;">
                        <input type="hidden" name="nc_id" value="<?= $nc['id'] ?>">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568; font-size: 0.9rem;">
                            یادداشت بستن (توسط ممیز):
                        </label>
                        <textarea name="closure_notes" rows="2" required
                                  style="width: 100%; padding: 8px; border: 2px solid #E2E8F0; border-radius: 8px; margin-bottom: 8px;"></textarea>
                        <button type="submit" 
                                style="width: 100%; background: #10B981; color: white; padding: 10px; border: none; border-radius: 8px; cursor: pointer;">
                            <i class="fas fa-check"></i> بستن عدم انطباق
                        </button>
                    </form>

                    <form method="POST" action="?controller=nonconformities&action=reject">
                        <input type="hidden" name="nc_id" value="<?= $nc['id'] ?>">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568; font-size: 0.9rem;">
                            دلیل رد (درخواست اقدام مجدد):
                        </label>
                        <textarea name="rejection_reason" rows="2" required
                                  style="width: 100%; padding: 8px; border: 2px solid #E2E8F0; border-radius: 8px; margin-bottom: 8px;"></textarea>
                        <button type="submit" 
                                style="width: 100%; background: #EF4444; color: white; padding: 10px; border: none; border-radius: 8px; cursor: pointer;">
                            <i class="fas fa-times"></i> رد و درخواست اقدام مجدد
                        </button>
                    </form>
                <?php else: ?>
                    <div style="background: #D1FAE5; padding: 15px; border-radius: 8px; text-align: center;">
                        <i class="fas fa-check-circle" style="color: #10B981; font-size: 2rem;"></i>
                        <div style="color: #065F46; margin-top: 5px;">این عدم انطباق بسته شده است</div>
                        <?php if (!empty($nc['closure_notes'])): ?>
                            <div style="margin-top: 10px; font-size: 0.85rem; color: #047857;">
                                <strong>یادداشت:</strong> <?= qms_e($nc['closure_notes']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 992px) {
    .container-fluid > div[style*="grid-template-columns: 2fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>