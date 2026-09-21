<?php
/**
 * PdM Analyzer - لیست حالات خرابی
 * مسیر: app/software/pdm/views/failure/index.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <!-- هدر -->
    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-exclamation-triangle"></i> خرابی‌ها و تحلیل FMEA
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                مجموع: <strong><?= pdm_num($stats['total']) ?></strong> حالت خرابی
                — بحرانی: <strong style="color: var(--pdm-danger);"><?= pdm_num($stats['critical']) ?></strong>
                — بالا: <strong style="color: var(--pdm-warning);"><?= pdm_num($stats['high']) ?></strong>
            </p>
        </div>
        <a href="<?= pdm_url('failure', 'create') ?>" class="btn-pdm-primary">
            <i class="fas fa-plus"></i> افزودن حالت خرابی
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="pdm-alert <?= pdm_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= pdm_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- توزیع ریسک -->
    <?php
    $totalRisk = array_sum($riskDistribution);
    if ($totalRisk > 0):
    ?>
    <div class="card pdm-mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-pie"></i>
                توزیع ریسک (RPN)
            </h3>
        </div>
        <div class="card-body">
            <?php
            $riskLabels = [
                'critical' => ['label' => 'بحرانی (RPN ≥ 200)', 'class' => 'pdm-criticality-critical'],
                'high'     => ['label' => 'بالا (100-199)',       'class' => 'pdm-criticality-high'],
                'medium'   => ['label' => 'متوسط (50-99)',        'class' => 'pdm-criticality-medium'],
                'low'      => ['label' => 'پایین (< 50)',         'class' => 'pdm-criticality-low'],
            ];
            foreach ($riskLabels as $key => $info):
                $count = $riskDistribution[$key] ?? 0;
                $percent = $totalRisk > 0 ? round(($count / $totalRisk) * 100, 1) : 0;
            ?>
                <div class="pdm-mb-3">
                    <div class="pdm-flex-between pdm-mb-2">
                        <span class="pdm-criticality-badge <?= $info['class'] ?>">
                            <?= $info['label'] ?>
                        </span>
                        <span>
                            <strong><?= pdm_num($count) ?></strong>
                            <small class="pdm-text-muted">(<?= pdm_num($percent) ?>%)</small>
                        </span>
                    </div>
                    <div style="height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">
                        <div style="width: <?= $percent ?>%; height: 100%; background: var(--pdm-primary); transition: width 0.5s;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- جدول -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                لیست حالات خرابی
                <span class="pdm-text-muted" style="font-weight: normal; font-size: 0.85rem;">
                    (<?= pdm_num(count($failureModes)) ?> مورد)
                </span>
            </h3>
        </div>

        <?php if (empty($failureModes)): ?>
            <div class="pdm-empty-state">
                <i class="fas fa-shield-alt"></i>
                <h4>هیچ حالت خرابی ثبت نشده است</h4>
                <p>اولین تحلیل FMEA خود را ایجاد کنید.</p>
                <a href="<?= pdm_url('failure', 'create') ?>" class="btn-pdm-primary">
                    <i class="fas fa-plus"></i> افزودن حالت خرابی
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="pdm-table">
                    <thead>
                        <tr>
                            <th>کد</th>
                            <th>نام حالت خرابی</th>
                            <th>دارایی</th>
                            <th>شدت</th>
                            <th>تکرار</th>
                            <th>تشخیص</th>
                            <th>RPN</th>
                            <th>سطح ریسک</th>
                            <th style="width: 120px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($failureModes as $fm): ?>
                            <?php $rpn = (int) $fm['rpn']; ?>
                            <tr>
                                <td><code><?= pdm_e($fm['code'] ?? '—') ?></code></td>
                                <td>
                                    <a href="<?= pdm_url('failure', 'show', ['id' => $fm['id']]) ?>"
                                       style="color: var(--pdm-primary-dark); font-weight: 600;">
                                        <?= pdm_e($fm['name']) ?>
                                    </a>
                                </td>
                                <td>
                                    <?= pdm_e($fm['asset_name'] ?? '—') ?>
                                    <?php if (!empty($fm['asset_code'])): ?>
                                        <br><small class="pdm-text-muted">
                                            <code><?= pdm_e($fm['asset_code']) ?></code>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?= pdm_num($fm['severity']) ?></td>
                                <td><?= pdm_num($fm['occurrence']) ?></td>
                                <td><?= pdm_num($fm['detection']) ?></td>
                                <td><strong><?= pdm_num($rpn) ?></strong></td>
                                <td>
                                    <span class="pdm-criticality-badge <?= \App\Software\Pdm\Models\FailureMode::getRiskClass($rpn) ?>">
                                        <?= \App\Software\Pdm\Models\FailureMode::getRiskLabel($rpn) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="pdm-flex pdm-gap-2" style="justify-content: center;">
                                        <a href="<?= pdm_url('failure', 'edit', ['id' => $fm['id']]) ?>"
                                           class="btn-pdm-outline btn-sm" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= pdm_url('failure', 'delete', ['id' => $fm['id']]) ?>"
                                           class="btn-pdm-outline btn-sm pdm-confirm-delete"
                                           data-message="آیا از حذف '<?= pdm_e($fm['name']) ?>' اطمینان دارید؟"
                                           title="حذف"
                                           style="color: var(--pdm-danger); border-color: var(--pdm-danger);">
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

<script src="/public/assets/js/software/pdm.js"></script>