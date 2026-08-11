<?php
$pageTitle = 'فرم‌های CAR';
$currentPage = 'car';

$statusColors = [
    'draft' => '#6B7280',
    'submitted' => '#3B82F6',
    'approved' => '#8B5CF6',
    'in_progress' => '#F59E0B',
    'implemented' => '#06B6D4',
    'verified' => '#10B981',
    'closed' => '#10B981',
    'rejected' => '#EF4444'
];

$severityColors = [
    'minor' => '#F59E0B',
    'major' => '#F97316',
    'critical' => '#EF4444'
];
?>

<div class="container-fluid" style="padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
            <i class="fas fa-clipboard-check" style="color: #10B981;"></i>
            فرم‌های اقدام اصلاحی (CAR)
        </h1>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div style="background: #D1FAE5; border: 1px solid #86EFAC; color: #065F46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?= qms_e($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <?php if (empty($cars)): ?>
            <div style="text-align: center; padding: 50px; color: #718096;">
                <i class="fas fa-clipboard-check" style="font-size: 4rem; opacity: 0.3; color: #10B981;"></i>
                <h4 style="margin-top: 20px;">هیچ فرم CAR ثبت نشده است</h4>
                <p>پس از ثبت عدم انطباق، می‌توانید CAR مربوطه را ایجاد کنید.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #F7FAFC; border-bottom: 2px solid #E2E8F0;">
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">شماره CAR</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">NC مرتبط</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">عنوان</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">وضعیت</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">اقدامات</th>
                            <th style="padding: 12px; text-align: center; color: #4A5568; font-weight: 600;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cars as $car): ?>
                            <tr style="border-bottom: 1px solid #F0F0F0;">
                                <td style="padding: 12px;">
                                    <strong style="color: #10B981;"><?= qms_e($car['car_number']) ?></strong>
                                </td>
                                <td style="padding: 12px;">
                                    <span style="background: #F3F4F6; padding: 3px 8px; border-radius: 10px; font-size: 0.85rem;">
                                        <?= qms_e($car['nc_number']) ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; color: #2D3748;">
                                    <?= qms_e($car['nc_title']) ?>
                                </td>
                                <td style="padding: 12px;">
                                    <?php $color = $statusColors[$car['status']] ?? '#6B7280'; ?>
                                    <span style="background: <?= $color ?>20; color: <?= $color ?>; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                        <?= qms_status_label($car['status']) ?>
                                    </span>
                                </td>
                                <td style="padding: 12px;">
                                    <span style="color: #4A5568;">
                                        <?= $car['completed_actions'] ?? 0 ?> / <?= $car['actions_count'] ?? 0 ?>
                                    </span>
                                    <?php if ($car['pending_actions'] > 0): ?>
                                        <span style="background: #FEF3C7; color: #92400E; padding: 2px 6px; border-radius: 8px; font-size: 0.7rem; margin-right: 5px;">
                                            <?= $car['pending_actions'] ?> در انتظار
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <a href="?controller=car&action=show&id=<?= $car['id'] ?>" 
                                       style="background: #10B981; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">
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