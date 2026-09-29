<?php

namespace App\Models;

use App\Core\Model;

/**
 * مدل بازدیدهای سایت
 */
class Visit extends Model
{
    protected $table = 'visits';

    /** Exclude administrator traffic and the site owner's account from visit analytics. */
    private const EXCLUDED_VISITOR_SQL = "NOT EXISTS (
        SELECT 1
        FROM users excluded_user
        WHERE excluded_user.id = v.user_id
          AND (
              LOWER(TRIM(COALESCE(excluded_user.role, ''))) = 'admin'
              OR LOWER(TRIM(COALESCE(excluded_user.name, ''))) = 'hamed yahoo'
          )
    )";

    /** Ignore static files, scanner probes, and other non-route URLs in legacy data too. */
    private const TRACKABLE_PAGE_SQL = "LOWER(SUBSTRING_INDEX(COALESCE(v.page_url, ''), '?', 1)) NOT REGEXP '\\\\.[a-z0-9]{1,10}$'";

    /**
     * ثبت یک بازدید جدید
     */
    public function record()
    {
        if ($this->shouldExcludeCurrentVisitor() || !$this->isTrackablePageRequest()) {
            return false;
        }

        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Tehran'));

        return $this->create([
            'page_url' => $_SERVER['REQUEST_URI'] ?? '/',
            'page_title' => null,
            'ip_address' => $this->getClientIP(),
            'user_id' => $_SESSION['user_id'] ?? null,
            'session_id' => session_id(),
            'referrer' => isset($_SERVER['HTTP_REFERER']) ? substr($_SERVER['HTTP_REFERER'], 0, 255) : null,
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null,
            'visit_date' => $now->format('Y-m-d'),
            'visit_time' => $now->format('H:i:s')
        ]);
    }

    /**
     * آمار کلی
     */
    public function getOverviewStats()
    {
        $today = new \DateTimeImmutable('today', new \DateTimeZone('Asia/Tehran'));
        $weekStart = $today->modify('-6 days')->format('Y-m-d');
        $monthStart = $today->modify('-29 days')->format('Y-m-d');
        $sql = "SELECT 
                    COUNT(*) AS total_visits,
                    COUNT(DISTINCT v.ip_address) AS unique_ips,
                    COUNT(DISTINCT v.session_id) AS unique_sessions,
                    COUNT(CASE WHEN v.user_id IS NOT NULL THEN 1 END) AS logged_visits,
                    COALESCE(SUM(CASE WHEN v.visit_date = ? THEN 1 ELSE 0 END), 0) AS today_visits,
                    COALESCE(SUM(CASE WHEN v.visit_date >= ? THEN 1 ELSE 0 END), 0) AS week_visits,
                    COALESCE(SUM(CASE WHEN v.visit_date >= ? THEN 1 ELSE 0 END), 0) AS month_visits,
                    COUNT(DISTINCT CASE WHEN v.visit_date >= ? THEN v.session_id END) AS week_unique_sessions
                FROM {$this->table} v
                WHERE " . self::EXCLUDED_VISITOR_SQL . " AND " . self::TRACKABLE_PAGE_SQL;
        return $this->queryOne($sql, [$today->format('Y-m-d'), $weekStart, $monthStart, $weekStart]);
    }

    /**
     * بازدید روزانه (برای نمودار)
     */
    public function getDailyStats($days = 14)
    {
        $days = max(1, (int) $days);
        $startDate = (new \DateTimeImmutable('today', new \DateTimeZone('Asia/Tehran')))
            ->modify('-' . ($days - 1) . ' days')
            ->format('Y-m-d');
        $sql = "SELECT v.visit_date,
                       COUNT(*) AS visits, 
                       COUNT(DISTINCT v.ip_address) AS unique_ips
                FROM {$this->table} v
                WHERE v.visit_date >= ?
                  AND " . self::EXCLUDED_VISITOR_SQL . "
                  AND " . self::TRACKABLE_PAGE_SQL . "
                GROUP BY v.visit_date
                ORDER BY v.visit_date ASC";
        return $this->query($sql, [$startDate]);
    }

    /**
     * پربازدیدترین صفحات
     */
    public function getTopPages($limit = 10)
    {
        $limit = (int) $limit;
        $sql = "SELECT page_url, COUNT(*) AS visits
                FROM {$this->table} v
                WHERE " . self::EXCLUDED_VISITOR_SQL . "
                  AND " . self::TRACKABLE_PAGE_SQL . "
                GROUP BY page_url
                ORDER BY visits DESC
                LIMIT {$limit}";
        return $this->query($sql);
    }

    /**
     * آخرین بازدیدها
     */
    public function getRecentVisits($limit = 20)
    {
        $limit = (int) $limit;
        $sql = "SELECT v.*, u.name AS user_name
                FROM {$this->table} v
                LEFT JOIN users u ON v.user_id = u.id
                WHERE " . self::EXCLUDED_VISITOR_SQL . "
                  AND " . self::TRACKABLE_PAGE_SQL . "
                ORDER BY v.id DESC
                LIMIT {$limit}";
        return $this->query($sql);
    }

    /**
     * منابع ورود (Referrers)
     */
    public function getTopReferrers($limit = 5)
    {
        $limit = (int) $limit;
        $sql = "SELECT referrer, COUNT(*) AS visits
                FROM {$this->table} v
                WHERE v.referrer IS NOT NULL AND v.referrer != ''
                  AND " . self::EXCLUDED_VISITOR_SQL . "
                  AND " . self::TRACKABLE_PAGE_SQL . "
                GROUP BY referrer
                ORDER BY visits DESC
                LIMIT {$limit}";
        return $this->query($sql);
    }

    /**
     * دریافت IP کاربر
     */
    private function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private function isTrackablePageRequest(): bool
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
            return false;
        }

        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return false;
        }

        $fileName = basename(rtrim($path, '/'));
        return preg_match('/\\.[a-z0-9]{1,10}$/i', $fileName) !== 1;
    }

    /** Prevent new analytics rows for excluded signed-in accounts. */
    private function shouldExcludeCurrentVisitor(): bool
    {
        if (strtolower(trim((string) ($_SESSION['user_role'] ?? ''))) === 'admin') {
            return true;
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            return false;
        }

        return (bool) $this->queryOne(
            "SELECT 1
             FROM users
             WHERE id = ?
               AND (
                   LOWER(TRIM(COALESCE(role, ''))) = 'admin'
                   OR LOWER(TRIM(COALESCE(name, ''))) = 'hamed yahoo'
               )
             LIMIT 1",
            [$userId]
        );
    }
}
