<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-report-header">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-chart-pie"></i> خلاصه سازمانی
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                نمای کلی منابع انسانی — <?= hr_e(hr_today('Y/m/d')) ?>
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

    <!-- کارت‌های آماری -->
    <div class="hr-stats-grid hr-mb-4">
        <div class="hr-stat-card">
            <small><i class="fas fa-users"></i> کل کارکنان</small>
            <h3><?= hr_num($overview['total_employees']) ?></h3>
        </div>
        <div class="hr-stat-card success">
            <small><i class="fas fa-user-check"></i> شاغل</small>
            <h3><?= hr_num($overview['active_employees']) ?></h3>
        </div>
        <div class="hr-stat-card">
            <small><i class="fas fa-building"></i> دپارتمان</small>
            <h3><?= hr_num($overview['total_departments']) ?></h3>
        </div>
        <div class="hr-stat-card warning">
            <small><i class="fas fa-briefcase"></i> پست</small>
            <h3><?= hr_num($overview['total_positions']) ?></h3>
        </div>
    </div>

    <div class="hr-stats-grid hr-mb-4">
        <div class="hr-stat-card">
            <small><i class="fas fa-user-plus"></i> متقاضیان</small>
            <h3><?= hr_num($overview['total_candidates']) ?></h3>
        </div>
        <div class="hr-stat-card success">
            <small><i class="fas fa-graduation-cap"></i> دوره‌های آموزشی</small>
            <h3><?= hr_num($overview['total_trainings']) ?></h3>
        </div>
        <div class="hr-stat-card warning">
            <small><i class="fas fa-bullseye"></i> اهداف</small>
            <h3><?= hr_num($overview['total_goals']) ?></h3>
        </div>
        <div class="hr-stat-card">
            <small><i class="fas fa-star"></i> ارزیابی‌ها</small>
            <h3><?= hr_num($overview['total_reviews']) ?></h3>
        </div>
    </div>

    <div class="hr-stats-grid hr-mb-4">
        <div class="hr-stat-card">
            <small><i class="fas fa-cubes"></i> شایستگی‌ها</small>
            <h3><?= hr_num($overview['total_competencies']) ?></h3>
        </div>
        <div class="hr-stat-card critical">
            <small><i class="fas fa-money-bill-wave"></i> احکام حقوقی</small>
            <h3><?= hr_num($overview['total_compensations']) ?></h3>
        </div>
    </div>

    <div class="hr-main-grid">
        <div>
            <!-- توزیع دپارتمان‌ها -->
            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-building"></i> توزیع کارکنان در دپارتمان‌ها</h3></div>
                <div class="card-body">
                    <?php if (empty($departments)): ?>
                        <p class="hr-text-muted">اطلاعاتی موجود نیست.</p>
                    <?php else: ?>
                        <?php
                        $maxDept = max(array_column($departments, 'emp_count')) ?: 1;
                        foreach ($departments as $d):
                            $pct = round(($d['emp_count'] / $maxDept) * 100);
                        ?>
                            <div class="hr-mb-3">
                                <div class="hr-flex-between hr-mb-1">
                                    <strong><?= hr_e($d['name']) ?></strong>
                                    <span><?= hr_num($d['emp_count']) ?> نفر</span>
                                </div>
                                <div style="background: #e5e7eb; border-radius: 4px; height: 8px; overflow: hidden;">
                                    <div style="width: <?= $pct ?>%; background: var(--hr-primary); height: 100%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- استخدام‌های ماهانه -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-plus"></i> استخدام‌های سال جاری</h3></div>
                <div class="card-body">
                    <?php
                    $months = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
                    $byMonth = [];
                    foreach ($hiresByMonth as $h) {
                        $byMonth[(int)$h['m']] = (int)$h['count'];
                    }
                    $maxHire = !empty($byMonth) ? max($byMonth) : 1;
                    ?>
                    <?php if (empty($byMonth)): ?>
                        <p class="hr-text-muted">استخدامی در سال جاری ثبت نشده است.</p>
                    <?php else: ?>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <div class="hr-mb-2">
                                <div class="hr-flex-between" style="font-size: 0.85rem;">
                                    <span><?= $months[$i-1] ?></span>
                                    <strong><?= hr_num($byMonth[$i] ?? 0) ?></strong>
                                </div>
                                <div style="background: #e5e7eb; border-radius: 4px; height: 6px; overflow: hidden; margin-top: 2px;">
                                    <div style="width: <?= (($byMonth[$i] ?? 0) / $maxHire) * 100 ?>%; background: var(--hr-success); height: 100%;"></div>
                                </div>
                            </div>
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div>
            <!-- توزیع جنسیت -->
            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-venus-mars"></i> توزیع جنسیت</h3></div>
                <div class="card-body">
                    <?php if (empty($genderDist)): ?>
                        <p class="hr-text-muted">اطلاعاتی موجود نیست.</p>
                    <?php else: ?>
                        <?php
                        $totalG = array_sum(array_column($genderDist, 'count'));
                        $genderLabels = ['male' => 'مرد', 'female' => 'زن', 'other' => 'سایر'];
                        ?>
                        <?php foreach ($genderDist as $g): ?>
                            <div class="hr-flex-between hr-mb-2">
                                <span><?= hr_e($genderLabels[$g['gender']] ?? $g['gender']) ?></span>
                                <span>
                                    <strong><?= hr_num($g['count']) ?></strong>
                                    <small class="hr-text-muted">
                                        (<?= hr_num(round(($g['count'] / $totalG) * 100)) ?>%)
                                    </small>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- توزیع نوع قرارداد -->
            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-file-signature"></i> نوع قرارداد</h3></div>
                <div class="card-body">
                    <?php if (empty($contractDist)): ?>
                        <p class="hr-text-muted">اطلاعاتی موجود نیست.</p>
                    <?php else: ?>
                        <?php
                        $contractLabels = [
                            'permanent' => 'دائمی', 'fixed_term' => 'مدت معین',
                            'project' => 'پروژه‌ای', 'intern' => 'کارآموز', 'consultant' => 'مشاور'
                        ];
                        foreach ($contractDist as $c):
                        ?>
                            <div class="hr-flex-between hr-mb-2">
                                <span><?= hr_e($contractLabels[$c['contract_type']] ?? $c['contract_type']) ?></span>
                                <strong><?= hr_num($c['count']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- توزیع تحصیلات -->
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-graduation-cap"></i> سطح تحصیلات</h3></div>
                <div class="card-body">
                    <?php if (empty($educationDist)): ?>
                        <p class="hr-text-muted">اطلاعاتی موجود نیست.</p>
                    <?php else: ?>
                        <?php foreach ($educationDist as $ed): ?>
                            <div class="hr-flex-between hr-mb-2">
                                <span><?= hr_e(hr_education_level_label($ed['education_level'])) ?></span>
                                <strong><?= hr_num($ed['count']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>