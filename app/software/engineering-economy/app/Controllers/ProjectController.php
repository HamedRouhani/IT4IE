<?php

declare(strict_types=1);

namespace App\Software\EngineeringEconomy\Controllers;

use App\Core\Controller;
use App\Software\EngineeringEconomy\Models\ProjectRepository;
use App\Software\EngineeringEconomy\Services\EeMath;

final class ProjectController extends Controller
{
    private const KINDS = ['investment', 'replacement', 'make_buy', 'public', 'cost_only'];
    private const DEPRECIATIONS = ['none', 'straight', 'declining', 'soyd', 'uop'];

    public function index(): void
    {
        $userId = $this->userId();
        $repo = new ProjectRepository();
        $showArchived = (string) ($_GET['archived'] ?? '') === '1';

        $this->renderSoftware('project/index', [
            'pageTitle' => 'اقتصاد مهندسی',
            'title' => 'اقتصاد مهندسی - IT4IE',
            'softwareName' => 'Engineering Economy Analyzer',
            'projects' => $repo->listForUser($userId, $showArchived),
            'stats' => $repo->stats($userId),
            'showArchived' => $showArchived,
            'flash' => ee_flash_get(),
        ], 'engineering-economy');
    }

    public function create(): void
    {
        $this->userId();

        $this->renderSoftware('project/form', [
            'pageTitle' => 'پروژهٔ جدید',
            'title' => 'پروژهٔ جدید - اقتصاد مهندسی',
            'softwareName' => 'Engineering Economy Analyzer',
            'project' => null,
            'old' => $this->oldInput(),
            'flash' => ee_flash_get(),
        ], 'engineering-economy');
    }

    public function store(): void
    {
        $userId = $this->userId();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(ee_url('project', 'create'));
        }

