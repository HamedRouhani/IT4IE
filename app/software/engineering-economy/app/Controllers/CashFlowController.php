<?php

declare(strict_types=1);

namespace App\Software\EngineeringEconomy\Controllers;

use App\Core\Controller;
use App\Software\EngineeringEconomy\Models\AlternativeRepository;
use App\Software\EngineeringEconomy\Models\ProjectRepository;
use App\Software\EngineeringEconomy\Services\EeMath;

/**
 * مدیریت گزینه‌ها، جریان نقدی و فرض‌های مستند.
 */
final class CashFlowController extends Controller
{
    public function index(int $projectId): void
    {
        $userId = $this->userId();
        $project = (new ProjectRepository())->find($userId, $projectId);

        if (!$project) {
            ee_flash_set('danger', 'پروژه پیدا نشد.');
            $this->redirect(ee_url('project', 'index'));
        }

        $this->renderSoftware('cashflow/index', [
            'pageTitle' => 'جریان نقدی',
            'title' => 'جریان نقدی - ' . $project['title'],
            'softwareName' => 'Engineering Economy Analyzer',
            'project' => $project,
            'alternatives' => (new AlternativeRepository())->listForProject($projectId),
            'flash' => ee_flash_get(),
        ], 'engineering-economy');
    }

    public function addAlternative(int $projectId): void
    {
        $userId = $this->userId();
        $this->requirePost(ee_url('cashflow', 'index', ['id' => $projectId]));

        $project = (new ProjectRepository())->find($userId, $projectId);
        if (!$project) {
            ee_flash_set('danger', 'پروژه پیدا نشد.');
            $this->redirect(ee_url('project', 'index'));
        }

        try {
            $name = trim((string) ($_POST['name'] ?? ''));
            if ($name === '') throw new \InvalidArgumentException('نام گزینه الزامی است.');

            $life = (int) ($_POST['life_years'] ?? $project['study_period']);
            if ($life < 1 || $life > EeMath::MAX_PERIODS) throw new \InvalidArgumentException('عمر گزینه باید بین ۱ و ' . EeMath::MAX_PERIODS . ' سال باشد.');

            $alternativeId = (new AlternativeRepository())->create($projectId, (int) $project['study_period'], [
                'name' => mb_substr($name, 0, 160),
                'description' => trim((string) ($_POST['description'] ?? '')) ?: null,
                'life_years' => $life,
                'is_baseline' => isset($_POST['is_baseline']) ? 1 : 0,
            ]);

            ee_flash_set('success', 'گزینه ساخته شد؛ اکنون جریان نقدی سال‌به‌سال آن را ثبت کنید.');
            $this->redirect(ee_url('cashflow', 'edit', ['id' => $alternativeId]));
        } catch (\InvalidArgumentException $e) {
            ee_flash_set('danger', $e->getMessage());
            $this->redirect(ee_url('cashflow', 'index', ['id' => $projectId]));
        } catch (\Throwable $e) {
            error_log('Engineering Economy alternative create failed: ' . $e->getMessage());
            ee_flash_set('danger', 'ساخت گزینه انجام نشد.');
            $this->redirect(ee_url('cashflow', 'index', ['id' => $projectId]));
        }
    }

    public function edit(int $alternativeId): void
    {
        $userId = $this->userId();
        $repository = new AlternativeRepository();
        $alternative = $repository->find($userId, $alternativeId);

        if (!$alternative) {
            ee_flash_set('danger', 'گزینه پیدا نشد.');
            $this->redirect(ee_url('project', 'index'));
        }

        // ورودی‌های تایپ‌شده پس از خطای اعتبارسنجی (۱۰ دقیقه، فقط همین گزینه).
        $payload = $_SESSION['ee_old_flows'] ?? null;
        unset($_SESSION['ee_old_flows']);
        $old = [];
        if (is_array($payload)
            && (int) ($payload['alternative'] ?? 0) === $alternativeId
            && (int) ($payload['at'] ?? 0) > time() - 600
            && is_array($payload['data'] ?? null)) {
            $old = $payload['data'];
        }

        $this->renderSoftware('cashflow/edit', [
            'pageTitle' => 'ویرایش جریان نقدی',
            'title' => 'جریان نقدی - ' . $alternative['name'],
            'softwareName' => 'Engineering Economy Analyzer',
            'alternative' => $alternative,
            'flowRows' => $this->yearRows($repository->flowsFor($alternativeId), (int) $alternative['study_period']),
            'old' => $old,
            'flash' => ee_flash_get(),
        ], 'engineering-economy');
    }

