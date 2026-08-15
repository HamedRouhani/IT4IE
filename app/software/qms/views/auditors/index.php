<?php
/**
 * ویو لیست ممیزان - ماژول QMS
 * مسیر: app/software/qms/views/auditors/index.php
 */
$pageTitle = 'ممیزان';
$currentPage = 'auditors';
?>

<div class="container-fluid" style="padding: 20px;">
    
    <!-- هدر -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
                <i class="fas fa-user-check" style="color: #6C3CE1;"></i>
                ممیزان
            </h1>
            <p style="color: #718096; margin-top: 5px;">مدیریت اطلاعات ممیزان داخلی و خارجی سازمان</p>
        </div>
        <a href="?controller=auditors&action=create" 
           style="background: linear-gradient(135deg, #6C3CE1, #8B6FE8); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600;">
            <i class="fas fa-plus"></i> ثبت ممیز جدید
        </a>
    </div>

    <!-- پیام‌ها -->
    <?php if (isset($_SESSION['message'])): ?>
        <div style="background: #D1FAE5; border: 1px solid #86EFAC; color: #065F46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?= qms_e($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i> <?= qms_e($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- کارت‌های آمار -->
    <?php
    $totalAuditors = count($auditors ?? []);
    $leadAuditors = count(array_filter($auditors ?? [], fn($a) => $a['lead_auditor'] ?? false));
    $certifiedAuditors = count(array_filter($auditors ?? [], fn($a) => $a['iso_9001_certified'] ?? false));
    ?>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px;">
        <div style="background: white; border-radius: 10px; padding: 20px; border-right: 4px solid #6C3CE1; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.85rem; color: #718096;">کل ممیزان</div>
                    <div style="font-size: 2rem; font-weight: 800; color: #6C3CE1;"><?= $totalAuditors ?></div>
                </div>
                <i class="fas fa-users" style="font-size: 2rem; color: #6C3CE1; opacity: 0.3;"></i>
            </div>
        </div>
        
        <div style="background: white; border-radius: 10px; padding: 20px; border-right: 4px solid #F59E0B; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.85rem; color: #718096;">سرممیزان</div>
                    <div style="font-size: 2rem; font-weight: 800; color: #F59E0B;"><?= $leadAuditors ?></div>
                </div>
                <i class="fas fa-user-tie" style="font-size: 2rem; color: #F59E0B; opacity: 0.3;"></i>
            </div>
        </div>
        
        <div style="background: white; border-radius: 10px; padding: 20px; border-right: 4px solid #10B981; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.85rem; color: #718096;">دارای گواهینامه ISO 9001</div>
                    <div style="font-size: 2rem; font-weight: 800; color: #10B981;"><?= $certifiedAuditors ?></div>
                </div>
                <i class="fas fa-certificate" style="font-size: 2rem; color: #10B981; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <!-- لیست ممیزان -->
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <h3 style="margin: 0 0 20px 0; color: #2D3748; border-bottom: 2px solid #E2E8F0; padding-bottom: 10px;">
            <i class="fas fa-list" style="color: #6C3CE1;"></i>
            لیست ممیزان ثبت شده
        </h3>

        <?php if (empty($auditors)): ?>
            <div style="text-align: center; padding: 50px; color: #718096;">
                <i class="fas fa-user-check" style="font-size: 4rem; opacity: 0.3; color: #6C3CE1;"></i>
                <h4 style="margin-top: 20px;">هیچ ممیزی ثبت نشده است</h4>
                <p style="margin: 10px 0 20px 0;">برای شروع ممیزی، ابتدا باید اطلاعات ممیزان را ثبت کنید</p>
                <a href="?controller=auditors&action=create" 
                   style="display: inline-block; background: #6C3CE1; color: white; padding: 10px 25px; border-radius: 8px; text-decoration: none;">
                    <i class="fas fa-plus"></i> ثبت اولین ممیز
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #F7FAFC; border-bottom: 2px solid #E2E8F0;">
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">نام و نام خانوادگی</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">ایمیل</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">تلفن</th>
                            <th style="padding: 12px; text-align: right; color: #4A5568; font-weight: 600;">صلاحیت</th>
                            <th style="padding: 12px; text-align: center; color: #4A5568; font-weight: 600;">سرممیز</th>
                            <th style="padding: 12px; text-align: center; color: #4A5568; font-weight: 600;">گواهینامه</th>
                            <th style="padding: 12px; text-align: center; color: #4A5568; font-weight: 600;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($auditors as $auditor): ?>
                            <tr style="border-bottom: 1px solid #F0F0F0;">
                                <td style="padding: 12px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #6C3CE1, #8B6FE8); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                            <?= mb_substr($auditor['full_name'] ?? 'م', 0, 1) ?>
                                        </div>
                                        <div>
                                            <strong style="color: #2D3748;"><?= qms_e($auditor['full_name'] ?? '-') ?></strong>
                                            <?php if (!empty($auditor['user_name'])): ?>
                                                <div style="font-size: 0.8rem; color: #718096;">کاربر: <?= qms_e($auditor['user_name']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 12px; color: #4A5568;">
                                    <?php if (!empty($auditor['email'])): ?>
                                        <a href="mailto:<?= qms_e($auditor['email']) ?>" style="color: #6C3CE1; text-decoration: none;">
                                            <?= qms_e($auditor['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #A0AEC0;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px; color: #4A5568; direction: ltr; text-align: right;">
                                    <?= qms_e($auditor['phone'] ?? '-') ?>
                                </td>
                                <td style="padding: 12px; color: #4A5568; font-size: 0.9rem;">
                                    <?= qms_e($auditor['qualification'] ?? '-') ?>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <?php if (!empty($auditor['lead_auditor'])): ?>
                                        <span style="background: #FEF3C7; color: #92400E; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                                            <i class="fas fa-star"></i> بله
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #A0AEC0; font-size: 0.85rem;">خیر</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <?php if (!empty($auditor['iso_9001_certified'])): ?>
                                        <span style="background: #D1FAE5; color: #065F46; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                                            <i class="fas fa-check"></i> دارد
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #A0AEC0; font-size: 0.85rem;">ندارد</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <div style="display: inline-flex; gap: 5px;">
                                        <a href="?controller=auditors&action=edit&id=<?= $auditor['id'] ?>" 
                                           style="background: #F59E0B; color: white; padding: 6px 10px; border-radius: 6px; text-decoration: none; font-size: 0.85rem;" 
                                           title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                                            <form method="POST" action="?controller=auditors&action=delete&id=<?= $auditor['id'] ?>" 
                                                  onsubmit="return confirm('آیا از حذف این ممیز مطمئن هستید؟');" 
                                                  style="display: inline;">
                                                <button type="submit" 
                                                        style="background: #EF4444; color: white; padding: 6px 10px; border: none; border-radius: 6px; cursor: pointer; font-size: 0.85rem;" 
                                                        title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .container-fluid > div[style*="grid-template-columns: repeat(auto-fit"] {
        grid-template-columns: 1fr !important;
    }
}
</style>