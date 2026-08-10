<?php

namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * مدل مدیریت محدودیت‌های استفاده از نرم‌افزارها
 * برای پیاده‌سازی محدودیت‌های رایگان بر اساس IP و کاربر
 */
class SoftwareUsageLimit extends Model
{
    protected $table = 'software_usage_limits';

    /**
     * تنظیمات پیش‌فرض محدودیت‌ها برای هر نرم‌افزار
     */
    private $defaultLimits = [
        'babok-analyzer' => [
            'projects' => 3,        // حداکثر ۳ پروژه رایگان
            'requirement_analysis' => 10  // حداکثر ۱۰ تحلیل نیازمندی رایگان
        ],
        'pmbok-analyzer' => [
            'projects' => 3,
            'processes' => 5
        ]
    ];

    /**
     * بررسی امکان ایجاد یک منبع جدید
     * 
     * @param string $softwareSlug شناسه نرم‌افزار
     * @param string $resourceType نوع منبع (projects, requirement_analysis و...)
     * @return array نتیجه بررسی شامل allowed, current, max, message
     */
    public function checkLimit($softwareSlug, $resourceType)
    {
        $userId = $_SESSION['user_id'] ?? null;
        $ipAddress = $this->getClientIP();
        
        // برای کاربر لاگین شده، چک کردن بر اساس user_id
        if ($userId) {
            $result = $this->queryOne(
                "SELECT current_count, max_allowed FROM {$this->table} 
                 WHERE software_slug = ? AND user_id = ? AND resource_type = ?",
                [$softwareSlug, $userId, $resourceType]
            );
        } else {
            // برای کاربر مهمان، چک کردن بر اساس IP
            $result = $this->queryOne(
                "SELECT current_count, max_allowed FROM {$this->table} 
                 WHERE software_slug = ? AND ip_address = ? AND resource_type = ? AND user_id IS NULL",
                [$softwareSlug, $ipAddress, $resourceType]
            );
        }

        $maxAllowed = $this->getMaxAllowed($softwareSlug, $resourceType);

        if (!$result) {
            return [
                'allowed' => true,
                'current' => 0,
                'max' => $maxAllowed,
                'message' => 'محدودیتی وجود ندارد'
            ];
        }

        $allowed = $result['current_count'] < $result['max_allowed'];
        
        return [
            'allowed' => $allowed,
            'current' => $result['current_count'],
            'max' => $result['max_allowed'],
            'message' => $allowed 
                ? sprintf('شما %d از %d امکان را استفاده کرده‌اید', $result['current_count'], $result['max_allowed'])
                : sprintf('به سقف %d امکان رسیده‌اید. برای استفاده بیشتر وارد شوید یا اکانت خود را ارتقا دهید.', $result['max_allowed'])
        ];
    }

    /**
     * افزایش شمارنده استفاده
     */
    public function incrementUsage($softwareSlug, $resourceType)
    {
        $userId = $_SESSION['user_id'] ?? null;
        $ipAddress = $this->getClientIP();
        $maxAllowed = $this->getMaxAllowed($softwareSlug, $resourceType);

        if ($userId) {
            $sql = "INSERT INTO {$this->table} (software_slug, user_id, ip_address, resource_type, current_count, max_allowed)
                    VALUES (?, ?, ?, ?, 1, ?)
                    ON DUPLICATE KEY UPDATE current_count = current_count + 1";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$softwareSlug, $userId, $ipAddress, $resourceType, $maxAllowed]);
        } else {
            $sql = "INSERT INTO {$this->table} (software_slug, user_id, ip_address, resource_type, current_count, max_allowed)
                    VALUES (?, NULL, ?, ?, 1, ?)
                    ON DUPLICATE KEY UPDATE current_count = current_count + 1";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$softwareSlug, $ipAddress, $resourceType, $maxAllowed]);
        }
    }

    /**
     * ریست کردن محدودیت‌ها (مثلاً ماهانه)
     */
    public function resetLimits($softwareSlug = null, $resourceType = null)
    {
        $sql = "UPDATE {$this->table} SET current_count = 0, reset_date = CURDATE()";
        $params = [];
        $conditions = [];
        
        if ($softwareSlug) {
            $conditions[] = "software_slug = ?";
            $params[] = $softwareSlug;
        }
        
        if ($resourceType) {
            $conditions[] = "resource_type = ?";
            $params[] = $resourceType;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * دریافت تمام محدودیت‌های یک کاربر
     */
    public function getUserLimits($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ?";
        return $this->query($sql, [$userId]);
    }

    /**
     * دریافت تمام محدودیت‌ها بر اساس IP
     */
    public function getIPLimits($ipAddress)
    {
        $sql = "SELECT * FROM {$this->table} WHERE ip_address = ?";
        return $this->query($sql, [$ipAddress]);
    }

    /**
     * دریافت آمار کلی محدودیت‌ها برای پنل ادمین
     */
    public function getAdminStats()
    {
        $sql = "SELECT 
                    software_slug,
                    resource_type,
                    COUNT(*) as total_records,
                    SUM(current_count) as total_usage,
                    COUNT(CASE WHEN current_count >= max_allowed THEN 1 END) as reached_limit_count
                FROM {$this->table}
                GROUP BY software_slug, resource_type
                ORDER BY software_slug, resource_type";
        return $this->query($sql);
    }

    /**
     * دریافت حداکثر مجاز
     */
    private function getMaxAllowed($softwareSlug, $resourceType)
    {
        if (isset($this->defaultLimits[$softwareSlug][$resourceType])) {
            return $this->defaultLimits[$softwareSlug][$resourceType];
        }
        return 5; // مقدار پیش‌فرض
    }

    /**
     * دریافت IP کاربر
     */
    private function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}