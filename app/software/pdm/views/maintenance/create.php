<?php
/**
 * PdM Analyzer - فرم ایجاد برنامه نگهداری
 * مسیر: app/software/pdm/views/maintenance/create.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-plus-circle"></i> افزودن برنامه نگهداری
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                برنامه نگهداری پیشگیرانه جدید ایجاد کنید.
            </p>
        </div>
        <a href="<?= pdm_url('maintenance') ?>" class="btn-pdm-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="pdm-alert <?= pdm_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= pdm_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= pdm_url('maintenance', 'store') ?>">
        <?= $this->csrfField() ?>

        <!-- اطلاعات پایه -->
        <div class="card pdm-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    اطلاعات پایه
                </h3>
            </div>
            <div class="card-body">
                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="title">
                        عنوان برنامه <span style="color: var(--pdm-danger);">*</span>
                    </label>
                    <input type="text" id="title" name="title"
                           class="pdm-form-control"
                           placeholder="مثال: بازرسی دوره‌ای پمپ خط ۱"
                           required maxlength="150">
                </div>

                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="asset_id">
                            دارایی <span style="color: var(--pdm-danger);">*</span>
                        </label>
                        <select id="asset_id" name="asset_id" class="pdm-form-control" required>
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($pdm_assets as $asset): ?>
                                <option value="<?= (int) $asset['id'] ?>">
                                    <?= pdm_e($asset['asset_code']) ?> — <?= pdm_e($asset['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="maintenance_type_id">
                            نوع نگهداری <span style="color: var(--pdm-danger);">*</span>
                        </label>
                        <select id="maintenance_type_id" name="maintenance_type_id"
                                class="pdm-form-control" required>
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($pdm_types as $type): ?>
                                <option value="<?= (int) $type['id'] ?>">
                                    <?= pdm_e($type['name']) ?> (<?= pdm_e($type['code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description"
                              class="pdm-form-control" rows="3"
                              maxlength="2000"
                              placeholder="شرح کارها و وظایف این برنامه..."></textarea>
                </div>
            </div>
        </div>

        <!-- فرکانس -->
        <div class="card pdm-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock"></i>
                    فرکانس اجرا
                </h3>
            </div>
            <div class="card-body">
                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="frequency_value">
                            هر <span style="color: var(--pdm-danger);">*</span>
                        </label>
                        <input type="number" id="frequency_value" name="frequency_value"
                               class="pdm-form-control"
                               placeholder="مثال: 30"
                               min="1" max="9999"
                               required>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="frequency_unit">واحد</label>
                        <select id="frequency_unit" name="frequency_unit" class="pdm-form-control">
                            <?php foreach ($frequencyUnits as $key => $label): ?>
                                <option value="<?= pdm_e($key) ?>" <?= $key === 'days' ? 'selected' : '' ?>>
                                    <?= pdm_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="start_date">تاریخ شروع (شمسی)</label>
                        <input type="text" id="start_date" name="start_date"
                               class="pdm-form-control pdm-datepicker"
                               placeholder="1404/07/01"
                               maxlength="10"
                               autocomplete="off">
                        <small class="pdm-text-muted">اگر خالی باشد، امروز در نظر گرفته می‌شود.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- اولویت و وضعیت -->
        <div class="card pdm-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-flag"></i>
                    اولویت و وضعیت
                </h3>
            </div>
            <div class="card-body">
                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="priority">اولویت</label>
                        <select id="priority" name="priority" class="pdm-form-control">
                            <option value="low">پایین</option>
                            <option value="normal" selected>عادی</option>
                            <option value="high">بالا</option>
                            <option value="urgent">فوری</option>
                        </select>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="status">وضعیت</label>
                        <select id="status" name="status" class="pdm-form-control">
                            <option value="active" selected>فعال</option>
                            <option value="inactive">غیرفعال</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="pdm-flex pdm-gap-2">
            <button type="submit" class="btn-pdm-primary">
                <i class="fas fa-save"></i> ذخیره برنامه
            </button>
            <a href="<?= pdm_url('maintenance') ?>" class="btn-pdm-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/pdm-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/pdm.js?v=<?= time() ?>"></script>