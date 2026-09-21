<?php
/**
 * PdM Analyzer - فرم ایجاد دارایی
 * مسیر: app/software/pdm/views/asset/create.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-plus-circle"></i> افزودن دارایی جدید
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                اطلاعات تجهیز جدید را وارد کنید.
            </p>
        </div>
        <a href="<?= pdm_url('asset') ?>" class="btn-pdm-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="pdm-alert <?= pdm_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= pdm_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= pdm_url('asset', 'store') ?>">
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
                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="asset_code">
                            کد دارایی <span style="color: var(--pdm-danger);">*</span>
                        </label>
                        <input type="text" id="asset_code" name="asset_code"
                               class="pdm-form-control"
                               placeholder="مثال: P-001"
                               required maxlength="50">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="name">
                            نام دارایی <span style="color: var(--pdm-danger);">*</span>
                        </label>
                        <input type="text" id="name" name="name"
                               class="pdm-form-control"
                               placeholder="مثال: پمپ سانتریفیوژ خط ۱"
                               required maxlength="150">
                    </div>
                </div>

                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="location_id">مکان</label>
                        <select id="location_id" name="location_id" class="pdm-form-control">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($pdm_locations as $loc): ?>
                                <option value="<?= (int) $loc['id'] ?>">
                                    <?= str_repeat('— ', (int) ($loc['level'] ?? 0)) . pdm_e($loc['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="category_id">دسته‌بندی</label>
                        <select id="category_id" name="category_id" class="pdm-form-control">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($pdm_categories as $cat): ?>
                                <option value="<?= (int) $cat['id'] ?>">
                                    <?= str_repeat('— ', (int) ($cat['level'] ?? 0)) . pdm_e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="parent_asset_id">دارایی والد</label>
                    <select id="parent_asset_id" name="parent_asset_id" class="pdm-form-control">
                        <option value="">— بدون والد —</option>
                        <?php foreach ($parentAssets as $pa): ?>
                            <option value="<?= (int) $pa['id'] ?>">
                                <?= pdm_e($pa['asset_code']) ?> — <?= pdm_e($pa['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="pdm-text-muted">اگر این دارایی بخشی از یک تجهیز بزرگ‌تر است.</small>
                </div>
            </div>
        </div>

        <!-- مشخصات فنی -->
        <div class="card pdm-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-microchip"></i>
                    مشخصات فنی
                </h3>
            </div>
            <div class="card-body">
                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="manufacturer">سازنده</label>
                        <input type="text" id="manufacturer" name="manufacturer"
                               class="pdm-form-control"
                               placeholder="مثال: Siemens" maxlength="100">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="model">مدل</label>
                        <input type="text" id="model" name="model"
                               class="pdm-form-control"
                               placeholder="مثال: S7-1200" maxlength="100">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="serial_number">شماره سریال</label>
                        <input type="text" id="serial_number" name="serial_number"
                               class="pdm-form-control"
                               placeholder="مثال: SN12345" maxlength="100">
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="installation_date">تاریخ نصب</label>
                        <input type="text" id="installation_date" name="installation_date"
                               class="pdm-form-control pdm-datepicker"
                               placeholder="1403/01/15" maxlength="10">
                        <small class="pdm-text-muted">فرمت: YYYY/MM/DD (شمسی)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- طبقه‌بندی -->
        <div class="card pdm-mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    طبقه‌بندی و وضعیت
                </h3>
            </div>
            <div class="card-body">
                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="criticality">بحرانیت</label>
                        <select id="criticality" name="criticality" class="pdm-form-control">
                            <option value="low">پایین</option>
                            <option value="medium" selected>متوسط</option>
                            <option value="high">بالا</option>
                            <option value="critical">بحرانی</option>
                        </select>
                    </div>

                    <div class="pdm-form-group">
                        <label class="pdm-form-label" for="status">وضعیت</label>
                        <select id="status" name="status" class="pdm-form-control">
                            <option value="active" selected>فعال</option>
                            <option value="inactive">غیرفعال</option>
                            <option value="maintenance">در تعمیر</option>
                            <option value="retired">بازنشسته</option>
                        </select>
                    </div>
                </div>

                <div class="pdm-form-group">
                    <label class="pdm-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description"
                              class="pdm-form-control" rows="3"
                              maxlength="2000"
                              placeholder="توضیحات تکمیلی..."></textarea>
                </div>
            </div>
        </div>

        <!-- دکمه‌ها -->
        <div class="pdm-flex pdm-gap-2">
            <button type="submit" class="btn-pdm-primary">
                <i class="fas fa-save"></i> ذخیره دارایی
            </button>
            <a href="<?= pdm_url('asset') ?>" class="btn-pdm-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/pdm-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/pdm.js"></script>