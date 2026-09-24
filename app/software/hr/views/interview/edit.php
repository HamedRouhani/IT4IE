<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-edit"></i> ویرایش مصاحبه
            </h2>
        </div>
        <a href="<?= hr_url('interview', 'show', ['id' => $interview['id']]) ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <?php
    // ─── استخراج تاریخ و ساعت از scheduled_date ───
    $scheduledDateJalali = !empty($interview['scheduled_date'])
        ? hr_date($interview['scheduled_date'], 'Y/m/d')
        : hr_today();

    $scheduledTime = !empty($interview['scheduled_date'])
        ? date('H:i', strtotime($interview['scheduled_date']))
        : '09:00';

    list($currentHour, $currentMinute) = explode(':', $scheduledTime);
    ?>

    <form method="POST" action="<?= hr_url('interview', 'update', ['id' => $interview['id']]) ?>">
        <?= $this->csrfField() ?>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات مصاحبه</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="candidate_id">متقاضی</label>
                        <select id="candidate_id" name="candidate_id" class="hr-form-control" required>
                            <?php foreach ($hr_candidates as $c): ?>
                                <option value="<?= (int) $c['id'] ?>"
                                    <?= (int) $interview['candidate_id'] === (int) $c['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($c['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="recruitment_id">آگهی</label>
                        <select id="recruitment_id" name="recruitment_id" class="hr-form-control">
                            <option value="">— انتخاب —</option>
                            <?php foreach ($hr_recruitments as $r): ?>
                                <option value="<?= (int) $r['id'] ?>"
                                    <?= (int) $interview['recruitment_id'] === (int) $r['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($r['request_number']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="interview_type">نوع</label>
                        <select id="interview_type" name="interview_type" class="hr-form-control">
                            <?php foreach ($typeOptions as $key => $label): ?>
                                <option value="<?= hr_e($key) ?>"
                                    <?= $interview['interview_type'] === $key ? 'selected' : '' ?>>
                                    <?= hr_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="round">دور</label>
                        <input type="number" id="round" name="round" class="hr-form-control"
                               value="<?= (int) $interview['round'] ?>" min="1" max="10">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="interviewer_id">مصاحبه‌گر</label>
                        <select id="interviewer_id" name="interviewer_id" class="hr-form-control">
                            <option value="">— انتخاب —</option>
                            <?php foreach ($hr_interviewers as $e): ?>
                                <option value="<?= (int) $e['id'] ?>"
                                    <?= (int) $interview['interviewer_id'] === (int) $e['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($e['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="scheduled_date">
                            تاریخ مصاحبه (شمسی) <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <input type="text" id="scheduled_date" name="scheduled_date"
                               class="hr-form-control hr-datepicker"
                               value="<?= hr_e($scheduledDateJalali) ?>"
                               placeholder="1404/07/15"
                               required maxlength="10" autocomplete="off">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">
                            ساعت مصاحبه <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <div style="display: flex; gap: 0.5rem;">
                            <select id="scheduled_hour" name="scheduled_hour"
                                    class="hr-form-control" required style="flex: 1;">
                                <?php for ($h = 0; $h <= 23; $h++): ?>
                                    <?php $hh = str_pad((string) $h, 2, '0', STR_PAD_LEFT); ?>
                                    <option value="<?= $hh ?>" <?= $hh === $currentHour ? 'selected' : '' ?>>
                                        <?= $hh ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <span style="align-self: center;">:</span>
                            <select id="scheduled_minute" name="scheduled_minute"
                                    class="hr-form-control" required style="flex: 1;">
                                <?php for ($m = 0; $m <= 59; $m += 5): ?>
                                    <?php $mm = str_pad((string) $m, 2, '0', STR_PAD_LEFT); ?>
                                    <option value="<?= $mm ?>" <?= $mm === $currentMinute ? 'selected' : '' ?>>
                                        <?= $mm ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <small class="hr-text-muted">ساعت و دقیقه (هر ۵ دقیقه)</small>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="duration_minutes">مدت (دقیقه)</label>
                        <input type="number" id="duration_minutes" name="duration_minutes"
                               class="hr-form-control"
                               value="<?= (int) $interview['duration_minutes'] ?>" min="15" max="480">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="status">وضعیت</label>
                        <select id="status" name="status" class="hr-form-control">
                            <?php foreach ($statusOptions as $key => $label): ?>
                                <option value="<?= hr_e($key) ?>"
                                    <?= $interview['status'] === $key ? 'selected' : '' ?>>
                                    <?= hr_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="location">مکان</label>
                        <input type="text" id="location" name="location" class="hr-form-control"
                               value="<?= hr_e($interview['location'] ?? '') ?>" maxlength="255">
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="meeting_link">لینک جلسه</label>
                        <input type="url" id="meeting_link" name="meeting_link" class="hr-form-control"
                               value="<?= hr_e($interview['meeting_link'] ?? '') ?>" maxlength="500">
                    </div>
                </div>
            </div>
        </div>

        <!-- ارزیابی -->
        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-star"></i> ارزیابی</h3></div>
            <div class="card-body">

                <?php
                $scoreFields = [
                    'technical_score'     => 'امتیاز فنی',
                    'communication_score' => 'امتیاز ارتباطی',
                    'culture_fit_score'   => 'تناسب فرهنگی',
                    'overall_score'       => 'امتیاز کلی',
                ];
                ?>

                <div class="hr-form-row">
                    <?php foreach ($scoreFields as $field => $label): ?>
                        <?php $currentValue = $interview[$field] ?? ''; ?>
                        <div class="hr-form-group">
                            <label class="hr-form-label">
                                <?= hr_e($label) ?>
                                <span class="hr-text-muted" style="font-weight: normal; font-size: 0.8rem;">(۰ تا ۵)</span>
                            </label>

                            <div class="hr-rating-wrapper">
                                <div class="hr-rating-stars"
                                    data-input="<?= $field ?>"
                                    data-max="5"
                                    data-step="0.5">
                                    <!-- ستاره‌ها با JS ساخته می‌شوند -->
                                </div>
                                <span class="hr-rating-value">—</span>
                                <a href="#" class="hr-rating-clear">پاک</a>
                            </div>

                            <input type="hidden"
                                id="<?= $field ?>"
                                name="<?= $field ?>"
                                value="<?= hr_e($currentValue) ?>">
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="strengths">نقاط قوت</label>
                        <textarea id="strengths" name="strengths" class="hr-form-control" rows="2" maxlength="2000"><?= hr_e($interview['strengths'] ?? '') ?></textarea>
                    </div>
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="weaknesses">نقاط ضعف</label>
                        <textarea id="weaknesses" name="weaknesses" class="hr-form-control" rows="2" maxlength="2000"><?= hr_e($interview['weaknesses'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="recommendation">توصیه</label>
                        <select id="recommendation" name="recommendation" class="hr-form-control">
                            <option value="">— انتخاب —</option>
                            <?php foreach ($recommendationOptions as $key => $label): ?>
                                <option value="<?= hr_e($key) ?>"
                                    <?= $interview['recommendation'] === $key ? 'selected' : '' ?>>
                                    <?= hr_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="notes">یادداشت</label>
                    <textarea id="notes" name="notes" class="hr-form-control" rows="3" maxlength="2000"><?= hr_e($interview['notes'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary">
                <i class="fas fa-save"></i> ذخیره تغییرات
            </button>
            <a href="<?= hr_url('interview', 'show', ['id' => $interview['id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/hr.js?v=<?= time() ?>"></script>