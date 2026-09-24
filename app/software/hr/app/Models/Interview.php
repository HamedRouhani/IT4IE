<?php
namespace App\Software\Hr\Models;

/**
 * ============================================================
 * Interview Model - مدل مصاحبه‌های استخدامی
 * ============================================================
 */
class Interview extends BaseModel
{
    protected $table = 'interviews';

    protected $fillable = [
        'candidate_id',
        'recruitment_id',
        'interview_type',
        'round',
        'interviewer_id',
        'scheduled_date',
        'duration_minutes',
        'location',
        'meeting_link',
        'status',
        'technical_score',
        'communication_score',
        'culture_fit_score',
        'overall_score',
        'strengths',
        'weaknesses',
        'notes',
        'recommendation',
    ];

    /**
     * جستجو
     */
    public function search(array $filters = []): array
    {
        $sql = "SELECT i.*, 
                       CONCAT(c.first_name, ' ', c.last_name) AS candidate_name,
                       c.candidate_code,
                       r.title AS recruitment_title,
                       CONCAT(e.first_name, ' ', e.last_name) AS interviewer_name
                FROM {$this->tableName()} i
                LEFT JOIN {$this->prefix}candidates c ON i.candidate_id = c.id
                LEFT JOIN {$this->prefix}recruitments r ON i.recruitment_id = r.id
                LEFT JOIN {$this->prefix}employees e ON i.interviewer_id = e.id
                WHERE i.system_id = ?";
        $params = [$this->systemId];

        if (!empty($filters['candidate_id'])) {
            $sql .= " AND i.candidate_id = ?";
            $params[] = (int) $filters['candidate_id'];
        }

        if (!empty($filters['recruitment_id'])) {
            $sql .= " AND i.recruitment_id = ?";
            $params[] = (int) $filters['recruitment_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND i.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['interview_type'])) {
            $sql .= " AND i.interview_type = ?";
            $params[] = $filters['interview_type'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(i.scheduled_date) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(i.scheduled_date) <= ?";
            $params[] = $filters['date_to'];
        }

        $sql .= " ORDER BY i.scheduled_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * دریافت با جزئیات
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT i.*, 
                       CONCAT(c.first_name, ' ', c.last_name) AS candidate_name,
                       c.candidate_code,
                       c.mobile AS candidate_mobile,
                       r.title AS recruitment_title,
                       CONCAT(e.first_name, ' ', e.last_name) AS interviewer_name
                FROM {$this->tableName()} i
                LEFT JOIN {$this->prefix}candidates c ON i.candidate_id = c.id
                LEFT JOIN {$this->prefix}recruitments r ON i.recruitment_id = r.id
                LEFT JOIN {$this->prefix}employees e ON i.interviewer_id = e.id
                WHERE i.id = ? AND i.system_id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * دریافت مصاحبه‌های یک متقاضی
     */
    public function getByCandidate(int $candidateId): array
    {
        $stmt = $this->db->prepare(
            "SELECT i.*, 
                    CONCAT(e.first_name, ' ', e.last_name) AS interviewer_name
             FROM {$this->tableName()} i
             LEFT JOIN {$this->prefix}employees e ON i.interviewer_id = e.id
             WHERE i.candidate_id = ? AND i.system_id = ?
             ORDER BY i.round ASC, i.scheduled_date ASC"
        );
        $stmt->execute([$candidateId, $this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * مصاحبه‌های امروز
     */
    public function getToday(): array
    {
        $stmt = $this->db->prepare(
            "SELECT i.*, 
                    CONCAT(c.first_name, ' ', c.last_name) AS candidate_name
             FROM {$this->tableName()} i
             LEFT JOIN {$this->prefix}candidates c ON i.candidate_id = c.id
             WHERE i.system_id = ? AND DATE(i.scheduled_date) = CURDATE()
             ORDER BY i.scheduled_date ASC"
        );
        $stmt->execute([$this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * آمار
     */
    public function getStats(): array
    {
        $stmt = $this->db->prepare(
            "SELECT 
                COUNT(*) AS total_count,
                SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) AS scheduled_count,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_count,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_count,
                SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) AS no_show_count,
                AVG(overall_score) AS avg_score
             FROM {$this->tableName()}
             WHERE system_id = ?"
        );
        $stmt->execute([$this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total'      => (int) ($row['total_count'] ?? 0),
            'scheduled'  => (int) ($row['scheduled_count'] ?? 0),
            'completed'  => (int) ($row['completed_count'] ?? 0),
            'cancelled'  => (int) ($row['cancelled_count'] ?? 0),
            'no_show'    => (int) ($row['no_show_count'] ?? 0),
            'avg_score'  => round((float) ($row['avg_score'] ?? 0), 2),
        ];
    }

    /**
     * گزینه‌ها
     */
    public static function getTypeOptions(): array
    {
        return [
            'phone'     => 'تلفنی',
            'video'     => 'ویدیویی',
            'in_person' => 'حضوری',
            'technical' => 'فنی',
            'hr'        => 'منابع انسانی',
            'panel'     => 'پنل',
            'final'     => 'نهایی',
        ];
    }

    public static function getStatusOptions(): array
    {
        return [
            'scheduled' => 'برنامه‌ریزی شده',
            'completed' => 'انجام شده',
            'cancelled' => 'لغو شده',
            'no_show'   => 'عدم حضور',
        ];
    }

    public static function getRecommendationOptions(): array
    {
        return [
            'hire'   => 'استخدام',
            'maybe'  => 'شاید',
            'reject' => 'رد',
        ];
    }
}