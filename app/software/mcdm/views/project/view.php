<?php
$projectId = (int)$project['id'];
$baseUrl = mcdm_url('controller=project&action=show&id=' . $projectId);
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><?= mcdm_e($project['name']) ?></h1>
    <a href="<?= mcdm_url('controller=project') ?>" class="btn btn-outline-secondary btn-sm">بازگشت</a>
</div>

<ul class="nav nav-tabs mb-3">
    <?php
    $tabs = [
        'info'         => 'اطلاعات',
        'criteria'     => 'معیارها',
        'alternatives' => 'گزینه‌ها',
        'matrix'       => 'ماتریس ارزیابی',
        'ahp'          => 'ماتریس AHP',
        'results'      => 'نتایج',
    ];
    foreach ($tabs as $key => $label): ?>
        <li class="nav-item">
            <a class="nav-link <?= $tab === $key ? 'active' : '' ?>"
               href="<?= $baseUrl . '&tab=' . $key ?>"><?= $label ?></a>
        </li>
    <?php endforeach; ?>
</ul>

<?php if ($tab === 'info'): ?>
    <div class="card"><div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="text-muted small">روش</div>
                <div class="fw-bold"><?= mcdm_e($project['method_name'] ?? '-') ?></div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="text-muted small">فاز</div>
                <span class="badge bg-<?= mcdm_getPhaseColor($project['phase']) ?>">
                    <?= mcdm_getPhaseLabel($project['phase']) ?>
                </span>
            </div>
            <div class="col-md-6 mb-3">
                <div class="text-muted small">نرخ ناسازگاری (CR)</div>
                <div class="fw-bold"><?= $project['consistency_ratio'] !== null ? number_format((float)$project['consistency_ratio'], 4) : '-' ?></div>
            </div>
            <div class="col-12">
                <div class="text-muted small">توضیحات</div>
                <div><?= nl2br(mcdm_e($project['description'] ?? '-')) ?></div>
            </div>
        </div>
    </div></div>

<?php elseif ($tab === 'criteria'): ?>
    <div class="card mb-3"><div class="card-body">
        <form method="post" action="<?= mcdm_url('controller=project&action=addCriterion&id=' . $projectId) ?>" class="row g-2">
            <div class="col-md-5"><input type="text" name="name" class="form-control" placeholder="نام معیار" required></div>
            <div class="col-md-4">
                <select name="type" class="form-select">
                    <option value="benefit">سودی (مثبت)</option>
                    <option value="cost">هزینه‌ای (منفی)</option>
                </select>
            </div>
            <div class="col-md-3"><button class="btn btn-primary w-100">افزودن</button></div>
        </form>
    </div></div>

    <div class="card"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>#</th><th>معیار</th><th>نوع</th><th>وزن</th></tr></thead>
            <tbody>
                <?php foreach ($criteria as $i => $c): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= mcdm_e($c['name']) ?></td>
                        <td><span class="badge bg-info"><?= mcdm_getCriterionTypeLabel($c['type']) ?></span></td>
                        <td><?= $c['weight'] !== null ? number_format((float)$c['weight'], 4) : '-' ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($criteria)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">معیاری ثبت نشده است.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div></div>

