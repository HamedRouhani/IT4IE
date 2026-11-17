<?php

declare(strict_types=1);

namespace App\Software\EngineeringEconomy\Controllers;

use App\Core\Controller;
use App\Core\Database;

/** Creates a labor-cost cash-flow study from an eligible Time Study report. */
final class CostIntegrationController extends Controller
{
    public function fromTimeStudy(int $studyId = 0): void
    {
        $userId = $this->userId();

        try {
            [$study, $result, $standardLaborSeconds] = $this->loadEligibleStudy($userId, $studyId);
            $this->renderSoftware('project/time-study-import', [
                'pageTitle' => 'برآورد هزینه از زمان‌سنجی',
                'title' => 'برآورد هزینه از زمان‌سنجی - اقتصاد مهندسی',
                'softwareName' => 'Engineering Economy Analyzer',
                'study' => $study,
                'result' => $result,
                'standardLaborSeconds' => $standardLaborSeconds,
                'flash' => ee_flash_get(),
            ], 'engineering-economy');
        } catch (\InvalidArgumentException $e) {
            ee_flash_set('danger', $e->getMessage());
            $this->redirect('/software/time-study-analyzer/?controller=study&action=show&id=' . $studyId);
        }
    }

    public function createFromTimeStudy(int $studyId = 0): void
    {
        $userId = $this->userId();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(ee_url('integration', 'fromTimeStudy', ['id' => $studyId]));
        }
        $this->verifyCsrf();

