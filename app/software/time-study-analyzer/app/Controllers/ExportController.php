<?php
namespace App\Software\TimeStudyAnalyzer\Controllers;

use App\Software\TimeStudyAnalyzer\Models\StudyRepository;
use App\Software\TimeStudyAnalyzer\Services\StudyCalculator;

final class ExportController extends StudyControllerBase
{
    private static function safeCell($value): string
    {
        $value = (string) ($value ?? '');
        return preg_match('/^[\x00-\x20]*[=+@\-]/u', $value) ? "'" . $value : $value;
    }

    public function study(int $id): void
    {
        $userId = $this->userId();
        $study = (new StudyRepository())->find($userId, $id);
        if (!$study) {
            http_response_code(404);
            ts_flash_set('danger', 'مطالعه برای خروجی پیدا نشد.');
            $this->redirect(ts_url('study'));
        }
        if (ob_get_length()) ob_clean();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="time-study-' . (int) $id . '.csv"');
        header('X-Content-Type-Options: nosniff');
        $out = fopen('php://output', 'wb');
        if (!$out) throw new \RuntimeException('ساخت فایل خروجی ممکن نشد.');
        fwrite($out, "\xEF\xBB\xBF");
        if ($study['method'] === 'stopwatch') {
            fputcsv($out, ['Study', self::safeCell($study['study_no']), self::safeCell($study['title']), 'Method', 'Stopwatch'], ',', '"', '');
            $result = StudyCalculator::stopwatch($study, $study['elements'], $study['cycles'], $study['observations']);
            fputcsv($out, ['Normal cycle seconds', $result['normal_cycle_seconds'], 'Standard cycle seconds', $result['standard_cycle_seconds'], 'Required cycles', $result['required_cycles'], 'Valid complete cycles', $result['complete_valid_cycles']], ',', '"', '');
            fputcsv($out, ['Cycle', 'Observed at (Jalali)', 'Cycle status', 'Element code', 'Element', 'Type', 'Observed seconds', 'Rating %', 'Normal seconds', 'Frequency per cycle', 'Record valid', 'Reason', 'Note'], ',', '"', '');
            foreach ($study['observations'] as $row) {
                fputcsv($out, [
                    $row['cycle_no'], ts_date($row['observed_at'], 'Y/m/d H:i'), $row['cycle_status'], $row['sequence_no'],
                    self::safeCell($row['element_name']), $row['element_type'], $row['observed_seconds'], $row['performance_rating_pct'],
                    (float) $row['observed_seconds'] * (float) $row['performance_rating_pct'] / 100,
                    $row['frequency_per_cycle'], (int) $row['is_valid'] ? 'Yes' : 'No', self::safeCell($row['exclusion_reason']), self::safeCell($row['note']),
                ], ',', '"', '');
            }
        } else {
            fputcsv($out, ['Study', self::safeCell($study['study_no']), self::safeCell($study['title']), 'Method', 'Work sampling'], ',', '"', '');
            $result = StudyCalculator::workSampling($study, $study['categories'], $study['slots']);
            fputcsv($out, ['Observed', $result['observed_count'], 'Missed', $result['missed_count'], 'Required conservative sample', $result['required_sample_size'], 'Productive share', $result['productive_share']], ',', '"', '');
            fputcsv($out, ['Planned at (Jalali)', 'Actual at (Jalali)', 'Status', 'Category', 'Category class', 'Observer', 'Note'], ',', '"', '');
            foreach ($study['slots'] as $row) {
                fputcsv($out, [
                    ts_date($row['scheduled_at'], 'Y/m/d H:i'), ts_date($row['actual_at'], 'Y/m/d H:i'),
                    $row['slot_status'], self::safeCell($row['category_name']), $row['category_type'] ?? '', self::safeCell($row['observer_name']), self::safeCell($row['note']),
                ], ',', '"', '');
            }
        }
        fclose($out);
        exit;
    }
}
