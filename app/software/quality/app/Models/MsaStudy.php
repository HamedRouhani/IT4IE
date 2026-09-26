<?php
/**
 * ============================================================
 * Quality Analyzer — MsaStudy Model
 * ============================================================
 * مسیر: app/software/quality/app/Models/MsaStudy.php
 * جدول: qc_msa_studies
 * ============================================================
 */

namespace App\Software\Quality\Models;

use App\Core\Model;

class MsaStudy extends Model
{
    protected $table      = 'qc_msa_studies';
    protected $primaryKey = 'id';

    /**
     * روش‌های پشتیبانی‌شده
     */
    public const METHODS = [
        'average_range' => 'Average & Range',
        'anova'         => 'ANOVA',
    ];

    /**
     * نتایج ممکن
     */
    public const VERDICTS = [
        'acceptable'   => 'قابل قبول',
        'marginal'     => 'مرزی',
        'unacceptable' => 'غیرقابل قبول',
    ];

    // ═════════════════════════════════════════════
    // دریافت‌ها
    // ═════════════════════════════════════════════

    /**
     * دریافت مطالعات MSA یک پروژه
     */
    public function findByProject(int $projectId): array
    {
        return $this->findAll(
            ['project_id' => $projectId],
            'created_at DESC'
        );
    }

    /**
     * دریافت مطالعات MSA یک سیستم
     */
    public function findBySystem(int $systemId): array
    {
        return $this->query(
            "SELECT m.*, p.name AS project_name
             FROM {$this->table} m
             JOIN qc_projects p ON p.id = m.project_id
             WHERE m.system_id = ?
             ORDER BY m.created_at DESC",
            [$systemId]
        );
    }

    /**
     * دریافت مطالعه با اطلاعات پروژه
     */
    public function findWithProject(int $studyId): ?array
    {
        return $this->queryOne(
            "SELECT m.*, p.name AS project_name
             FROM {$this->table} m
             JOIN qc_projects p ON p.id = m.project_id
             WHERE m.id = ? LIMIT 1",
            [$studyId]
        );
    }

    // ═════════════════════════════════════════════
    // ذخیره
    // ═════════════════════════════════════════════

    /**
     * ایجاد مطالعه MSA جدید
     */
    public function createForProject(int $systemId, int $projectId, array $data): int
    {
        $payload = [
            'system_id'     => $systemId,
            'project_id'    => $projectId,
            'name'          => $data['name']          ?? '',
            'method'        => $data['method']        ?? 'anova',
            'num_parts'     => (int) ($data['num_parts']     ?? 0),
            'num_operators' => (int) ($data['num_operators'] ?? 0),
            'num_trials'    => (int) ($data['num_trials']    ?? 0),
            'data_json'     => isset($data['data_json'])
                                ? (is_string($data['data_json'])
                                    ? $data['data_json']
                                    : json_encode($data['data_json'], JSON_UNESCAPED_UNICODE))
                                : null,
            'ev'            => $data['ev']            ?? null,
            'av'            => $data['av']            ?? null,
            'grr'           => $data['grr']           ?? null,
            'pv'            => $data['pv']            ?? null,
            'tv'            => $data['tv']            ?? null,
            'pct_ev'        => $data['pct_ev']        ?? null,
            'pct_av'        => $data['pct_av']        ?? null,
            'pct_grr'       => $data['pct_grr']       ?? null,
            'pct_pv'        => $data['pct_pv']        ?? null,
            'ndc'           => isset($data['ndc']) ? (int) $data['ndc'] : null,
            'verdict'       => $data['verdict']       ?? null,
            'created_at'    => date('Y-m-d H:i:s'),
        ];

        return (int) $this->create($payload);
    }

    /**
     * به‌روزرسانی نتایج MSA
     */
    public function updateResults(int $studyId, array $data): bool
    {
        $payload = [];
        foreach (['ev','av','grr','pv','tv','pct_ev','pct_av','pct_grr','pct_pv'] as $k) {
            if (isset($data[$k])) $payload[$k] = $data[$k];
        }
        if (isset($data['ndc']))     $payload['ndc']     = (int) $data['ndc'];
        if (isset($data['verdict'])) $payload['verdict'] = $data['verdict'];

        if (empty($payload)) return false;
        return $this->update($studyId, $payload);
    }

    // ═════════════════════════════════════════════
    // Decode
    // ═════════════════════════════════════════════

    /**
     * دیکد کردن داده‌های MSA
     */
    public function decodeData(array $study): array
    {
        if (empty($study['data_json'])) return [];
        $decoded = json_decode($study['data_json'], true);
        return is_array($decoded) ? $decoded : [];
    }

    // ═════════════════════════════════════════════
    // ارزیابی
    // ═════════════════════════════════════════════

    /**
     * ارزیابی نهایی بر اساس %GRR
     */
    public static function verdict(?float $pctGrr): string
    {
        if ($pctGrr === null) return 'unknown';
        if ($pctGrr < 10.0)   return 'acceptable';
        if ($pctGrr <= 30.0)  return 'marginal';
        return 'unacceptable';
    }

    /**
     * برچسب فارسی ارزیابی
     */
    public static function verdictLabel(?float $pctGrr): string
    {
        return match (self::verdict($pctGrr)) {
            'acceptable'   => 'قابل قبول',
            'marginal'     => 'مرزی',
            'unacceptable' => 'غیرقابل قبول',
            default        => 'نامشخص',
        };
    }

    /**
     * رنگ ارزیابی (برای badge)
     */
    public static function verdictColor(?float $pctGrr): string
    {
        return match (self::verdict($pctGrr)) {
            'acceptable'   => 'success',
            'marginal'     => 'warning',
            'unacceptable' => 'danger',
            default        => 'secondary',
        };
    }

    // ═════════════════════════════════════════════
    // آمار
    // ═════════════════════════════════════════════

    /**
     * شمارش کل مطالعات MSA
     */
    public function countBySystem(int $systemId): int
    {
        $row = $this->queryOne(
            "SELECT COUNT(*) AS c FROM {$this->table} WHERE system_id = ?",
            [$systemId]
        );
        return (int) ($row['c'] ?? 0);
    }

    /**
     * شمارش مطالعات قابل قبول (%GRR < 10)
     */
    public function countAcceptable(int $systemId): int
    {
        $row = $this->queryOne(
            "SELECT COUNT(*) AS c FROM {$this->table}
             WHERE system_id = ? AND pct_grr < 10",
            [$systemId]
        );
        return (int) ($row['c'] ?? 0);
    }

    /**
     * میانگین %GRR سیستم
     */
    public function averagePctGrr(int $systemId): ?float
    {
        $row = $this->queryOne(
            "SELECT AVG(pct_grr) AS avg_grr FROM {$this->table}
             WHERE system_id = ? AND pct_grr IS NOT NULL",
            [$systemId]
        );
        return $row && $row['avg_grr'] !== null ? (float) $row['avg_grr'] : null;
    }

    /**
     * حذف مطالعه MSA
     */
    public function deleteStudy(int $studyId): bool
    {
        return $this->delete($studyId);
    }
}