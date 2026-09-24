<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-edit"></i> ویرایش پست
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                ویرایش: <strong><?= hr_e($position['title']) ?></strong>
            </p>
        </div>
        <a href="<?= hr_url('position', 'show', ['id' => $position['id']]) ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('position', 'update', ['id' => $position['id']]) ?>">
        <?= $this->csrfField() ?>

        <div class="card hr-mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات پست</h3>
            </div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="title">عنوان پست <span style="color: var(--hr-danger);">*</span></label>
                        <input type="text" id="title" name="title"
                               class="hr-form-control"
                               value="<?= hr_e($position['title']) ?>"
                               required maxlength="200">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="code">کد پست</label>
                        <input type="text" id="code" name="code"
                               class="hr-form-control"
                               value="<?= hr_e($position['code'] ?? '') ?>"
                               maxlength="50">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="department_id">دپارتمان</label>
                        <select id="department_id" name="department_id" class="hr-form-control">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($hr_departments as $d): ?>
                                <option value="<?= (int) $d['id'] ?>"
                                    <?= (int) ($position['department_id'] ?? 0) === (int) $d['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($d['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="grade_id">طبقه شغلی</label>
                        <select id="grade_id" name="grade_id" class="hr-form-control">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($hr_grades as $g): ?>
                                <option value="<?= (int) $g['id'] ?>"
                                    <?= (int) ($position['grade_id'] ?? 0) === (int) $g['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($g['code']) ?> — <?= hr_e($g['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="parent_position_id">پست بالادستی</label>
                    <select id="parent_position_id" name="parent_position_id" class="hr-form-control">
                        <option value="">— بدون والد —</option>
                        <?php foreach ($hr_parents as $p): ?>
                            <option value="<?= (int) $p['id'] ?>"
                                <?= (int) ($position['parent_position_id'] ?? 0) === (int) $p['id'] ? 'selected' : '' ?>>
                                <?= hr_e($p['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="position_type">نوع پست</label>
                        <select id="position_type" name="position_type" class="hr-form-control">
                            <?php
                            $ptypes = ['permanent' => 'دائمی', 'contract' => 'قراردادی', 'temporary' => 'موقت', 'intern' => 'کارآموز', 'consultant' => 'مشاور'];
                            foreach ($ptypes as $key => $label):
                            ?>
                                <option value="<?= $key ?>" <?= $position['position_type'] === $key ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="employment_type">نوع استخدام</label>
                        <select id="employment_type" name="employment_type" class="hr-form-control">
                            <?php
                            $etypes = ['full_time' => 'تمام‌وقت', 'part_time' => 'پاره‌وقت', 'remote' => 'دورکاری', 'hybrid' => 'ترکیبی'];
                            foreach ($etypes as $key => $label):
                            ?>
                                <option value="<?= $key ?>" <?= $position['employment_type'] === $key ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="headcount">ظرفیت</label>
                        <input type="number" id="headcount" name="headcount"
                               class="hr-form-control"
                               value="<?= (int) $position['headcount'] ?>"
                               min="1" max="999">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="min_education">حداقل تحصیلات</label>
                        <input type="text" id="min_education" name="min_education"
                               class="hr-form-control"
                               value="<?= hr_e($position['min_education'] ?? '') ?>"
                               maxlength="100">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="min_experience_years">حداقل سابقه (سال)</label>
                        <input type="number" id="min_experience_years" name="min_experience_years"
                               class="hr-form-control"
                               value="<?= (int) $position['min_experience_years'] ?>"
                               min="0" max="50">
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label">ویژگی‌ها</label>
                    <div class="hr-flex hr-gap-3" style="flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="is_managerial" value="1"
                                <?= $position['is_managerial'] ? 'checked' : '' ?>>
                            <span>پست مدیریتی</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="is_critical" value="1"
                                <?= $position['is_critical'] ? 'checked' : '' ?>>
                            <span>پست کلیدی</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="is_active" value="1"
                                <?= $position['is_active'] ? 'checked' : '' ?>>
                            <span>فعال</span>
                        </label>
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="description">توضیحات</label>
                    <textarea id="description" name="description"
                              class="hr-form-control" rows="3"
                              maxlength="2000"><?= hr_e($position['description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary">
                <i class="fas fa-save"></i> ذخیره تغییرات
            </button>
            <a href="<?= hr_url('position') ?>" class="btn-hr-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr.js"></script>