    /**
     * همهٔ سال‌های ۰ تا دورهٔ مطالعه را تضمین می‌کند (حتی اگر سطری در دیتابیس نباشد).
     *
     * @param array<int,array<string,mixed>> $flows
     * @return array<int,array<string,mixed>>
     */
    private function yearRows(array $flows, int $studyPeriod): array
    {
        $byYear = [];
        foreach ($flows as $flow) {
            $byYear[(int) $flow['year_no']] = $flow;
        }

        $rows = [];
        for ($year = 0; $year <= $studyPeriod; $year++) {
            $rows[] = $byYear[$year] ?? ['year_no' => $year, 'inflow' => 0, 'outflow' => 0, 'label' => null];
        }

        return $rows;
    }

    public function save(int $alternativeId): void
    {
        $userId = $this->userId();
        $this->requirePost(ee_url('project', 'index'));

        $repository = new AlternativeRepository();
        $alternative = $repository->find($userId, $alternativeId);

        if (!$alternative) {
            ee_flash_set('danger', 'گزینه پیدا نشد.');
            $this->redirect(ee_url('project', 'index'));
        }

        try {
            $name = trim((string) ($_POST['name'] ?? ''));
            if ($name === '') throw new \InvalidArgumentException('نام گزینه الزامی است.');

            $life = (int) ($_POST['life_years'] ?? $alternative['life_years']);
            if ($life < 1 || $life > EeMath::MAX_PERIODS) throw new \InvalidArgumentException('عمر گزینه معتبر نیست.');

            $repository->update($alternativeId, [
                'name' => mb_substr($name, 0, 160),
                'description' => trim((string) ($_POST['description'] ?? '')) ?: null,
                'life_years' => $life,
            ]);

            $studyPeriod = (int) $alternative['study_period'];
            $rows = [];
            for ($year = 0; $year <= $studyPeriod; $year++) {
                $inflow = $this->amount($_POST['inflow'][$year] ?? null, 'ورودی سال ' . $year);
                $outflow = $this->amount($_POST['outflow'][$year] ?? null, 'خروجی سال ' . $year);
                $label = trim((string) ($_POST['label'][$year] ?? ''));

                $rows[] = [
                    'year' => $year,
                    'inflow' => $inflow,
                    'outflow' => $outflow,
                    'label' => $label === '' ? null : mb_substr($label, 0, 120),
                ];
            }

            $anyValue = false;
            foreach ($rows as $row) {
                if ($row['inflow'] > 0 || $row['outflow'] > 0) {
                    $anyValue = true;
                    break;
                }
            }
            if (!$anyValue) throw new \InvalidArgumentException('دست‌کم برای یک سال، مبلغ ورودی یا خروجی ثبت کنید.');

            $repository->saveFlows((int) $alternative['project_id'], $alternativeId, $studyPeriod, $rows);

            ee_flash_set('success', 'جریان نقدی ذخیره شد.');
            $this->redirect(ee_url('cashflow', 'edit', ['id' => $alternativeId]));
        } catch (\InvalidArgumentException $e) {
            ee_flash_set('danger', $e->getMessage());
            $_SESSION['ee_old_flows'] = ['data' => $_POST, 'at' => time(), 'alternative' => $alternativeId];
            $this->redirect(ee_url('cashflow', 'edit', ['id' => $alternativeId]));
        } catch (\Throwable $e) {
            error_log('Engineering Economy cash flow save failed: ' . $e->getMessage());
            ee_flash_set('danger', 'ذخیرهٔ جریان نقدی انجام نشد.');
            $this->redirect(ee_url('cashflow', 'edit', ['id' => $alternativeId]));
        }
    }

