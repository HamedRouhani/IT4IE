<?php
/**
 * صفحه مدیریت اعلان‌ها و یادآوری‌ها
 * مسیر: app/software/babok/views/notifications/index.php
 */

// تابع کمکی برای نمایش زمان نسبی (مثلاً "۲ ساعت پیش")
function timeAgo($datetime) {
    if (empty($datetime)) return 'نامشخص';
    $time = strtotime($datetime);
    if ($time === false) return 'نامشخص';
    
    $diff = time() - $time;
    
    if ($diff < 60) return 'لحظاتی پیش';
    if ($diff < 3600) return floor($diff / 60) . ' دقیقه پیش';
    if ($diff < 86400) return floor($diff / 3600) . ' ساعت پیش';
    if ($diff < 604800) return floor($diff / 86400) . ' روز پیش';
    if ($diff < 2592000) return floor($diff / 604800) . ' هفته پیش';
    return date('Y/m/d', $time);
}

$typeIcons = [
    'system' => ['icon' => '⚙️', 'color' => '#64748b', 'label' => 'سیستمی'],
    'reminder' => ['icon' => '⏰', 'color' => '#0ea5e9', 'label' => 'یادآوری'],
    'quality' => ['icon' => '✨', 'color' => '#f59e0b', 'label' => 'کیفیت'],
    'traceability' => ['icon' => '🔗', 'color' => '#10b981', 'label' => 'ردیابی'],
    'recommendation' => ['icon' => '💡', 'color' => '#8b5cf6', 'label' => 'پیشنهاد']
];

$priorityColors = [
    'low' => '#94a3b8',
    'normal' => '#3b82f6',
    'high' => '#f59e0b',
    'urgent' => '#ef4444'
];
?>

