<?php
$pageTitle = 'فرآیندها - PMBOK';
$activePage = 'task';
?>

<div class="page-header">
    <div>
        <h2><i class="fas fa-tasks"></i> فرآیندهای استاندارد PMBOK</h2>
        <p class="text-muted"><?= $stats['all_tasks'] ?? 0 ?> فرآیند در <?= $stats['ka_count'] ?? 0 ?> حوزه دانشی</p>
    </div>
</div>

<!-- فیلتر -->
<div class="card filter-card">
    <form method="GET" class="filter-form">
        <input type="hidden" name="controller" value="task">
        <div class="filter-grid">
            <div class="form-group">
                <label class="form-label">جستجو</label>
                <input type="text" name="search" class="form-control" placeholder="کد یا نام فرآیند..." 
                       value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">حوزه دانشی</label>
                <select name="ka_id" class="form-select">
                    <option value="">همه حوزه‌ها</option>
                    <?php foreach ($knowledgeAreas as $ka): ?>
                        <option value="<?= $ka['id'] ?>" <?= ($ka_id ?? 0) == $ka['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ka['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="align-self: end;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> اعمال فیلتر
                </button>
                <a href="?controller=task" class="btn btn-secondary">
                    <i class="fas fa-times"></i> پاک کردن
                </a>
            </div>
        </div>
    </form>
</div>

<!-- لیست فرآیندها -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>کد</th>
                    <th>نام فرآیند</th>
                    <th>حوزه دانشی</th>
                    <th>تکنیک‌ها</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tasks)): ?>
                    <tr><td colspan="5" class="text-center text-muted">فرآیندی یافت نشد.</td></tr>
                <?php else: ?>
                    <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($task['code']) ?></code></td>
                        <td>
                            <a href="?controller=task&action=show&id=<?= $task['id'] ?>" style="font-weight: 600;">
                                <?= htmlspecialchars($task['name']) ?>
                            </a>
                        </td>
                        <td>
                            <a href="?controller=knowledgeArea&action=show&id=<?= $task['knowledge_area_id'] ?>">
                                <?= htmlspecialchars($task['ka_name']) ?>
                            </a>
                        </td>
                        <td>
                            <span class="badge badge-primary"><?= $task['technique_count'] ?? 0 ?></span>
                        </td>
                        <td>
                            <a href="?controller=task&action=show&id=<?= $task['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>