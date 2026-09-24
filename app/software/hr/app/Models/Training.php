<?php
namespace App\Software\Hr\Models;

/**
 * ============================================================
 * Training Model - دوره‌های آموزشی
 * ============================================================
 */
class Training extends BaseModel
{
    protected $table = 'trainings';

    protected $fillable = [
        'code', 'title', 'training_type', 'category', 'provider', 'instructor',
        'description', 'objectives', 'content', 'target_audience', 'prerequisites',
        'duration_hours', 'delivery_mode', 'location',
        'start_date', 'end_date', 'registration_deadline',
        'max_participants', 'current_participants',
        'cost_per_person', 'total_cost', 'has_certificate', 'status',
    ];

    public static function getTypeOptions(): array
    {
        return [
            'internal'      => 'درون‌سازمانی',
            'external'      => 'برون‌سازمانی',
            'online'        => 'آنلاین',
            'workshop'      => 'کارگاه',
            'seminar'       => 'سمینار',
            'certification' => 'گواهی‌نامه',
            'on_the_job'    => 'آموزش ضمن کار',
            'mentoring'     => 'منتورینگ',
            'coaching'      => 'کوچینگ',
        ];
    }

    public static function getDeliveryModeOptions(): array
    {
        return [
            'in_person'          => 'حضوری',
            'online_live'        => 'آنلاین زنده',
            'online_self_paced'  => 'آنلاین خودآموز',
            'hybrid'             => 'ترکیبی',
        ];
    }

    public static function getStatusOptions(): array
    {
        return [
            'draft'       => 'پیش‌نویس',
            'planned'     => 'برنامه‌ریزی شده',
            'open'        => 'ثبت‌نام باز',
            'in_progress' => 'در حال اجرا',
            'completed'   => 'تکمیل شده',
            'cancelled'   => 'لغو شده',
        ];
    }

    public function search(array $filters = []): array
    {
        $sql = "SELECT t.* FROM {$this->tableName()} t WHERE t.system_id = :sid";
        $params = ['sid' => $this->systemId];

        if (!empty($filters['q'])) {
            $sql .= " AND (t.title LIKE :q OR t.code LIKE :q2 OR t.provider LIKE :q3)";
            $params['q']  = '%' . $filters['q'] . '%';
            $params['q2'] = '%' . $filters['q'] . '%';
            $params['q3'] = '%' . $filters['q'] . '%';
        }
        if (!empty($filters['training_type'])) {
            $sql .= " AND t.training_type = :tt";
            $params['tt'] = $filters['training_type'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :st";
            $params['st'] = $filters['status'];
        }
        if (!empty($filters['category'])) {
            $sql .= " AND t.category = :cat";
            $params['cat'] = $filters['category'];
        }

        $sql .= " ORDER BY t.start_date DESC, t.id DESC LIMIT 500";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getStats(): array
    {
        $sql = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status='open' THEN 1 ELSE 0 END) AS open,
                    SUM(CASE WHEN status='in_progress' THEN 1 ELSE 0 END) AS in_progress,
                    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) AS completed,
                    SUM(CASE WHEN status IN ('draft','planned') THEN 1 ELSE 0 END) AS draft,
                    COALESCE(SUM(current_participants), 0) AS total_enrolled
                FROM {$this->tableName()}
                WHERE system_id = :sid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['sid' => $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total'          => (int) ($row['total'] ?? 0),
            'open'           => (int) ($row['open'] ?? 0),
            'in_progress'    => (int) ($row['in_progress'] ?? 0),
            'completed'      => (int) ($row['completed'] ?? 0),
            'draft'          => (int) ($row['draft'] ?? 0),
            'total_enrolled' => (int) ($row['total_enrolled'] ?? 0),
        ];
    }

    public function findWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT t.* FROM {$this->tableName()} t WHERE t.id = :id AND t.system_id = :sid LIMIT 1"
        );
        $stmt->execute(['id' => $id, 'sid' => $this->systemId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getSelectList(): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, title, code, status FROM {$this->tableName()}
             WHERE system_id = :sid ORDER BY title ASC"
        );
        $stmt->execute(['sid' => $this->systemId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * هماهنگ‌سازی شمارنده ثبت‌نام (current_participants)
     */
    public function syncEnrolledCount(int $id): void
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM hr_enrollments
             WHERE training_id = :tid AND system_id = :sid
             AND status IN ('approved','attended','completed')"
        );
        $stmt->execute(['tid' => $id, 'sid' => $this->systemId]);
        $count = (int) $stmt->fetchColumn();
        $this->update($id, ['current_participants' => $count]);
    }
}