        try {
            $data = $this->payload();
            $id = (new ProjectRepository())->create($userId, $data);
            ee_flash_set('success', 'پروژه ذخیره شد. در گام بعد گزینه‌ها و جریان نقدی را تعریف کنید.');
            $this->redirect(ee_url('project', 'show', ['id' => $id]));
        } catch (\InvalidArgumentException $e) {
            ee_flash_set('danger', $e->getMessage());
            $_SESSION['ee_old_project'] = ['data' => $_POST, 'at' => time()];
            $this->redirect(ee_url('project', 'create'));
        } catch (\Throwable $e) {
            error_log('Engineering Economy project create failed: ' . $e->getMessage());
            ee_flash_set('danger', 'ثبت پروژه انجام نشد؛ ورودی‌ها را بازبینی کنید.');
            $_SESSION['ee_old_project'] = ['data' => $_POST, 'at' => time()];
            $this->redirect(ee_url('project', 'create'));
        }
    }

    public function show(int $id): void
    {
        $userId = $this->userId();
        $repo = new ProjectRepository();
        $project = $repo->find($userId, $id);

        if (!$project) {
            http_response_code(404);
            ee_flash_set('danger', 'پروژه پیدا نشد.');
            $this->redirect(ee_url('project', 'index'));
        }

        $rate = ((float) $project['marr_pct']) / 100.0;
        $rowsByAlternative = [];
        foreach ($project['cashflows'] as $flow) {
            $rowsByAlternative[(int) $flow['alternative_id']][] = $flow;
        }

        $analyses = [];
        foreach ($project['alternatives'] as $alternative) {
            $flows = EeMath::netFlows($rowsByAlternative[(int) $alternative['id']] ?? []);
            $analyses[] = [
                'alternative' => $alternative,
                'flows' => $flows,
                'issues' => $flows === [] ? [] : EeMath::validate($flows),
                'npv' => $flows === [] ? null : EeMath::npv($rate, $flows),
                'irr' => $flows === [] ? null : EeMath::irr($flows),
                'err' => $flows === [] ? null : EeMath::err($flows, $rate),
                'mirr' => $flows === [] ? null : EeMath::mirr($flows, $rate),
                'payback' => $flows === [] ? null : EeMath::paybackSimple($flows),
                'discountedPayback' => $flows === [] ? null : EeMath::paybackDiscounted($flows, $rate),
                'pi' => $flows === [] ? null : EeMath::profitabilityIndex($flows, $rate),
            ];
        }

        $this->renderSoftware('project/show', [
            'pageTitle' => $project['title'],
            'title' => $project['title'] . ' - اقتصاد مهندسی',
            'softwareName' => 'Engineering Economy Analyzer',
            'project' => $project,
            'analyses' => $analyses,
            'rate' => $rate,
            'flash' => ee_flash_get(),
        ], 'engineering-economy');
    }

    public function delete(int $id): void
    {
        $userId = $this->userId();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(ee_url('project', 'index'));
        }

        try {
            (new ProjectRepository())->delete($userId, $id);
            ee_flash_set('success', 'پروژه و داده‌های وابسته به آن حذف شد.');
        } catch (\InvalidArgumentException $e) {
            ee_flash_set('danger', $e->getMessage());
        } catch (\Throwable $e) {
            error_log('Engineering Economy project delete failed: ' . $e->getMessage());
            ee_flash_set('danger', 'حذف پروژه انجام نشد.');
        }

        $this->redirect(ee_url('project', 'index'));
    }

    /**
     * تغییر وضعیت پروژه (پیش‌نویس ← فعال ← نهایی ← بایگانی).
     */
    public function status(int $id): void
    {
        $userId = $this->userId();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(ee_url('project', 'show', ['id' => $id]));
        }

        $status = (string) ($_POST['status'] ?? '');

        try {
            (new ProjectRepository())->setStatus($userId, $id, $status);
            ee_flash_set('success', 'وضعیت پروژه به «' . ee_status_label($status) . '» تغییر کرد.');
        } catch (\InvalidArgumentException $e) {
            ee_flash_set('danger', $e->getMessage());
        } catch (\Throwable $e) {
            error_log('Engineering Economy project status failed: ' . $e->getMessage());
            ee_flash_set('danger', 'تغییر وضعیت انجام نشد.');
        }

        $this->redirect(ee_url('project', 'show', ['id' => $id]));
    }

    /* ---------------------------------------------------------------
     * کمکی‌ها
     * --------------------------------------------------------------- */

    /**
     * اعتبارسنجی و نرمال‌سازی ورودی فرم پروژه.
     *
     * @return array<string,mixed>
     */
    private function payload(): array
    {
        $title = $this->text('title', 180);
        if ($title === '') throw new \InvalidArgumentException('عنوان پروژه الزامی است.');

        $kind = (string) ($_POST['study_kind'] ?? 'investment');
        if (!in_array($kind, self::KINDS, true)) throw new \InvalidArgumentException('نوع مطالعه معتبر نیست.');

        $depreciation = (string) ($_POST['depreciation_method'] ?? 'none');
        if (!in_array($depreciation, self::DEPRECIATIONS, true)) throw new \InvalidArgumentException('روش استهلاک معتبر نیست.');

        $currency = strtoupper(trim((string) ($_POST['currency'] ?? 'IRT')));
        if (!preg_match('/^[A-Z]{3}$/', $currency)) throw new \InvalidArgumentException('کد ارز باید سه حرف لاتین باشد (مثلاً IRT).');

        $period = $this->integer('study_period', 1, EeMath::MAX_PERIODS, 'دورهٔ مطالعه');
        $marr = $this->decimal('marr_pct', -99.999, 100, 'نرخ بازدهٔ حداقلی');
        $inflation = $this->decimal('inflation_pct', -99.999, 100, 'نرخ تورم');
        $tax = $this->decimal('tax_rate_pct', 0, 60, 'نرخ مالیات');

        $baseYear = null;
        if (isset($_POST['base_year']) && trim((string) $_POST['base_year']) !== '') {
            $baseYear = $this->integer('base_year', 1300, 2200, 'سال پایه');
        }

        return [
            'title' => $title,
            'description' => $this->text('description', 5000, false),
            'study_kind' => $kind,
            'currency' => $currency,
            'base_year' => $baseYear,
            'study_period' => $period,
            'marr_pct' => $marr,
            'inflation_pct' => $inflation,
            'tax_rate_pct' => $tax,
            'depreciation_method' => $depreciation,
            'notes' => $this->text('notes', 5000, false),
        ];
    }

    /** ورودی متنی پاک‌سازی‌شده */
    private function text(string $field, int $maxLength, bool $required = true): string
    {
        $value = trim((string) ($_POST[$field] ?? ''));
        if ($value === '' && $required) return '';
        if (mb_strlen($value) > $maxLength) {
            $value = mb_substr($value, 0, $maxLength);
        }

        return $value;
    }

    /** تبدیل ارقام فارسی/عربی و ممیز فارسی به لاتین */
    private function latinDigits(string $value): string
    {
        return strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '٫' => '.', '،' => '.',
        ]);
    }

    private function integer(string $field, int $min, int $max, string $label): int
    {
        $raw = preg_replace('/[^\d\-]/', '', $this->latinDigits(trim((string) ($_POST[$field] ?? ''))));

        if ($raw === null || $raw === '' || $raw === '-' || !preg_match('/^-?\d+$/', $raw)) {
            throw new \InvalidArgumentException($label . ' باید عدد صحیح باشد.');
        }

        $value = (int) $raw;
        if ($value < $min || $value > $max) {
            throw new \InvalidArgumentException($label . ' باید بین ' . $min . ' و ' . $max . ' باشد.');
        }

        return $value;
    }

    private function decimal(string $field, float $min, float $max, string $label): float
    {
        $raw = preg_replace('/[^\d.\-]/', '', $this->latinDigits(trim((string) ($_POST[$field] ?? ''))));

        if ($raw === null || $raw === '' || $raw === '-' || !is_numeric($raw)) {
            throw new \InvalidArgumentException($label . ' باید عدد باشد.');
        }

        $value = (float) $raw;
        if ($value < $min || $value > $max) {
            throw new \InvalidArgumentException($label . ' باید بین ' . ee_num($min, 3) . ' و ' . ee_num($max, 3) . ' باشد.');
        }

        return round($value, 3);
    }

    /**
     * ورودی‌های تایپ‌شده پس از خطای اعتبارسنجی (حداکثر ۱۰ دقیقه، فقط فرم ایجاد).
     *
     * @return array<string,mixed>
     */
    private function oldInput(): array
    {
        $payload = $_SESSION['ee_old_project'] ?? null;
        unset($_SESSION['ee_old_project']);

        if (!is_array($payload) || !isset($payload['data'], $payload['at'])) return [];
        if ((time() - (int) $payload['at']) > 600) return [];

        return is_array($payload['data']) ? $payload['data'] : [];
    }

    /** شناسهٔ کاربر جاری با اجبار ورود */
    private function userId(): int
    {
        $this->requireAuth();

        return (int) ($_SESSION['user_id'] ?? 0);
    }
}
