<?php
namespace App\Software\TimeStudyAnalyzer\Models;

use App\Core\Database;

final class StudyRepository
{
    private \PDO $db;

    public function __construct() { $this->db = Database::getInstance(); }

    public function dashboard(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total,
                    SUM(status='draft') AS drafts,
                    SUM(status='active') AS active,
                    SUM(status='completed') AS completed,
                    SUM(method='stopwatch') AS stopwatch,
                    SUM(method='work_sampling') AS sampling
             FROM ts_studies WHERE user_id=? AND status<>'archived'"
        );
        $stmt->execute([$userId]);
        $stats = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        foreach ($stats as &$value) $value = (int) $value;
        unset($value);
        $stats['recent'] = $this->list($userId, false, 8);
        return $stats;
    }

    public function list(int $userId, bool $includeArchived = false, int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));
        $where = $includeArchived ? '' : " AND s.status <> 'archived'";
        $stmt = $this->db->prepare(
            'SELECT s.*,
                    (SELECT COUNT(*) FROM ts_cycles c WHERE c.study_id=s.id) AS cycle_count,
                    (SELECT COUNT(*) FROM ts_sampling_slots p WHERE p.study_id=s.id AND p.slot_status<>\'void\') AS sampling_plan_count,
                    (SELECT COUNT(*) FROM ts_sampling_slots p WHERE p.study_id=s.id AND p.slot_status=\'observed\') AS sampling_observed_count
             FROM ts_studies s WHERE s.user_id=?' . $where .
            ' ORDER BY FIELD(s.status, \'active\', \'draft\', \'completed\', \'archived\'), s.study_date DESC, s.id DESC LIMIT ' . $limit
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function create(int $userId, array $data, array $children): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO ts_studies
                 (user_id, method, title, process_name, department, product_name, work_center, operator_ref,
                  study_date, shift_label, unit_name, method_description, work_conditions, confidence_level,
                  precision_pct, allowance_basis, personal_allowance_pct, fatigue_allowance_pct,
                  delay_allowance_pct, other_allowance_pct, allowance_rationale)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            );
            $stmt->execute([
                $userId, $data['method'], $data['title'], $data['process_name'], $data['department'],
                $data['product_name'], $data['work_center'], $data['operator_ref'], $data['study_date'],
                $data['shift_label'], $data['unit_name'], $data['method_description'], $data['work_conditions'],
                $data['confidence_level'], $data['precision_pct'], $data['allowance_basis'],
                $data['personal_allowance_pct'], $data['fatigue_allowance_pct'], $data['delay_allowance_pct'],
                $data['other_allowance_pct'], $data['allowance_rationale'],
            ]);
            $id = (int) $this->db->lastInsertId();
            $code = 'TS-' . date('ymd') . '-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
            $this->db->prepare('UPDATE ts_studies SET study_no=? WHERE id=? AND user_id=?')->execute([$code, $id, $userId]);
            $this->replaceChildren($id, $data['method'], $children);
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function updateDraft(int $userId, int $id, array $data, array $children): void
    {
        $this->db->beginTransaction();
        try {
            $study = $this->lockStudy($userId, $id);
            if ($study['status'] !== 'draft') throw new \InvalidArgumentException('پس از شروع مطالعه، مشخصات پایه و عناصر آن قفل می‌شوند.');
            if ($study['method'] !== $data['method']) throw new \InvalidArgumentException('روش مطالعه پس از ایجاد قابل تغییر نیست.');
            $stmt = $this->db->prepare(
                'UPDATE ts_studies SET title=?, process_name=?, department=?, product_name=?, work_center=?, operator_ref=?,
                 study_date=?, shift_label=?, unit_name=?, method_description=?, work_conditions=?, confidence_level=?,
                 precision_pct=?, allowance_basis=?, personal_allowance_pct=?, fatigue_allowance_pct=?,
                 delay_allowance_pct=?, other_allowance_pct=?, allowance_rationale=? WHERE id=? AND user_id=? AND status=\'draft\''
            );
            $stmt->execute([
                $data['title'], $data['process_name'], $data['department'], $data['product_name'], $data['work_center'],
                $data['operator_ref'], $data['study_date'], $data['shift_label'], $data['unit_name'],
                $data['method_description'], $data['work_conditions'], $data['confidence_level'], $data['precision_pct'],
                $data['allowance_basis'], $data['personal_allowance_pct'], $data['fatigue_allowance_pct'],
                $data['delay_allowance_pct'], $data['other_allowance_pct'], $data['allowance_rationale'], $id, $userId,
            ]);
            $this->db->prepare('DELETE FROM ts_elements WHERE study_id=?')->execute([$id]);
            $this->db->prepare('DELETE FROM ts_sampling_categories WHERE study_id=?')->execute([$id]);
            $this->replaceChildren($id, $data['method'], $children);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function find(int $userId, int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM ts_studies WHERE user_id=? AND id=? LIMIT 1');
        $stmt->execute([$userId, $id]);
        $study = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$study) return null;
        $study['elements'] = $this->elements($id);
        $study['cycles'] = $this->cycles($id);
        $study['observations'] = $this->timeObservations($id);
        $study['categories'] = $this->categories($id);
        $study['slots'] = $this->samplingSlots($id);
        return $study;
    }

    public function elements(int $studyId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM ts_elements WHERE study_id=? ORDER BY sequence_no, id');
        $stmt->execute([$studyId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function cycles(int $studyId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM ts_cycles WHERE study_id=? ORDER BY cycle_no DESC');
        $stmt->execute([$studyId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function timeObservations(int $studyId): array
    {
        $stmt = $this->db->prepare(
            'SELECT o.*, c.cycle_no, c.cycle_status, c.observed_at, e.sequence_no, e.name AS element_name,
                    e.element_type, e.frequency_per_cycle
             FROM ts_time_observations o
             JOIN ts_cycles c ON c.id=o.cycle_id AND c.study_id=o.study_id
             JOIN ts_elements e ON e.id=o.element_id AND e.study_id=o.study_id
             WHERE o.study_id=? ORDER BY c.cycle_no DESC, e.sequence_no'
        );
        $stmt->execute([$studyId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function categories(int $studyId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM ts_sampling_categories WHERE study_id=? ORDER BY sequence_no, id');
        $stmt->execute([$studyId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function samplingSlots(int $studyId): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.*, c.name AS category_name, c.category_type
             FROM ts_sampling_slots p LEFT JOIN ts_sampling_categories c
               ON c.id=p.category_id AND c.study_id=p.study_id
             WHERE p.study_id=? ORDER BY p.scheduled_at DESC, p.id DESC'
        );
        $stmt->execute([$studyId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function start(int $userId, int $id): void
    {
        $study = $this->lockStudy($userId, $id);
        if ($study['status'] !== 'draft') throw new \InvalidArgumentException('فقط مطالعهٔ پیش‌نویس قابل شروع است.');
        $this->db->prepare("UPDATE ts_studies SET status='active' WHERE id=? AND user_id=? AND status='draft'")->execute([$id, $userId]);
    }

    public function addCycle(int $userId, int $studyId, array $observations, string $cycleStatus, ?string $exclusionReason, ?string $observer, ?string $note): void
    {
        $this->db->beginTransaction();
        try {
            $study = $this->lockStudy($userId, $studyId);
            if ($study['method'] !== 'stopwatch' || $study['status'] !== 'active') throw new \InvalidArgumentException('ثبت چرخه فقط برای مطالعهٔ زمان‌سنجی فعال مجاز است.');
            $elements = $this->elements($studyId);
            if (!$elements || count($observations) !== count($elements)) throw new \InvalidArgumentException('برای همهٔ عناصر مطالعه، مشاهده وارد کنید.');
            if ($cycleStatus === 'excluded' && trim((string) $exclusionReason) === '') throw new \InvalidArgumentException('برای چرخهٔ نامعتبر، علت حذف الزامی است.');
            $cycleNo = (int) $this->scalar('SELECT COALESCE(MAX(cycle_no),0)+1 FROM ts_cycles WHERE study_id=?', [$studyId]);
            $stmt = $this->db->prepare(
                'INSERT INTO ts_cycles (study_id, cycle_no, observed_at, cycle_status, exclusion_reason, observer_name, note)
                 VALUES (?, ?, CURRENT_TIMESTAMP, ?, ?, ?, ?)'
            );
            $stmt->execute([$studyId, $cycleNo, $cycleStatus, $cycleStatus === 'excluded' ? trim((string) $exclusionReason) : null, $observer, $note]);
            $cycleId = (int) $this->db->lastInsertId();
            $insert = $this->db->prepare(
                'INSERT INTO ts_time_observations
                 (study_id, cycle_id, element_id, observed_seconds, performance_rating_pct, is_valid, exclusion_reason, note)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            foreach ($elements as $element) {
                $elementId = (int) $element['id'];
                if (!isset($observations[$elementId]) || !is_array($observations[$elementId])) throw new \InvalidArgumentException('مشاهدهٔ یکی از عناصر ناقص است.');
                $row = $observations[$elementId];
                $insert->execute([
                    $studyId, $cycleId, $elementId, $row['seconds'],
                    $element['element_type'] === 'machine' ? 100 : $row['rating'],
                    $row['valid'] ? 1 : 0, $row['valid'] ? null : $row['reason'], $row['note'],
                ]);
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function generateSamplingSlots(int $userId, int $studyId, array $dateTimes): int
    {
        $this->db->beginTransaction();
        try {
            $study = $this->lockStudy($userId, $studyId);
            if ($study['method'] !== 'work_sampling' || $study['status'] !== 'active') throw new \InvalidArgumentException('برنامه فقط برای مطالعهٔ نمونه‌برداری فعال ساخته می‌شود.');
            if (count($dateTimes) < 1 || count($dateTimes) > 500) throw new \InvalidArgumentException('تعداد زمان‌های نمونه باید بین ۱ تا ۵۰۰ باشد.');
            $insert = $this->db->prepare('INSERT IGNORE INTO ts_sampling_slots (study_id, scheduled_at) VALUES (?, ?)');
            $inserted = 0;
            foreach ($dateTimes as $dateTime) {
                $insert->execute([$studyId, $dateTime]);
                $inserted += $insert->rowCount();
            }
            $this->db->commit();
            return $inserted;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function recordSamplingSlot(int $userId, int $studyId, int $slotId, ?int $categoryId, string $status, ?string $observer, ?string $note): void
    {
        if (!in_array($status, ['observed', 'missed', 'void'], true)) throw new \InvalidArgumentException('وضعیت مشاهده معتبر نیست.');
        $this->db->beginTransaction();
        try {
            $study = $this->lockStudy($userId, $studyId);
            if ($study['method'] !== 'work_sampling' || $study['status'] !== 'active') throw new \InvalidArgumentException('ثبت نتیجه فقط برای مطالعهٔ نمونه‌برداری فعال مجاز است.');
            if ($status === 'observed') {
                if (!$categoryId) throw new \InvalidArgumentException('دستهٔ مشاهده را انتخاب کنید.');
                $check = $this->db->prepare('SELECT 1 FROM ts_sampling_categories WHERE study_id=? AND id=?');
                $check->execute([$studyId, $categoryId]);
                if (!$check->fetchColumn()) throw new \InvalidArgumentException('دستهٔ مشاهده برای این مطالعه معتبر نیست.');
            } else {
                $categoryId = null;
                if (trim((string) $note) === '') throw new \InvalidArgumentException('برای مشاهدهٔ ازدست‌رفته یا باطل، علت را ثبت کنید.');
            }
            $stmt = $this->db->prepare(
                'UPDATE ts_sampling_slots SET category_id=?, slot_status=?, actual_at=IF(?=\'observed\', CURRENT_TIMESTAMP, NULL),
                 observer_name=?, note=? WHERE study_id=? AND id=? AND slot_status=\'planned\''
            );
            $stmt->execute([$categoryId, $status, $status, $observer, $note, $studyId, $slotId]);
            if ($stmt->rowCount() !== 1) throw new \InvalidArgumentException('زمان نمونه پیدا نشد یا قبلاً تعیین تکلیف شده است.');
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function complete(int $userId, int $studyId): void
    {
        $this->db->beginTransaction();
        try {
            $study = $this->lockStudy($userId, $studyId);
            if ($study['status'] !== 'active') throw new \InvalidArgumentException('فقط مطالعهٔ فعال قابل نهایی‌سازی است.');
            if ($study['method'] === 'stopwatch') {
                $stmt = $this->db->prepare("SELECT COUNT(*) FROM ts_cycles c WHERE c.study_id=? AND c.cycle_status='valid' AND NOT EXISTS (
                    SELECT 1 FROM ts_elements e LEFT JOIN ts_time_observations o ON o.study_id=e.study_id AND o.element_id=e.id AND o.cycle_id=c.id AND o.is_valid=1
                    WHERE e.study_id=c.study_id AND o.id IS NULL)");
                $stmt->execute([$studyId]);
                if ((int) $stmt->fetchColumn() < 1) throw new \InvalidArgumentException('برای نهایی‌سازی، دست‌کم یک چرخهٔ کامل و معتبر لازم است.');
            } else {
                $stmt = $this->db->prepare("SELECT SUM(slot_status='planned'), SUM(slot_status='observed') FROM ts_sampling_slots WHERE study_id=?");
                $stmt->execute([$studyId]);
                [$pending, $observed] = array_map('intval', $stmt->fetch(\PDO::FETCH_NUM) ?: [0, 0]);
                if ($pending > 0) throw new \InvalidArgumentException('همهٔ زمان‌های برنامه‌ریزی‌شده را مشاهده کنید، ازدست‌رفته علامت بزنید یا با علت باطل کنید.');
                if ($observed < 1) throw new \InvalidArgumentException('برای نهایی‌سازی، دست‌کم یک مشاهدهٔ معتبر لازم است.');
            }
            $this->db->prepare("UPDATE ts_studies SET status='completed', completed_at=CURRENT_TIMESTAMP WHERE id=? AND user_id=?")->execute([$studyId, $userId]);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function archive(int $userId, int $studyId): void
    {
        $study = $this->lockStudy($userId, $studyId);
        if ($study['status'] !== 'completed') throw new \InvalidArgumentException('فقط مطالعهٔ نهایی‌شده قابل بایگانی است.');
        $this->db->prepare("UPDATE ts_studies SET status='archived' WHERE id=? AND user_id=? AND status='completed'")->execute([$studyId, $userId]);
    }

    private function replaceChildren(int $studyId, string $method, array $children): void
    {
        if ($method === 'stopwatch') {
            $stmt = $this->db->prepare(
                'INSERT INTO ts_elements (study_id, sequence_no, element_code, name, element_type, frequency_per_cycle, notes)
                 VALUES (?,?,?,?,?,?,?)'
            );
            foreach ($children as $index => $child) {
                $stmt->execute([$studyId, $index + 1, $child['code'], $child['name'], $child['type'], $child['frequency'], $child['notes']]);
            }
        } else {
            $stmt = $this->db->prepare('INSERT INTO ts_sampling_categories (study_id, sequence_no, name, category_type) VALUES (?,?,?,?)');
            foreach ($children as $index => $child) $stmt->execute([$studyId, $index + 1, $child['name'], $child['type']]);
        }
    }

    private function lockStudy(int $userId, int $studyId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM ts_studies WHERE user_id=? AND id=? FOR UPDATE');
        $stmt->execute([$userId, $studyId]);
        $study = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$study) throw new \InvalidArgumentException('مطالعه پیدا نشد.');
        return $study;
    }

    private function scalar(string $sql, array $params): mixed
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
