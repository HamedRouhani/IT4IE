<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-comments"></i> مصاحبه‌ها
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                مجموع: <strong><?= hr_num($stats['total']) ?></strong>
                — برنامه‌ریزی شده: <strong><?= hr_num($stats['scheduled']) ?></strong>
                — امروز: <strong style="color: var(--hr-danger);"><?= hr_num(count($todayInterviews)) ?></strong>
            </p>
        </div>
        <a href="<?= hr_url('interview', 'create') ?>" class="btn-hr-primary">
            <i class="fas fa-plus"></i> برنامه‌ریزی مصاحبه
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- هشدار مصاحبه‌های امروز -->
    <?php if (!empty($todayInterviews)): ?>
        <div class="hr-alert info hr-mb-3">
            <i class="fas fa-calendar-day"></i>
            <div style="flex: 1;">
                <strong><?= hr_num(count($todayInterviews)) ?> مصاحبه امروز</strong>
                <ul style="margin: 0.5rem 0 0 0; padding-right: 1.25rem;">
                    <?php foreach (array_slice($todayInterviews, 0, 3) as $i): ?>
                        <li>
                            <?= hr_e($i['candidate_name'] ?? '') ?>
                            — <?= hr_date($i['scheduled_date'], 'H:i') ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <!-- فیلتر -->
    <div class="card hr-mb-3">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-filter"></i> فیلتر</h3></div>
        <div class="card-body">
            <form method="GET" action="<?= hr_url('interview') ?>">
                <input type="hidden" name="controller" value="interview">
                <input type="hidden" name="action" value="index">

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label">آگهی</label>
                        <select name="recruitment_id" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($hr_recruitments as $r): ?>
                                <option value="<?= (int) $r['id'] ?>"
                                    <?= (int) $filters['recruitment_id'] === (int) $r['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($r['request_number']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">نوع</label>
                        <select name="interview_type" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($typeOptions as $key => $label): ?>
                                <option value="<?= hr_e($key) ?>" <?= $filters['interview_type'] === $key ? 'selected' : '' ?>>
                                    <?= hr_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">وضعیت</label>
                        <select name="status" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($statusOptions as $key => $label): ?>
                                <option value="<?= hr_e($key) ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>>
                                    <?= hr_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-flex hr-gap-2">
                    <button type="submit" class="btn-hr-primary"><i class="fas fa-search"></i> اعمال</button>
                    <a href="<?= hr_url('interview') ?>" class="btn-hr-outline"><i class="fas fa-redo"></i> پاک کردن</a>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> لیست مصاحبه‌ها</h3>
        </div>

        <?php if (empty($interviews)): ?>
            <div class="hr-empty-state">
                <i class="fas fa-comments"></i>
                <h4>هیچ مصاحبه‌ای ثبت نشده است</h4>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="hr-table">
                    <thead>
                        <tr>
                            <th>متقاضی</th>
                            <th>آگهی</th>
                            <th>نوع</th>
                            <th>دور</th>
                            <th>مصاحبه‌گر</th>
                            <th>زمان</th>
                            <th>وضعیت</th>
                            <th>امتیاز</th>
                            <th style="width: 120px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($interviews as $i): ?>
                            <tr>
                                <td>
                                    <a href="<?= hr_url('candidate', 'show', ['id' => $i['candidate_id']]) ?>"
                                       style="color: var(--hr-primary-dark); font-weight: 600;">
                                        <?= hr_e($i['candidate_name'] ?? '—') ?>
                                    </a>
                                </td>
                                <td><small><?= hr_e($i['request_number'] ?? '—') ?></small></td>
                                <td><?= hr_e(\App\Software\Hr\Models\Interview::getTypeOptions()[$i['interview_type']] ?? '') ?></td>
                                <td><?= hr_num($i['round']) ?></td>
                                <td><?= hr_e($i['interviewer_name'] ?? '—') ?></td>
                                <td><?= hr_date($i['scheduled_date'], 'Y/m/d H:i') ?></td>
                                <td>
                                    <span class="hr-status-badge <?= hr_status_class($i['status']) ?>">
                                        <?= hr_e(\App\Software\Hr\Models\Interview::getStatusOptions()[$i['status']] ?? $i['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($i['overall_score'])): ?>
                                        <strong><?= hr_num($i['overall_score']) ?></strong>
                                    <?php else: ?>—<?php endif; ?>
                                </td>
                                <td>
                                    <div class="hr-flex hr-gap-2" style="justify-content: center;">
                                        <a href="<?= hr_url('interview', 'show', ['id' => $i['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="مشاهده">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= hr_url('interview', 'edit', ['id' => $i['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= hr_url('interview', 'delete', ['id' => $i['id']]) ?>"
                                           class="btn-hr-outline btn-sm hr-confirm-delete"
                                           data-message="حذف این مصاحبه؟"
                                           style="color: var(--hr-danger); border-color: var(--hr-danger);">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>