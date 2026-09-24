<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-edit"></i> ویرایش دپارتمان
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                ویرایش: <strong><?= hr_e($department['name']) ?></strong>
            </p>
        </div>
        <a href="<?= hr_url('department') ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('department', 'update', ['id' => $department['id']]) ?>">
        <?= $this->csrfField() ?>

        <div class="card hr-mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات دپارتمان</h3>
            </div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="name">
                            نام دپارتمان <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <input type="text" id="name" name="name"
                               class="hr-form-control"
                               value="<?= hr_e($department['name']) ?>"
                               required maxlength="200">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="code">کد دپارتمان</label>
                        <input type="text" id="code" name="code"
                               class="hr-form-control"
                               value="<?= hr_e($department['code'] ?? '') ?>"
                               maxlength="50">
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="parent_id">دپارتمان والد</label>
                    <select id="parent_id" name="parent_id" class="hr-form-control">
                        <option value="">— بدون والد (ریشه) —</option>
                        <?php foreach ($pdm_parents as $opt): ?>
                            <option value="<?= (int) $opt['id'] ?>"
                                <?= (int) ($department['parent_id'] ?? 0) === (int) $opt['id'] ? 'selected' : '' ?>>
                                <?= str_repeat('— ', (int) ($opt['level'] ?? 0)) . hr_e($opt['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="cost_center">مرکز هزینه</label>
                        <input type="text" id="cost_center" name="cost_center"
                               class="hr-form-control"
                               value="<?= hr_e($department['cost_center'] ?? '') ?>"
                               maxlength="50">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="budget">بودجه سالانه (ریال)</label>
                        <input type="text" id="budget" name="budget"
                               class="hr-form-control"
                               value="<?= hr_e($department['budget'] ?? '') ?>"
                               inputmode="numeric">
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="location">موقعیت فیزیکی</label>
                    <input type="text" id="location" name="location"
                           class="hr-form-control"
                           value="<?= hr_e($department['location'] ?? '') ?>"
                           maxlength="255">
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description"
                              class="hr-form-control" rows="3"
                              maxlength="1000"><?= hr_e($department['description'] ?? '') ?></textarea>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="sort_order">ترتیب نمایش</label>
                        <input type="number" id="sort_order" name="sort_order"
                               class="hr-form-control"
                               value="<?= (int) ($department['sort_order'] ?? 0) ?>"
                               min="0" max="999">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="status">وضعیت</label>
                        <select id="status" name="status" class="hr-form-control">
                            <option value="active" <?= $department['status'] === 'active' ? 'selected' : '' ?>>فعال</option>
                            <option value="inactive" <?= $department['status'] === 'inactive' ? 'selected' : '' ?>>غیرفعال</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary">
                <i class="fas fa-save"></i> ذخیره تغییرات
            </button>
            <a href="<?= hr_url('department') ?>" class="btn-hr-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr.js"></script>