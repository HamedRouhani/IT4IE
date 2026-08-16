<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-user-edit me-2"></i>ویرایش پروفایل ممیز</h4>
        <a href="<?= CURRENT_MODULE_URL ?>?controller=auditors" class="btn btn-outline-secondary btn-sm">بازگشت</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?= CURRENT_MODULE_URL ?>?controller=auditors&action=update&id=<?= $auditor['id'] ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">نام و نام خانوادگی <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" required
                               value="<?= htmlspecialchars($auditor['full_name']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ایمیل</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($auditor['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">تلفن</label>
                        <input type="text" name="phone" class="form-control"
                               value="<?= htmlspecialchars($auditor['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">صلاحیت / مدرک</label>
                        <input type="text" name="qualification" class="form-control"
                               value="<?= htmlspecialchars($auditor['qualification'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">تخصص</label>
                        <input type="text" name="specialization" class="form-control"
                               value="<?= htmlspecialchars($auditor['specialization'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">سال تجربه</label>
                        <input type="number" name="experience_years" class="form-control" min="0"
                               value="<?= (int)$auditor['experience_years'] ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">تعداد ممیزی‌ها</label>
                        <input type="number" name="audit_count" class="form-control" min="0"
                               value="<?= (int)$auditor['audit_count'] ?>">
                    </div>

                    <div class="col-12"><hr></div>

                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="lead_auditor" id="lead_auditor"
                                <?= $auditor['lead_auditor'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="lead_auditor">
                                <strong>سرممیز (Lead Auditor)</strong>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="iso_9001_certified" id="iso9001"
                                <?= $auditor['iso_9001_certified'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="iso9001">گواهینامه ISO 9001</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="iso_19011_certified" id="iso19011"
                                <?= $auditor['iso_19011_certified'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="iso19011">گواهینامه ISO 19011 (ممیزی)</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                <?= $auditor['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">فعال</label>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">سایر گواهینامه‌ها</label>
                        <input type="text" name="other_certifications" class="form-control"
                               value="<?= htmlspecialchars($auditor['other_certifications'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">یادداشت</label>
                        <textarea name="notes" rows="2" class="form-control"><?= htmlspecialchars($auditor['notes'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                </div>
            </form>
        </div>
    </div>
</div>