<?php
$pageTitle = $pageTitle ?? 'ایجاد پروژه جدید';
$currentPage = $currentPage ?? 'project';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-plus-circle"></i> ایجاد پروژه جدید مبتنی بر PMBOK</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="?controller=project&action=create">
            <div class="form-group">
                <label class="form-label">نام پروژه *</label>
                <input type="text" name="name" class="form-control" required placeholder="مثال: پیاده‌سازی سیستم ERP در کارخانه فولاد">
            </div>
            
            <div class="form-group">
                <label class="form-label">توضیحات و اهداف پروژه</label>
                <textarea name="description" class="form-control" rows="3" placeholder="شرح مختصری از اهداف کسب‌وکار و محدوده پروژه..."></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">صنعت فعالیت (برای پیشنهادات هوشمند) *</label>
                    <select name="industry" class="form-select" required>
                        <option value="">انتخاب کنید...</option>
                        <option value="manufacturing">تولیدی (Manufacturing)</option>
                        <option value="oil_gas">نفت و گاز (Oil & Gas)</option>
                        <option value="steel">فولادی (Steel)</option>
                        <option value="fmcg">کالاهای مصرفی سریع (FMCG)</option>
                        <option value="services">خدماتی (Services)</option>
                    </select>
                    <small class="text-muted">انتخاب صنعت باعث می‌شود سیستم به صورت خودکار فرآیندها و ریسک‌های مرتبط با آن صنعت را به پروژه شما اضافه کند.</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">متدولوژی مدیریت پروژه</label>
                    <select name="methodology" class="form-select">
                        <option value="hybrid" selected>ترکیبی (Hybrid)</option>
                        <option value="predictive">پیش‌بینی‌کننده (Predictive / Waterfall)</option>
                        <option value="agile">چابک (Agile)</option>
                        <option value="adaptive">تطبیقی (Adaptive)</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">فاز فعلی پروژه</label>
                    <select name="phase" class="form-select">
                        <option value="initiation" selected>آغازین (Initiation)</option>
                        <option value="planning">برنامه‌ریزی (Planning)</option>
                        <option value="execution">اجرا (Execution)</option>
                        <option value="monitoring">پایش و کنترل (Monitoring & Controlling)</option>
                        <option value="closure">اختتام (Closure)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">تعداد تقریبی ذی‌نفعان کلیدی</label>
                    <input type="number" name="stakeholder_count" class="form-control" value="5" min="1">
                </div>
            </div>
            
            <div class="form-actions" style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> ایجاد پروژه و پیشنهاد هوشمند
                </button>
                <a href="?controller=project" class="btn btn-secondary">
                    <i class="fas fa-times"></i> انصراف
                </a>
            </div>
        </form>
    </div>
</div>