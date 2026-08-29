<?php
/**
 * نمایش اختصاصی پروژه کوتاه‌ترین مسیر
 * مسیر: app/software/or/views/shortest/show.php
 */
?>

<div class="container-fluid py-4">
    <!-- هدر صفحه -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-route text-primary"></i> <?= or_e($project['name']) ?></h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('controller=shortest') ?>">کوتاه‌ترین مسیر</a></li>
                    <li class="breadcrumb-item active"><?= or_e($project['name']) ?></li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="<?= or_url('controller=shortest&action=edit&id=' . $project['id']) ?>" class="btn btn-outline-warning btn-sm">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <button class="btn btn-outline-danger btn-sm" onclick="deleteProject(<?= $project['id'] ?>, '<?= or_e($project['name']) ?>')">
                <i class="fas fa-trash"></i> حذف
            </button>
        </div>
    </div>

    <!-- تب‌های اختصاصی -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'info' ? 'active' : '' ?>" href="<?= or_url('controller=shortest&action=show&id=' . $project['id'] . '&tab=info') ?>">
                <i class="fas fa-info-circle"></i> اطلاعات
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'graph' ? 'active' : '' ?>" href="<?= or_url('controller=shortest&action=show&id=' . $project['id'] . '&tab=graph') ?>">
                <i class="fas fa-project-diagram"></i> گراف
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'solve' ? 'active' : '' ?>" href="<?= or_url('controller=shortest&action=show&id=' . $project['id'] . '&tab=solve') ?>">
                <i class="fas fa-play-circle"></i> حل مسئله
            </a>
        </li>
        <?php if ($project['status'] === 'solved'): ?>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'result' ? 'active' : '' ?>" href="<?= or_url('controller=shortest&action=show&id=' . $project['id'] . '&tab=result') ?>">
                <i class="fas fa-chart-bar"></i> نتایج
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <!-- محتوای تب‌ها -->
    <div class="tab-content">
        
        <!-- ==================== تب ۱: اطلاعات ==================== -->
        <?php if ($tab === 'info'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">توضیحات پروژه</h5>
                    <p class="text-muted"><?= nl2br(or_e($project['description'] ?? 'توضیحاتی ثبت نشده است.')) ?></p>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <i class="fas fa-circle text-primary fa-2x mb-2"></i>
                                <h6 class="text-muted mb-1">تعداد گره‌ها</h6>
                                <h3 class="mb-0 text-primary"><?= count($nodes) ?></h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <i class="fas fa-arrow-right text-info fa-2x mb-2"></i>
                                <h6 class="text-muted mb-1">تعداد یال‌ها</h6>
                                <h3 class="mb-0 text-info"><?= count($edges) ?></h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <i class="fas fa-flag-checkered text-success fa-2x mb-2"></i>
                                <h6 class="text-muted mb-1">وضعیت پروژه</h6>
                                <h5 class="mb-0">
                                    <span class="or-badge <?= $project['status'] ?? 'draft' ?>">
                                        <?= or_getStatusLabel($project['status'] ?? 'draft') ?>
                                    </span>
                                </h5>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($project['status'] === 'solved'): ?>
                    <hr>
                    <div class="alert alert-success">
                        <h6 class="mb-2"><i class="fas fa-check-circle"></i> مسئله حل شده است</h6>
                        <p class="mb-0">این پروژه با موفقیت حل شده و نتایج آن در تب "نتایج" قابل مشاهده است.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        <!-- ==================== تب ۲: گراف ==================== -->
        <?php elseif ($tab === 'graph'): ?>
            <div class="row g-4">
                <!-- لیست گره‌ها -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-primary text-white py-3">
                            <h6 class="mb-0">
                                <i class="fas fa-circle"></i> گره‌ها (<?= count($nodes) ?>)
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($nodes)): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p class="mb-0">هنوز گره‌ای تعریف نشده است</p>
                                </div>
                            <?php else: ?>
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($nodes as $node): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-circle text-primary me-2" style="font-size: 8px;"></i>
                                                <span><?= or_e($node['name']) ?></span>
                                            </div>
                                            <span class="badge bg-primary rounded-pill"><?= $node['id'] ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- لیست یال‌ها -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-info text-white py-3">
                            <h6 class="mb-0">
                                <i class="fas fa-arrow-right"></i> یال‌ها (<?= count($edges) ?>)
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($edges)): ?>
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p class="mb-0">هنوز یالی تعریف نشده است</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>مبدأ</th>
                                                <th>مقصد</th>
                                                <th>وزن/هزینه</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $nodeMap = [];
                                            foreach ($nodes as $n) {
                                                $nodeMap[$n['id']] = $n['name'];
                                            }
                                            foreach ($edges as $idx => $edge): 
                                            ?>
                                                <tr>
                                                    <td><?= $idx + 1 ?></td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">
                                                            <?= or_e($nodeMap[$edge['source_id']] ?? '?') ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark">
                                                            <?= or_e($nodeMap[$edge['destination_id']] ?? '?') ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success">
                                                            <?= number_format($edge['cost'], 2) ?>
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
                </div>
            </div>

            <!-- خلاصه گراف -->
            <?php if (!empty($nodes) && !empty($edges)): ?>
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="mb-3">
                                    <i class="fas fa-chart-bar text-info"></i> آمار گراف
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded text-center">
                                            <h3 class="mb-0 text-primary"><?= count($nodes) ?></h3>
                                            <small class="text-muted">تعداد گره‌ها</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded text-center">
                                            <h3 class="mb-0 text-info"><?= count($edges) ?></h3>
                                            <small class="text-muted">تعداد یال‌ها</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded text-center">
                                            <h3 class="mb-0 text-success">
                                                <?= count($edges) > 0 ? number_format(count($edges) / count($nodes), 2) : 0 ?>
                                            </h3>
                                            <small class="text-muted">میانگین درجه</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="p-3 bg-light rounded text-center">
                                            <h3 class="mb-0 text-warning">
                                                <?= number_format(array_sum(array_column($edges, 'cost')), 2) ?>
                                            </h3>
                                            <small class="text-muted">مجموع وزن‌ها</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        <!-- ==================== تب ۳: حل مسئله ==================== -->
        <?php elseif ($tab === 'solve'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <?php if (empty($nodes) || empty($edges)): ?>
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                            <h5>داده‌های کافی نیست</h5>
                            <p>برای حل مسئله، ابتدا باید گره‌ها و یال‌ها را تعریف کنید.</p>
                        </div>
                    <?php else: ?>
                        <h4 class="mb-4 text-center">
                            <i class="fas fa-calculator text-primary"></i>
                            حل مسئله کوتاه‌ترین مسیر
                        </h4>
                        
                        <form id="solveForm" class="mb-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">الگوریتم</label>
                                    <select class="form-select" id="algorithm" required>
                                        <option value="dijkstra">Dijkstra (از یک مبدأ به همه)</option>
                                        <option value="floyd">Floyd-Warshall (بین همه جفت گره‌ها)</option>
                                    </select>
                                </div>
                                <div class="col-md-6" id="sourceDiv">
                                    <label class="form-label">گره مبدأ</label>
                                    <select class="form-select" id="sourceId">
                                        <?php foreach ($nodes as $node): ?>
                                            <option value="<?= $node['id'] ?>"><?= or_e($node['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="button" class="btn btn-or-success btn-lg" onclick="runShortestSolve()">
                                    <i class="fas fa-play"></i> شروع حل مسئله
                                </button>
                            </div>
                        </form>

                        <div id="solveLoader" class="d-none text-center py-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2">در حال حل مسئله...</p>
                        </div>

                        <div id="solveResult" class="d-none mt-4">
                            <!-- نتایج اینجا نمایش داده می‌شود -->
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <!-- ==================== تب ۴: نتایج ==================== -->
        <?php elseif ($tab === 'result'): ?>
            <?php 
            $solution = json_decode($project['solution_data'] ?? 'null', true);
            ?>
            
            <?php if ($project['status'] !== 'solved' || empty($solution)): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-hourglass-half fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted mb-3">هنوز مسئله حل نشده است</h4>
                        <p class="text-muted mb-4">
                            وضعیت فعلی: <strong><?= or_getStatusLabel($project['status']) ?></strong><br>
                            لطفاً به تب "حل مسئله" بروید و الگوریتم را اجرا کنید.
                        </p>
                        <a href="<?= or_url('controller=shortest&action=show&id=' . $project['id'] . '&tab=solve') ?>" class="btn btn-or-primary">
                            <i class="fas fa-play"></i> رفتن به تب حل مسئله
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> مسئله با موفقیت حل شد!
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="mb-0"><i class="fas fa-route text-success"></i> مسیر بهینه</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">نتایج حل مسئله در اینجا نمایش داده خواهد شد.</p>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteProject(id, name) {
    if (confirm('آیا از حذف پروژه "' + name + '" مطمئن هستید؟ این عملیات غیرقابل بازگشت است.')) {
        window.location.href = '<?= or_url("controller=shortest&action=delete&id=") ?>' + id;
    }
}
</script>

<script>
// تغییر visibility بر اساس الگوریتم
document.getElementById('algorithm')?.addEventListener('change', function() {
    const sourceDiv = document.getElementById('sourceDiv');
    if (sourceDiv) {
        sourceDiv.style.display = this.value === 'dijkstra' ? 'block' : 'none';
    }
});

async function runShortestSolve() {
    const algorithm = document.getElementById('algorithm').value;
    const sourceId = document.getElementById('sourceId')?.value;
    const projectId = <?= $project['id'] ?>;
    
    const loader = document.getElementById('solveLoader');
    const resultDiv = document.getElementById('solveResult');
    const form = document.getElementById('solveForm');
    
    loader.classList.remove('d-none');
    resultDiv.classList.add('d-none');
    form.classList.add('d-none');

    try {
        const payload = {
            algorithm: algorithm,
            source_id: sourceId
        };

        const res = await fetch('<?= or_url("controller=shortest&action=solve&id=") ?>' + projectId, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        
        if (data.success) {
            displayResults(data.result);
        } else {
            alert('❌ خطا: ' + data.error);
            form.classList.remove('d-none');
        }
    } catch (e) {
        alert('❌ خطای شبکه: ' + e.message);
        form.classList.remove('d-none');
    } finally {
        loader.classList.add('d-none');
    }
}

function displayResults(result) {
    const resultDiv = document.getElementById('solveResult');
    let html = '';

    if (result.method === 'Dijkstra') {
        html = `
            <div class="alert alert-success">
                <h5><i class="fas fa-check-circle"></i> الگوریتم Dijkstra</h5>
                <p>مبدأ: <strong>${result.source}</strong></p>
                <p>تعداد مسیرها: ${result.total_paths}</p>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>مقصد</th>
                            <th>فاصله</th>
                            <th>مسیر</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${result.paths.map(p => `
                            <tr>
                                <td><strong>${p.destination}</strong></td>
                                <td><span class="badge bg-success">${p.distance}</span></td>
                                <td><small>${p.path_str}</small></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    } else if (result.method === 'Floyd-Warshall') {
        html = `
            <div class="alert alert-success">
                <h5><i class="fas fa-check-circle"></i> الگوریتم Floyd-Warshall</h5>
                <p>تعداد مسیرها: ${result.total_paths} (بین همه جفت گره‌ها)</p>
            </div>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>از</th>
                            <th>به</th>
                            <th>فاصله</th>
                            <th>مسیر</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${result.paths.map(p => `
                            <tr>
                                <td>${p.from}</td>
                                <td>${p.to}</td>
                                <td><span class="badge bg-success">${p.distance}</span></td>
                                <td><small>${p.path.join(' → ')}</small></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    resultDiv.innerHTML = html;
    resultDiv.classList.remove('d-none');
}
</script>