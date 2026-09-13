<div class="checklist-history-page">
    <div class="container">
        <h1>📋 تاریخچه چک‌لیست‌های من</h1>
        
        <?php if (empty($submissions)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>هنوز چک‌لیستی پر نکرده‌اید.</p>
                <a href="/checklist" class="btn-primary">مشاهده چک‌لیست‌ها</a>
            </div>
        <?php else: ?>
            <div class="history-list">
                <?php foreach ($submissions as $sub): 
                    $riskColors = [
                        'critical' => '#dc3545', 'high' => '#fd7e14',
                        'medium' => '#ffc107', 'low' => '#28a745'
                    ];
                    $riskLabels = [
                        'critical' => 'بحرانی', 'high' => 'بالا',
                        'medium' => 'متوسط', 'low' => 'پایین'
                    ];
                    $color = $riskColors[$sub['risk_level']] ?? '#718096';
                    $label = $riskLabels[$sub['risk_level']] ?? 'نامشخص';
                    $percentage = round(($sub['total_score'] / max(1, $sub['max_score'])) * 100);
                ?>
                <div class="history-item">
                    <div class="item-info">
                        <h3><?= htmlspecialchars($sub['checklist_title']) ?></h3>
                        <div class="item-meta">
                            <span><i class="fas fa-calendar"></i> <?= jdate($sub['created_at']) ?></span>
                            <span><i class="fas fa-star"></i> <?= $sub['total_score'] ?>/<?= $sub['max_score'] ?> (<?= $percentage ?>٪)</span>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div class="item-badge" style="background: <?= $color ?>;"><?= $label ?></div>
                        <form method="POST" action="/checklist/delete/<?= $sub['id'] ?>" style="margin: 0;"
                            onsubmit="return confirm('این ارزیابی برای همیشه از تاریخچه شما حذف می‌شود. مطمئن هستید؟');">
                            <button type="submit" class="btn-delete-history" title="حذف از تاریخچه">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>