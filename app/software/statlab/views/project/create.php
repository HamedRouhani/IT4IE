<?php
/**
 * فرم ایجاد پروژه جدید
 */
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="fas fa-plus-circle text-success me-2"></i>ایجاد پروژه آماری</h3>
        <a href="<?= stat_url('controller=project') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right me-1"></i> بازگشت
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form id="projectForm">
                        <div class="mb-4">
                            <label class="form-label fw-bold">نام پروژه <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" 
                                   placeholder="مثال: تحلیل وزن دانشجویان" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">توضیحات</label>
                            <textarea name="description" class="form-control" rows="3" 
                                      placeholder="توضیحات اختیاری درباره پروژه..."></textarea>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">دسته تحلیل</label>
                                <select name="category" class="form-select">
                                    <option value="descriptive">آمار توصیفی</option>
                                    <option value="distribution">توزیع‌های احتمال</option>
                                    <option value="hypothesis">آزمون فرض</option>
                                    <option value="regression">رگرسیون</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">هدف تحلیل</label>
                                <select name="objective" class="form-select">
                                    <option value="descriptive">توصیف داده‌ها</option>
                                    <option value="compare">مقایسه گروه‌ها</option>
                                    <option value="predict">پیش‌بینی</option>
                                    <option value="fit">برازش توزیع</option>
                                    <option value="test">آزمون فرض</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">سطح معناداری (α)</label>
                            <select name="alpha" class="form-select">
                                <option value="0.05">0.05 (استاندارد)</option>
                                <option value="0.01">0.01 (دقیق)</option>
                                <option value="0.10">0.10 (آسان‌گیر)</option>
                                <option value="0.001">0.001 (بسیار دقیق)</option>
                            </select>
                            <small class="text-muted">احتمال خطای نوع اول (رد فرض صفر درست)</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-1"></i> ایجاد پروژه
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="createAndAnalyze()">
                                <i class="fas fa-magic me-1"></i> ایجاد و شروع تحلیل سریع
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('projectForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const payload = Object.fromEntries(formData);
    
    const btn = e.target.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> در حال ایجاد...';
    btn.disabled = true;
    
    try {
        const res = await fetch('<?= stat_url('controller=project&action=store') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        
        const data = await res.json();
        
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            alert('❌ خطا: ' + (data.error || 'نامشخص'));
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    } catch (err) {
        alert('❌ خطای شبکه: ' + err.message);
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
});

function createAndAnalyze() {
    // ایجاد پروژه و هدایت به صفحه آمار توصیفی
    window.location.href = '<?= stat_url('controller=descriptive') ?>';
}
</script>