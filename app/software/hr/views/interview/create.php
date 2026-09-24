<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-comments"></i> برنامه‌ریزی مصاحبه
            </h2>
        </div>
        <a href="<?= hr_url('interview') ?>" class="btn-hr-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= hr_url('interview', 'store') ?>">
        <?= $this->csrfField() ?>

        <div class="card hr-mb-3">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات مصاحبه</h3></div>
            <div class="card-body">
                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="candidate_id">
                            متقاضی <span style="color: var(--hr-danger);">*</span>
                        </label>
                        <select id="candidate_id" name="candidate_id" class="hr-form-control" required>
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($hr_candidates as $c): ?>
                                <option value="<?= (int) $c['id'] ?>"
                                    <?= (int) $preselectedCandidateId === (int) $c['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($c['full_name']) ?>
                                    <?php if (!empty($c['candidate_code'])): ?>
                                        (<?= hr_e($c['candidate_code']) ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="recruitment_id">آگهی مرتبط</label>
                        <select id="recruitment_id" name="recruitment_id" class="hr-form-control">
                            <option value="">— انتخاب —</option>
                            <?php foreach ($hr_recruitments as $r): ?>
                                <option value="<?= (int) $r['id'] ?>">
                                    <?= hr_e($r['request_number']) ?> — <?= hr_e(hr_truncate($r['title'], 40)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="interview_type">نوع مصاحبه</label>
                        <select id="interview_type" name="interview_type" class="hr-form-control">
                            <?php foreach ($typeOptions as $key => $label): ?>
                                <option value="<?= hr_e($key) ?>"><?= hr_e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="round">دور</label>
                        <input type="number" id="round" name="round" class="hr-form-control" value="1" min="1" max="10">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="interviewer_id">مصاحبه‌گر</label>
                        <select id="interviewer_id" name="interviewer_id" class="hr-form-control">
                            <option value="">— انتخاب —</option>
                            <?php foreach ($hr_interviewers as $e): ?>
                                <option value="<?= (int) $e['id'] ?>">
                                    <?= hr_e($e['full_name']) ?> (<?= hr_e($e['employee_code']) ?>)
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
                            value="<?= hr_e(hr_today()) ?>"
                            placeholder="1404/07/15"
                            required maxlength="10" autocomplete="off">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">ساعت مصاحبه <span style="color: var(--hr-danger);">*</span></label>
                        <div style="display: flex; gap: 0.5rem;">
                            <select id="scheduled_hour" name="scheduled_hour" class="hr-form-control" required style="flex: 1;">
                                <?php for ($h = 0; $h <= 23; $h++): ?>
                                    <option value="<?= str_pad((string)$h, 2, '0', STR_PAD_LEFT) ?>"
                                        <?= $h === 9 ? 'selected' : '' ?>>
                                        <?= str_pad((string)$h, 2, '0', STR_PAD_LEFT) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <span style="align-self: center;">:</span>
                            <select id="scheduled_minute" name="scheduled_minute" class="hr-form-control" required style="flex: 1;">
                                <?php for ($m = 0; $m <= 59; $m += 5): ?>
                                    <option value="<?= str_pad((string)$m, 2, '0', STR_PAD_LEFT) ?>"
                                        <?= $m === 0 ? 'selected' : '' ?>>
                                        <?= str_pad((string)$m, 2, '0', STR_PAD_LEFT) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <small class="hr-text-muted">ساعت و دقیقه (هر ۵ دقیقه)</small>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="duration_minutes">مدت (دقیقه)</label>
                        <input type="number" id="duration_minutes" name="duration_minutes"
                            class="hr-form-control" value="60" min="15" max="480">
                    </div>
                </div>

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label" for="location">مکان</label>
                        <input type="text" id="location" name="location"
                               class="hr-form-control" maxlength="255"
                               placeholder="مثال: اتاق جلسات ۱">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label" for="meeting_link">لینک جلسه</label>
                        <input type="url" id="meeting_link" name="meeting_link"
                               class="hr-form-control" maxlength="500"
                               placeholder="https://meet.example.com/...">
                    </div>
                </div>

                <div class="hr-form-group">
                    <label class="hr-form-label" for="notes">یادداشت</label>
                    <textarea id="notes" name="notes" class="hr-form-control" rows="3" maxlength="2000"></textarea>
                </div>
            </div>
        </div>

        <div class="hr-flex hr-gap-2">
            <button type="submit" class="btn-hr-primary">
                <i class="fas fa-save"></i> ذخیره
            </button>
            <a href="<?= hr_url('interview') ?>" class="btn-hr-outline">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>

</div>

<script src="/public/assets/js/software/hr-datepicker.js?v=<?= time() ?>"></script>
<script src="/public/assets/js/software/hr.js?v=<?= time() ?>"></script>