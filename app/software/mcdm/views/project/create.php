<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">ایجاد پروژه تصمیم‌گیری</div>
            <div class="card-body">
                <form method="post" action="<?= mcdm_url('controller=project&action=store') ?>">
                    <div class="mb-3">
                        <label class="form-label">نام پروژه *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">توضیحات</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">روش تصمیم‌گیری</label>
                        <select name="method_id" class="form-select">
                            <option value="">— انتخاب کنید —</option>
                            <?php foreach ($methods as $m): ?>
                                <option value="<?= (int)$m['id'] ?>">
                                    <?= mcdm_e($m['name_fa'] ?? $m['name']) ?>
                                    (<?= mcdm_getMethodCategoryLabel($m['category'] ?? '') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">صنعت</label>
                        <select name="industry" class="form-select">
                            <option value="general">عمومی</option>
                            <?php foreach ($industries as $ind): ?>
                                <option value="<?= mcdm_e($ind['code'] ?? $ind['id']) ?>">
                                    <?= mcdm_e($ind['name_fa'] ?? $ind['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">ایجاد پروژه</button>
                    <a href="<?= mcdm_url('controller=project') ?>" class="btn btn-outline-secondary">انصراف</a>
                </form>
            </div>
        </div>
    </div>
</div>