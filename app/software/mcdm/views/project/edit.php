<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">ویرایش پروژه</div>
            <div class="card-body">
                <form method="post" action="<?= mcdm_url('controller=project&action=update&id=' . (int)$project['id']) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="project-name">نام پروژه *</label>
                        <input id="project-name" type="text" name="name" class="form-control" required maxlength="255" value="<?= mcdm_e($project['name']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="project-description">توضیحات</label>
                        <textarea id="project-description" name="description" class="form-control" rows="4"><?= mcdm_e($project['description'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="project-industry">صنعت</label>
                        <select id="project-industry" name="industry" class="form-select">
                            <option value="general" <?= ($project['industry'] ?? 'general') === 'general' ? 'selected' : '' ?>>عمومی</option>
                            <?php foreach ($industries as $ind): ?>
                                <?php $industryValue = (string)($ind['code'] ?? $ind['id']); ?>
                                <option value="<?= mcdm_e($industryValue) ?>" <?= (string)($project['industry'] ?? '') === $industryValue ? 'selected' : '' ?>>
                                    <?= mcdm_e($ind['name_fa'] ?? $ind['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="alert alert-info" role="note">
                        روش تصمیم‌گیری و فاز پروژه در این فرم تغییر نمی‌کنند تا ساختار ارزیابی و نتایج فعلی حفظ شوند.
                        <?php if (!empty($project['method_name'])): ?>
                            <span class="d-block mt-1">روش فعلی: <?= mcdm_e($project['method_name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                    <a href="<?= mcdm_url('controller=project') ?>" class="btn btn-outline-secondary">انصراف</a>
                </form>
            </div>
        </div>
    </div>
</div>
