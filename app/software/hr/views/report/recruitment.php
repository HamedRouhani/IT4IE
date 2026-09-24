<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-report-header">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-user-plus"></i> گزارش جذب و استخدام
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                میانگین زمان جذب: <strong><?= hr_num($avgTimeToHire) ?> روز</strong>
            </p>
        </div>
        <div class="hr-report-actions">
            <button onclick="window.print()" class="btn-hr-outline">
                <i class="fas fa-print"></i> چاپ
            </button>
            <a href="<?= hr_url('report') ?>" class="btn-hr-outline">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>

    <div class="hr-main-grid">
        <div>
            <!-- نیازهای استخدامی -->
            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-bullhorn"></i> نیازهای استخدامی (<?= hr_num(count($recruitments)) ?>)</h3></div>
                <div class="card-body" style="padding: 0;">
                    <?php if (empty($recruitments)): ?>
                        <div class="hr-empty-state" style="padding: 2rem 1rem;">
                            <p class="hr-text-muted">نیازی ثبت نشده است.</p>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="hr-table">
                                <thead>
                                    <tr>
                                        <th>شماره</th>
                                        <th>عنوان</th>
                                        <th>دپارتمان</th>
                                        <th>ظرفیت</th>
                                        <th>متقاضی</th>
                                        <th>استخدام</th>
                                        <th>وضعیت</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recruitments as $r): ?>
                                        <tr>
                                            <td><code><?= hr_e($r['request_number']) ?></code></td>
                                            <td>
                                                <a href="<?= hr_url('recruitment', 'show', ['id' => $r['id']]) ?>">
                                                    <?= hr_e(hr_truncate($r['title'], 30)) ?>
                                                </a>
                                            </td>
                                            <td><?= hr_e($r['department_name'] ?? '—') ?></td>
                                            <td><?= hr_num($r['headcount']) ?></td>
                                            <td><strong><?= hr_num($r['candidates_count']) ?></strong></td>
                                            <td><strong style="color: var(--hr-success);"><?= hr_num($r['hired_count']) ?></strong></td>
                                            <td>
                                                <span class="hr-status-badge <?= hr_status_class($r['status']) ?>">
                                                    <?= hr_e($statusOptions[$r['status']] ?? '') ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- مصاحبه‌ها -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-comments"></i> مصاحبه‌های اخیر (<?= hr_num(count($interviews)) ?>)</h3></div>
                <div class="card-body" style="padding: 0;">
                    <?php if (empty($interviews)): ?>
                        <div class="hr-empty-state" style="padding: 2rem 1rem;">
                            <p class="hr-text-muted">مصاحبه‌ای ثبت نشده است.</p>
                        </div>
                    <?php else: ?>
                        <div style="overflow-x: auto;">
                            <table class="hr-table">
                                <thead>
                                    <tr>
                                        <th>متقاضی</th>
                                        <th>مصاحبه‌گر</th>
                                        <th>زمان</th>
                                        <th>وضعیت</th>
                                        <th>امتیاز</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($interviews as $i): ?>
                                        <tr>
                                            <td><?= hr_e(trim(($i['first_name'] ?? '') . ' ' . ($i['last_name'] ?? ''))) ?: '—' ?></td>
                                            <td><?= hr_e(trim(($i['interviewer_first'] ?? '') . ' ' . ($i['interviewer_last'] ?? ''))) ?: '—' ?></td>
                                            <td><small><?= hr_date($i['scheduled_date'], 'Y/m/d H:i') ?></small></td>
                                            <td>
                                                <span class="hr-status-badge <?= hr_status_class($i['status']) ?>">
                                                    <?= hr_e(\App\Software\Hr\Models\Interview::getStatusOptions()[$i['status']] ?? '') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($i['overall_score'])): ?>
                                                    <strong><?= hr_num($i['overall_score']) ?></strong>
                                                <?php else: ?>—<?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div>
            <!-- قیف متقاضیان -->
            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-filter"></i> قیف متقاضیان</h3></div>
                <div class="card-body">
                    <?php if (empty($candidateFunnel)): ?>
                        <p class="hr-text-muted">اطلاعاتی موجود نیست.</p>
                    <?php else: ?>
                        <?php
                        $funnelOrder = ['new', 'screening', 'interview', 'technical_test', 'offer', 'hired'];
                        $funnelMap = [];
                        foreach ($candidateFunnel as $cf) {
                            $funnelMap[$cf['status']] = (int) $cf['count'];
                        }
                        $maxFunnel = !empty($funnelMap) ? max($funnelMap) : 1;
                        foreach ($funnelOrder as $st):
                            if (!isset($funnelMap[$st])) continue;
                            $count = $funnelMap[$st];
                        ?>
                            <div class="hr-mb-3">
                                <div class="hr-flex-between hr-mb-1">
                                    <span><?= hr_e($candidateStatus[$st] ?? $st) ?></span>
                                    <strong><?= hr_num($count) ?></strong>
                                </div>
                                <div style="background: #e5e7eb; border-radius: 4px; height: 10px; overflow: hidden;">
                                    <div style="width: <?= ($count / $maxFunnel) * 100 ?>%; background: var(--hr-primary); height: 100%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php
                        // نمایش وضعیت‌های rejected/withdrawn
                        foreach (['rejected', 'withdrawn'] as $st):
                            if (!isset($funnelMap[$st])) continue;
                        ?>
                            <div class="hr-flex-between hr-mb-2">
                                <span class="hr-status-badge <?= hr_status_class($st) ?>"><?= hr_e($candidateStatus[$st] ?? $st) ?></span>
                                <strong><?= hr_num($funnelMap[$st]) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- منابع جذب -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-bullseye"></i> منابع جذب</h3></div>
                <div class="card-body">
                    <?php if (empty($sources)): ?>
                        <p class="hr-text-muted">اطلاعاتی موجود نیست.</p>
                    <?php else: ?>
                        <?php foreach ($sources as $s): ?>
                            <div class="hr-flex-between hr-mb-2">
                                <span><?= hr_e($sourceOptions[$s['source']] ?? $s['source']) ?></span>
                                <strong><?= hr_num($s['count']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>