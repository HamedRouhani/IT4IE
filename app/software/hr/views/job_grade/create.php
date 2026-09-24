<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-plus-circle"></i> افزودن طبقه شغلی
            </h2>
        </div>
        <a href="<?= hr_url('job_grade') ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('job_grade', 'store') ?>">
        <?= $this->csrfField() ?>

        <div class="card hr-mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات طبقه</h3>
            </div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="code">
                            کد <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <input type="text" id="code" name="code"
                               class="hr-form-control"
                               placeholder="مثال: G1" required maxlength="50">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="name">
                            نام <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <input type="text" id="name" name="name"
                               class="hr-form-control"
                               placeholder="مثال: کارشناس پایه" required maxlength="100">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="level">
                            سطح (عدد) <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <input type="number" id="level" name="level"
                               class="hr-form-control"
                               value="1" min="1" max="99" required>
                        <small class="hr-text-muted">1 = پایین‌ترین سطح</small>
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="min_salary">حداقل حقوق (ریال)</label>
                        <input type="text" id="min_salary" name="min_salary"
                               class="hr-form-control"
                               inputmode="numeric"
                               placeholder="مثال: 50000000">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="max_salary">حداکثر حقوق (ریال)</label>
                        <input type="text" id="max_salary" name="max_salary"
                               class="hr-form-control"
                               inputmode="numeric"
                               placeholder="مثال: 100000000">
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description"
                              class="hr-form-control" rows="3"
                              maxlength="1000"></textarea>
                </div>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary">
                <i class="fas fa-save"></i> ذخیره
            </button>
            <a href="<?= hr_url('job_grade') ?>" class="btn-hr-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr.js"></script>