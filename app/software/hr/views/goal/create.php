<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-plus-circle"></i> ایجاد هدف جدید
            </h2>
        </div>
        <a href="<?= hr_url('goal') ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('goal', 'store') ?>">
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
                                <option value="<?= (int) $e['id'] ?>"><?= hr_e($e['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="goal_type">نوع هدف</label>
                        <select id="goal_type" name="goal_type" class="hr-form-control">
                            <?php foreach ($typeOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>"><?= hr_e($v) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="parent_goal_id">هدف والد (اختیاری)</label>
                        <select id="parent_goal_id" name="parent_goal_id" class="hr-form-control">
                            <option value="">— ندارد —</option>
                            <?php foreach ($hr_parent_goals as $p): ?>
                                <option value="<?= (int) $p['id'] ?>">
                                    <?= hr_e(hr_truncate($p['title'], 50)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="title">
                        عنوان هدف <span style="color: var(--hr-danger);">*</span>
                    </label>
                    <input type="text" id="title" name="title" class="hr-form-control"
                           placeholder="مثال: افزایش رضایت مشتری به ۹۰٪"
                           required maxlength="255">
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description" class="hr-form-control" rows="3" maxlength="2000"></textarea>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="category">دسته‌بندی</label>
                        <input type="text" id="category" name="category" class="hr-form-control"
                               placeholder="مثال: فروش، فنی، منابع انسانی" maxlength="100">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="weight">وزن (٪)</label>
                        <input type="number" id="weight" name="weight" class="hr-form-control"
                               value="100" min="0" max="100" step="0.01">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="review_period">دوره ارزیابی</label>
                        <input type="text" id="review_period" name="review_period" class="hr-form-control"
                               placeholder="مثال: Q1-1404" maxlength="20">
                    </div>
                </div>
            </div>
        </div>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-line"></i> سنجش و مهلت</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="target_value">مقدار هدف</label>
                        <input type="number" id="target_value" name="target_value" class="hr-form-control"
                               step="0.01" placeholder="مثال: 100">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="current_value">مقدار فعلی</label>
                        <input type="number" id="current_value" name="current_value" class="hr-form-control"
                               step="0.01" value="0">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="unit">واحد</label>
                        <input type="text" id="unit" name="unit" class="hr-form-control"
                               placeholder="مثال: ٪، نفر، تومان" maxlength="50">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="progress">پیشرفت (٪)</label>
                        <input type="number" id="progress" name="progress" class="hr-form-control"
                               value="0" min="0" max="100">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="start_date">
                            تاریخ شروع (شمسی) <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <input type="text" id="start_date" name="start_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= hr_e(hr_today()) ?>"
                               required maxlength="10" autocomplete="off">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="due_date">
                            تاریخ پایان (شمسی) <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <input type="text" id="due_date" name="due_date"
                               class="hr-form-control hr-datepicker"
                               required maxlength="10" autocomplete="off">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="priority">اولویت</label>
                        <select id="priority" name="priority" class="hr-form-control">
                            <?php foreach ($priorityOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $k === 'normal' ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="status">وضعیت</label>
                        <select id="status" name="status" class="hr-form-control">
                            <?php foreach ($statusOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $k === 'draft' ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
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
            <a href="<?= hr_url('goal') ?>" class="btn-hr-outline"><i class="fas fa-times"></i> انصراف</a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/hr.js?v=<?= time() ?>"></script>