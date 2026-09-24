<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-plus-circle"></i> ایجاد شاخص جدید
            </h2>
        </div>
        <a href="<?= hr_url('kpi') ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('kpi', 'store') ?>">
        <?= $this->csrfField() ?>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات پایه</h3></div>
            <div class="card-body">

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="code">
                            کد شاخص <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <input type="text" id="code" name="code" class="hr-form-control"
                               placeholder="مثال: HC_NEW_001"
                               required maxlength="50" style="direction: ltr;">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="name">
                            نام شاخص <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <input type="text" id="name" name="name" class="hr-form-control"
                               required maxlength="200">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="unit">واحد</label>
                        <input type="text" id="unit" name="unit" class="hr-form-control"
                               placeholder="نفر، درصد، ریال، ساعت..." maxlength="50">
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description"
                              class="hr-form-control" rows="2" maxlength="2000"></textarea>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="formula">فرمول محاسبه</label>
                    <input type="text" id="formula" name="formula" class="hr-form-control"
                           placeholder="مثال: (contract / total) × 100"
                           maxlength="500" style="direction: ltr;">
                </div>
            </div>
        </div>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-tags"></i> دسته‌بندی</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="category">دسته‌بندی</label>
                        <select id="category" name="category" class="hr-form-control">
                            <?php foreach ($categoryOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $k === 'other' ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="kpi_type">نوع شاخص</label>
                        <select id="kpi_type" name="kpi_type" class="hr-form-control">
                            <?php foreach ($typeOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $k === 'manual' ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="sort_order">ترتیب نمایش</label>
                        <input type="number" id="sort_order" name="sort_order"
                               class="hr-form-control" value="100" min="0" max="9999">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="color">رنگ</label>
                        <input type="color" id="color" name="color"
                               class="hr-form-control" value="#1E40AF" style="height: 42px;">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="icon">آیکون (FontAwesome class)</label>
                        <input type="text" id="icon" name="icon" class="hr-form-control"
                               value="fas fa-chart-bar" maxlength="50" style="direction: ltr;">
                        <small class="hr-text-muted">
                            نمونه: <code>fas fa-users</code>، <code>fas fa-chart-line</code>
                        </small>
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="is_active">وضعیت</label>
                    <select id="is_active" name="is_active" class="hr-form-control">
                        <option value="1" selected>فعال</option>
                        <option value="0">غیرفعال</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary"><i class="fas fa-save"></i> ذخیره</button>
            <a href="<?= hr_url('kpi') ?>" class="btn-hr-outline"><i class="fas fa-times"></i> انصراف</a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr.js"></script>