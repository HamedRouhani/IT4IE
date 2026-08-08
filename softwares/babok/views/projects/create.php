<?php
$pageTitle = 'ایجاد پروژه جدید - BABOK Analyzer';
$activePage = 'projects';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-plus-circle" style="color: var(--secondary-color);"></i>
        ایجاد پروژه جدید
    </h2>
    <a href="/babok/public/?route=projects" class="btn btn-secondary">
        <i class="fas fa-arrow-right"></i> بازگشت
    </a>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-body" style="padding: 30px;">
                <form action="/babok/public/?route=projects_store" method="POST" id="projectForm">
                    
                    <!-- نام پروژه -->
                    <div class="mb-4">
                        <label for="name" class="form-label fw-bold">
                            <i class="fas fa-tag" style="color: var(--secondary-color);"></i>
                            نام پروژه <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="name" name="name" 
                               placeholder="مثال: سیستم مدیریت مشتریان" 
                               style="padding: 10px 14px; border-radius: 8px; border: 1px solid #ddd;"
                               required autofocus>
                        <small class="text-muted">یک نام مناسب و گویا برای پروژه انتخاب کنید.</small>
                    </div>

                    <!-- توضیحات -->
                    <div class="mb-4">
                        <label for="description" class="form-label fw-bold">
                            <i class="fas fa-align-left" style="color: var(--secondary-color);"></i>
                            توضیحات
                        </label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                                  placeholder="توضیحات کامل پروژه را وارد کنید..."
                                  style="padding: 10px 14px; border-radius: 8px; border: 1px solid #ddd; resize: vertical;"></textarea>
                        <small class="text-muted">می‌توانید اهداف، محدوده و سایر جزئیات پروژه را وارد کنید.</small>
                    </div>

                    <hr class="my-4">

                    <!-- متدلوژی و فاز -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="methodology" class="form-label fw-bold">
                                <i class="fas fa-code-branch" style="color: var(--secondary-color);"></i>
                                متدلوژی <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="methodology" name="methodology" 
                                    style="padding: 10px 14px; border-radius: 8px; border: 1px solid #ddd;" required>
                                <option value="waterfall">💧 Waterfall (آبشاری)</option>
                                <option value="agile" selected>🔄 Agile (چابک)</option>
                                <option value="hybrid">🔀 Hybrid (ترکیبی)</option>
                            </select>
                            <small class="text-muted">روش مدیریت پروژه را انتخاب کنید.</small>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="phase" class="form-label fw-bold">
                                <i class="fas fa-flag" style="color: var(--secondary-color);"></i>
                                فاز فعلی <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="phase" name="phase" 
                                    style="padding: 10px 14px; border-radius: 8px; border: 1px solid #ddd;" required>
                                <option value="initiation">🚀 شروع</option>
                                <option value="planning" selected>📋 برنامه‌ریزی</option>
                                <option value="analysis">🔍 تحلیل</option>
                                <option value="design">🎨 طراحی</option>
                                <option value="implementation">⚙️ پیاده‌سازی</option>
                                <option value="evaluation">📊 ارزیابی</option>
                            </select>
                            <small class="text-muted">فاز فعلی پروژه را مشخص کنید.</small>
                        </div>
                    </div>

                    <!-- تعداد ذی‌نفعان -->
                    <div class="mb-4">
                        <label for="stakeholder_count" class="form-label fw-bold">
                            <i class="fas fa-users" style="color: var(--secondary-color);"></i>
                            تعداد ذی‌نفعان
                        </label>
                        <input type="number" class="form-control" id="stakeholder_count" name="stakeholder_count" 
                               value="0" min="0" 
                               style="padding: 10px 14px; border-radius: 8px; border: 1px solid #ddd;">
                        <small class="text-muted">تعداد افرادی که در پروژه نقش دارند یا تحت تأثیر آن هستند.</small>
                    </div>

                    <hr class="my-4">

                    <!-- دکمه‌ها -->
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary" style="padding: 10px 30px; font-weight: 600;">
                            <i class="fas fa-save"></i> ذخیره پروژه
                        </button>
                        <a href="/babok/public/?route=projects" class="btn btn-secondary" style="padding: 10px 30px;">
                            <i class="fas fa-times"></i> انصراف
                        </a>
                    </div>

                </form>
            </div>
        </div>

        <!-- راهنمای سریع -->
        <div class="card mt-3" style="background: #f8f9fa; border: 1px dashed #ddd;">
            <div class="card-body" style="padding: 15px 20px;">
                <p class="mb-0" style="font-size: 0.9rem;">
                    <i class="fas fa-lightbulb" style="color: var(--warning-color);"></i>
                    <strong>نکته:</strong> پس از ایجاد پروژه، می‌توانید از طریق بخش 
                    <a href="/babok/public/?route=projects" style="color: var(--secondary-color);">پروژه‌ها</a> 
                    آن را مدیریت کرده و وظایف مرتبط را به آن اضافه کنید.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
/* استایل‌های بهبودیافته */
.form-label {
    font-weight: 600;
    margin-bottom: 6px;
}
.form-control, .form-select {
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}
.form-control:focus, .form-select:focus {
    border-color: var(--secondary-color);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15);
}
.btn {
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
}
.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
.card {
    border-radius: 16px;
    border: none;
    box-shadow: 0 2px 16px rgba(0, 0, 0, 0.08);
}
hr {
    opacity: 0.3;
}
</style>

<script>
// اعتبارسنجی سمت کلاینت
document.getElementById('projectForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    if (!name) {
        e.preventDefault();
        alert('لطفاً نام پروژه را وارد کنید.');
        document.getElementById('name').focus();
        return false;
    }
    return true;
});

// نمایش راهنما هنگام انتخاب متدلوژی
document.getElementById('methodology').addEventListener('change', function() {
    const phaseSelect = document.getElementById('phase');
    if (this.value === 'agile') {
        // پیشنهاد فازهای مناسب برای Agile
        phaseSelect.value = 'planning';
    } else if (this.value === 'waterfall') {
        phaseSelect.value = 'initiation';
    }
});
</script>