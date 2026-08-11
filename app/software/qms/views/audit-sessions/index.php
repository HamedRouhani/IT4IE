<?php
$pageTitle = 'جلسات ممیزی';
$currentPage = 'auditsessions';
?>

<div class="container-fluid" style="padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
            <i class="fas fa-clipboard-check" style="color: #6C3CE1;"></i>
            جلسات ممیزی
        </h1>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div style="background: #D1FAE5; border: 1px solid #86EFAC; color: #065F46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?= qms_e($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <?php if (empty($sessions)): ?>
            <div style="text-align: center; padding: 50px; color: #718096;">
                <i class="fas fa-inbox" style="font-size: 4rem; opacity: 0.3;"></i>
                <h4 style="margin-top: 20px;">هیچ جلسه ممیزی ثبت نشده است</h4>
                <p>ابتدا یک برنامه ممیزی ایجاد کنید تا جلسات آن نمایش داده شوند.</p>
                <a href="?controller=auditplans&action=create" 
                   style="display: inline-block; background: #6C3CE1; color: white; padding: 10px 25px; border-radius: 8px; text-decoration: none; margin-top: 15px;">
                    <i class="fas fa-plus"></i> ایجاد برنامه ممیزی
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #F7FAFC; border-bottom: 2px solid #E2E8F0;">
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">عنوان برنامه</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">واحد</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">ممیز</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">تاریخ</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">وضعیت</th>
                            <th style="padding: 12px; text-align: center; color: #4A5568; font-weight: 600;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                            <tr style="border-bottom: 1px solid #F0F0F0;">
                                <td style="padding: 12px; color: #2D3748;"><?= qms_e($session['plan_title'] ?? '-') ?></td>
                                <td style="padding: 12px; color: #4A5568;"><?= qms_e($session['department_name'] ?? '-') ?></td>
                                <td style="padding: 12px; color: #4A5568;"><?= qms_e($session['auditor_name'] ?? '-') ?></td>
                                <td style="padding: 12px; color: #4A5568;"><?= qms_e($session['actual_date'] ?? $session['audit_date'] ?? '-') ?></td>
                                <td style="padding: 12px;">
                                    <?php
                                    $statusColors = [
                                        'not_started' => '#6B7280',
                                        'in_progress' => '#F59E0B',
                                        'completed' => '#10B981',
                                        'postponed' => '#EF4444'
                                    ];
                                    $color = $statusColors[$session['overall_status']] ?? '#6B7280';
                                    ?>
                                    <span style="background: <?= $color ?>20; color: <?= $color ?>; padding: 4px 12px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                        <?= qms_status_label($session['overall_status']) ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <a href="?controller=auditsessions&action=show&id=<?= $session['id'] ?>" 
                                       style="background: #6C3CE1; color: white; padding: 6px 15px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">
                                        <i class="fas fa-eye"></i> مشاهده
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>