<?php

namespace App\Models;

use App\Core\Model;

class SoftwareUsage extends Model
{
    protected $table = 'software_usage';

    /**
     * ثبت یک فعالیت در لاگ
     */
    public function logActivity($softwareId, $action, $recordType = null, $recordId = null, $details = null)
    {
        $userId = $_SESSION['user_id'] ?? null;
        $userName = $_SESSION['user_name'] ?? 'مهمان';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // محدود کردن طول details
        if ($details && strlen($details) > 60000) {
            $details = substr($details, 0, 60000) . '... [truncated]';
        }

        return $this->create([
            'software_id' => $softwareId,
            'user_id' => $userId,
            'user_name' => $userName,
            'ip_address' => $ip,
            'action' => $action,
            'record_type' => $recordType,
            'record_id' => $recordId,
            'details' => $details
        ]);
    }

    /**
     * دریافت آمار کلی برای یک نرم‌افزار
     */
    public function getStats($softwareId)
    {
        $sql = "SELECT 
                    COUNT(*) as total_activities,
                    COUNT(DISTINCT user_id) as unique_users,
                    COUNT(DISTINCT ip_address) as unique_ips,
                    MAX(created_at) as last_activity
                FROM {$this->table}
                WHERE software_id = ?";
        return $this->queryOne($sql, [$softwareId]);
    }

    /**
     * دریافت آخرین فعالیت‌ها
     */
    public function getRecentActivities($limit = 50, $softwareId = null)
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        if ($softwareId) {
            $sql .= " WHERE software_id = ?";
            $params[] = $softwareId;
        }
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = (int)$limit;
        return $this->query($sql, $params);
    }

    /**
     * دریافت فعالیت‌های یک کاربر
     */
    public function getUserActivities($userId, $limit = 100)
    {
        $sql = "SELECT su.*, s.name as software_name 
                FROM {$this->table} su
                JOIN software s ON su.software_id = s.id
                WHERE su.user_id = ?
                ORDER BY su.created_at DESC
                LIMIT ?";
        return $this->query($sql, [$userId, (int)$limit]);
    }

    /**
     * دریافت فعالیت‌ها بر اساس IP
     */
    public function getActivitiesByIP($ipAddress, $limit = 100)
    {
        $sql = "SELECT su.*, s.name as software_name 
                FROM {$this->table} su
                JOIN software s ON su.software_id = s.id
                WHERE su.ip_address = ?
                ORDER BY su.created_at DESC
                LIMIT ?";
        return $this->query($sql, [$ipAddress, (int)$limit]);
    }

    /**
     * گروه‌بندی فعالیت‌ها بر اساس نوع
     */
    public function getActivitiesByType($softwareId)
    {
        $sql = "SELECT action, COUNT(*) as count 
                FROM {$this->table}
                WHERE software_id = ?
                GROUP BY action
                ORDER BY count DESC";
        return $this->query($sql, [$softwareId]);
    }

    /**
     * حذف لاگ‌های قدیمی (مثلا بیش از 90 روز)
     */
    public function cleanup($days = 90)
    {
        $sql = "DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$days]);
    }
}