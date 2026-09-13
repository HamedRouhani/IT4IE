<div class="admin-container">
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>
    <div class="admin-content">
        <div class="admin-header">
            <h1>📋 مدیریت چک‌لیست‌ها</h1>
            <a href="/admin/checklist/create" class="btn-register">
                <i class="fas fa-plus"></i> چک‌لیست جدید
            </a>
        </div>

        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>عنوان</th>
                        <th>اسلاگ</th>
                        <th>سوالات</th>
                        <th>زمان</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($checklists as $cl): ?>
                    <tr>
                        <td><?= $cl['id'] ?></td>
                        <td><strong><?= htmlspecialchars($cl['title']) ?></strong></td>
                        <td><code><?= htmlspecialchars($cl['slug']) ?></code></td>
                        <td><?= $cl['questions_count'] ?></td>
                        <td><?= $cl['estimated_time'] ?> دقیقه</td>
                        <td>
                            <span class="status-badge <?= $cl['is_active'] ? 'published' : 'draft' ?>">
                                <?= $cl['is_active'] ? 'فعال' : 'غیرفعال' ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="/checklist/view/<?= htmlspecialchars($cl['slug']) ?>" class="btn-view" target="_blank">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="/admin/checklist/edit/<?= $cl['id'] ?>" class="btn-edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="/admin/checklist/questions/<?= $cl['id'] ?>" class="btn-edit">
                                <i class="fas fa-question-circle"></i>
                            </a>
                            <button type="button" class="btn-delete"
                                    onclick="if(confirm('حذف شود؟')) window.location='/admin/checklist/delete/<?= $cl['id'] ?>'">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>