<div class="container mt-4" dir="rtl">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">
                <i class="fas fa-sitemap me-2"></i>
                انتخاب نوع مسئله
                <?php if (!empty($method_info)): ?>
                    <small class="d-block mt-1 text-light">
                        (مسائل سازگار با روش: <?= htmlspecialchars($method_info['name_fa']) ?>)
                    </small>
                <?php endif; ?>
            </h3>
        </div>
        <div class="card-body">
            <?php if (empty($problem_types)): ?>
                <div class="alert alert-warning">نوع مسئله‌ای تعریف نشده است.</div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($problem_types as $type): 
                        $is_recommended = ($filtered_type == $type['id']);
                        $controller = $type_to_controller[$type['id']] ?? 'dashboard';
                    ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 <?= $is_recommended ? 'border-success border-3 shadow' : '' ?>">
                                <?php if ($is_recommended): ?>
                                    <div class="card-header bg-success text-white text-center">
                                        <i class="fas fa-star me-1"></i>
                                        پیشنهادی برای روش انتخابی
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <span class="badge bg-secondary me-2"><?= $type['code'] ?></span>
                                        <?= htmlspecialchars($type['name_fa']) ?>
                                    </h5>
                                    <p class="card-text text-muted">
                                        <?= htmlspecialchars($type['description'] ?? '') ?>
                                    </p>
                                </div>
                                
                                <div class="card-footer bg-transparent">
                                    <!-- گام بعدی: انتخاب روش از بین روش‌های این مسئله -->
                                    <a href="?controller=problem_type&action=methods&problem_type_id=<?= $type['id'] ?>&method_id=<?= $method_id ?>" 
                                       class="btn <?= $is_recommended ? 'btn-success' : 'btn-outline-primary' ?> w-100">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        مشاهده روش‌های حل
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <div class="mt-4">
                <a href="?controller=dashboard" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-2"></i>
                    بازگشت به داشبورد
                </a>
            </div>
        </div>
    </div>
</div>