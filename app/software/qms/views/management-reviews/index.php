<?php
$pageTitle = 'بازنگری مدیریت';
$currentPage = 'managementreviews';

$statusColors = [
    'draft' => '#6B7280',
    'scheduled' => '#3B82F6',
    'completed' => '#10B981',
    'archived' => '#8B5CF6'
];
?>

<div class="container-fluid" style="padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
            <i class="fas fa-users-cog" style="color: #6C3CE1;"></i>
            بازنگری مدیریت (بند 9.3)
        </h1>
        <a href="?controller=managementreviews&action=create" 
           style="background: linear-gradient(135deg, #6C3CE1, #8B6FE8); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-plus"></i> بازنگری جدید
        </a>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div style="background: #D1FAE5; border: 1px solid #86EFAC; color: #065F46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?= qms_e($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <?php if (empty($reviews)): ?>
            <div style="text-align: center; padding: 50px; color: #718096;">
                <i class="fas fa-clipboard-list" style="font-size: 4rem; opacity: 0.3;"></i>
                <h4 style="margin-top: 20px;">هنوز جلسه بازنگری مدیریتی ثبت نشده است</h4>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #F7FAFC; border-bottom: 2px solid #E2E8F0;">
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">شماره</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">عنوان</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">تاریخ</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">تهیه‌کننده</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">وضعیت</th>
                            <th style="padding: 12px; text-align: center; color: #4A5568; font-weight: 600;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reviews as $review): ?>
                            <tr style="border-bottom: 1px solid #F0F0F0;">
                                <td style="padding: 12px;"><strong style="color: #6C3CE1;"><?= qms_e($review['review_number']) ?></strong></td>
                                <td style="padding: 12px; color: #2D3748;"><?= qms_e($review['title']) ?></td>
                                <td style="padding: 12px; color: #4A5568;"><?= qms_e($review['review_date']) ?></td>
                                <td style="padding: 12px; color: #4A5568;"><?= qms_e($review['created_by_name'] ?? '-') ?></td>
                                <td style="padding: 12px;">
                                    <?php $color = $statusColors[$review['status']] ?? '#6B7280'; ?>
                                    <span style="background: <?= $color ?>20; color: <?= $color ?>; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                        <?= qms_status_label($review['status']) ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <a href="?controller=managementreviews&action=show&id=<?= $review['id'] ?>" 
                                       style="background: #6C3CE1; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;">
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