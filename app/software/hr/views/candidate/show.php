<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-user"></i> <?= hr_e(trim($candidate['first_name'] . ' ' . $candidate['last_name'])) ?>
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                <?php if (!empty($candidate['candidate_code'])): ?>
                    کد: <code><?= hr_e($candidate['candidate_code']) ?></code> —
                <?php endif; ?>
                <span class="hr-status-badge <?= \App\Software\Hr\Models\Candidate::getStatusClass($candidate['status']) ?>">
                    <?= hr_e(\App\Software\Hr\Models\Candidate::getStatusOptions()[$candidate['status']] ?? $candidate['status']) ?>
                </span>
            </p>
        </div>
        <div class="hr-flex hr-gap-2">
            <a href="<?= hr_url('interview', 'create', ['candidate_id' => $candidate['id']]) ?>"
               class="btn-hr-primary">
                <i class="fas fa-comments"></i> برنامه‌ریزی مصاحبه
            </a>
            <a href="<?= hr_url('candidate', 'edit', ['id' => $candidate['id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <a href="<?= hr_url('candidate') ?>" class="btn-hr-outline">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="hr-main-grid">

        <div>
            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-id-card"></i> اطلاعات شخصی</h3></div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <tr><th style="width: 180px;">کد متقاضی</th><td><code><?= hr_e($candidate['candidate_code'] ?? '—') ?></code></td></tr>
                            <tr><th>کد ملی</th><td><?= hr_e($candidate['national_id'] ?? '—') ?></td></tr>
                            <tr><th>جنسیت</th><td><?= hr_gender_label($candidate['gender']) ?></td></tr>
                            <tr><th>تاریخ تولد</th><td><?= hr_date($candidate['birth_date'], 'Y/m/d') ?></td></tr>
                            <tr><th>موبایل</th><td><?= hr_e($candidate['mobile'] ?? '—') ?></td></tr>
                            <tr><th>ایمیل</th><td><?= hr_e($candidate['email'] ?? '—') ?></td></tr>
                            <tr><th>آدرس</th><td><?= hr_e($candidate['address'] ?? '—') ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-graduation-cap"></i> تحصیلات و سابقه</h3></div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <tr><th style="width: 180px;">مقطع</th><td><?= hr_e($candidate['education_level'] ? hr_education_level_label($candidate['education_level']) : '—') ?></td></tr>
                            <tr><th>رشته</th><td><?= hr_e($candidate['field_of_study'] ?? '—') ?></td></tr>
                            <tr><th>دانشگاه</th><td><?= hr_e($candidate['university'] ?? '—') ?></td></tr>
                            <tr><th>سابقه</th><td><?= hr_num($candidate['experience_years'] ?? 0) ?> سال</td></tr>
                            <tr><th>شرکت فعلی</th><td><?= hr_e($candidate['current_company'] ?? '—') ?></td></tr>
                            <tr><th>سمت فعلی</th><td><?= hr_e($candidate['current_position'] ?? '—') ?></td></tr>
                            <tr><th>حقوق مورد انتظار</th><td><?= $candidate['expected_salary'] ? hr_money($candidate['expected_salary']) : '—' ?></td></tr>
                            <?php if (!empty($candidate['portfolio_url'])): ?>
                                <tr><th>نمونه‌کار</th><td><a href="<?= hr_e($candidate['portfolio_url']) ?>" target="_blank">مشاهده</a></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (!empty($candidate['notes'])): ?>
                <div class="card">
                    <div class="card-header"><h3 class="card-title"><i class="fas fa-comment"></i> یادداشت</h3></div>
                    <div class="card-body"><?= nl2br(hr_e($candidate['notes'])) ?></div>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> خلاصه</h3></div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <tr><th>آگهی</th><td>
                                <?php if (!empty($candidate['recruitment_id'])): ?>
                                    <a href="<?= hr_url('recruitment', 'show', ['id' => $candidate['recruitment_id']]) ?>">
                                        <?= hr_e($candidate['request_number'] ?? '') ?>
                                    </a>
                                <?php else: ?>—<?php endif; ?>
                            </td></tr>
                            <tr><th>منبع</th><td><?= hr_e(\App\Software\Hr\Models\Candidate::getSourceOptions()[$candidate['source']] ?? '—') ?></td></tr>
                            <tr><th>تاریخ درخواست</th><td><?= hr_date($candidate['applied_date'], 'Y/m/d') ?></td></tr>
                            <tr><th>امتیاز</th><td>
                                <?php if (!empty($candidate['rating'])): ?>
                                    <strong><?= hr_num($candidate['rating']) ?></strong> / ۵
                                <?php else: ?>—<?php endif; ?>
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-comments"></i> مصاحبه‌ها</h3>
                    <a href="<?= hr_url('interview', 'create', ['candidate_id' => $candidate['id']]) ?>"
                       class="btn-hr-primary btn-sm">
                        <i class="fas fa-plus"></i> جدید
                    </a>
                </div>
                <?php if (empty($interviews)): ?>
                    <div class="hr-empty-state" style="padding: 2rem 1rem;">
                        <i class="fas fa-comments"></i>
                        <p>مصاحبه‌ای ثبت نشده است.</p>
                    </div>
                <?php else: ?>
                    <ul style="list-style: none; padding: 1rem;">
                        <?php foreach ($interviews as $i): ?>
                            <li style="padding: 0.75rem 0; border-bottom: 1px solid #e5e7eb;">
                                <div class="hr-flex-between">
                                    <div>
                                        <span class="hr-status-badge hr-status-info" style="font-size: 0.7rem;">
                                            دور <?= hr_num($i['round']) ?>
                                        </span>
                                        <strong><?= hr_e(\App\Software\Hr\Models\Interview::getTypeOptions()[$i['interview_type']] ?? '') ?></strong>
                                        <br><small class="hr-text-muted">
                                            <?= hr_date($i['scheduled_date'], 'Y/m/d H:i') ?>
                                        </small>
                                        <?php if ($i['overall_score']): ?>
                                            <br><small>امتیاز: <strong><?= hr_num($i['overall_score']) ?></strong></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="hr-flex hr-gap-2">
                                        <a href="<?= hr_url('interview', 'show', ['id' => $i['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="مشاهده">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= hr_url('interview', 'edit', ['id' => $i['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>