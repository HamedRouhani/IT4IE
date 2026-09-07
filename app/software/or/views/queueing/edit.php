<?php
$project = $project ?? [];
$models = $models ?? [];
$code = $project['model_code'] ?? 'MM1';
?>
<div class="container-fluid py-3 py-md-4">
    <div class="or-page-header">
        <div>
            <h3 class="mb-1"><i class="fas fa-edit text-primary me-2"></i>ویرایش پروژه صف</h3>
            <small class="text-muted"><?= or_e($project['name']) ?></small>
        </div>
        <a href="<?= or_url('controller=queueing&action=show&id=' . (int)$project['id']) ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i><span class="d-none d-md-inline">بازگشت</span>
        </a>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6 col-xl-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 py-md-3"><h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>پارامترهای مدل</h6></div>
                <div class="card-body p-3">
                    <form method="post" action="<?= or_url('controller=queueing&action=update&id=' . (int)$project['id']) ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold small mb-1">نام پروژه</label>
                            <input type="text" name="name" class="form-control form-control-sm" value="<?= or_e($project['name']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small mb-1">مدل صف</label>
                            <select name="model" class="form-select form-select-sm" onchange="renderEditModel()">
                                <?php foreach ($models as $c => $m): ?>
                                    <option value="<?= $c ?>" <?= $c === $code ? 'selected' : '' ?>><?= or_e($m['name']) ?> — <?= or_e($m['desc']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small mb-1">λ (نرخ ورود)</label>
                                <input type="number" step="any" min="0.0001" name="lambda" class="form-control form-control-sm" value="<?= or_e($project['lambda']) ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-1">μ (نرخ خدمت)</label>
                                <input type="number" step="any" min="0.0001" name="mu" class="form-control form-control-sm" value="<?= or_e($project['mu']) ?>" required>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6" id="editServersBox">
                                <label class="form-label small mb-1">تعداد سرور c</label>
                                <input type="number" min="1" max="50" name="servers" class="form-control form-control-sm" value="<?= (int)$project['servers'] ?>">
                            </div>
                            <div class="col-6" id="editCapacityBox">
                                <label class="form-label small mb-1">ظرفیت K</label>
                                <input type="number" min="1" name="capacity" class="form-control form-control-sm" value="<?= $project['capacity'] !== null ? (int)$project['capacity'] : 10 ?>">
                            </div>
                        </div>
                        <div class="mb-3" id="editStdBox">
                            <label class="form-label small mb-1">σ (انحراف زمان خدمت)</label>
                            <input type="number" step="any" min="0" name="service_std" class="form-control form-control-sm" value="<?= or_e($project['service_std'] ?? 0) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small mb-1">توضیحات</label>
                            <textarea name="description" class="form-control form-control-sm" rows="3"><?= or_e($project['description']) ?></textarea>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> ذخیره و حل مجدد
                            </button>
                            <a href="<?= or_url('controller=queueing&action=show&id=' . (int)$project['id']) ?>" class="btn btn-outline-secondary btn-sm">انصراف</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 col-xl-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 py-md-3"><h6 class="mb-0"><i class="fas fa-circle-info me-2 text-info"></i>راهنما</h6></div>
                <div class="card-body p-3 small text-muted">
                    <ul class="mb-0 ps-3">
                        <li>پس از ذخیره، مدل به‌صورت خودکار مجدداً حل و نتایج به‌روزرسانی می‌شوند.</li>
                        <li>اگر پارامترهای جدید منجر به ρ ≥ ۱ شوند، ذخیره انجام نمی‌شود و پیام خطا نمایش داده می‌شود.</li>
                        <li>فیلدهای c / K / σ فقط برای مدل‌هایی که به آن‌ها نیاز دارند اعمال می‌شوند.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function renderEditModel() {
    const code = document.querySelector('select[name="model"]').value;
    document.getElementById('editServersBox').style.display = ['MMc','MMcK'].includes(code) ? '' : 'none';
    document.getElementById('editCapacityBox').style.display = ['MM1K','MMcK'].includes(code) ? '' : 'none';
    document.getElementById('editStdBox').style.display = (code === 'MG1') ? '' : 'none';
}
document.addEventListener('DOMContentLoaded', renderEditModel);
</script>