<?php
namespace App\Software\Hr\Models;

/**
 * ============================================================
 * PerformanceReview Model - ارزیابی‌های عملکرد
 * ============================================================
 */
class PerformanceReview extends BaseModel
{
    protected $table = 'performance_reviews';

    protected $fillable = [
        'employee_id', 'reviewer_id', 'review_period', 'period_start', 'period_end',
        'review_type',
        'job_knowledge_score', 'quality_score', 'productivity_score',
        'communication_score', 'teamwork_score', 'initiative_score',
        'leadership_score', 'discipline_score',
        'overall_score', 'goals_score', 'rating',
        'strengths', 'weaknesses', 'goals_achievement', 'training_needs',
        'manager_comments', 'employee_comments', 'recommendation',
        'status', 'completed_at',
    ];

    public static function getTypeOptions(): array
    {
        return [
            'annual'      => 'سالانه',
            'semi_annual' => 'شش‌ماهه',
            'quarterly'   => 'فصلی',
            'monthly'     => 'ماهانه',
            'probation'   => 'پایان دوره آزمایشی',
            '360'         => '۳۶۰ درجه',
        ];
    }

    public static function getStatusOptions(): array
    {
        return [
            'draft'            => 'پیش‌نویس',
            'in_progress'      => 'در حال انجام',
            'pending_employee' => 'منتظر نظر کارمند',
            'pending_manager'  => 'منتظر تأیید مدیر',
            'completed'        => 'تکمیل شده',
            'cancelled'        => 'لغو شده',
        ];
    }

    public static function getRatingOptions(): array
    {
        return [
            'excellent'         => 'عالی',
            'very_good'         => 'خیلی خوب',
            'good'              => 'خوب',
            'satisfactory'      => 'قابل قبول',
            'needs_improvement' => 'نیاز به بهبود',
            'unsatisfactory'    => 'غیرقابل قبول',
        ];
    }

    public static function getRecommendationOptions(): array
    {
        return [
            'promote'         => 'ارتقا',
            'increase_salary' => 'افزایش حقوق',
            'bonus'           => 'پاداش',
            'training'        => 'آموزش',
            'no_action'       => 'بدون اقدام',
            'pip'             => 'برنامه بهبود عملکرد (PIP)',
        ];
    }

    public static function getScoreFields(): array
    {
        return [
            'job_knowledge_score' => 'دانش شغلی',
            'quality_score'       => 'کیفیت کار',
            'productivity_score'  => 'بهره‌وری',
            'communication_score' => 'ارتباطات',
            'teamwork_score'      => 'کار تیمی',
            'initiative_score'    => 'ابتکار عمل',
            'leadership_score'    => 'رهبری',
            'discipline_score'    => 'نظم و انضباط',
        ];
    }

    public function search(array $filters = []): array
    {
        $sql = "SELECT r.*,
                       e.first_name, e.last_name, e.employee_code,
                       d.name AS department_name,
                       rev.first_name AS reviewer_first, rev.last_name AS reviewer_last
                FROM {$this->tableName()} r
                LEFT JOIN hr_employees e ON e.id = r.employee_id AND e.system_id = r.system_id
                LEFT JOIN hr_departments d ON d.id = e.department_id AND d.system_id = r.system_id
                LEFT JOIN hr_employees rev ON rev.id = r.reviewer_id AND rev.system_id = r.system_id
                WHERE r.system_id = :sid";
        $params = ['sid' => $this->systemId];

        if (!empty($filters['q'])) {
            $sql .= " AND (e.first_name LIKE :q OR e.last_name LIKE :q2 OR r.review_period LIKE :q3)";
            $params['q']  = '%' . $filters['q'] . '%';
            $params['q2'] = '%' . $filters['q'] . '%';
            $params['q3'] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['employee_id'])) {
            $sql .= " AND r.employee_id = :eid";
            $params['eid'] = (int) $filters['employee_id'];
        }
        if (!empty($filters['reviewer_id'])) {
            $sql .= " AND r.reviewer_id = :rid";
            $params['rid'] = (int) $filters['reviewer_id'];
        }
        if (!empty($filters['review_type'])) {
            $sql .= " AND r.review_type = :rt";
            $params['rt'] = $filters['review_type'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND r.status = :st";
            $params['st'] = $filters['status'];
        }
        if (!empty($filters['rating'])) {
            $sql .= " AND r.rating = :ra";
            $params['ra'] = $filters['rating'];
        }

        $sql .= " ORDER BY r.period_end DESC, r.id DESC LIMIT 500";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getStats(): array
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) AS completed,
                    SUM(CASE WHEN status='in_progress' THEN 1 ELSE 0 END) AS in_progress,
                    SUM(CASE WHEN status IN ('pending_employee','pending_manager') THEN 1 ELSE 0 END) AS pending,
                    COALESCE(AVG(overall_score), 0) AS avg_score
                FROM {$this->tableName()}
                WHERE system_id = :sid";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['sid' => $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total'       => (int) ($row['total'] ?? 0),
            'completed'   => (int) ($row['completed'] ?? 0),
            'in_progress' => (int) ($row['in_progress'] ?? 0),
            'pending'     => (int) ($row['pending'] ?? 0),
            'avg_score'   => round((float) ($row['avg_score'] ?? 0), 2),
        ];
    }

    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT r.*,
                       e.first_name, e.last_name, e.employee_code, e.mobile, e.email,
                       e.hire_date, e.position_id,
                       d.name AS department_name,
                       p.title AS position_title,
                       rev.first_name AS reviewer_first, rev.last_name AS reviewer_last,
                       rev.employee_code AS reviewer_code
                FROM {$this->tableName()} r
                LEFT JOIN hr_employees e ON e.id = r.employee_id AND e.system_id = r.system_id
                LEFT JOIN hr_departments d ON d.id = e.department_id AND d.system_id = r.system_id
                LEFT JOIN hr_positions p ON p.id = e.position_id AND p.system_id = r.system_id
                LEFT JOIN hr_employees rev ON rev.id = r.reviewer_id AND rev.system_id = r.system_id
                WHERE r.id = :id AND r.system_id = :sid LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'sid' => $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) return null;

        $row['employee_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        $row['reviewer_name'] = trim(($row['reviewer_first'] ?? '') . ' ' . ($row['reviewer_last'] ?? ''));
        return $row;
    }

    public function recalcOverall(int $id): void
    {
        $review = $this->find($id);
        if (!$review) return;

        $fields = array_keys(self::getScoreFields());
        $values = [];
        foreach ($fields as $f) {
            if ($review[$f] !== null && $review[$f] !== '') {
                $values[] = (float) $review[$f];
            }
        }
        if (empty($values)) return;

        $avg = round(array_sum($values) / count($values), 2);
        $this->update($id, ['overall_score' => $avg]);
    }

    public static function scoreToRating(float $score): string
    {
        if ($score >= 4.5) return 'excellent';
        if ($score >= 4.0) return 'very_good';
        if ($score >= 3.0) return 'good';
        if ($score >= 2.5) return 'satisfactory';
        if ($score >= 1.5) return 'needs_improvement';
        return 'unsatisfactory';
    }
}