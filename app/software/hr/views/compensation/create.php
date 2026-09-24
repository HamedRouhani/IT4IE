<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-plus-circle"></i> ایجاد حکم حقوقی
            </h2>
        </div>
        <a href="<?= hr_url('compensation') ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('compensation', 'store') ?>">
        <?= $this->csrfField() ?>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات پایه</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="employee_id">
                            کارمند <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <select id="employee_id" name="employee_id" class="hr-form-control" required>
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($hr_employees as $e): ?>
                                <option value="<?= (int) $e['id'] ?>"
                                    <?= (int) $preselectedEmployeeId === (int) $e['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($e['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="effective_date">
                            تاریخ اجرا (شمسی) <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <input type="text" id="effective_date" name="effective_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= hr_e(hr_today()) ?>"
                               required maxlength="10" autocomplete="off">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="end_date">تاریخ پایان (شمسی)</label>
                        <input type="text" id="end_date" name="end_date"
                               class="hr-form-control hr-datepicker"
                               maxlength="10" autocomplete="off">
                        <small class="hr-text-muted">اگر خالی باشد، یعنی حکم فعلی و باز است.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-money-bill"></i> حقوق و مزایا</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <?php foreach ($salaryFields as $field => $label): ?>
                        <div class="hr-form-group">
                            <label class="hr-form-label" for="<?= $field ?>"><?= hr_e($label) ?></label>
                            <input type="text" id="<?= $field ?>" name="<?= $field ?>"
                                   class="hr-form-control hr-money-input"
                                   inputmode="numeric" value="0">
                        </div>
                    <?php endforeach; ?>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="overtime_rate">نرخ اضافه‌کاری (ساعتی)</label>
                        <input type="text" id="overtime_rate" name="overtime_rate"
                               class="hr-form-control hr-money-input"
                               inputmode="numeric" value="0">
                    </div>
                </div>

                <div class="hr-form-row" style="margin-top: 1rem;">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="currency">واحد پول</label>
                        <select id="currency" name="currency" class="hr-form-control">
                            <?php foreach ($currencyOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $k === 'IRR' ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-cog"></i> اطلاعات تکمیلی</h3></div>
            <div class="card-body">
                <div class="hr-form-group">
                    <label class="hr-form-label" for="change_reason">دلیل تغییر</label>
                    <input type="text" id="change_reason" name="change_reason"
                           class="hr-form-control" maxlength="255"
                           placeholder="مثال: ترفیع، تمدید قرارداد، تورم سالانه...">
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="document_ref">شماره سند / مرجع</label>
                        <input type="text" id="document_ref" name="document_ref"
                               class="hr-form-control" maxlength="100">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="approved_by">تأییدکننده</label>
                        <select id="approved_by" name="approved_by" class="hr-form-control">
                            <option value="">— انتخاب —</option>
                            <?php foreach ($hr_employees as $e): ?>
                                <option value="<?= (int) $e['id'] ?>"><?= hr_e($e['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="notes">یادداشت</label>
                    <textarea id="notes" name="notes" class="hr-form-control" rows="3" maxlength="2000"></textarea>
                </div>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary"><i class="fas fa-save"></i> ذخیره</button>
            <a href="<?= hr_url('compensation') ?>" class="btn-hr-outline"><i class="fas fa-times"></i> انصراف</a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/hr.js?v=<?= time() ?>"></script>