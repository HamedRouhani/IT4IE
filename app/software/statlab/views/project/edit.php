<?php
/**
 * ویرایش پروژه آماری
 * مسیر: views/project/edit.php
 */
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1"><i class="fas fa-edit text-warning me-2"></i>ویرایش پروژه</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= stat_url('controller=project') ?>">پروژه‌ها</a></li>
                    <li class="breadcrumb-item">
                        <a href="<?= stat_url('controller=project&action=show&id=' . $project['id']) ?>">
                            <?= stat_e($project['name']) ?>
                        </a>
                    </li>
                    <li class="breadcrumb-item active">ویرایش</li>
                </ol>
            </nav>
        </div>
        <a href="<?= stat_url('controller=project&action=show&id=' . $project['id']) ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-right me-1"></i> بازگشت
        </a>
    </div>

    <div class="row g-4">
        <!-- ستون سمت راست: اطلاعات کلی پروژه -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>اطلاعات پروژه</h5>
                </div>
                <div class="card-body">
                    <form id="projectEditForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">نام پروژه <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" 
                                   value="<?= stat_e($project['name']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">توضیحات</label>
                            <textarea name="description" class="form-control" rows="4"><?= stat_e($project['description']) ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">دسته تحلیل</label>
                            <select name="category" class="form-select">
                                <option value="descriptive" <?= ($project['category_code'] ?? '') === 'descriptive' ? 'selected' : '' ?>>آمار توصیفی</option>
                                <option value="distribution" <?= ($project['category_code'] ?? '') === 'distribution' ? 'selected' : '' ?>>توزیع‌های احتمال</option>
                                <option value="hypothesis" <?= ($project['category_code'] ?? '') === 'hypothesis' ? 'selected' : '' ?>>آزمون فرض</option>
                                <option value="regression" <?= ($project['category_code'] ?? '') === 'regression' ? 'selected' : '' ?>>رگرسیون</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">هدف تحلیل</label>
                            <select name="objective" class="form-select">
                                <option value="descriptive" <?= ($project['objective'] ?? '') === 'descriptive' ? 'selected' : '' ?>>توصیف داده‌ها</option>
                                <option value="compare" <?= ($project['objective'] ?? '') === 'compare' ? 'selected' : '' ?>>مقایسه گروه‌ها</option>
                                <option value="predict" <?= ($project['objective'] ?? '') === 'predict' ? 'selected' : '' ?>>پیش‌بینی</option>
                                <option value="fit" <?= ($project['objective'] ?? '') === 'fit' ? 'selected' : '' ?>>برازش توزیع</option>
                                <option value="test" <?= ($project['objective'] ?? '') === 'test' ? 'selected' : '' ?>>آزمون فرض</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">سطح α</label>
                            <select name="alpha" class="form-select">
                                <option value="0.05" <?= abs(($project['significance_level'] ?? 0) - 0.05) < 0.001 ? 'selected' : '' ?>>0.05</option>
                                <option value="0.01" <?= abs(($project['significance_level'] ?? 0) - 0.01) < 0.001 ? 'selected' : '' ?>>0.01</option>
                                <option value="0.10" <?= abs(($project['significance_level'] ?? 0) - 0.10) < 0.001 ? 'selected' : '' ?>>0.10</option>
                                <option value="0.001" <?= abs(($project['significance_level'] ?? 0) - 0.001) < 0.0001 ? 'selected' : '' ?>>0.001</option>
                            </select>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> ذخیره تغییرات
                            </button>
                            <button type="button" class="btn btn-outline-danger" onclick="deleteProject(<?= $project['id'] ?>)">
                                <i class="fas fa-trash me-1"></i> حذف کل پروژه
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ستون سمت چپ: مدیریت داده‌ها -->
        <div class="col-lg-8">
            <!-- کارت مجموعه داده‌ها -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-database me-2"></i>
                        مجموعه داده‌ها (<?= count($datasets) ?> متغیر)
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($datasets)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            هنوز داده‌ای برای این پروژه ثبت نشده است.
                            <a href="<?= stat_url('controller=descriptive') ?>" class="alert-link">افزودن داده جدید</a>
                        </div>
                    <?php else: ?>
                        <div id="datasetsAccordion" class="accordion">
                            <?php foreach ($datasets as $idx => $d): 
                                $data = json_decode($d['data_json'] ?? '[]', true) ?: [];
                                $collapseId = 'dataset_' . $d['id'];
                            ?>
                                <div class="accordion-item" id="dataset_item_<?= $d['id'] ?>">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button <?= $idx > 0 ? 'collapsed' : '' ?>" 
                                                type="button" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#<?= $collapseId ?>">
                                            <span class="badge bg-primary me-2">x<?= $idx + 1 ?></span>
                                            <strong><?= stat_e($d['name']) ?></strong>
                                            <span class="badge bg-info ms-2"><?= (int)$d['sample_size'] ?> داده</span>
                                            <span class="badge bg-secondary ms-1"><?= (int)($d['result_count'] ?? 0) ?> نتیجه</span>
                                        </button>
                                    </h2>
                                    <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $idx === 0 ? 'show' : '' ?>" 
                                         data-bs-parent="#datasetsAccordion">
                                        <div class="accordion-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold small">نام متغیر:</label>
                                                    <input type="text" class="form-control form-control-sm" 
                                                           value="<?= stat_e($d['name']) ?>" 
                                                           id="dataset_name_<?= $d['id'] ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold small">منبع:</label>
                                                    <input type="text" class="form-control form-control-sm" 
                                                           value="<?= stat_e($d['source']) ?>" disabled>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-bold small">
                                                        داده‌ها <small class="text-muted">(هر خط یک عدد)</small>
                                                    </label>
                                                    <textarea class="form-control font-monospace" 
                                                              id="dataset_data_<?= $d['id'] ?>" 
                                                              rows="8"><?= stat_e(implode("\n", $data)) ?></textarea>
                                                </div>
                                                <div class="col-12">
                                                    <div class="d-flex gap-2 flex-wrap">
                                                        <button type="button" class="btn btn-primary btn-sm" 
                                                                onclick="updateDatasetData(<?= $d['id'] ?>)">
                                                            <i class="fas fa-sync me-1"></i> به‌روزرسانی و محاسبه مجدد
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                                onclick="deleteDataset(<?= $d['id'] ?>, '<?= stat_e($d['name']) ?>')">
                                                            <i class="fas fa-trash me-1"></i> حذف این متغیر
                                                        </button>
                                                        <small class="text-muted align-self-center" id="dataset_status_<?= $d['id'] ?>">
                                                            آخرین به‌روزرسانی: <?= stat_e($d['created_at']) ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- کارت نتایج -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        نتایج تحلیل (<?= count($results) ?> مورد)
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($results)): ?>
                        <div class="p-4 text-center text-muted">
                            هنوز نتیجه‌ای ثبت نشده است.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>روش</th>
                                        <th>متغیر</th>
                                        <th>وضعیت</th>
                                        <th>تاریخ</th>
                                        <th class="text-center">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($results as $r): ?>
                                        <tr id="result_row_<?= $r['id'] ?>">
                                            <td><strong><?= stat_e($r['method_name'] ?? '-') ?></strong></td>
                                            <td><?= stat_e($r['dataset_name'] ?? '-') ?></td>
                                            <td>
                                                <span class="badge bg-<?= ($r['conclusion'] ?? '') === 'significant' ? 'success' : 'secondary' ?>">
                                                    <?= stat_e($r['conclusion'] ?? 'نامشخص') ?>
                                                </span>
                                            </td>
                                            <td class="text-muted small"><?= stat_e($r['created_at']) ?></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-outline-danger btn-sm"
                                                        onclick="deleteResult(<?= $r['id'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loader overlay -->