        try {
            [$study, , $standardLaborSeconds] = $this->loadEligibleStudy($userId, $studyId);
            $rate = $this->number($_POST['labor_rate_per_hour'] ?? null, 'نرخ دستمزد ساعتی', 0.0001, 1.0e12);
            $quantity = $this->number($_POST['annual_quantity'] ?? null, 'تیراژ سالانه', 0.0001, 1.0e10);
            $period = (int) $this->number($_POST['study_period'] ?? null, 'افق تحلیل', 1, 100, true);
            $growth = $this->number($_POST['annual_growth_pct'] ?? '0', 'رشد سالانه هزینهٔ نیروی کار', -99, 100);
            $marr = $this->number($_POST['marr_pct'] ?? '12', 'نرخ بازده حداقل (MARR)', -99.999, 100);
            $currency = strtoupper(trim((string) ($_POST['currency'] ?? 'IRT')));
            if (!in_array($currency, ['IRT', 'IRR', 'USD', 'EUR'], true)) {
                throw new \InvalidArgumentException('واحد پول انتخاب‌شده معتبر نیست.');
            }

            $unitLaborCost = ($standardLaborSeconds / 3600.0) * $rate;
            $annualBaseCost = $unitLaborCost * $quantity;
            if (!is_finite($annualBaseCost) || $annualBaseCost <= 0 || $annualBaseCost > 9.0e17) {
                throw new \InvalidArgumentException('هزینهٔ سالانه با ورودی‌های فعلی قابل محاسبه یا ثبت نیست.');
            }

            $db = Database::getInstance();
            $db->beginTransaction();
            try {
                $title = 'هزینهٔ نیروی کار - ' . (string) $study['process_name'];
                $description = 'برآورد اقتصادی بر پایهٔ زمان استاندارد نیروی انسانی در مطالعهٔ '
                    . (string) $study['study_no'] . ' (' . (string) $study['title'] . ').';
                $stmt = $db->prepare(
                    "INSERT INTO ee_projects
                        (user_id, title, description, study_kind, currency, study_period,
                         marr_pct, inflation_pct, tax_rate_pct, depreciation_method, status, notes)
                     VALUES (?, ?, ?, 'cost_only', ?, ?, ?, 0, 0, 'none', 'draft', ?)"
                );
                $stmt->execute([
                    $userId,
                    function_exists('mb_substr') ? mb_substr($title, 0, 180) : substr($title, 0, 180),
                    $description,
                    $currency,
                    $period,
                    $marr,
                    'جریان‌های نقدی اسمی؛ رشد سالانهٔ هزینهٔ نیروی کار برابر نرخ ثبت‌شده است. '
                    . 'هزینهٔ ماشین، انرژی، سربار و مواد در این برآورد وارد نشده‌اند.',
                ]);
                $projectId = (int) $db->lastInsertId();
                $projectNo = 'EE-' . date('ymd') . '-' . str_pad((string) $projectId, 5, '0', STR_PAD_LEFT);
                $db->prepare('UPDATE ee_projects SET project_no=? WHERE id=? AND user_id=?')
                    ->execute([$projectNo, $projectId, $userId]);

                $alternative = $db->prepare(
                    'INSERT INTO ee_alternatives (project_id, name, description, life_years, is_baseline, sequence_no)
                     VALUES (?, ?, ?, ?, 1, 1)'
                );
                $alternative->execute([
                    $projectId,
                    'هزینهٔ نیروی کار استاندارد',
                    'هزینهٔ مستقیم نیروی انسانی بر پایهٔ زمان استاندارد هر واحد و تیراژ سالانه.',
                    $period,
                ]);
                $alternativeId = (int) $db->lastInsertId();

                $flow = $db->prepare(
                    'INSERT INTO ee_cashflows (project_id, alternative_id, year_no, inflow, outflow, label, note)
                     VALUES (?, ?, ?, 0, ?, ?, ?)'
                );
                $flow->execute([$projectId, $alternativeId, 0, 0, 'سال پایه', 'هزینهٔ عملیاتی در سال پایه منظور نشده است.']);
                for ($year = 1; $year <= $period; $year++) {
                    $annualCost = $annualBaseCost * ((1.0 + ($growth / 100.0)) ** ($year - 1));
                    if (!is_finite($annualCost) || $annualCost < 0 || $annualCost > 9.0e13) {
                        throw new \InvalidArgumentException('برآورد هزینه در افق انتخابی از محدودهٔ قابل ثبت خارج می‌شود.');
                    }
                    $flow->execute([
                        $projectId,
                        $alternativeId,
                        $year,
                        $annualCost,
                        'هزینهٔ نیروی کار سال ' . $year,
                        'زمان استاندارد نیروی انسانی هر واحد × تیراژ همان سال × نرخ دستمزد ساعتی.',
                    ]);
                }

                $assumptions = [
                    ['منبع زمان استاندارد', (string) $study['study_no'], 'شناسهٔ مطالعه', 'Time Study Analyzer', 'گزارش نهایی زمان‌سنجی مستقیم.'],
                    ['زمان استاندارد نیروی انسانی هر واحد', $standardLaborSeconds, 'ثانیه/واحد', 'Time Study Analyzer', 'زمان نرمال دستی پس از اعمال allowance؛ زمان ماشین جدا شده است.'],
                    ['نرخ دستمزد ساعتی', $rate, $currency . '/ساعت', 'ورودی کاربر', 'نرخ مستقیم نیروی انسانی؛ اجزای هزینهٔ دیگر منظور نشده‌اند.'],
                    ['تیراژ سالانهٔ پایه', $quantity, 'واحد/سال', 'ورودی کاربر', 'مبنای هزینه در سال اول تحلیل.'],
                    ['هزینهٔ مستقیم نیروی کار هر واحد', $unitLaborCost, $currency . '/واحد', 'زمان استاندارد × نرخ ساعتی', 'فقط هزینهٔ مستقیم نیروی انسانی.'],
                    ['هزینهٔ سالانهٔ پایه', $annualBaseCost, $currency . '/سال', 'هزینهٔ واحد × تیراژ', 'مبنای جریان هزینه در سال اول.'],
                    ['رشد سالانهٔ هزینهٔ نیروی کار', $growth, '%', 'ورودی کاربر', 'برای جریان‌های اسمی سال‌های بعد اعمال شده است.'],
                ];
                $assumptionStmt = $db->prepare(
                    'INSERT INTO ee_assumptions (project_id, title, value, unit, source, note, sequence_no)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                foreach ($assumptions as $index => [$name, $value, $unit, $source, $note]) {
                    $assumptionStmt->execute([
                        $projectId,
                        $name,
                        is_numeric($value) ? number_format((float) $value, 8, '.', '') : $value,
                        $unit,
                        $source,
                        $note,
                        $index + 1,
                    ]);
                }
                $db->commit();
            } catch (\Throwable $e) {
                if ($db->inTransaction()) $db->rollBack();
                throw $e;
            }

            ee_flash_set('success', 'پروژهٔ هزینه با زمان استاندارد نیروی انسانی و فرض‌های ثبت‌شده ساخته شد.');
            $this->redirect(ee_url('project', 'show', ['id' => $projectId]));
        } catch (\InvalidArgumentException $e) {
            ee_flash_set('danger', $e->getMessage());
            $this->redirect(ee_url('integration', 'fromTimeStudy', ['id' => $studyId]));
        } catch (\Throwable $e) {
            error_log('Time Study cost integration failed: ' . $e->getMessage());
            ee_flash_set('danger', 'ساخت پروژهٔ هزینه انجام نشد؛ داده‌های مطالعه و ورودی‌ها را بررسی کنید.');
            $this->redirect(ee_url('integration', 'fromTimeStudy', ['id' => $studyId]));
        }
    }

    /** @return array{0:array,1:array,2:float} */
    private function loadEligibleStudy(int $userId, int $studyId): array
    {
        if ($studyId < 1) {
            throw new \InvalidArgumentException('شناسهٔ مطالعه معتبر نیست.');
        }
        require_once APP_PATH . '/software/time-study-analyzer/app/Models/StudyRepository.php';
        require_once APP_PATH . '/software/time-study-analyzer/app/Services/StudyCalculator.php';

        $repository = 'App\\Software\\TimeStudyAnalyzer\\Models\\StudyRepository';
        $study = (new $repository())->find($userId, $studyId);
        if (!$study || $study['method'] !== 'stopwatch' || $study['status'] !== 'completed') {
            throw new \InvalidArgumentException('فقط مطالعهٔ زمان‌سنجی مستقیمِ نهایی‌شده و متعلق به حساب شما قابل انتقال است.');
        }

        $calculator = 'App\\Software\\TimeStudyAnalyzer\\Services\\StudyCalculator';
        $result = $calculator::stopwatch($study, $study['elements'], $study['cycles'], $study['observations']);
        if (empty($result['sample_is_sufficient']) || (int) $result['complete_valid_cycles'] < 1) {
            throw new \InvalidArgumentException('برای برآورد هزینه، مطالعه باید چرخه‌های کامل معتبر و نمونهٔ کافی داشته باشد.');
        }

        $allowance = (
            (float) $study['personal_allowance_pct'] + (float) $study['fatigue_allowance_pct']
            + (float) $study['delay_allowance_pct'] + (float) $study['other_allowance_pct']
        ) / 100.0;
        $laborNormal = (float) $result['labor_normal_seconds'];
        $standardLabor = $study['allowance_basis'] === 'standard'
            ? ($allowance < 1.0 ? $laborNormal / (1.0 - $allowance) : 0.0)
            : $laborNormal * (1.0 + $allowance);
        if (!is_finite($standardLabor) || $standardLabor <= 0) {
            throw new \InvalidArgumentException('زمان استاندارد نیروی انسانی مثبت و معتبر از مطالعه به دست نیامد.');
        }

        return [$study, $result, $standardLabor];
    }

    private function number($raw, string $label, float $min, float $max, bool $integer = false): float
    {
        if (is_array($raw) || is_object($raw)) {
            throw new \InvalidArgumentException($label . ' معتبر نیست.');
        }
        $value = trim(str_replace([',', '٬', ' '], '', $this->latinDigits((string) $raw)));
        if ($value === '' || !is_numeric($value) || !is_finite((float) $value)) {
            throw new \InvalidArgumentException($label . ' را به‌صورت عدد معتبر وارد کنید.');
        }
        $number = (float) $value;
        if ($number < $min || $number > $max || ($integer && floor($number) !== $number)) {
            throw new \InvalidArgumentException($label . ' خارج از محدودهٔ مجاز است.');
        }
        return $number;
    }

    private function latinDigits(string $value): string
    {
        return strtr($value, [
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
            '٫'=>'.',
        ]);
    }

    private function userId(): int
    {
        $this->requireAuth();
        return (int) ($_SESSION['user_id'] ?? 0);
    }
}
