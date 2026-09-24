<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-edit"></i> ویرایش هدف
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                <?= hr_e(hr_truncate($goal['title'], 60)) ?>
            </p>
        </div>
        <a href="<?= hr_url('goal', 'show', ['id' => $goal['id']]) ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('goal', 'update', ['id' => $goal['id']]) ?>">
        <?= $this->csrfField() ?>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات پایه</h3></div>
            <div class="card-body">

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="employee_id">کارمند</label>
                        <select id="employee_id" name="employee_id" class="hr-form-control" required>
                            <?php foreach ($hr_employees as $e): ?>
                                <option value="<?= (int) $e['id'] ?>"
                                    <?= (int) $goal['employee_id'] === (int) $e['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($e['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="goal_type">نوع هدف</label>
                        <select id="goal_type" name="goal_type" class="hr-form-control">
                            <?php foreach ($typeOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $goal['goal_type'] === $k ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="parent_goal_id">هدف والد</label>
                        <select id="parent_goal_id" name="parent_goal_id" class="hr-form-control">
                            <option value="">— ندارد —</option>
                            <?php foreach ($hr_parent_goals as $p): ?>
                                <?php if ((int) $p['id'] === (int) $goal['id']) continue; ?>
                                <option value="<?= (int) $p['id'] ?>"
                                    <?= (int) $goal['parent_goal_id'] === (int) $p['id'] ? 'selected' : '' ?>>
                                    <?= hr_e(hr_truncate($p['title'], 50)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="title">عنوان هدف</label>
                    <input type="text" id="title" name="title" class="hr-form-control"
                           value="<?= hr_e($goal['title']) ?>" required maxlength="255">
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description" class="hr-form-control" rows="3" maxlength="2000"><?= hr_e($goal['description'] ?? '') ?></textarea>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="category">دسته‌بندی</label>
                        <input type="text" id="category" name="category" class="hr-form-control"
                               value="<?= hr_e($goal['category'] ?? '') ?>" maxlength="100">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="weight">وزن (٪)</label>
                        <input type="number" id="weight" name="weight" class="hr-form-control"
                               value="<?= hr_e($goal['weight'] ?? 100) ?>" min="0" max="100" step="0.01">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="review_period">دوره ارزیابی</label>
                        <input type="text" id="review_period" name="review_period" class="hr-form-control"
                               value="<?= hr_e($goal['review_period'] ?? '') ?>" maxlength="20">
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
                               value="<?= hr_e($goal['target_value'] ?? '') ?>" step="0.01">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="current_value">مقدار فعلی</label>
                        <input type="number" id="current_value" name="current_value" class="hr-form-control"
                               value="<?= hr_e($goal['current_value'] ?? 0) ?>" step="0.01">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="unit">واحد</label>
                        <input type="text" id="unit" name="unit" class="hr-form-control"
                               value="<?= hr_e($goal['unit'] ?? '') ?>" maxlength="50">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="progress">پیشرفت (٪)</label>
                        <input type="number" id="progress" name="progress" class="hr-form-control"
                               value="<?= (int) $goal['progress'] ?>" min="0" max="100">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="start_date">تاریخ شروع (شمسی)</label>
                        <input type="text" id="start_date" name="start_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= hr_e(hr_date($goal['start_date'], 'Y/m/d')) ?>"
                               required maxlength="10" autocomplete="off">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="due_date">تاریخ پایان (شمسی)</label>
                        <input type="text" id="due_date" name="due_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= hr_e(hr_date($goal['due_date'], 'Y/m/d')) ?>"
                               required maxlength="10" autocomplete="off">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="priority">اولویت</label>
                        <select id="priority" name="priority" class="hr-form-control">
                            <?php foreach ($priorityOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $goal['priority'] === $k ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="status">وضعیت</label>
                        <select id="status" name="status" class="hr-form-control">
                            <?php foreach ($statusOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $goal['status'] === $k ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="notes">یادداشت</label>
                    <textarea id="notes" name="notes" class="hr-form-control" rows="3" maxlength="2000"><?= hr_e($goal['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary"><i class="fas fa-save"></i> ذخیره تغییرات</button>
            <a href="<?= hr_url('goal', 'show', ['id' => $goal['id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/hr.js?v=<?= time() ?>"></script>