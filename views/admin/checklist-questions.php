<div class="admin-container">
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>
    <div class="admin-content">
        <div class="admin-header">
            <h1>❓ مدیریت سوالات: <?= htmlspecialchars($checklist['title']) ?></h1>
            <div style="display: flex; gap: 8px;">
                <a href="/admin/checklist/edit/<?= $checklist['id'] ?>" class="btn-register" style="padding: 8px 16px; font-size: 13px; text-decoration: none;">
                    <i class="fas fa-edit"></i> ویرایش مشخصات چک‌لیست
                </a>
                <a href="/admin/checklists" class="btn-register" style="padding: 8px 16px; font-size: 13px; text-decoration: none;">
                    <i class="fas fa-arrow-right"></i> بازگشت
                </a>
            </div>
        </div>

        <?php $isEdit = !empty($editQuestion); ?>

        <!-- فرم افزودن / ویرایش -->
        <div class="admin-form" style="margin-bottom: 20px;">
            <h3><?= $isEdit ? '✏️ ویرایش سوال #' . $editQuestion['id'] : '➕ افزودن سوال جدید' ?></h3>
            <?php if ($isEdit): ?>
                <p style="font-size: 0.85rem; color: var(--gray-dark); margin-bottom: 10px;">
                    در حال ویرایش سوال هستید.
                    <a href="/admin/checklist/questions/<?= $checklist['id'] ?>" style="color: var(--primary); font-weight: 700;">انصراف</a>
                </p>
            <?php else: ?>
                <p style="font-size: 0.85rem; color: var(--gray-dark); margin-bottom: 10px;">
                    سوال جدید به <strong>انتهای لیست</strong> اضافه می‌شود؛ پس از افزودن، با دکمه‌های
                    <i class="fas fa-arrow-up"></i> و <i class="fas fa-arrow-down"></i>
                    آن را دقیقاً در موقعیت دلخواه قرار دهید.
                </p>
            <?php endif; ?>

            <form method="POST" action="<?= $isEdit ? '/admin/checklist/question/edit/' . $editQuestion['id'] : '/admin/checklist/question/add/' . $checklist['id'] ?>">
                <div class="form-group">
                    <label>متن سوال *</label>
                    <textarea name="question_text" required placeholder="مثال: آیا مستندات نیازمندی‌های پروژه مبهم یا ناقص است؟"><?= htmlspecialchars($isEdit ? $editQuestion['question_text'] : '') ?></textarea>
                    <small style="color: var(--gray); font-size: 0.75rem;">سوال را به صورت «شاخص ریسک» بنویسید تا پاسخ «بله» به معنای ریسک بالا باشد.</small>
                </div>
                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 12px;">
                    <div class="form-group">
                        <label>دسته (گروه تحلیل)</label>
                        <input type="text" name="category" value="<?= htmlspecialchars($isEdit ? $editQuestion['category'] : 'general') ?>">
                        <small style="color: var(--gray); font-size: 0.75rem;">مثل sign1 تا sign5 — سوالات هم‌دسته با هم تحلیل می‌شوند.</small>
                    </div>
                    <div class="form-group">
                        <label>وزن (اهمیت)</label>
                        <input type="number" name="weight" min="1" max="5" value="<?= $isEdit ? $editQuestion['weight'] : 3 ?>">
                    </div>
                    <div class="form-group">
                        <label>ترتیب</label>
                        <input type="number" name="sort_order" value="<?= $isEdit ? $editQuestion['sort_order'] : 0 ?>">
                    </div>
                    <div class="form-group">
                        <label>وضعیت</label>
                        <select name="is_active">
                            <option value="1" <?= ($isEdit && $editQuestion['is_active']) ? 'selected' : '' ?>>فعال</option>
                            <option value="0" <?= ($isEdit && !$editQuestion['is_active']) ? 'selected' : '' ?>>غیرفعال</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn-admin-submit">
                    <i class="fas fa-<?= $isEdit ? 'save' : 'plus' ?>"></i>
                    <?= $isEdit ? 'ذخیره تغییرات' : 'افزودن سوال' ?>
                </button>
            </form>
        </div>

        <!-- لیست سوالات با دکمه‌های جابه‌جایی -->
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>موقعیت</th>
                        <th>سوال</th>
                        <th>دسته</th>
                        <th>وزن</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total = count($questions); foreach ($questions as $i => $q): ?>
                    <tr>
                        <td><strong><?= $i + 1 ?></strong></td>
                        <td><?= htmlspecialchars($q['question_text']) ?></td>
                        <td><code><?= htmlspecialchars($q['category']) ?></code></td>
                        <td><?= $q['weight'] ?></td>
                        <td>
                            <span class="status-badge <?= $q['is_active'] ? 'published' : 'draft' ?>">
                                <?= $q['is_active'] ? 'فعال' : 'غیرفعال' ?>
                            </span>
                        </td>
                        <td class="actions">
                            <?php if ($i > 0): ?>
                            <a href="/admin/checklist/question/move/<?= $q['id'] ?>?dir=up&checklist_id=<?= $checklist['id'] ?>" class="btn-edit" title="انتقال به بالا">
                                <i class="fas fa-arrow-up"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($i < $total - 1): ?>
                            <a href="/admin/checklist/question/move/<?= $q['id'] ?>?dir=down&checklist_id=<?= $checklist['id'] ?>" class="btn-edit" title="انتقال به پایین">
                                <i class="fas fa-arrow-down"></i>
                            </a>
                            <?php endif; ?>
                            <a href="/admin/checklist/questions/<?= $checklist['id'] ?>?edit=<?= $q['id'] ?>" class="btn-edit" title="ویرایش">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="/admin/checklist/question/delete/<?= $q['id'] ?>?checklist_id=<?= $checklist['id'] ?>" class="btn-delete" title="حذف" onclick="return confirm('سوال حذف شود؟')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>