<?php elseif ($tab === 'alternatives'): ?>
    <div class="card mb-3"><div class="card-body">
        <form method="post" action="<?= mcdm_url('controller=project&action=addAlternative&id=' . $projectId) ?>" class="row g-2">
            <div class="col-md-5"><input type="text" name="name" class="form-control" placeholder="نام گزینه" required></div>
            <div class="col-md-4"><input type="text" name="description" class="form-control" placeholder="توضیحات"></div>
            <div class="col-md-3"><button class="btn btn-primary w-100">افزودن</button></div>
        </form>
    </div></div>

    <div class="card"><div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>#</th><th>گزینه</th><th>توضیحات</th></tr></thead>
            <tbody>
                <?php foreach ($alternatives as $i => $a): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= mcdm_e($a['name']) ?></td>
                        <td><?= mcdm_e($a['description'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($alternatives)): ?>
                    <tr><td colspan="3" class="text-center text-muted py-4">گزینه‌ای ثبت نشده است.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div></div>

<?php elseif ($tab === 'matrix'): ?>
    <?php if (empty($criteria) || empty($alternatives)): ?>
        <div class="alert alert-warning">ابتدا معیارها و گزینه‌ها را تعریف کنید.</div>
    <?php else: ?>
        <div class="card"><div class="card-body">
            <div class="alert alert-info">امتیاز هر گزینه را نسبت به هر معیار وارد کنید (عدد).</div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead>
                        <tr>
                            <th>گزینه \ معیار</th>
                            <?php foreach ($criteria as $c): ?>
                                <th><?= mcdm_e($c['name']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alternatives as $a): ?>
                            <tr>
                                <td class="fw-bold text-end"><?= mcdm_e($a['name']) ?></td>
                                <?php foreach ($criteria as $c): ?>
                                    <td>
                                        <input type="number" step="any" class="form-control form-control-sm eval-cell"
                                               data-project="<?= $projectId ?>"
                                               data-criterion="<?= (int)$c['id'] ?>"
                                               data-alternative="<?= (int)$a['id'] ?>"
                                               value="">
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <button id="run-ranking" class="btn btn-success">
                    <i class="fas fa-calculator"></i> محاسبه رتبه‌بندی
                </button>
                <span id="run-status" class="ms-2 text-muted"></span>
            </div>
            <div id="ranking-result" class="mt-3"></div>
        </div></div>

        <script>
        (function () {
            const evalUrl = <?= json_encode(mcdm_url('controller=project&action=setEvaluation&id=' . $projectId)) ?>;
            const runUrl  = <?= json_encode(mcdm_url('controller=calculator&action=run&id=' . $projectId)) ?>;

            document.querySelectorAll('.eval-cell').forEach(function (input) {
                input.addEventListener('change', function () {
                    const fd = new FormData();
                    fd.append('criterion_id', input.dataset.criterion);
                    fd.append('alternative_id', input.dataset.alternative);
                    fd.append('value', input.value || 0);

                    fetch(evalUrl, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: fd
                    });
                });
            });

            const runBtn = document.getElementById('run-ranking');
            if (runBtn) {
                runBtn.addEventListener('click', async function () {
                    const status = document.getElementById('run-status');
                    const wrap = document.getElementById('ranking-result');
                    runBtn.disabled = true;
                    status.textContent = 'در حال محاسبه...';

                    try {
                        const res = await fetch(runUrl, {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        });
                        const data = await res.json();

                        if (data.success && data.ranking_detail) {
                            let html = '<div class="table-responsive"><table class="table table-bordered">';
                            html += '<thead><tr><th>رتبه</th><th>گزینه</th><th>امتیاز</th></tr></thead><tbody>';
                            data.ranking_detail.forEach(function (r) {
                                html += '<tr><td>' + r.rank + '</td><td>' + r.alternative_name +
                                        '</td><td>' + Number(r.score).toFixed(4) + '</td></tr>';
                            });
                            html += '</tbody></table></div>';
                            wrap.innerHTML = html;
                            status.textContent = '✅ محاسبه انجام شد.';
                        } else {
                            wrap.innerHTML = '<div class="alert alert-danger">' + (data.error || data.message || 'خطا') + '</div>';
                            status.textContent = '';
                        }
                    } catch (e) {
                        wrap.innerHTML = '<div class="alert alert-danger">خطای ارتباط با سرور.</div>';
                        status.textContent = '';
                    } finally {
                        runBtn.disabled = false;
                    }
                });
            }
        })();
        </script>
    <?php endif; ?>