<div id="editLoader" class="d-none position-fixed top-0 start-0 w-100 h-100" 
     style="background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
    <div class="spinner-border text-light" style="width: 4rem; height: 4rem;"></div>
</div>

<script>
const PROJECT_ID = <?= $project['id'] ?>;
const UPDATE_URL = '<?= stat_url('controller=project&action=update&id=' . $project['id']) ?>';

// ─────────────────────────────────────────────
// ارسال درخواست AJAX به update
// ─────────────────────────────────────────────
async function sendUpdate(payload) {
    const loader = document.getElementById('editLoader');
    loader.classList.remove('d-none');
    
    try {
        const res = await fetch(UPDATE_URL, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        
        const text = await res.text();
        loader.classList.add('d-none');
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('پاسخ غیر JSON سرور:', text);
            showToast('❌ پاسخ سرور نامعتبر است (جزئیات در کنسول مرورگر)', 'danger');
            return { success: false };
        }
        
        if (data.success) {
            showToast('✅ ' + (data.message || 'عملیات موفق'), 'success');
            if (data.redirect) {
                setTimeout(() => { window.location.href = data.redirect; }, 800);
            }
        } else {
            showToast('❌ ' + (data.error || 'خطا'), 'danger');
        }
        return data;
        
    } catch (err) {
        loader.classList.add('d-none');
        showToast('❌ خطای شبکه: ' + err.message, 'danger');
        return { success: false };
    }
}

