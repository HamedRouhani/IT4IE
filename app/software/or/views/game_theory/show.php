<?php
/**
 * OR Analyzer - نمایش جزئیات پروژه نظریه بازی
 * مسیر: app/software/or/views/game_theory/show.php
 */

// تعریف تابع کمکی در صورت عدم وجود
if (!function_exists('or_stat_box')) {
    function or_stat_box(string $label, string $value, string $valueClass = ''): string {
        $esc = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
        return '<div class="border rounded bg-light text-center p-2 h-100">'
            . '<small class="d-block text-muted mb-1" style="font-size:.72rem;line-height:1.3;">' . $esc($label) . '</small>'
            . '<strong class="d-block' . ($valueClass !== '' ? ' ' . $valueClass : '') . '" style="line-height:1.3;word-break:break-word;">' . $esc($value) . '</strong>'
            . '</div>';
    }
}

$project = $project ?? [];
$result = $result ?? [];
$p1_strats = $p1_strats ?? [];
$p2_strats = $p2_strats ?? [];
$matrix_a = $matrix_a ?? [];
$matrix_b = $matrix_b ?? [];
$ok = ($result['status'] ?? '') === 'ok';
$game_mode = $result['game_mode'] ?? 'zero_sum';
$type = $result['type'] ?? 'unknown';
?>
<div class="container-fluid py-3 py-md-4">

    <!-- هدر صفحه -->
    <div class="or-page-header">
        <div>
            <h3 class="mb-1"><i class="fas fa-chess text-primary me-2"></i><?= or_e($project['name']) ?></h3>
            <div class="d-flex flex-wrap gap-1">
                <span class="badge bg-<?= $game_mode === 'bimatrix' ? 'info' : 'primary' ?>"><?= $game_mode === 'bimatrix' ? 'مجموع غیر صفر' : 'مجموع صفر' ?></span>
                <span class="badge bg-<?= $ok ? 'success' : 'danger' ?>"><?= $ok ? 'حل‌شده' : 'خطا' ?></span>
                <small class="text-muted align-self-center">آخرین به‌روزرسانی: <?= or_e($project['updated_at']) ?></small>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="if(confirm('آیا از حذف این پروژه مطمئن هستید؟')) location.href='<?= or_url('controller=game_theory&action=delete&id=' . (int)$project['id']) ?>'">
                <i class="fas fa-trash me-1"></i><span class="d-none d-md-inline">حذف</span>
            </button>
            <a href="<?= or_url('controller=game_theory') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i><span class="d-none d-md-inline">بازگشت به لیست</span>
            </a>
        </div>
    </div>

    <?php if (!$ok): ?>
        <div class="alert alert-danger">
            <h6><i class="fas fa-exclamation-triangle me-2"></i>خطا در حل بازی</h6>
            <p class="mb-0 small"><?= or_e($result['message'] ?? 'نتیجه معتبری برای این پروژه ذخیره نشده است.') ?></p>
        </div>
    <?php else: ?>

        <!-- تفسیر خودکار -->
        <div class="alert alert-<?= ($type === 'pure' || $type === 'pure_nash') ? 'success' : 'info' ?> py-3 mb-3">
            <h6 class="fw-bold mb-2"><i class="fas fa-lightbulb me-2"></i>تفسیر نتیجه</h6>
            <pre class="mb-0 small" style="white-space: pre-wrap; line-height: 1.7;"><?= or_e($result['interpretation']) ?></pre>
        </div>

        <!-- ماتریس‌های پرداخت -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2 py-md-3">
                <h6 class="mb-0"><i class="fas fa-table me-2 text-primary"></i>ماتریس‌های پرداخت</h6>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <h6 class="fw-bold small mb-2 text-primary"><i class="fas fa-user me-1"></i>پرداخت بازیکن ۱ (سطر)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered text-center mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th>بازیکن ۱ \ ۲</th>
                                        <?php foreach ($p2_strats as $s): ?>
                                            <th><?= or_e($s) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($matrix_a as $i => $row): ?>
                                    <tr>
                                        <td class="fw-bold text-start"><?= or_e($p1_strats[$i] ?? 'سطر ' . ($i + 1)) ?></td>
                                        <?php foreach ($row as $val): ?>
                                            <td class="font-monospace"><?= number_format((float)$val, 2) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php if ($game_mode === 'bimatrix' && !empty($matrix_b)): ?>
                    <div class="col-12 col-md-6">
                        <h6 class="fw-bold small mb-2 text-success"><i class="fas fa-user me-1"></i>پرداخت بازیکن ۲ (ستون)</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered text-center mb-0 small">
                                <thead class="table-light">
                                    <tr>
                                        <th>بازیکن ۱ \ </th>
                                        <?php foreach ($p2_strats as $s): ?>
                                            <th><?= or_e($s) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($matrix_b as $i => $row): ?>
                                    <tr>
                                        <td class="fw-bold text-start"><?= or_e($p1_strats[$i] ?? 'سطر ' . ($i + 1)) ?></td>
                                        <?php foreach ($row as $val): ?>
                                            <td class="font-monospace"><?= number_format((float)$val, 2) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="col-12 col-md-6">
                        <div class="border rounded p-3 h-100 bg-light d-flex align-items-center justify-content-center">
                            <div class="text-center text-muted">
                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                <p class="small mb-0">در بازی مجموع صفر، پرداخت بازیکن ۲ قرینه پرداخت بازیکن ۱ است.</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- نتایج حل -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2 py-md-3">
                <h6 class="mb-0"><i class="fas fa-trophy me-2 text-warning"></i>نتایج و استراتژی بهینه</h6>
            </div>
            <div class="card-body p-3 p-md-4">
                <?php if ($game_mode === 'zero_sum'): ?>
                    <?php if ($type === 'pure'): ?>
                        <!-- استراتژی خالص (نقطه زینی) -->
                        <div class="row g-1 g-md-2 mb-3">
                            <div class="col-6 col-md-4"><?= or_stat_box('نوع تعادل', 'استراتژی خالص (نقطه زینی)', 'text-success') ?></div>
                            <div class="col-6 col-md-4"><?= or_stat_box('استراتژی بهینه بازیکن ۱', or_e($p1_strats[$result['saddle_point']['row']] ?? '-'), 'text-primary') ?></div>
                            <div class="col-6 col-md-4"><?= or_stat_box('استراتژی بهینه بازیکن ۲', or_e($p2_strats[$result['saddle_point']['col']] ?? '-'), 'text-primary') ?></div>
                            <div class="col-6 col-md-4"><?= or_stat_box('موقعیت نقطه زینی', 'سطر ' . ($result['saddle_point']['row'] + 1) . '، ستون ' . ($result['saddle_point']['col'] + 1)) ?></div>
                            <div class="col-6 col-md-4"><?= or_stat_box('ارزش بازی (V)', number_format((float)$result['value_of_game'], 4), 'fw-bold text-danger') ?></div>
                        </div>
                    <?php else: ?>
                        <!-- استراتژی ترکیبی -->
                        <div class="row g-1 g-md-2 mb-3">
                            <div class="col-6 col-md-4"><?= or_stat_box('نوع تعادل', 'استراتژی ترکیبی', 'text-info') ?></div>
                            <div class="col-6 col-md-4"><?= or_stat_box('احتمال «' . or_e($p1_strats[0] ?? 'استراتژی ۱') . '»', number_format((float)$result['p1_probs'][0] * 100, 1) . '%', 'text-primary') ?></div>
                            <div class="col-6 col-md-4"><?= or_stat_box('احتمال «' . or_e($p1_strats[1] ?? 'استراتژی ۲') . '»', number_format((float)$result['p1_probs'][1] * 100, 1) . '%', 'text-primary') ?></div>
                            <div class="col-6 col-md-4"><?= or_stat_box('احتمال «' . or_e($p2_strats[0] ?? 'استراتژی ') . '»', number_format((float)$result['p2_probs'][0] * 100, 1) . '%', 'text-success') ?></div>
                            <div class="col-6 col-md-4"><?= or_stat_box('احتمال «' . or_e($p2_strats[1] ?? 'استراتژی ۲') . '»', number_format((float)$result['p2_probs'][1] * 100, 1) . '%', 'text-success') ?></div>
                            <div class="col-6 col-md-4"><?= or_stat_box('ارزش بازی (V)', number_format((float)$result['value_of_game'], 4), 'fw-bold text-danger') ?></div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- تعادل نش برای بازی مجموع غیر صفر -->
                    <h6 class="fw-bold small mb-2"><i class="fas fa-balance-scale me-1"></i>تعادل‌های نش یافت‌شده</h6>
                    <div class="row g-2">
                        <?php foreach ($result['nash_equilibria'] ?? [] as $idx => $ne): ?>
                            <div class="col-12 col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="fw-bold small mb-2 text-primary">تعادل نش #<?= $idx + 1 ?></div>
                                    <div class="row g-1 small">
                                        <div class="col-12"><?= or_stat_box('استراتژی بازیکن ۱', or_e($ne['p1_strat']), 'text-primary') ?></div>
                                        <div class="col-12"><?= or_stat_box('استراتژی بازیکن ۲', or_e($ne['p2_strat']), 'text-success') ?></div>
                                        <div class="col-6"><?= or_stat_box('پرداخت بازیکن ۱', number_format((float)$ne['p1_payoff'], 2)) ?></div>
                                        <div class="col-6"><?= or_stat_box('پرداخت بازیکن ', number_format((float)$ne['p2_payoff'], 2)) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- اطلاعات پروژه -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2 py-md-3">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2 text-info"></i>اطلاعات پروژه</h6>
            </div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <table class="table table-borderless align-middle mb-0 small">
                            <tr>
                                <td class="fw-bold" style="width:40%;">شناسه پروژه</td>
                                <td class="font-monospace">#<?= (int)$project['id'] ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">تعداد استراتژی بازیکن ۱</td>
                                <td><?= count($p1_strats) ?> مورد</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">تعداد استراتژی بازیکن </td>
                                <td><?= count($p2_strats) ?> مورد</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-12 col-md-6">
                        <table class="table table-borderless align-middle mb-0 small">
                            <tr>
                                <td class="fw-bold" style="width:40%;">تاریخ ایجاد</td>
                                <td><?= or_e($project['created_at']) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">آخرین به‌روزرسانی</td>
                                <td><?= or_e($project['updated_at']) ?></td>
                            </tr>
                            <?php if (!empty($project['description'])): ?>
                            <tr>
                                <td class="fw-bold">توضیحات</td>
                                <td><?= nl2br(or_e($project['description'])) ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>