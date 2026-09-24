<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-plus-circle"></i> ایجاد ارزیابی عملکرد
            </h2>
        </div>
        <a href="<?= hr_url('performance') ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('performance', 'store') ?>">
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
                            <option value="">— انتخاب —</option>
                            <?php foreach ($hr_employees as $e): ?>
                                <option value="<?= (int) $e['id'] ?>"
                                    <?= (int) $preselectedEmployeeId === (int) $e['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($e['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="reviewer_id">ارزیاب</label>
                        <select id="reviewer_id" name="reviewer_id" class="hr-form-control">
                            <option value="">— انتخاب —</option>
                            <?php foreach ($hr_reviewers as $e): ?>
                                <option value="<?= (int) $e['id'] ?>"><?= hr_e($e['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="review_type">نوع ارزیابی</label>
                        <select id="review_type" name="review_type" class="hr-form-control">
                            <?php foreach ($typeOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>" <?= $k === 'annual' ? 'selected' : '' ?>>
                                    <?= hr_e($v) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="review_period">
                            دوره <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <input type="text" id="review_period" name="review_period"
                               class="hr-form-control" placeholder="مثال: 1403-سالانه"
                               required maxlength="50">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="period_start">
                            شروع دوره <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <input type="text" id="period_start" name="period_start"
                               class="hr-form-control hr-datepicker"
                               value="<?= hr_e(hr_today()) ?>"
                               required maxlength="10" autocomplete="off">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="period_end">
                            پایان دوره <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <input type="text" id="period_end" name="period_end"
                               class="hr-form-control hr-datepicker"
                               required maxlength="10" autocomplete="off">
                    </div>
                </div>
            </div>
        </div>

        <!-- امتیازها -->
        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-star"></i> امتیازها (۰ تا ۵)</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <?php foreach ($scoreFields as $field => $label): ?>
                        <div class="hr-form-group">
                            <label class="hr-form-label"><?= hr_e($label) ?></label>
                            <div class="hr-rating-wrapper">
                                <div class="hr-rating-stars" data-input="<?= $field ?>" data-max="5" data-step="0.5"></div>
                                <span class="hr-rating-value">—</span>
                                <a href="#" class="hr-rating-clear">پاک</a>
                            </div>
                            <input type="hidden" id="<?= $field ?>" name="<?= $field ?>" value="">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-comment"></i> توضیحات و توصیه</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="strengths">نقاط قوت</label>
                        <textarea id="strengths" name="strengths" class="hr-form-control" rows="2" maxlength="2000"></textarea>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="weaknesses">نقاط ضعف</label>
                        <textarea id="weaknesses" name="weaknesses" class="hr-form-control" rows="2" maxlength="2000"></textarea>
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="goals_achievement">دستاورد اهداف</label>
                        <textarea id="goals_achievement" name="goals_achievement" class="hr-form-control" rows="2" maxlength="2000"></textarea>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="training_needs">نیازهای آموزشی</label>
                        <textarea id="training_needs" name="training_needs" class="hr-form-control" rows="2" maxlength="2000"></textarea>
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="manager_comments">نظر مدیر</label>
                        <textarea id="manager_comments" name="manager_comments" class="hr-form-control" rows="2" maxlength="2000"></textarea>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="employee_comments">نظر کارمند</label>
                        <textarea id="employee_comments" name="employee_comments" class="hr-form-control" rows="2" maxlength="2000"></textarea>
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="recommendation">توصیه</label>
                        <select id="recommendation" name="recommendation" class="hr-form-control">
                            <option value="">— انتخاب —</option>
                            <?php foreach ($recommendationOptions as $k => $v): ?>
                                <option value="<?= hr_e($k) ?>"><?= hr_e($v) ?></option>
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
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary"><i class="fas fa-save"></i> ذخیره</button>
            <a href="<?= hr_url('performance') ?>" class="btn-hr-outline"><i class="fas fa-times"></i> انصراف</a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/hr.js?v=<?= time() ?>"></script>