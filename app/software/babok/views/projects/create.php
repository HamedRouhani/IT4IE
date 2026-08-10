<?php
/**
 * ویو ایجاد پروژه جدید
 * مسیر: app/software/babok/views/projects/create.php
 */
$pageTitle = 'ایجاد پروژه جدید - BABOK Analyzer';
$activePage = 'projects';
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-plus-circle"></i> ایجاد پروژه جدید
        </div>
        <a href="?route=projects" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>
    
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="alert-software warning">
            <i class="fas fa-info-circle"></i>
            <span>
                شما در حالت مهمان هستید. برای ایجاد پروژه، ابتدا 
                <a href="/login" style="color: var(--soft-secondary);">وارد شوید</a>.
            </span>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="?route=projects_store">
        <div class="form-group">
            <label class="form-label" for="name">نام پروژه *</label>
            <input type="text" id="name" name="name" class="form-control" required 
                   placeholder="مثلاً: پروژه تحلیل نیازمندی‌های سیستم مالی">
        </div>
        
        <div class="form-group">
            <label class="form-label" for="description">توضیحات پروژه</label>
            <textarea id="description" name="description" class="form-control" rows="4"
                      placeholder="توضیحات مختصری درباره اهداف و محدوده پروژه..."></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="phase">فاز فعلی پروژه</label>
                <select id="phase" name="phase" class="form-control">
                    <option value="initiation">شروع (Initiation)</option>
                    <option value="planning">برنامه‌ریزی (Planning)</option>
                    <option value="analysis" selected>تحلیل (Analysis)</option>
                    <option value="design">طراحی (Design)</option>
                    <option value="implementation">پیاده‌سازی (Implementation)</option>
                    <option value="evaluation">ارزیابی (Evaluation)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="methodology">متدولوژی توسعه</label>
                <select id="methodology" name="methodology" class="form-control">
                    <option value="waterfall">آبشاری (Waterfall)</option>
                    <option value="agile">چابک (Agile)</option>
                    <option value="hybrid" selected>ترکیبی (Hybrid)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="stakeholder_count">تعداد ذی‌نفعان</label>
                <input type="number" id="stakeholder_count" name="stakeholder_count" 
                       class="form-control" min="0" value="0">
            </div>
        </div>
        
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> ایجاد پروژه
            </button>
            <a href="?route=projects" class="btn btn-secondary btn-lg">انصراف</a>
        </div>
    </form>
</div>

<!-- راهنمای متدولوژی‌ها -->
<div class="card" style="margin-top: 20px; background: #f8f9fa;">
    <h4 class="card-title"><i class="fas fa-info-circle"></i> راهنمای انتخاب متدولوژی</h4>
    <div class="row" style="margin-top: 15px;">
        <div class="card" style="margin-bottom: 0;">
            <h5 style="color: var(--soft-primary);">🌊 آبشاری (Waterfall)</h5>
            <p class="text-muted" style="font-size: 0.85rem;">
                مناسب برای پروژه‌هایی با نیازمندی‌های ثابت و مشخص. مراحل به صورت متوالی انجام می‌شوند.
            </p>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <h5 style="color: var(--soft-success);">🔄 چابک (Agile)</h5>
            <p class="text-muted" style="font-size: 0.85rem;">
                مناسب برای پروژه‌هایی با نیازمندی‌های متغیر. توسعه در اسپرینت‌های کوتاه انجام می‌شود.
            </p>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <h5 style="color: var(--soft-warning);">⚖️ ترکیبی (Hybrid)</h5>
            <p class="text-muted" style="font-size: 0.85rem;">
                ترکیب مزایای هر دو روش. مناسب برای پروژه‌های با پیچیدگی متوسط.
            </p>
        </div>
    </div>
</div>