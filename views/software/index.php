<div class="container" style="padding: 40px 0;">
    
    <div class="babok-page-header" style="text-align: center; margin-bottom: 40px;">
        <h1 style="font-size: 2.5rem; font-weight: 800; color: var(--dark);">
            📦 نرم‌افزارهای تخصصی 
            <span style="color: var(--primary);">IT4IE</span>
        </h1>
        <p style="color: var(--gray-dark); font-size: 1.1rem; max-width: 600px; margin: 10px auto 0;">
            ابزارها و تحلیلگرهای پیشرفته برای مدیریت کسب‌وکار و پروژه
        </p>
    </div>

    <div class="babok-stats" style="margin-bottom: 40px;">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="fas fa-cubes"></i></div>
            <div class="stat-info">
                <h3><?php echo $totalSoftware ?? 0; ?></h3>
                <p>نرم‌افزار فعال</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="fas fa-download"></i></div>
            <div class="stat-info">
                <h3><?php echo number_format($totalDownloads ?? 0); ?></h3>
                <p>کل دانلودها</p>
            </div>
        </div>
    </div>

    <div class="projects-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 25px;">
        <?php if (isset($softwareList) && !empty($softwareList)): ?>
            <?php foreach ($softwareList as $software): ?>
                <?php 
                $features = json_decode($software['features'] ?? '[]', true);
                $techStack = json_decode($software['tech_stack'] ?? '[]', true);
                $iconClass = 'fas fa-cubes';
                if (stripos($software['name'], 'babok') !== false) $iconClass = 'fas fa-robot';
                if (stripos($software['name'], 'pmbok') !== false) $iconClass = 'fas fa-chart-line';
                ?>
                <div class="project-card" style="padding: 25px; transition: all 0.3s ease; position: relative; display: flex; flex-direction: column; justify-content: space-between;">
                    
                    <div style="position: absolute; top: 15px; right: 15px;">
                        <?php 
                        $statusColors = [
                            'beta' => ['bg' => '#FEF3C7', 'color' => '#D97706', 'text' => 'Beta'],
                            'stable' => ['bg' => '#D1FAE5', 'color' => '#16A34A', 'text' => 'Stable'],
                            'development' => ['bg' => '#DBEAFE', 'color' => '#2563EB', 'text' => 'Dev']
                        ];
                        $status = $software['status'] ?? 'development';
                        $style = $statusColors[$status] ?? $statusColors['development'];
                        ?>
                        <span style="background: <?php echo $style['bg']; ?>; color: <?php echo $style['color']; ?>; padding: 3px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 700;"><?php echo $style['text']; ?></span>
                    </div>

                    <div>
                        <div class="project-card-header" style="margin-bottom: 12px;">
                            <h3 style="font-size: 1.4rem; margin: 0; display: flex; align-items: center; gap: 10px;">
                                <i class="<?php echo $iconClass; ?>" style="color: var(--primary); font-size: 1.8rem;"></i>
                                <?php echo htmlspecialchars($software['name']); ?>
                            </h3>
                            <span style="font-size: 0.85rem; color: var(--gray); margin-top: 4px; display: block;">
                                <i class="fas fa-tag"></i> نسخه <?php echo htmlspecialchars($software['version'] ?? '1.0'); ?>
                            </span>
                        </div>
                        <p class="project-description" style="color: var(--gray-dark); line-height: 1.7; margin-bottom: 15px;">
                            <?php echo htmlspecialchars($software['description']); ?>
                        </p>
                        <?php if (!empty($techStack)): ?>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 15px;">
                            <?php foreach ($techStack as $tech): ?>
                                <span style="background: #E0E7FF; color: #4F46E5; padding: 2px 10px; border-radius: 12px; font-size: 0.7rem; font-weight: 500;"><?php echo htmlspecialchars($tech); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($features)): ?>
                        <ul style="list-style: none; padding: 0; margin-bottom: 20px;">
                            <?php foreach (array_slice($features, 0, 3) as $feature): ?>
                                <li style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; color: var(--gray-dark); margin-bottom: 4px;">
                                    <i class="fas fa-check-circle" style="color: #10B981; font-size: 0.8rem;"></i> 
                                    <?php echo htmlspecialchars($feature); ?>
                                </li>
                            <?php endforeach; ?>
                            <?php if (count($features) > 3): ?>
                                <li style="font-size: 0.8rem; color: var(--gray-dark); margin-top: 2px;">... و <?php echo count($features) - 3; ?> مورد دیگر</li>
                            <?php endif; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                    
                    <div style="margin-top: auto; display: flex; gap: 10px; flex-wrap: wrap;">
                        <a href="/software/babok/" class="btn-babok-primary">
                            <i class="fas fa-arrow-left"></i> ورود و اجرا
                        </a>
                        <?php if (!empty($software['demo_url'])): ?>
                            <a href="<?php echo htmlspecialchars($software['demo_url']); ?>" target="_blank" class="btn-babok-secondary" style="background: var(--gray-lighter); color: var(--dark); border: 1px solid var(--gray-light);">
                                <i class="fas fa-eye"></i> دمو
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($software['github_url'])): ?>
                            <a href="<?php echo htmlspecialchars($software['github_url']); ?>" target="_blank" class="btn-babok-secondary" style="background: transparent; color: #333; border: 1px solid var(--gray-light); padding: 10px 16px;">
                                <i class="fab fa-github"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 0; color: var(--gray);">
                <i class="fas fa-folder-open" style="font-size: 4rem; display: block; margin-bottom: 15px; opacity: 0.5;"></i>
                <p style="font-size: 1.2rem;">در حال حاضر هیچ نرم‌افزاری برای نمایش وجود ندارد.</p>
            </div>
        <?php endif; ?>
    </div>
</div>