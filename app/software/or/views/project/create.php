<?php
/**
 * ایجاد پروژه جدید OR
 * مسیر: app/software/or/views/project/create.php
 */
$selectedType = $_GET['type'] ?? '';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-plus-circle text-primary"></i> ایجاد پروژه جدید
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('') ?>">OR Analyzer</a></li>
                    <li class="breadcrumb-item"><a href="<?= or_url('controller=project') ?>">پروژه‌ها</a></li>
                    <li class="breadcrumb-item active">ایجاد جدید</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="<?= or_url('controller=project&action=store') ?>" method="POST">
                        
                        <!-- اطلاعات پایه -->
                        <h5 class="mb-4">
                            <i class="fas fa-info-circle text-primary"></i> اطلاعات پایه
                        </h5>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">نام پروژه <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   placeholder="مثال: بهینه‌سازی حمل و نقل محصولات" required>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">توضیحات</label>
                            <textarea class="form-control" id="description" name="description" rows="3"
                                      placeholder="توضیحات مختصر درباره مسئله..."></textarea>
                        </div>

                        <hr class="my-4">

                        <!-- نوع مسئله -->
                        <h5 class="mb-4">
                            <i class="fas fa-cubes text-info"></i> نوع مسئله
                        </h5>
                        
                        <div class="row g-3 mb-4">
                            <?php foreach ($problemTypes as $pt): ?>
                                <div class="col-md-6">
                                    <div class="form-check card border p-3 h-100 problem-type-card">
                                        <input class="form-check-input" type="radio" 
                                               name="problem_type_id" id="pt_<?= $pt['id'] ?>" 
                                               value="<?= $pt['id'] ?>"
                                               <?= ($selectedType === $pt['code']) ? 'checked' : '' ?>
                                               required>
                                        <label class="form-check-label w-100" for="pt_<?= $pt['id'] ?>">
                                            <strong><?= htmlspecialchars($pt['name_fa']) ?></strong>
                                            <span class="badge bg-light text-dark ms-2"><?= $pt['code'] ?></span>
                                            <br>
                                            <small class="text-muted">
                                                <?= or_truncateText($pt['description'] ?? '', 80) ?>
                                            </small>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <hr class="my-4">

                        <!-- روش حل -->
                        <h5 class="mb-4">
                            <i class="fas fa-calculator text-warning"></i> روش حل (اختیاری)
                        </h5>
                        
                        <div class="mb-4">
                            <select class="form-select" id="method_id" name="method_id">
                                <option value="">انتخاب خودکار (بر اساس نوع مسئله)</option>
                                <?php foreach ($methods as $method): ?>
                                    <option value="<?= $method['id'] ?>" data-type="<?= $method['problem_type_code'] ?? '' ?>">
                                        <?= htmlspecialchars($method['name_fa']) ?> (<?= $method['code'] ?>)
                                        - <?= or_getMethodCategoryLabel($method['category'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <hr class="my-4">

                        <!-- هدف مسئله -->
                        <h5 class="mb-4">
                            <i class="fas fa-bullseye text-danger"></i> هدف مسئله
                        </h5>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="form-check card border p-3">
                                    <input class="form-check-input" type="radio" name="objective" 
                                           id="obj_min" value="minimize" checked>
                                    <label class="form-check-label" for="obj_min">
                                        <strong>کمینه‌سازی</strong>
                                        <br><small class="text-muted">مینیمم کردن هزینه، زمان، ضایعات...</small>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check card border p-3">
                                    <input class="form-check-input" type="radio" name="objective" 
                                           id="obj_max" value="maximize">
                                    <label class="form-check-label" for="obj_max">
                                        <strong>بیشینه‌سازی</strong>
                                        <br><small class="text-muted">ماکزیمم کردن سود، بهره‌وری، تولید...</small>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- دکمه‌ها -->
                        <div class="d-flex justify-content-between">
                            <a href="<?= or_url('controller=project') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-right"></i> بازگشت
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> ایجاد پروژه و ادامه
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.problem-type-card {
    cursor: pointer;
    transition: all 0.2s;
}
.problem-type-card:hover {
    border-color: #0d6efd !important;
    background-color: #f8f9fa;
}
.problem-type-card:has(input:checked) {
    border-color: #0d6efd !important;
    background-color: #e7f1ff;
}
</style>

<script>
// فیلتر کردن روش‌ها بر اساس نوع مسئله
document.querySelectorAll('input[name="problem_type_id"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const typeCode = this.dataset?.code || '';
        const methodSelect = document.getElementById('method_id');
        
        // ریست انتخاب
        methodSelect.value = '';
        
        // فیلتر آپشن‌ها
        Array.from(methodSelect.options).forEach(option => {
            if (option.value === '') {
                option.hidden = false;
            } else {
                const optType = option.dataset.type || '';
                option.hidden = typeCode && optType && optType !== typeCode;
            }
        });
    });
});

// انتخاب اولیه بر اساس پارامتر URL
document.addEventListener('DOMContentLoaded', function() {
    const selectedType = '<?= $selectedType ?>';
    if (selectedType) {
        const radio = document.querySelector(`input[value="${selectedType}"]`);
        if (radio) {
            radio.checked = true;
            radio.dispatchEvent(new Event('change'));
        }
    }
});
</script>