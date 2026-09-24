<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-users"></i> متقاضیان استخدام
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                مجموع: <strong><?= hr_num($stats['total']) ?></strong>
                — جدید: <strong><?= hr_num($stats['new']) ?></strong>
                — استخدام‌شده: <strong style="color: var(--hr-success);"><?= hr_num($stats['hired']) ?></strong>
            </p>
        </div>
        <a href="<?= hr_url('candidate', 'create') ?>" class="btn-hr-primary">
            <i class="fas fa-plus"></i> افزودن متقاضی
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- فیلتر -->
    <div class="card hr-mb-3">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-filter"></i> فیلتر</h3></div>
        <div class="card-body">
            <form method="GET" action="<?= hr_url('candidate') ?>">
                <input type="hidden" name="controller" value="candidate">
                <input type="hidden" name="action" value="index">

                <div class="hr-form-row">
                    <div class="hr-form-group">
                        <label class="hr-form-label">جستجو</label>
                        <input type="text" name="q" class="hr-form-control"
                               value="<?= hr_e($filters['q']) ?>"
                               placeholder="نام، موبایل، ایمیل...">
                    </div>

                    <div class="hr-form-group">
                        <label class="hr-form-label">آگهی</label>
                        <select name="recruitment_id" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($hr_recruitments as $r): ?>
                                <option value="<?= (int) $r['id'] ?>"
                                    <?= (int) $filters['recruitment_id'] === (int) $r['id'] ? 'selected' : '' ?>>
                                    <?= hr_e($r['request_number']) ?> — <?= hr_e(hr_truncate($r['title'], 40)) ?>
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

                    <div class="hr-form-group">
                        <label class="hr-form-label">منبع</label>
                        <select name="source" class="hr-form-control">
                            <option value="">— همه —</option>
                            <?php foreach ($sourceOptions as $key => $label): ?>
                                <option value="<?= hr_e($key) ?>" <?= $filters['source'] === $key ? 'selected' : '' ?>>
                                    <?= hr_e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="hr-flex hr-gap-2">
                    <button type="submit" class="btn-hr-primary">
                        <i class="fas fa-search"></i> اعمال
                    </button>
                    <a href="<?= hr_url('candidate') ?>" class="btn-hr-outline">
                        <i class="fas fa-redo"></i> پاک کردن
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                لیست متقاضیان
                <span class="hr-text-muted" style="font-weight: normal; font-size: 0.85rem;">
                    (<?= hr_num(count($candidates)) ?> مورد)
                </span>
            </h3>
        </div>

        <?php if (empty($candidates)): ?>
            <div class="hr-empty-state">
                <i class="fas fa-users"></i>
                <h4>هیچ متقاضی یافت نشد</h4>
                <p>اولین متقاضی را اضافه کنید.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="hr-table">
                    <thead>
                        <tr>
                            <th>کد</th>
                            <th>نام</th>
                            <th>آگهی</th>
                            <th>موبایل</th>
                            <th>تحصیلات</th>
                            <th>سابقه</th>
                            <th>وضعیت</th>
                            <th>امتیاز</th>
                            <th style="width: 150px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($candidates as $c): ?>
                            <tr>
                                <td><code><?= hr_e($c['candidate_code'] ?? '—') ?></code></td>
                                <td>
                                    <a href="<?= hr_url('candidate', 'show', ['id' => $c['id']]) ?>"
                                       style="color: var(--hr-primary-dark); font-weight: 600;">
                                        <?= hr_e($c['full_name'] ?? trim($c['first_name'] . ' ' . $c['last_name'])) ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (!empty($c['request_number'])): ?>
                                        <small>
                                            <code><?= hr_e($c['request_number']) ?></code>
                                            <br><?= hr_e(hr_truncate($c['recruitment_title'] ?? '', 30)) ?>
                                        </small>
                                    <?php else: ?>
                                        <span class="hr-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= hr_e($c['mobile'] ?? '—') ?></td>
                                <td>
                                    <?php if (!empty($c['education_level'])): ?>
                                        <?= hr_e(hr_education_level_label($c['education_level'])) ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td><?= hr_num($c['experience_years'] ?? 0) ?> سال</td>
                                <td>
                                    <span class="hr-status-badge <?= \App\Software\Hr\Models\Candidate::getStatusClass($c['status']) ?>">
                                        <?= hr_e(\App\Software\Hr\Models\Candidate::getStatusOptions()[$c['status']] ?? $c['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($c['rating'])): ?>
                                        <strong><?= hr_num($c['rating']) ?></strong>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="hr-flex hr-gap-2" style="justify-content: center;">
                                        <a href="<?= hr_url('candidate', 'show', ['id' => $c['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="مشاهده">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= hr_url('candidate', 'edit', ['id' => $c['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= hr_url('candidate', 'delete', ['id' => $c['id']]) ?>"
                                           class="btn-hr-outline btn-sm hr-confirm-delete"
                                           data-message="حذف متقاضی '<?= hr_e($c['full_name'] ?? '') ?>'؟"
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