// ─────────────────────────────────────────────
// ذخیره اطلاعات کلی پروژه
// ─────────────────────────────────────────────
document.getElementById('projectEditForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const payload = Object.fromEntries(formData);
    payload.action = 'update_info';
    await sendUpdate(payload);
});

// ─────────────────────────────────────────────
// به‌روزرسانی داده‌های یک dataset
// ─────────────────────────────────────────────
async function updateDatasetData(datasetId) {
    const data = document.getElementById(`dataset_data_${datasetId}`).value;
    const statusEl = document.getElementById(`dataset_status_${datasetId}`);
    
    const result = await sendUpdate({
        action: 'update_dataset_data',
        dataset_id: datasetId,
        data: data
    });
    
    if (result.success) {
        statusEl.innerHTML = `<span class="text-success">✓ به‌روزرسانی شد (${result.count} داده)</span>`;
    }
}

// ─────────────────────────────────────────────
// حذف یک dataset
// ─────────────────────────────────────────────
async function deleteDataset(datasetId, name) {
    if (!confirm(`آیا از حذف متغیر "${name}" مطمئن هستید؟\nتمام نتایج مرتبط نیز حذف خواهند شد.`)) return;
    
    const result = await sendUpdate({
        action: 'delete_dataset',
        dataset_id: datasetId
    });
    
    if (result.success) {
        const item = document.getElementById(`dataset_item_${datasetId}`);
        if (item) {
            item.style.transition = 'opacity 0.3s';
            item.style.opacity = '0';
            setTimeout(() => item.remove(), 300);
        }
    }
}

// ─────────────────────────────────────────────
// حذف یک result
// ─────────────────────────────────────────────
async function deleteResult(resultId) {
    if (!confirm('آیا از حذف این نتیجه مطمئن هستید؟')) return;
    
    const result = await sendUpdate({
        action: 'delete_result',
        result_id: resultId
    });
    
    if (result.success) {
        const row = document.getElementById(`result_row_${resultId}`);
        if (row) {
            row.style.transition = 'opacity 0.3s';
            row.style.opacity = '0';
            setTimeout(() => row.remove(), 300);
        }
    }
}

// ─────────────────────────────────────────────
// حذف کل پروژه
// ─────────────────────────────────────────────
function deleteProject(id) {
    if (confirm('⚠️ آیا از حذف کامل این پروژه مطمئن هستید؟\nاین عملیات غیرقابل بازگشت است.')) {
        window.location.href = '<?= stat_url('controller=project&action=delete&id=') ?>' + id;
    }
}

// ─────────────────────────────────────────────
// Toast notification ساده
// ─────────────────────────────────────────────
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed`;
    toast.style.cssText = 'top: 80px; left: 50%; transform: translateX(-50%); z-index: 10000; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
    toast.innerHTML = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.transition = 'opacity 0.3s';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>