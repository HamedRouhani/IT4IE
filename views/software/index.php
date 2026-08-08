<div class="software-page">
    <div class="software-header">
        <div class="software-header-content">
            <span class="software-badge">🧩 نرم‌افزارهای تخصصی</span>
            <h1 class="software-title">
                ابزارهای هوشمند <span>IT4IE</span>
            </h1>
            <p class="software-subtitle">
                مجموعه‌ای از نرم‌افزارهای تخصصی برای مدیریت پروژه، تحلیل کسب‌وکار و بهینه‌سازی فرآیندها
            </p>
            <div class="software-stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo $totalSoftware; ?></span>
                    <span class="stat-label">نرم‌افزار</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo number_format($totalDownloads); ?></span>
                    <span class="stat-label">دانلود</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="software-grid-container">
        <div class="software-grid">
            <?php foreach ($softwareList as $software): ?>
                <div class="software-card">
                    <div class="software-card-header">
                        <div class="software-icon">
                            <i class="fas fa-cube"></i>
                        </div>
                        <span class="software-status status-<?php echo $software['status']; ?>">
                            <?php 
                                $statusLabels = [
                                    'development' => 'در حال توسعه',
                                    'beta' => 'بتا',
                                    'stable' => 'پایدار',
                                    'deprecated' => 'منسوخ'
                                ];
                                echo $statusLabels[$software['status']] ?? $software['status'];
                            ?>
                        </span>
                    </div>
                    <h3 class="software-name">
                        <a href="/software/<?php echo $software['slug']; ?>">
                            <?php echo $software['name']; ?>
                        </a>
                    </h3>
                    <p class="software-description">
                        <?php echo truncate_text($software['description'], 120); ?>
                    </p>
                    <div class="software-tech">
                        <?php 
                        $techStack = json_decode($software['tech_stack'], true);
                        if ($techStack && is_array($techStack)):
                            foreach (array_slice($techStack, 0, 3) as $tech):
                        ?>
                            <span class="tech-tag"><?php echo $tech; ?></span>
                        <?php endforeach; endif; ?>
                    </div>
                    <div class="software-card-footer">
                        <span class="software-version">نسخه <?php echo $software['version'] ?? '1.0'; ?></span>
                        <a href="/software/<?php echo $software['slug']; ?>" class="btn-software">
                            مشاهده <i class="fas fa-arrow-left"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>