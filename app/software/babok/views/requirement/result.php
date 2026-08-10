<?php
/**
 * ویو نتایج تحلیل نیازمندی (صفحه جایگزین)
 * مسیر: app/software/babok/views/requirement/result.php
 * 
 * نکته: در پیاده‌سازی فعلی، نتایج به صورت AJAX در صفحه index نمایش داده می‌شوند.
 * این صفحه برای استفاده‌های آینده یا نمایش نتایج ذخیره شده است.
 */
$pageTitle = 'نتایج تحلیل نیازمندی - BABOK Analyzer';
$activePage = 'requirement';
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-clipboard-check"></i> نتایج تحلیل نیازمندی
        </div>
        <div class="card-tools">
            <a href="?route=requirement" class="btn btn-primary">
                <i class="fas fa-redo"></i> تحلیل جدید
            </a>
        </div>
    </div>
    
    <div class="text-muted text-center" style="padding: 50px 0;">
        <i class="fas fa-info-circle" style="font-size: 3rem; opacity: 0.3;"></i>
        <p style="margin-top: 15px;">
            در نسخه فعلی، نتایج تحلیل به صورت مستقیم در صفحه اصلی نمایش داده می‌شوند.
        </p>
        <a href="?route=requirement" class="btn btn-primary btn-lg">
            <i class="fas fa-arrow-right"></i> بازگشت به صفحه تحلیل
        </a>
    </div>
</div>