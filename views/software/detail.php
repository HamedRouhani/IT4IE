<div class="software-detail-page">
    <div class="software-detail-container">
        <div class="software-detail-header">
            <div class="software-detail-icon">
                <i class="fas fa-cube"></i>
            </div>
            <div class="software-detail-title">
                <h1><?php echo $software['name']; ?></h1>
                <span class="software-detail-status status-<?php echo $software['status']; ?>">
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
        </div>
        
        <div class="software-detail-body">
            <div class="software-detail-info">
                <h3>📋 درباره نرم‌افزار</h3>
                <p><?php echo nl2br($software['description']); ?></p>
                
                <?php if ($software['features']): 
                    $features = json_decode($software['features'], true);
                ?>
                    <h3>✨ ویژگی‌ها</h3>
                    <ul class="feature-list">
                        <?php foreach ($features as $feature): ?>
                            <li><i class="fas fa-check-circle"></i> <?php echo $feature; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                
                <?php if ($software['tech_stack']): 
                    $techStack = json_decode($software['tech_stack'], true);
                ?>
                    <h3>🛠️ تکنولوژی‌ها</h3>
                    <div class="tech-stack-list">
                        <?php foreach ($techStack as $tech): ?>
                            <span class="tech-tag-large"><?php echo $tech; ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="software-detail-sidebar">
                <div class="detail-card">
                    <h4>📊 اطلاعات</h4>
                    <ul>
                        <li><strong>نسخه:</strong> <?php echo $software['version'] ?? '1.0.0'; ?></li>
                        <li><strong>وضعیت:</strong> <?php echo $statusLabels[$software['status']] ?? $software['status']; ?></li>
                        <li><strong>دانلود:</strong> <?php echo number_format($software['download_count'] ?? 0); ?></li>
                    </ul>
                </div>
                
                <?php if ($software['github_url'] || $software['demo_url'] || $software['documentation_url']): ?>
                    <div class="detail-card">
                        <h4>🔗 لینک‌ها</h4>
                        <ul>
                            <?php if ($software['demo_url']): ?>
                                <li><a href="<?php echo $software['demo_url']; ?>" target="_blank"><i class="fas fa-external-link-alt"></i> نسخه آزمایشی</a></li>
                            <?php endif; ?>
                            <?php if ($software['github_url']): ?>
                                <li><a href="<?php echo $software['github_url']; ?>" target="_blank"><i class="fab fa-github"></i> گیت‌هاب</a></li>
                            <?php endif; ?>
                            <?php if ($software['documentation_url']): ?>
                                <li><a href="<?php echo $software['documentation_url']; ?>" target="_blank"><i class="fas fa-book"></i> مستندات</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <a href="/software" class="btn-back-software">
                    <i class="fas fa-arrow-right"></i> بازگشت به لیست
                </a>
            </div>
        </div>
    </div>
</div>