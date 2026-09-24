<?php
namespace App\Software\Hr\Models;

/**
 * ============================================================
 * Candidate Model - مدل متقاضیان استخدام
 * ============================================================
 */
class Candidate extends BaseModel
{
    protected $table = 'candidates';

    protected $fillable = [
        'recruitment_id',
        'candidate_code',
        'first_name',
        'last_name',
        'national_id',
        'gender',
        'birth_date',
        'mobile',
        'email',
        'address',
        'education_level',
        'field_of_study',
        'university',
        'experience_years',
        'current_company',
        'current_position',
        'expected_salary',
        'resume_path',
        'portfolio_url',
        'source',
        'status',
        'rating',
        'notes',
        'applied_date',
    ];

    /**
     * جستجو و فیلتر
     */
    public function search(array $filters = []): array
    {
        $sql = "SELECT c.*, 
                       r.title AS recruitment_title,
                       r.request_number,
                       CONCAT(c.first_name, ' ', c.last_name) AS full_name
                FROM {$this->tableName()} c
                LEFT JOIN {$this->prefix}recruitments r ON c.recruitment_id = r.id
                WHERE c.system_id = ?";
        $params = [$this->systemId];

        if (!empty($filters['q'])) {
            $q = '%' . $filters['q'] . '%';
            $sql .= " AND (c.first_name LIKE ? OR c.last_name LIKE ? 
                       OR c.mobile LIKE ? OR c.email LIKE ? OR c.national_id LIKE ?)";
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        if (!empty($filters['recruitment_id'])) {
            $sql .= " AND c.recruitment_id = ?";
            $params[] = (int) $filters['recruitment_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['source'])) {
            $sql .= " AND c.source = ?";
            $params[] = $filters['source'];
        }

        if (!empty($filters['gender'])) {
            $sql .= " AND c.gender = ?";
            $params[] = $filters['gender'];
        }

        $sql .= " ORDER BY c.applied_date DESC, c.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * دریافت با جزئیات
     */
    public function findWithDetails(int $id): ?array
    {
        $sql = "SELECT c.*, 
                       r.title AS recruitment_title,
                       r.request_number,
                       CONCAT(c.first_name, ' ', c.last_name) AS full_name
                FROM {$this->tableName()} c
                LEFT JOIN {$this->prefix}recruitments r ON c.recruitment_id = r.id
                WHERE c.id = ? AND c.system_id = ?
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * تولید کد متقاضی
     */
    public function generateCandidateCode(): string
    {
        $prefix = 'CAND-';
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->tableName()} WHERE system_id = ?"
        );
        $stmt->execute([$this->systemId]);
        $count = (int) $stmt->fetchColumn() + 1;

        do {
            $code = $prefix . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
            $count++;
        } while ($this->candidateCodeExists($code));

        return $code;
    }

    /**
     * بررسی تکراری بودن کد
     */
    public function candidateCodeExists(string $code, ?int $exceptId = null): bool
    {
        if (empty($code)) return false;

        $sql = "SELECT 1 FROM {$this->tableName()} 
                WHERE system_id = ? AND candidate_code = ?";
        $params = [$this->systemId, $code];

        if ($exceptId !== null) {
            $sql .= " AND id != ?";
            $params[] = $exceptId;
        }
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * بررسی تکراری بودن کد ملی
     */
    public function nationalIdExists(string $nationalId, ?int $exceptId = null): bool
    {
        if (empty($nationalId)) return false;

        $sql = "SELECT 1 FROM {$this->tableName()} 
                WHERE system_id = ? AND national_id = ?";
        $params = [$this->systemId, $nationalId];

        if ($exceptId !== null) {
            $sql .= " AND id != ?";
            $params[] = $exceptId;
        }
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * دریافت متقاضیان یک آگهی
     */
    public function getByRecruitment(int $recruitmentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->tableName()} 
             WHERE recruitment_id = ? AND system_id = ?
             ORDER BY applied_date DESC"
        );
        $stmt->execute([$recruitmentId, $this->systemId]);
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
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) AS new_count,
                SUM(CASE WHEN status IN ('screening', 'interview', 'technical_test') THEN 1 ELSE 0 END) AS in_progress_count,
                SUM(CASE WHEN status = 'offer' THEN 1 ELSE 0 END) AS offer_count,
                SUM(CASE WHEN status = 'hired' THEN 1 ELSE 0 END) AS hired_count,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count,
                AVG(rating) AS avg_rating
             FROM {$this->tableName()}
             WHERE system_id = ?"
        );
        $stmt->execute([$this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total'        => (int) ($row['total_count'] ?? 0),
            'new'          => (int) ($row['new_count'] ?? 0),
            'in_progress'  => (int) ($row['in_progress_count'] ?? 0),
            'offer'        => (int) ($row['offer_count'] ?? 0),
            'hired'        => (int) ($row['hired_count'] ?? 0),
            'rejected'     => (int) ($row['rejected_count'] ?? 0),
            'avg_rating'   => round((float) ($row['avg_rating'] ?? 0), 2),
        ];
    }

    /**
     * گزینه‌های وضعیت
     */
    public static function getStatusOptions(): array
    {
        return [
            'new'            => 'جدید',
            'screening'      => 'بررسی اولیه',
            'interview'      => 'مصاحبه',
            'technical_test' => 'آزمون فنی',
            'offer'          => 'پیشنهاد',
            'hired'          => 'استخدام شده',
            'rejected'       => 'رد شده',
            'withdrawn'      => 'انصراف',
        ];
    }

    /**
     * گزینه‌های منبع
     */
    public static function getSourceOptions(): array
    {
        return [
            'website'      => 'وب‌سایت',
            'linkedin'     => 'لینکدین',
            'referral'     => 'معرفی',
            'agency'       => 'آژانس',
            'job_fair'     => 'نمایشگاه کار',
            'university'   => 'دانشگاه',
            'social_media' => 'شبکه اجتماعی',
            'other'        => 'سایر',
        ];
    }

    /**
     * رنگ وضعیت
     */
    public static function getStatusClass(string $status): string
    {
        $classes = [
            'new'            => 'hr-status-info',
            'screening'      => 'hr-status-warning',
            'interview'      => 'hr-status-warning',
            'technical_test' => 'hr-status-warning',
            'offer'          => 'hr-status-info',
            'hired'          => 'hr-status-active',
            'rejected'       => 'hr-status-danger',
            'withdrawn'      => 'hr-status-inactive',
        ];
        return $classes[$status] ?? 'hr-status-info';
    }
}