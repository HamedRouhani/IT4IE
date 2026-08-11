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

    <!-- آمار (فقط تعداد نرم‌افزارهای فعال) -->
    <div class="babok-stats" style="margin-bottom: 40px;">
        <div class="stat-card" style="max-width: 280px; margin: 0 auto;">
            <div class="stat-icon purple"><i class="fas fa-cubes"></i></div>
            <div class="stat-info">
                <h3><?php echo $totalSoftware ?? 0; ?></h3>
                <p>نرم‌افزار فعال</p>
            </div>
        </div>
    </div>

    <div class="projects-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 25px;">
        <?php if (isset($softwareList) && !empty($softwareList)): ?>
            <?php foreach ($softwareList as $software): ?>
                <?php 
                $features = json_decode($software['features'] ?? '[]', true);
                $techStack = json_decode($software['tech_stack'] ?? '[]', true);
                
                // آیکون پویا بر اساس نام نرم‌افزار
                $iconClass = 'fas fa-cubes';
                $iconColor = 'var(--primary)';
                $nameLower = strtolower($software['name'] ?? '');
                $slugLower = strtolower($software['slug'] ?? '');
                
                if (stripos($nameLower, 'babok') !== false || stripos($slugLower, 'babok') !== false) {
                    $iconClass = 'fas fa-robot';
                    $iconColor = '#667eea';
                } elseif (stripos($nameLower, 'pmbok') !== false || stripos($slugLower, 'pmbok') !== false) {
                    $iconClass = 'fas fa-project-diagram';
                    $iconColor = '#ed8936';
                } elseif (stripos($nameLower, 'itil') !== false || stripos($slugLower, 'itil') !== false) {
                    $iconClass = 'fas fa-server';
                    $iconColor = '#10B981';
                } elseif (stripos($nameLower, 'togaf') !== false || stripos($slugLower, 'togaf') !== false) {
                    $iconClass = 'fas fa-network-wired';
                    $iconColor = '#3B82F6';
                }
                
                // رنگ خاص هر ماژول برای دکمه
                $primaryBtnGradient = 'linear-gradient(135deg, #667eea, #764ba2)';
                if (stripos($nameLower, 'pmbok') !== false || stripos($slugLower, 'pmbok') !== false) {
                    $primaryBtnGradient = 'linear-gradient(135deg, #ed8936, #dd6b20)';
                } elseif (stripos($nameLower, 'itil') !== false) {
                    $primaryBtnGradient = 'linear-gradient(135deg, #10B981, #059669)';
                }
                ?>
                <div class="project-card" style="padding: 25px; transition: all 0.3s ease; position: relative; display: flex; flex-direction: column; justify-content: space-between;">
                    
                    <!-- Badge وضعیت -->
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
                                <i class="<?php echo $iconClass; ?>" style="color: <?php echo $iconColor; ?>; font-size: 1.8rem;"></i>
                                <?php echo htmlspecialchars($software['name']); ?>
                            </h3>
                            <span style="font-size: 0.85rem; color: var(--gray); margin-top: 4px; display: block;">
                                <i class="fas fa-tag"></i> نسخه <?php echo htmlspecialchars($software['version'] ?? '1.0'); ?>
                            </span>
                        </div>
                        <p class="project-description" style="color: var(--gray-dark); line-height: 1.7; margin-bottom: 15px;">
                            <?php echo htmlspecialchars($software['description'] ?? ''); ?>
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
                    
                    <!-- دکمه‌ها -->
                    <div style="margin-top: auto; display: flex; gap: 10px; flex-wrap: wrap; align-items: stretch;">
                        
                        <!-- دکمه اصلی: ورود و اجرا (فرم برای POST و امنیت بیشتر) -->
                        <form method="POST" action="/software/run/<?php echo urlencode($software['slug']); ?>" style="flex: 1; min-width: 140px;">
                            <button type="submit" 
                                    class="btn-run-software"
                                    style="
                                        width: 100%;
                                        height: 44px;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        gap: 8px;
                                        background: <?php echo $primaryBtnGradient; ?>;
                                        color: white;
                                        border: none;
                                        border-radius: 10px;
                                        padding: 0 18px;
                                        font-size: 0.95rem;
                                        font-weight: 600;
                                        font-family: 'Vazirmatn', 'Tahoma', sans-serif;
                                        cursor: pointer;
                                        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.25);
                                        transition: all 0.3s ease;
                                    "
                                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.2)';"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(102, 126, 234, 0.25)';">
                                <i class="fas fa-play-circle"></i>
                                <span>ورود و اجرا</span>
                            </button>
                        </form>

                        <!-- دکمه دمو (اختیاری) -->
                        <?php if (!empty($software['demo_url'])): ?>
                            <a href="<?php echo htmlspecialchars($software['demo_url']); ?>" 
                               target="_blank" 
                               class="btn-babok-secondary" 
                               style="
                                   display: flex;
                                   align-items: center;
                                   justify-content: center;
                                   gap: 6px;
                                   height: 44px;
                                   padding: 0 16px;
                                   background: var(--gray-lighter, #f1f5f9);
                                   color: var(--dark, #333);
                                   border: 1px solid var(--gray-light, #e2e8f0);
                                   border-radius: 10px;
                                   text-decoration: none;
                                   font-size: 0.9rem;
                                   font-weight: 500;
                                   transition: all 0.3s ease;
                               "
                               onmouseover="this.style.background='#e2e8f0'; this.style.transform='translateY(-2px)';"
                               onmouseout="this.style.background='var(--gray-lighter, #f1f5f9)'; this.style.transform='translateY(0)';">
                                <i class="fas fa-eye"></i>
                                <span>دمو</span>
                            </a>
                        <?php endif; ?>
                        
                        <!-- دکمه گیت‌هاب (آیکونی) -->
                        <?php if (!empty($software['github_url'])): ?>
                            <a href="<?php echo htmlspecialchars($software['github_url']); ?>" 
                               target="_blank" 
                               class="btn-babok-secondary" 
                               style="
                                   display: flex;
                                   align-items: center;
                                   justify-content: center;
                                   width: 44px;
                                   height: 44px;
                                   background: transparent;
                                   color: #333;
                                   border: 1px solid var(--gray-light, #e2e8f0);
                                   border-radius: 10px;
                                   text-decoration: none;
                                   font-size: 1.2rem;
                                   transition: all 0.3s ease;
                               "
                               onmouseover="this.style.background='#f1f5f9'; this.style.transform='translateY(-2px)';"
                               onmouseout="this.style.background='transparent'; this.style.transform='translateY(0)';"
                               title="مشاهده در گیت‌هاب">
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