<div style="max-width: 1000px; margin: 0 auto; padding: 20px;">
    
    <!-- هدر صفحه -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h2 style="margin: 0 0 10px 0; font-size: 1.8rem;">
                    🔔 اعلان‌ها و یادآوری‌های هوشمند
                </h2>
                <p style="margin: 0; opacity: 0.95;">
                    <?= $unreadCount > 0 ? "<strong>{$unreadCount}</strong> اعلان خوانده‌نشده دارید" : 'همه اعلان‌ها خوانده شده‌اند' ?>
                </p>
            </div>
            <?php if ($unreadCount > 0): ?>
                <a href="?route=notifications_mark_all_read" 
                   style="padding: 10px 20px; background: rgba(255,255,255,0.2); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; backdrop-filter: blur(10px); transition: background 0.2s;"
                   onmouseover="this.style.background='rgba(255,255,255,0.3)'" 
                   onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                    ✓ علامت‌گذاری همه به عنوان خوانده‌شده
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- فیلترها -->
    <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
        <?php 
        $filters = [
            'all' => ['label' => 'همه', 'count' => count($notifications)],
            'unread' => ['label' => 'خوانده‌نشده', 'count' => $unreadCount],
            'reminder' => ['label' => '⏰ یادآوری‌ها'],
            'quality' => ['label' => '✨ کیفیت'],
            'traceability' => ['label' => '🔗 ردیابی'],
        ];
        foreach ($filters as $key => $filter): 
            $isActive = $currentFilter === $key;
        ?>
            <a href="?route=notifications&filter=<?= $key ?>"
               style="padding: 8px 16px; background: <?= $isActive ? '#6C3CE1' : 'white' ?>; color: <?= $isActive ? 'white' : '#475569' ?>; text-decoration: none; border-radius: 99px; font-size: 0.85rem; font-weight: 500; border: 1px solid <?= $isActive ? '#6C3CE1' : '#e2e8f0' ?>; transition: all 0.2s;">
                <?= $filter['label'] ?>
                <?php if (isset($filter['count'])): ?>
                    <span style="background: <?= $isActive ? 'rgba(255,255,255,0.3)' : '#f1f5f9' ?>; padding: 2px 8px; border-radius: 99px; margin-right: 5px; font-size: 0.75rem;">
                        <?= $filter['count'] ?>
                    </span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- لیست اعلان‌ها -->
    <?php if (empty($notifications)): ?>
        <div style="text-align: center; padding: 60px 20px; background: #f8fafc; border-radius: 12px; border: 2px dashed #cbd5e1;">
            <div style="font-size: 4rem; margin-bottom: 15px;">📭</div>
            <h3 style="color: #475569; margin: 0 0 10px 0;">اعلانی وجود ندارد</h3>
            <p style="color: #64748b; margin: 0;">
                <?php if ($currentFilter === 'unread'): ?>
                    تمام اعلان‌های شما خوانده شده‌اند!
                <?php else: ?>
                    سیستم به صورت خودکار اعلان‌های هوشمند را برای شما تولید می‌کند.
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php foreach ($notifications as $notification): 
                $typeInfo = $typeIcons[$notification['type']] ?? $typeIcons['system'];
                $priorityColor = $priorityColors[$notification['priority']] ?? '#3b82f6';
                $isUnread = !$notification['is_read'];
            ?>
                <div id="notification-<?= $notification['id'] ?>"
                     style="background: <?= $isUnread ? '#f0f9ff' : 'white' ?>; 
                            border: 1px solid <?= $isUnread ? '#bae6fd' : '#e2e8f0' ?>; 
                            border-right: 4px solid <?= $typeInfo['color'] ?>; 
                            border-radius: 10px; 
                            padding: 18px; 
                            display: flex; 
                            align-items: flex-start; 
                            gap: 15px; 
                            transition: all 0.2s;
                            opacity: <?= $isUnread ? '1' : '0.75' ?>;"
                     onmouseover="this.style.transform='translateX(3px)'"
                     onmouseout="this.style.transform='translateX(0)'">
                    
                    <!-- آیکون نوع -->
                    <div style="min-width: 50px; height: 50px; background: <?= $typeInfo['color'] ?>15; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        <?= $typeInfo['icon'] ?>
                    </div>

                    <!-- محتوا -->
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap;">
                            <strong style="color: #1e293b; font-size: 1rem; <?= $isUnread ? 'font-weight: 700;' : '' ?>">
                                <?= htmlspecialchars($notification['title']) ?>
                            </strong>
                            <?php if ($isUnread): ?>
                                <span style="background: #ef4444; color: white; padding: 2px 8px; border-radius: 99px; font-size: 0.65rem; font-weight: 600;">
                                    جدید
                                </span>
                            <?php endif; ?>
                            <span style="background: <?= $priorityColor ?>15; color: <?= $priorityColor ?>; padding: 2px 8px; border-radius: 99px; font-size: 0.7rem; font-weight: 600;">
                                <?= $notification['priority'] === 'urgent' ? 'فوری' : ($notification['priority'] === 'high' ? 'مهم' : ($notification['priority'] === 'normal' ? 'معمولی' : 'کم')) ?>
                            </span>
                            <span style="background: <?= $typeInfo['color'] ?>15; color: <?= $typeInfo['color'] ?>; padding: 2px 8px; border-radius: 99px; font-size: 0.7rem;">
                                <?= $typeInfo['label'] ?>
                            </span>
                        </div>
                        
                        <p style="margin: 0 0 8px 0; color: #475569; font-size: 0.9rem; line-height: 1.6;">
                            <?= htmlspecialchars($notification['message']) ?>
                        </p>
                        
                        <div style="display: flex; align-items: center; gap: 15px; font-size: 0.8rem; color: #94a3b8; flex-wrap: wrap;">
                            <?php if (!empty($notification['project_name'])): ?>
                                <span>
                                    📁 پروژه: <strong style="color: #64748b;"><?= htmlspecialchars($notification['project_name']) ?></strong>
                                </span>
                            <?php endif; ?>
                            <span>
                                🕒 <?= timeAgo($notification['created_at']) ?>
                            </span>
                        </div>
                    </div>

                    <!-- دکمه‌های عملیات -->
                    <div style="display: flex; flex-direction: column; gap: 6px;">
                        <?php if (!empty($notification['link'])): ?>
                            <a href="<?= htmlspecialchars($notification['link']) ?>" 
                               onclick="markNotificationRead(<?= $notification['id'] ?>)"
                               style="padding: 6px 12px; background: #6C3CE1; color: white; text-decoration: none; border-radius: 6px; font-size: 0.8rem; text-align: center; white-space: nowrap;">
                                مشاهده
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($isUnread): ?>
                            <button onclick="markNotificationRead(<?= $notification['id'] ?>)"
                                    style="padding: 6px 12px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.8rem; cursor: pointer;">
                                ✓ خواندم
                            </button>
                        <?php endif; ?>
                        
                        <a href="?route=notifications_delete&id=<?= $notification['id'] ?>"
                           onclick="return confirm('آیا از حذف این اعلان اطمینان دارید؟')"
                           style="padding: 6px 12px; background: #fee2e2; color: #991b1b; text-decoration: none; border-radius: 6px; font-size: 0.8rem; text-align: center;">
                            🗑️ حذف
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function markNotificationRead(id) {
    const formData = new FormData();
    formData.append('id', id);
    
    fetch('?route=notifications_mark_read', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const el = document.getElementById('notification-' + id);
            if (el) {
                el.style.opacity = '0.75';
                el.style.background = 'white';
                el.style.borderColor = '#e2e8f0';
                // حذف badge "جدید"
                const badge = el.querySelector('span[style*="background: #ef4444"]');
                if (badge) badge.remove();
            }
            // به‌روزرسانی bell icon در سایدبار
            updateBellIcon(data.unread_count);
        }
    })
    .catch(error => console.error('Error:', error));
}

function updateBellIcon(count) {
    const badge = document.querySelector('.notification-badge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    }
}
</script>