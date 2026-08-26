<?php
/**
 * نمایش جزئیات پروژه OR
 * مسیر: app/software/or/views/project/view.php
 */
?>

<div class="container-fluid py-4">
    <!-- هدر صفحه -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-project-diagram text-primary"></i>
                <?= htmlspecialchars($project['name']) ?>
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('') ?>">OR Analyzer</a></li>
                    <li class="breadcrumb-item"><a href="<?= or_url('controller=project') ?>">پروژه‌ها</a></li>
                    <li class="breadcrumb-item active"><?= htmlspecialchars($project['name']) ?></li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="<?= or_url('controller=project&action=edit&id=' . $project['id']) ?>" class="btn btn-outline-warning">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <button type="button" class="btn btn-outline-danger" 
                    onclick="deleteProject(<?= $project['id'] ?>, '<?= htmlspecialchars($project['name']) ?>')">
                <i class="fas fa-trash"></i> حذف
            </button>
        </div>
    </div>

    <!-- کارت اطلاعات پروژه -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="text-center">
                        <small class="text-muted">نوع مسئله</small>
                        <h5 class="mb-0">
                            <span class="badge bg-secondary">
                                <?= or_getProblemTypeLabel($project['problem_type_code'] ?? '') ?>
                            </span>
                        </h5>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <small class="text-muted">روش حل</small>
                        <h5 class="mb-0"><?= htmlspecialchars($project['method_name'] ?? '-') ?></h5>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <small class="text-muted">ابعاد مسئله</small>
                        <h5 class="mb-0">
                            <?= $project['variables_count'] ?? 0 ?> متغیر × 
                            <?= $project['constraints_count'] ?? 0 ?> محدودیت
                        </h5>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <small class="text-muted">وضعیت</small>
                        <h5 class="mb-0">
                            <span class="badge bg-<?= or_getStatusColor($project['status'] ?? 'draft') ?>">
                                <?= or_getStatusLabel($project['status'] ?? 'draft') ?>
                            </span>
                        </h5>
                    </div>
                </div>
            </div>
            <?php if (!empty($project['description'])): ?>
                <hr>
                <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($project['description'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- تب‌ها -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'info' ? 'active' : '' ?>" 
               href="<?= or_url('controller=project&action=show&id=' . $project['id'] . '&tab=info') ?>">
                <i class="fas fa-info-circle"></i> اطلاعات
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'nodes' ? 'active' : '' ?>" 
               href="<?= or_url('controller=project&action=show&id=' . $project['id'] . '&tab=nodes') ?>">
                <i class="fas fa-circle-nodes"></i> منابع و مقاصد
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'matrix' ? 'active' : '' ?>" 
               href="<?= or_url('controller=project&action=show&id=' . $project['id'] . '&tab=matrix') ?>">
                <i class="fas fa-table-cells"></i> ماتریس هزینه
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'solve' ? 'active' : '' ?>" 
               href="<?= or_url('controller=project&action=show&id=' . $project['id'] . '&tab=solve') ?>">
                <i class="fas fa-play"></i> حل مسئله
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'result' ? 'active' : '' ?>" 
               href="<?= or_url('controller=project&action=show&id=' . $project['id'] . '&tab=result') ?>">
                <i class="fas fa-chart-line"></i> نتیجه و گزارش
            </a>
        </li>
    </ul>

    <!-- محتوای تب‌ها -->
    <div class="tab-content">
        
        <!-- تب اطلاعات -->
        <?php if ($tab === 'info'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-4">اطلاعات پروژه</h5>
                    <table class="table">
                        <tr>
                            <th class="w-25">نام پروژه:</th>
                            <td><?= htmlspecialchars($project['name']) ?></td>
                        </tr>
                        <tr>
                            <th>نوع مسئله:</th>
                            <td><?= htmlspecialchars($project['problem_type_name'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th>روش حل:</th>
                            <td><?= htmlspecialchars($project['method_name'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th>هدف:</th>
                            <td>
                                <?php if (($project['objective'] ?? 'minimize') === 'minimize'): ?>
                                    <span class="badge bg-info">کمینه‌سازی</span>
                                <?php else: ?>
                                    <span class="badge bg-success">بیشینه‌سازی</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>تعداد متغیر:</th>
                            <td><?= $project['variables_count'] ?? 0 ?></td>
                        </tr>
                        <tr>
                            <th>تعداد محدودیت:</th>
                            <td><?= $project['constraints_count'] ?? 0 ?></td>
                        </tr>
                        <tr>
                            <th>تاریخ ایجاد:</th>
                            <td><?= or_showDate($project['created_at'] ?? '', 'Y/m/d H:i') ?></td>
                        </tr>
                        <tr>
                            <th>آخرین ویرایش:</th>
                            <td><?= or_showDate($project['updated_at'] ?? '', 'Y/m/d H:i') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- تب منابع و مقاصد -->
        <?php if ($tab === 'nodes'): ?>
            <div class="row g-4">
                <!-- منابع -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-warehouse"></i> منابع (Sources)
                                <span class="badge bg-light text-dark ms-2"><?= count($sources) ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($sources)): ?>
                                <p class="text-muted text-center">هنوز منبعی اضافه نشده</p>
                            <?php else: ?>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>نام</th>
                                            <th>عرضه</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sources as $source): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($source['name']) ?></td>
                                                <td><strong><?= number_format($source['capacity']) ?></strong></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteNode(<?= $source['id'] ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-info">
                                            <th>مجموع:</th>
                                            <th><?= number_format(array_sum(array_column($sources, 'capacity'))) ?></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            <?php endif; ?>
                            
                            <hr>
                            <form id="addSourceForm" class="row g-2">
                                <div class="col-5">
                                    <input type="text" class="form-control form-control-sm" 
                                           name="name" placeholder="نام منبع" required>
                                </div>
                                <div class="col-5">
                                    <input type="number" class="form-control form-control-sm" 
                                           name="capacity" placeholder="عرضه" required>
                                </div>
                                <div class="col-4">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">
                                        <i class="fas fa-plus"></i> افزودن
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- مقاصد -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-map-marker-alt"></i> مقاصد (Destinations)
                                <span class="badge bg-light text-dark ms-2"><?= count($destinations) ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($destinations)): ?>
                                <p class="text-muted text-center">هنوز مقصدی اضافه نشده</p>
                            <?php else: ?>
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>نام</th>
                                            <th>تقاضا</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($destinations as $dest): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($dest['name']) ?></td>
                                                <td><strong><?= number_format($dest['capacity']) ?></strong></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteNode(<?= $dest['id'] ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-info">
                                            <th>مجموع:</th>
                                            <th><?= number_format(array_sum(array_column($destinations, 'capacity'))) ?></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            <?php endif; ?>
                            
                            <hr>
                            <form id="addDestForm" class="row g-2">
                                <div class="col-5">
                                    <input type="text" class="form-control form-control-sm" 
                                           name="name" placeholder="نام مقصد" required>
                                </div>
                                <div class="col-5">
                                    <input type="number" class="form-control form-control-sm" 
                                           name="capacity" placeholder="تقاضا" required>
                                </div>
                                <div class="col-4">
                                    <button type="submit" class="btn btn-sm btn-info w-100">
                                        <i class="fas fa-plus"></i> افزودن
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- دکمه توازن -->
            <?php if ($balance['supply'] !== $balance['demand']): ?>
                <div class="alert alert-warning mt-4">
                    <i class="fas fa-exclamation-triangle"></i>
                    مسئله نامتوازن است! 
                    عرضه: <?= number_format($balance['supply']) ?>، تقاضا: <?= number_format($balance['demand']) ?>
                    <button class="btn btn-sm btn-warning ms-3" onclick="balanceProject()">
                        <i class="fas fa-balance-scale"></i> متوازن‌سازی خودکار
                    </button>
                </div>
            <?php else: ?>
                <div class="alert alert-success mt-4">
                    <i class="fas fa-check-circle"></i>
                    مسئله متوازن است. عرضه و تقاضا: <?= number_format($balance['supply']) ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- تب ماتریس هزینه -->
        <?php if ($tab === 'matrix'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-4">ماتریس هزینه حمل و نقل</h5>
                    
                    <?php if (empty($sources) || empty($destinations)): ?>
                        <div class="alert alert-warning">
                            ابتدا منابع و مقاصد را در تب "منابع و مقاصد" تعریف کنید.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center">منبع \ مقصد</th>
                                        <?php foreach ($destinations as $dest): ?>
                                            <th class="text-center"><?= htmlspecialchars($dest['name']) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sources as $source): ?>
                                        <tr>
                                            <th><?= htmlspecialchars($source['name']) ?></th>
                                            <?php foreach ($destinations as $dest): ?>
                                                <?php 
                                                $edgeKey = $source['id'] . '_' . $dest['id'];
                                                $edgeCost = '';
                                                foreach ($edges as $edge) {
                                                    if ($edge['source_id'] == $source['id'] && $edge['destination_id'] == $dest['id']) {
                                                        $edgeCost = $edge['cost'] ?? '';
                                                        break;
                                                    }
                                                }
                                                ?>
                                                <td>
                                                    <input type="number" step="0.01"
                                                           class="form-control form-control-sm cost-input"
                                                           data-source="<?= $source['id'] ?>"
                                                           data-dest="<?= $dest['id'] ?>"
                                                           value="<?= $edgeCost ?>"
                                                           placeholder="∞">
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <button class="btn btn-primary" onclick="saveMatrix()">
                            <i class="fas fa-save"></i> ذخیره ماتریس
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- تب حل مسئله -->
        <?php if ($tab === 'solve'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-4">حل مسئله</h5>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6>انتخاب روش حل:</h6>
                            <select class="form-select mb-3" id="solveMethod">
                                <option value="NWC">گوشه شمال غربی (NWC)</option>
                                <option value="LCM">کمترین هزینه (LCM)</option>
                                <option value="VAM" selected>تقریب ووگل (VAM)</option>
                            </select>
                            
                            <button class="btn btn-success btn-lg w-100" onclick="solveProject()">
                                <i class="fas fa-play"></i> حل مسئله
                            </button>
                        </div>
                        
                        <div class="col-md-6">
                            <h6>راهنما:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> <strong>NWC:</strong> سریع‌ترین روش</li>
                                <li><i class="fas fa-check text-success"></i> <strong>LCM:</strong> اولویت کمترین هزینه</li>
                                <li><i class="fas fa-check text-success"></i> <strong>VAM:</strong> نزدیک‌ترین به جواب بهینه</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- تب نتیجه -->
        <?php if ($tab === 'result'): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-4">نتیجه حل مسئله</h5>
                    
                    <?php if (empty($result)): ?>
                        <div class="alert alert-info">
                            هنوز مسئله حل نشده است. به تب "حل مسئله" بروید.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            <h5>
                                <i class="fas fa-check-circle"></i> مسئله با موفقیت حل شد!
                            </h5>
                            <p class="mb-0">
                                هزینه بهینه: <strong><?= number_format($result['total_cost'], 2) ?></strong>
                                <br>
                                تعداد تکرار: <?= $result['iterations_count'] ?? 0 ?>
                            </p>
                        </div>

                        <!-- جدول تخصیص‌ها -->
                        <h6 class="mt-4">جزئیات تخصیص:</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>منبع</th>
                                        <th>مقصد</th>
                                        <th>مقدار</th>
                                        <th>هزینه واحد</th>
                                        <th>هزینه کل</th>
                                        <th>سلول پایه</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allocations as $alloc): ?>
                                        <tr>
                                            <td><?= $alloc['source_id'] ?></td>
                                            <td><?= $alloc['destination_id'] ?></td>
                                            <td><strong><?= number_format($alloc['allocated_amount']) ?></strong></td>
                                            <td><?= number_format($alloc['unit_cost'], 2) ?></td>
                                            <td><strong><?= number_format($alloc['total_cost'], 2) ?></strong></td>
                                            <td>
                                                <?php if ($alloc['is_basic_cell']): ?>
                                                    <span class="badge bg-success">بله</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">خیر</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <a href="<?= or_url('controller=report&action=projectReport&id=' . $project['id']) ?>" 
                           class="btn btn-info">
                            <i class="fas fa-file-pdf"></i> گزارش کامل
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
const projectId = <?= $project['id'] ?>;

function deleteProject(id, name) {
    if (confirm('آیا از حذف پروژه "' + name + '" مطمئن هستید؟')) {
        window.location.href = '<?= or_url('controller=project&action=delete&id=') ?>' + id;
    }
}

function deleteNode(nodeId) {
    if (confirm('آیا از حذف این گره مطمئن هستید؟')) {
        fetch('<?= or_url('controller=project&action=deleteNode&id=') ?>' + projectId, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'node_id=' + nodeId
        }).then(() => window.location.reload());
    }
}

function balanceProject() {
    fetch('<?= or_url('controller=project&action=balance&id=') ?>' + projectId, {
        method: 'POST'
    }).then(() => window.location.reload());
}

function saveMatrix() {
    const inputs = document.querySelectorAll('.cost-input');
    const matrix = {};
    
    inputs.forEach(input => {
        const source = input.dataset.source;
        const dest = input.dataset.dest;
        const value = input.value || '';
        
        if (!matrix[source]) matrix[source] = {};
        matrix[source][dest] = value;
    });
    
    fetch('<?= or_url('controller=project&action=saveMatrix&id=') ?>' + projectId, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({matrix: matrix})
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              alert('ماتریس با موفقیت ذخیره شد!');
          } else {
              alert('خطا: ' + data.error);
          }
      });
}

function solveProject() {
    const method = document.getElementById('solveMethod').value;
    
    fetch('<?= or_url('controller=solver&action=solve&id=') ?>' + projectId, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({method_code: method})
    }).then(response => response.json())
      .then(data => {
          if (data.success) {
              alert('مسئله با موفقیت حل شد! هزینه بهینه: ' + data.optimal_cost);
              window.location.href = '<?= or_url('controller=project&action=show&id=') ?>' + projectId + '&tab=result';
          } else {
              alert('خطا در حل مسئله: ' + data.error);
          }
      });
}

// فرم افزودن منبع
document.getElementById('addSourceForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('type', 'source');
    
    fetch('<?= or_url('controller=project&action=addNode&id=') ?>' + projectId, {
        method: 'POST',
        body: formData
    }).then(() => window.location.reload());
});

// فرم افزودن مقصد
document.getElementById('addDestForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('type', 'destination');
    
    fetch('<?= or_url('controller=project&action=addNode&id=') ?>' + projectId, {
        method: 'POST',
        body: formData
    }).then(() => window.location.reload());
});
</script>