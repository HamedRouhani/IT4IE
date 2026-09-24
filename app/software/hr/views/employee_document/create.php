<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-file-plus"></i> افزودن سند پرسنلی
            </h2>
        </div>
        <a href="<?= hr_url('employee') ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" 
          action="<?= hr_url('employee_document', 'store') ?>"
          enctype="multipart/form-data">
        <?= $this->csrfField() ?>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات سند</h3></div>
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
                                    <?= hr_e($e['full_name']) ?> (<?= hr_e($e['employee_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="document_type">نوع سند</label>
                        <select id="document_type" name="document_type" class="hr-form-control">
                            <?php foreach ($documentTypes as $key => $label): ?>
                                <option value="<?= hr_e($key) ?>"><?= hr_e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="title">
                        عنوان سند <span style="color: var(--hr-danger);">*</span>
                    </label>
                    <input type="text" id="title" name="title"
                           class="hr-form-control"
                           placeholder="مثال: قرارداد استخدام ۱۴۰۴"
                           required maxlength="200">
                </div>

                <!-- آپلود فایل -->
                <div class="hr-form-group">
                    <label class="hr-form-label" for="document_file">فایل سند</label>
                    <input type="file" 
                           id="document_file" 
                           name="document_file"
                           class="hr-form-control"
                           accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xls,.xlsx,.txt,.zip">
                    <small class="hr-text-muted">
                        حداکثر حجم: <strong>۱۰ مگابایت</strong> — 
                        پسوندهای مجاز: PDF, JPG, PNG, DOC, DOCX, XLS, XLSX, ZIP
                    </small>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="issue_date">تاریخ صدور (شمسی)</label>
                        <input type="text" id="issue_date" name="issue_date"
                               class="hr-form-control hr-datepicker"
                               placeholder="1404/01/15"
                               maxlength="10" autocomplete="off">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="expiry_date">تاریخ انقضا (شمسی)</label>
                        <input type="text" id="expiry_date" name="expiry_date"
                               class="hr-form-control hr-datepicker"
                               placeholder="1405/01/14"
                               maxlength="10" autocomplete="off">
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
                <i class="fas fa-save"></i> ذخیره سند
            </button>
            <a href="<?= hr_url('employee') ?>" class="btn-hr-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/hr.js?v=<?= time() ?>"></script>