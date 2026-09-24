<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-plus-circle"></i> ایجاد شایستگی
            </h2>
        </div>
        <a href="<?= hr_url('competency') ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('competency', 'store') ?>">
        <?= $this->csrfField() ?>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات پایه</h3></div>
            <div class="card-body">

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="name">
                            نام شایستگی <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <input type="text" id="name" name="name" class="hr-form-control"
                               placeholder="مثال: تفکر استراتژیک"
                               required maxlength="200">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="code">کد</label>
                        <input type="text" id="code" name="code" class="hr-form-control"
                               placeholder="مثال: COMP-STRAT-001" maxlength="50">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="parent_id">شایستگی والد</label>
                        <select id="parent_id" name="parent_id" class="hr-form-control">
                            <option value="">— بدون والد —</option>
                            <?php foreach ($hr_parent_competencies as $p): ?>
                                <option value="<?= (int) $p['id'] ?>">
                                    <?= hr_e($p['name']) ?>
                                    <?php if (!empty($p['code'])): ?>
                                        (<?= hr_e($p['code']) ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="category">دسته‌بندی</label>
                        <select id="category" name="category" class="hr-form-control">
                            <?php foreach ($categoryOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $k === 'technical' ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="competency_type">نوع شایستگی</label>
                        <select id="competency_type" name="competency_type" class="hr-form-control">
                            <?php foreach ($typeOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $k === 'skill' ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="sort_order">ترتیب نمایش</label>
                        <input type="number" id="sort_order" name="sort_order"
                               class="hr-form-control" value="0" min="0" max="9999">
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description" class="hr-form-control"
                              rows="3" maxlength="2000"
                              placeholder="توضیح مختصر درباره این شایستگی..."></textarea>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="levels">سطوح (متن آزاد یا JSON)</label>
                    <textarea id="levels" name="levels" class="hr-form-control"
                              rows="4" maxlength="3000"
                              placeholder='مثال: {"1": "مبتدی", "2": "متوسط", "3": "پیشرفته", "4": "خبره"}'></textarea>
                    <small class="hr-text-muted">
                        می‌توانید سطوح مختلف شایستگی را به صورت متن یا JSON وارد کنید.
                    </small>
                </div>
            </div>
        </div>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-cog"></i> وضعیت</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="status">وضعیت</label>
                        <select id="status" name="status" class="hr-form-control">
                            <?php foreach ($statusOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $k === 'active' ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="is_core">شایستگی هسته‌ای</label>
                        <select id="is_core" name="is_core" class="hr-form-control">
                            <option value="0">خیر</option>
                            <option value="1">بله (هسته‌ای)</option>
                        </select>
                        <small class="hr-text-muted">
                            شایستگی‌های هسته‌ای برای همه کارکنان الزامی‌اند.
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary">
                <i class="fas fa-save"></i> ذخیره
            </button>
            <a href="<?= hr_url('competency') ?>" class="btn-hr-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr.js"></script>