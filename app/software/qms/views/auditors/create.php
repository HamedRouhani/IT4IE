<?php
$pageTitle = 'ثبت ممیز جدید';
$currentPage = 'auditors';
?>

<div class="container-fluid" style="padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
            <i class="fas fa-user-check" style="color: #6C3CE1;"></i>
            ثبت ممیز جدید
        </h1>
        <a href="?controller=auditors" style="background: #718096; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-arrow-right"></i> بازگشت به لیست
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 800px;">
        <form method="POST" action="?controller=auditors&action=store">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">نام و نام خانوادگی *</label>
                    <input type="text" name="full_name" required style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;">
                </div>
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">ایمیل</label>
                    <input type="email" name="email" style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;">
                </div>
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">شماره تماس</label>
                    <input type="text" name="phone" style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;">
                </div>
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #4A5568;">صلاحیت‌ها و گواهینامه‌ها</label>
                    <input type="text" name="qualification" placeholder="مثال: ممیز ارشد ISO 9001" style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;">
                </div>
            </div>

            <div style="display: flex; gap: 30px; margin-bottom: 30px; padding: 15px; background: #F7FAFC; border-radius: 8px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="lead_auditor" value="1" style="width: 18px; height: 18px;">
                    <span style="color: #2D3748; font-weight: 500;">سرممیز (Lead Auditor) است</span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="iso_9001_certified" value="1" checked style="width: 18px; height: 18px;">
                    <span style="color: #2D3748; font-weight: 500;">گواهینامه ISO 9001 دارد</span>
                </label>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" style="background: linear-gradient(135deg, #6C3CE1, #8B6FE8); color: white; padding: 12px 30px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-save"></i> ثبت ممیز
                </button>
                <a href="?controller=auditors" style="background: #E2E8F0; color: #4A5568; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600;">انصراف</a>
            </div>
        </form>
    </div>
</div>