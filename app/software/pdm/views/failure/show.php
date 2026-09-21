<?php
/**
 * PdM Analyzer - نمایش جزئیات حالت خرابی
 * مسیر: app/software/pdm/views/failure/show.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-exclamation-triangle"></i> <?= pdm_e($fm['name']) ?>
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                <?php if (!empty($fm['code'])): ?>
                    کد: <code><?= pdm_e($fm['code']) ?></code> —
                <?php endif; ?>
                دارایی: <strong><?= pdm_e($fm['asset_name'] ?? '—') ?></strong>
            </p>
        </div>
        <div class="pdm-flex pdm-gap-2">
            <a href="<?= pdm_url('failure', 'edit', ['id' => $fm['id']]) ?>" class="btn-pdm-outline">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <a href="<?= pdm_url('failure') ?>" class="btn-pdm-outline">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="pdm-alert <?= pdm_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= pdm_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <?php
    $rpn = (int) $fm['rpn'];
    $riskClass = \App\Software\Pdm\Models\FailureMode::getRiskClass($rpn);
    $riskLabel = \App\Software\Pdm\Models\FailureMode::getRiskLabel($rpn);
    ?>

    <!-- RPN بزرگ -->
    <div class="pdm-stat-card <?= $rpn >= 200 ? 'critical' : ($rpn >= 100 ? 'warning' : 'success') ?> pdm-mb-4">
        <small><i class="fas fa-chart-line"></i> RPN (اولویت ریسک)</small>
        <h3 style="font-size: 3rem;"><?= pdm_num($rpn) ?></h3>
        <span class="pdm-criticality-badge <?= $riskClass ?>" style="font-size: 0.9rem;">
            سطح ریسک: <?= $riskLabel ?>
        </span>
    </div>

    <div class="main-grid">
        <!-- ستون چپ -->
        <div>
            <div class="card pdm-mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i>
                        اطلاعات
                    </h3>
                </div>
                <div class="card-body">
                    <table class="pdm-table">
                        <tbody>
                            <tr>
                                <th style="width: 150px;">کد</th>
                                <td><code><?= pdm_e($fm['code'] ?? '—') ?></code></td>
                            </tr>
                            <tr>
                                <th>دارایی</th>
                                <td>
                                    <a href="<?= pdm_url('asset', 'show', ['id' => $fm['asset_id']]) ?>"
                                       style="color: var(--pdm-primary-dark);">
                                        <?= pdm_e($fm['asset_name'] ?? '—') ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th>تاریخ ایجاد</th>
                                <td><?= pdm_date($fm['created_at'] ?? null, 'Y/m/d H:i') ?></td>
                            </tr>
                            <?php if (!empty($fm['description'])): ?>
                            <tr>
                                <th>توضیحات</th>
                                <td><?= nl2br(pdm_e($fm['description'])) ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (!empty($fm['recommended_action'])): ?>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tools"></i>
                        اقدام اصلاحی پیشنهادی
                    </h3>
                </div>
                <div class="card-body">
                    <?= nl2br(pdm_e($fm['recommended_action'])) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ستون راست -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-calculator"></i>
                        تحلیل FMEA
                    </h3>
                </div>
                <div class="card-body">
                    <table class="pdm-table">
                        <tbody>
                            <tr>
                                <th>شدت (S)</th>
                                <td><?= pdm_num($fm['severity']) ?></td>
                            </tr>
                            <tr>
                                <th>تکرار (O)</th>
                                <td><?= pdm_num($fm['occurrence']) ?></td>
                            </tr>
                            <tr>
                                <th>تشخیص (D)</th>
                                <td><?= pdm_num($fm['detection']) ?></td>
                            </tr>
                            <tr style="background: var(--pdm-light);">
                                <th><strong>RPN (S×O×D)</strong></th>
                                <td><strong style="color: var(--pdm-primary-dark); font-size: 1.2rem;">
                                    <?= pdm_num($rpn) ?>
                                </strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/pdm.js"></script>