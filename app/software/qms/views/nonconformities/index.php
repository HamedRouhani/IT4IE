<?php
$pageTitle = 'عدم انطباق‌ها';
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
            <i class="fas fa-exclamation-triangle" style="color: #EF4444;"></i>
            عدم انطباق‌ها
        </h1>
        <a href="?controller=nonconformities&action=create" 
           style="background: linear-gradient(135deg, #EF4444, #F87171); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-plus"></i> ثبت عدم انطباق جدید
        </a>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div style="background: #D1FAE5; border: 1px solid #86EFAC; color: #065F46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?= qms_e($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <?php if (empty($ncs)): ?>
            <div style="text-align: center; padding: 50px; color: #718096;">
                <i class="fas fa-check-circle" style="font-size: 4rem; opacity: 0.3; color: #10B981;"></i>
                <h4 style="margin-top: 20px;">هیچ عدم انطباقی ثبت نشده است</h4>
                <p>سیستم مدیریت کیفیت شما در وضعیت مطلوبی قرار دارد!</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #F7FAFC; border-bottom: 2px solid #E2E8F0;">
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">شماره</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">عنوان</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">بند</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">واحد</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">شدت</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">وضعیت</th>
                            <th style="padding: 12px; text-align: center; color: #4A5568; font-weight: 600;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ncs as $nc): ?>
                            <tr style="border-bottom: 1px solid #F0F0F0;">
                                <td style="padding: 12px;">
                                    <strong style="color: #6C3CE1;"><?= qms_e($nc['nc_number']) ?></strong>
                                </td>
                                <td style="padding: 12px; color: #2D3748;">
                                    <?= qms_e($nc['title']) ?>
                                </td>
                                <td style="padding: 12px;">
                                    <span style="background: #F3F4F6; padding: 3px 8px; border-radius: 10px; font-size: 0.85rem;">
                                        <?= qms_e($nc['clause_number'] ?? '-') ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; color: #4A5568;">
                                    <?= qms_e($nc['dept_name'] ?? '-') ?>
                                </td>
                                <td style="padding: 12px;">
                                    <?php $color = $severityColors[$nc['severity']] ?? '#6B7280'; ?>
                                    <span style="background: <?= $color ?>20; color: <?= $color ?>; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                        <?= qms_status_label($nc['severity']) ?>
                                    </span>
                                </td>
                                <td style="padding: 12px;">
                                    <?php $sColor = $statusColors[$nc['status']] ?? '#6B7280'; ?>
                                    <span style="background: <?= $sColor ?>20; color: <?= $sColor ?>; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                        <?= qms_status_label($nc['status']) ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <a href="?controller=nonconformities&action=show&id=<?= $nc['id'] ?>" 
                                       style="background: #6C3CE1; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">
                                        <i class="fas fa-eye"></i>
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