    public function deleteAlternative(int $alternativeId): void
    {
        $userId = $this->userId();
        $this->requirePost(ee_url('project', 'index'));

        $alternative = (new AlternativeRepository())->find($userId, $alternativeId);
        if (!$alternative) {
            ee_flash_set('danger', 'گزینه پیدا نشد.');
            $this->redirect(ee_url('project', 'index'));
        }

        try {
            (new AlternativeRepository())->delete($alternativeId);
            ee_flash_set('success', 'گزینه و جریان نقدی آن حذف شد.');
        } catch (\Throwable $e) {
            error_log('Engineering Economy alternative delete failed: ' . $e->getMessage());
            ee_flash_set('danger', 'حذف گزینه انجام نشد.');
        }

        $this->redirect(ee_url('cashflow', 'index', ['id' => (int) $alternative['project_id']]));
    }

    public function addAssumption(int $projectId): void
    {
        $userId = $this->userId();
        $this->requirePost(ee_url('cashflow', 'index', ['id' => $projectId]));

        $project = (new ProjectRepository())->find($userId, $projectId);
        if (!$project) {
            ee_flash_set('danger', 'پروژه پیدا نشد.');
            $this->redirect(ee_url('project', 'index'));
        }

        try {
            $title = trim((string) ($_POST['title'] ?? ''));
            if ($title === '') throw new \InvalidArgumentException('عنوان فرض الزامی است.');

            (new ProjectRepository())->addAssumption($projectId, [
                'title' => mb_substr($title, 0, 180),
                'value' => trim((string) ($_POST['value'] ?? '')) ?: null,
                'unit' => trim((string) ($_POST['unit'] ?? '')) ?: null,
                'source' => trim((string) ($_POST['source'] ?? '')) ?: null,
                'note' => trim((string) ($_POST['note'] ?? '')) ?: null,
            ]);

            ee_flash_set('success', 'فرض مستند ثبت شد.');
        } catch (\InvalidArgumentException $e) {
            ee_flash_set('danger', $e->getMessage());
        } catch (\Throwable $e) {
            error_log('Engineering Economy assumption create failed: ' . $e->getMessage());
            ee_flash_set('danger', 'ثبت فرض انجام نشد.');
        }

        $this->redirect(ee_url('cashflow', 'index', ['id' => $projectId]));
    }

    public function deleteAssumption(int $assumptionId): void
    {
        $userId = $this->userId();
        $this->requirePost(ee_url('project', 'index'));

        $projectId = (int) ($_POST['project_id'] ?? 0);
        $project = (new ProjectRepository())->find($userId, $projectId);
        if (!$project) {
            ee_flash_set('danger', 'پروژه پیدا نشد.');
            $this->redirect(ee_url('project', 'index'));
        }

        try {
            (new ProjectRepository())->deleteAssumption($projectId, $assumptionId);
            ee_flash_set('success', 'فرض حذف شد.');
        } catch (\InvalidArgumentException $e) {
            ee_flash_set('danger', $e->getMessage());
        } catch (\Throwable $e) {
            error_log('Engineering Economy assumption delete failed: ' . $e->getMessage());
            ee_flash_set('danger', 'حذف فرض انجام نشد.');
        }

        $this->redirect(ee_url('cashflow', 'index', ['id' => $projectId]));
    }

    /* ---------------------------------------------------------------
     * کمکی‌ها
     * --------------------------------------------------------------- */

    private function requirePost(string $fallback): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect($fallback);
        }
    }

    /** مبلغ نامنفی با پذیرش ارقام فارسی و جداکنندهٔ هزارگان */
    private function amount($raw, string $label): float
    {
        $value = trim((string) $raw);
        $value = strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٫' => '.', '،' => '',
        ]);
        $value = str_replace([',', ' ', "\u{066C}"], '', $value);

        if ($value === '') return 0.0;
        if (!is_numeric($value)) throw new \InvalidArgumentException($label . ' باید عدد باشد.');

        $number = (float) $value;
        if ($number < 0) throw new \InvalidArgumentException($label . ' نمی‌تواند منفی باشد.');

        return round($number, 4);
    }

    private function userId(): int
    {
        $this->requireAuth();

        return (int) ($_SESSION['user_id'] ?? 0);
    }
}
