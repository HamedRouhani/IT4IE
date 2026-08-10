<div class="admin-container">
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>⚠️ خطا در بارگذاری آمار</h1>
            <span>مشکل در جدول دیتابیس</span>
        </div>
        
        <div class="admin-widget" style="background: #FEF3C7; border: 2px solid #F59E0B;">
            <h3 style="color: #92400E;"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error ?? 'خطای ناشناخته'); ?></h3>
        </div>
        
        <?php if (!empty($sql)): ?>
        <div class="admin-widget" style="margin-top: 20px;">
            <h3><i class="fas fa-database"></i> SQL مورد نیاز</h3>
            <p style="color: var(--gray-dark); margin-bottom: 15px;">
                این SQL را در <strong>phpMyAdmin</strong> → دیتابیس <code>itieir_maindb</code> → تب SQL اجرا کنید:
            </p>
            <pre style="background: #1e293b; color: #10b981; padding: 20px; border-radius: 8px; overflow-x: auto; direction: ltr; text-align: left; font-size: 0.85rem; line-height: 1.6;"><?php echo htmlspecialchars($sql); ?></pre>
            <div style="margin-top: 15px; padding: 12px; background: #D1FAE5; border-radius: 8px; color: #065f46;">
                <i class="fas fa-check-circle"></i>
                پس از اجرای SQL، صفحه را <a href="/admin/software-activity" style="color: #065f46; font-weight: bold;">رفرش</a> کنید.
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>