<?php elseif ($tab === 'ahp'): ?>
    <?php if (count($criteria) < 2): ?>
        <div class="alert alert-warning">برای AHP حداقل دو معیار لازم است.</div>
    <?php else: ?>
        <div class="card"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="alert alert-info mb-0 flex-grow-1 me-2">
                    اهمیت معیار سطر را نسبت به معیار ستون انتخاب کنید؛ معکوس آن خودکار پر می‌شود.
                </div>
                <button id="ahp-calc" class="btn btn-success">محاسبه وزن‌ها</button>
            </div>
            <div id="ahp-matrix"></div>
            <hr>
            <div id="ahp-result"></div>
        </div></div>

        <script>
        (function () {
            const cfg = {
                endpoint: <?= json_encode(mcdm_url('controller=calculator&action=ahpPairwise&id=' . $projectId)) ?>,
                criteria: <?= json_encode(array_values(array_map(fn($c) => ['id' => (int)$c['id'], 'name' => $c['name']], $criteria)), JSON_UNESCAPED_UNICODE) ?>
            };
            const criteria = cfg.criteria;
            const n = criteria.length;
            const matrixWrap = document.getElementById('ahp-matrix');
            const resultWrap = document.getElementById('ahp-result');
            const saaty = [9,8,7,6,5,4,3,2,1,1/2,1/3,1/4,1/5,1/6,1/7,1/8,1/9];

            function lbl(v) { return v >= 1 ? String(v) : '1/' + Math.round(1/v); }
            function fmt(v) { v = Number(v); if (v === 1) return '1'; if (v < 1) return '1/' + Math.round(1/v); return String(Math.round(v*10000)/10000); }
            function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

            let html = '<div class="table-responsive"><table class="table table-bordered table-sm text-center"><thead><tr><th>معیار</th>';
            criteria.forEach(c => html += '<th>' + esc(c.name) + '</th>');
            html += '</tr></thead><tbody>';
            for (let i = 0; i < n; i++) {
                html += '<tr><td class="fw-bold text-end">' + esc(criteria[i].name) + '</td>';
                for (let j = 0; j < n; j++) {
                    if (i === j) {
                        html += '<td class="table-light">1<input type="hidden" name="cell-' + i + '-' + j + '" value="1"></td>';
                    } else if (i < j) {
                        html += '<td><select class="form-select form-select-sm ahp-cell" data-row="' + i + '" data-col="' + j + '" name="cell-' + i + '-' + j + '">';
                        saaty.forEach(v => html += '<option value="' + v + '"' + (v===1?' selected':'') + '>' + lbl(v) + '</option>');
                        html += '</select></td>';
                    } else {
                        html += '<td><input id="disp-' + i + '-' + j + '" class="form-control form-control-sm text-center" value="1" readonly>';
                        html += '<input type="hidden" name="cell-' + i + '-' + j + '" value="1"></td>';
                    }
                }
                html += '</tr>';
            }
            html += '</tbody></table></div>';
            matrixWrap.innerHTML = html;

            matrixWrap.addEventListener('change', function (e) {
                const sel = e.target.closest('select.ahp-cell');
                if (!sel) return;
                const i = +sel.dataset.row, j = +sel.dataset.col;
                const inv = 1 / Number(sel.value);
                const hidden = matrixWrap.querySelector('input[name="cell-' + j + '-' + i + '"]');
                const disp = document.getElementById('disp-' + j + '-' + i);
                if (hidden) hidden.value = inv;
                if (disp) disp.value = fmt(inv);
            });

            function collect() {
                const m = [];
                for (let i = 0; i < n; i++) {
                    const row = [];
                    for (let j = 0; j < n; j++) {
                        const inp = matrixWrap.querySelector('input[name="cell-' + i + '-' + j + '"]');
                        row.push(inp ? parseFloat(inp.value) : 1);
                    }
                    m.push(row);
                }
                return m;
            }

            document.getElementById('ahp-calc').addEventListener('click', async function () {
                const btn = this;
                btn.disabled = true; btn.textContent = 'در حال محاسبه...';
                resultWrap.innerHTML = '';
                try {
                    const res = await fetch(cfg.endpoint, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify({ matrix: collect() })
                    });
                    const data = await res.json();
                    if (data.success) {
                        const cm = data.consistency_metrics;
                        let out = '<div class="alert ' + (cm.is_consistent ? 'alert-success' : 'alert-warning') + '">' + data.smart_feedback + '</div>';
                        out += '<div class="row g-2 mb-3">';
                        out += '<div class="col-3"><div class="border rounded p-2 text-center bg-light"><div class="small text-muted">λmax</div><b>' + cm.lambda_max + '</b></div></div>';
                        out += '<div class="col-3"><div class="border rounded p-2 text-center bg-light"><div class="small text-muted">CI</div><b>' + cm.CI + '</b></div></div>';
                        out += '<div class="col-3"><div class="border rounded p-2 text-center bg-light"><div class="small text-muted">RI</div><b>' + cm.RI + '</b></div></div>';
                        out += '<div class="col-3"><div class="border rounded p-2 text-center bg-light"><div class="small text-muted">CR</div><b>' + cm.CR + '</b></div></div>';
                        out += '</div>';
                        out += '<table class="table table-bordered"><thead><tr><th>معیار</th><th>وزن</th><th style="width:40%">نمودار</th></tr></thead><tbody>';
                        (data.criteria || criteria.map(c => c.name)).forEach((name, idx) => {
                            const w = data.weights[idx] || 0;
                            const pct = (w * 100).toFixed(2);
                            out += '<tr><td>' + esc(name) + '</td><td>' + Number(w).toFixed(4) + '</td>';
                            out += '<td><div class="progress" style="height:12px"><div class="progress-bar bg-success" style="width:' + pct + '%"></div></div></td></tr>';
                        });
                        out += '</tbody></table>';
                        resultWrap.innerHTML = out;
                    } else {
                        resultWrap.innerHTML = '<div class="alert alert-danger">' + (data.error || data.message) + '</div>';
                    }
                } catch (e) {
                    resultWrap.innerHTML = '<div class="alert alert-danger">خطای ارتباط با سرور.</div>';
                } finally {
                    btn.disabled = false; btn.textContent = 'محاسبه وزن‌ها';
                }
            });
        })();
        </script>
    <?php endif; ?>

<?php elseif ($tab === 'results'): ?>
    <div class="card"><div class="card-body">
        <?php if (empty($results)): ?>
            <div class="alert alert-warning mb-0">هنوز رتبه‌بندی محاسبه نشده است. از تب «ماتریس ارزیابی» اقدام کنید.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead><tr><th>رتبه</th><th>گزینه</th><th>امتیاز</th></tr></thead>
                    <tbody>
                        <?php foreach ($results as $r): ?>
                            <tr>
                                <td><span class="badge bg-primary"><?= (int)$r['rank'] ?></span></td>
                                <td><?= mcdm_e($r['alternative_name'] ?? '') ?></td>
                                <td><?= number_format((float)$r['score'], 4) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <a href="<?= mcdm_url('controller=report&action=projectReport&id=' . $projectId) ?>" class="btn btn-outline-primary">
                <i class="fas fa-file-alt"></i> گزارش کامل
            </a>
        <?php endif; ?>
    </div></div>
<?php endif; ?>