<?php
$pageTitle = 'ویرایش پروژه - ' . htmlspecialchars($project['name']) . ' - BABOK Analyzer';
$activePage = 'projects';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-edit" style="color: var(--warning-color);"></i>
        ویرایش پروژه: <?= htmlspecialchars($project['name']) ?>
    </h2>
    <a href="/babok/public/?route=projects_view&id=<?= $project['id'] ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-right"></i> بازگشت
    </a>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-body" style="padding: 30px;">
                <form action="/babok/public/?route=projects_update&id=<?= $project['id'] ?>" method="POST" id="projectForm">
                    
                    <!-- نام پروژه -->
                    <div class="mb-4">
                        <label for="name" class="form-label fw-bold">
                            <i class="fas fa-tag" style="color: var(--secondary-color);"></i>
                            نام پروژه <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= htmlspecialchars($project['name']) ?>"
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
                                  style="padding: 10px 14px; border-radius: 8px; border: 1px solid #ddd; resize: vertical;"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
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
                                <option value="waterfall" <?= $project['methodology'] === 'waterfall' ? 'selected' : '' ?>>💧 Waterfall (آبشاری)</option>
                                <option value="agile" <?= $project['methodology'] === 'agile' ? 'selected' : '' ?>>🔄 Agile (چابک)</option>
                                <option value="hybrid" <?= $project['methodology'] === 'hybrid' ? 'selected' : '' ?>>🔀 Hybrid (ترکیبی)</option>
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
                                <option value="initiation" <?= $project['phase'] === 'initiation' ? 'selected' : '' ?>>🚀 شروع</option>
                                <option value="planning" <?= $project['phase'] === 'planning' ? 'selected' : '' ?>>📋 برنامه‌ریزی</option>
                                <option value="analysis" <?= $project['phase'] === 'analysis' ? 'selected' : '' ?>>🔍 تحلیل</option>
                                <option value="design" <?= $project['phase'] === 'design' ? 'selected' : '' ?>>🎨 طراحی</option>
                                <option value="implementation" <?= $project['phase'] === 'implementation' ? 'selected' : '' ?>>⚙️ پیاده‌سازی</option>
                                <option value="evaluation" <?= $project['phase'] === 'evaluation' ? 'selected' : '' ?>>📊 ارزیابی</option>
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
                               value="<?= $project['stakeholder_count'] ?? 0 ?>" min="0"
                               style="padding: 10px 14px; border-radius: 8px; border: 1px solid #ddd;">
                        <small class="text-muted">تعداد افرادی که در پروژه نقش دارند یا تحت تأثیر آن هستند.</small>
                    </div>

                    <hr class="my-4">

                    <!-- دکمه‌ها -->
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-warning" style="padding: 10px 30px; font-weight: 600;">
                            <i class="fas fa-save"></i> به‌روزرسانی پروژه
                        </button>
                        <a href="/babok/public/?route=projects_view&id=<?= $project['id'] ?>" class="btn btn-secondary" style="padding: 10px 30px;">
                            <i class="fas fa-times"></i> انصراف
                        </a>
                    </div>

                </form>
            </div>
        </div>

        <!-- اطلاعات جانبی -->
        <div class="card mt-3" style="background: #f8f9fa; border: 1px dashed #ddd;">
            <div class="card-body" style="padding: 15px 20px;">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-0" style="font-size: 0.9rem;">
                            <i class="fas fa-calendar-alt" style="color: var(--secondary-color);"></i>
                            <strong>تاریخ ایجاد:</strong> <?= date('Y-m-d H:i', strtotime($project['created_at'])) ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-0" style="font-size: 0.9rem;">
                            <i class="fas fa-clock" style="color: var(--secondary-color);"></i>
                            <strong>آخرین بروزرسانی:</strong> <?= date('Y-m-d H:i', strtotime($project['updated_at'])) ?>
                        </p>
                    </div>
                </div>
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
.btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(243, 156, 18, 0.3);
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
        if (phaseSelect.value === 'initiation' || phaseSelect.value === 'waterfall') {
            phaseSelect.value = 'planning';
        }
    } else if (this.value === 'waterfall') {
        if (phaseSelect.value === 'planning' || phaseSelect.value === 'agile') {
            phaseSelect.value = 'initiation';
        }
    }
});
</script>