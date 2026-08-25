<div class="d-flex justify-content-between mb-3">
    <h1 class="h3">گزارش پروژه: <?= mcdm_e($project['name']) ?></h1>
    <button onclick="window.print()" class="btn btn-outline-secondary"><i class="fas fa-print"></i> چاپ</button>
</div>

<div class="card mb-3"><div class="card-body">
    <div class="row">
        <div class="col-md-4"><b>روش:</b> <?= mcdm_e($project['method_name'] ?? '-') ?></div>
        <div class="col-md-4"><b>فاز:</b> <?= mcdm_getPhaseLabel($project['phase']) ?></div>
        <div class="col-md-4"><b>CR:</b> <?= $project['consistency_ratio'] !== null ? number_format((float)$project['consistency_ratio'], 4) : '-' ?></div>
    </div>
</div></div>

<div class="card"><div class="card-header">رتبه‌بندی نهایی گزینه‌ها</div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead><tr><th>رتبه</th><th>گزینه</th><th>امتیاز</th></tr></thead>
            <tbody>
                <?php foreach ($results as $r): ?>
                    <tr>
                        <td><?= (int)$r['rank'] ?></td>
                        <td><?= mcdm_e($r['alternative_name'] ?? '') ?></td>
                        <td><?= number_format((float)$r['score'], 4) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>