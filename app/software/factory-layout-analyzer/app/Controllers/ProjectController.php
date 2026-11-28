<?php

declare(strict_types=1);

namespace App\Software\FactoryLayoutAnalyzer\Controllers;

use App\Core\Controller;
use App\Software\FactoryLayoutAnalyzer\Models\ProductRepository;
use App\Software\FactoryLayoutAnalyzer\Models\ProjectRepository;
use App\Software\FactoryLayoutAnalyzer\Models\RouteStepRepository;
use App\Software\FactoryLayoutAnalyzer\Models\SupportServiceRepository;

final class ProjectController extends Controller
{
    private const PERIODS = ['day' => 'روز', 'week' => 'هفته', 'month' => 'ماه', 'year' => 'سال'];

    public function index(): void
    {
        $userId = $this->userId();
        $repository = new ProjectRepository();
        $this->renderSoftware('project/index', [
            'pageTitle' => 'پروژه‌های چیدمان',
            'title' => 'پروژه‌های چیدمان کارخانه - IT4IE',
            'softwareName' => 'Factory Layout Analyzer',
            'projects' => $repository->listForUser($userId),
            'stats' => $repository->stats($userId),
            'flash' => fl_flash_get(),
            'csrfField' => $this->csrfField(),
        ], 'factory-layout-analyzer');
    }

    public function create(): void
    {
        $this->userId();
        $this->renderForm(null);
    }

    public function store(): void
    {
        $userId = $this->userId();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('project', 'create'));
        }

        try {
            $id = (new ProjectRepository())->create($userId, $this->payload());
            fl_flash_set('success', 'پروژه ذخیره شد. حالا می‌توانید مشخصات آن را بازبینی کنید.');
            $this->redirect(fl_url('project', 'show', ['id' => $id]));
        } catch (\InvalidArgumentException $e) {
            fl_flash_set('danger', $e->getMessage());
            $_SESSION['fl_old_project'] = ['data' => $_POST, 'at' => time()];
        } catch (\Throwable $e) {
            error_log('Factory Layout project create failed: ' . $e->getMessage());
            fl_flash_set('danger', 'ثبت پروژه انجام نشد؛ ورودی‌ها را بازبینی کنید.');
            $_SESSION['fl_old_project'] = ['data' => $_POST, 'at' => time()];
        }
        $this->redirect(fl_url('project', 'create'));
    }

    public function show(int $id): void
    {
        $userId = $this->userId();
        $project = (new ProjectRepository())->findForUser($userId, $id);
        if (!$project) {
            http_response_code(404);
            fl_flash_set('danger', 'پروژه پیدا نشد یا دسترسی به آن ندارید.');
            $this->redirect(fl_url('project', 'index'));
        }
        $routeStepsByProduct = [];
        foreach ((new RouteStepRepository())->listForProject($userId, $id) as $routeStep) {
            $routeStepsByProduct[(int) $routeStep['product_id']][] = $routeStep;
        }
        $this->renderSoftware('project/show', [
            'pageTitle' => $project['title'],
            'title' => $project['title'] . ' - چیدمان کارخانه',
            'softwareName' => 'Factory Layout Analyzer',
            'project' => $project,
            'products' => (new ProductRepository())->listForProject($userId, $id),
            'routeStepsByProduct' => $routeStepsByProduct,
            'supportServices' => (new SupportServiceRepository())->listForProject($userId, $id),
            'periodLabel' => self::PERIODS[$project['planning_period']] ?? 'نامشخص',
            'flash' => fl_flash_get(),
            'csrfField' => $this->csrfField(),
        ], 'factory-layout-analyzer');
    }

    public function edit(int $id): void
    {
        $project = (new ProjectRepository())->findForUser($this->userId(), $id);
        if (!$project) {
            http_response_code(404);
            fl_flash_set('danger', 'پروژه پیدا نشد یا دسترسی به آن ندارید.');
            $this->redirect(fl_url('project', 'index'));
        }
        $this->renderForm($project);
    }

    public function update(int $id): void
    {
        $userId = $this->userId();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('project', 'edit', ['id' => $id]));
        }

        try {
            (new ProjectRepository())->update($userId, $id, $this->payload());
            fl_flash_set('success', 'مشخصات و تقویم پروژه به‌روزرسانی شد.');
            $this->redirect(fl_url('project', 'show', ['id' => $id]));
        } catch (\InvalidArgumentException $e) {
            fl_flash_set('danger', $e->getMessage());
        } catch (\Throwable $e) {
            error_log('Factory Layout project update failed: ' . $e->getMessage());
            fl_flash_set('danger', 'به‌روزرسانی پروژه انجام نشد؛ ورودی‌ها را بازبینی کنید.');
        }
        $_SESSION['fl_old_project'] = ['data' => $_POST, 'at' => time()];
        $this->redirect(fl_url('project', 'edit', ['id' => $id]));
    }

    public function delete(int $id): void
    {
        $userId = $this->userId();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('project', 'index'));
        }
        try {
            (new ProjectRepository())->delete($userId, $id);
            fl_flash_set('success', 'پروژه حذف شد.');
        } catch (\InvalidArgumentException $e) {
            fl_flash_set('danger', $e->getMessage());
        } catch (\Throwable $e) {
            error_log('Factory Layout project delete failed: ' . $e->getMessage());
            fl_flash_set('danger', 'حذف پروژه انجام نشد.');
        }
        $this->redirect(fl_url('project', 'index'));
    }

    private function renderForm(?array $project): void
    {
        $oldState = $_SESSION['fl_old_project'] ?? null;
        unset($_SESSION['fl_old_project']);
        $old = is_array($oldState) && (time() - (int) ($oldState['at'] ?? 0)) < 900 && is_array($oldState['data'] ?? null)
            ? $oldState['data']
            : [];

        $this->renderSoftware('project/form', [
            'pageTitle' => $project ? 'ویرایش پروژه' : 'پروژهٔ جدید',
            'title' => ($project ? 'ویرایش پروژه' : 'پروژهٔ جدید') . ' - چیدمان کارخانه',
            'softwareName' => 'Factory Layout Analyzer',
            'project' => $project,
            'old' => $old,
            'periods' => self::PERIODS,
            'flash' => fl_flash_get(),
            'csrfField' => $this->csrfField(),
        ], 'factory-layout-analyzer');
    }

    /** @return array<string,mixed> */
    private function payload(): array
    {
        $title = $this->text('title', 180);
        if ($title === '') throw new \InvalidArgumentException('عنوان پروژه الزامی است.');

        $period = (string) ($_POST['planning_period'] ?? '');
        if (!isset(self::PERIODS[$period])) throw new \InvalidArgumentException('دورهٔ برنامه‌ریزی معتبر نیست.');

        $shifts = $this->integer('shifts_per_day', 1, 4, 'تعداد شیفت روزانه');
        $hours = $this->decimal('hours_per_shift', 0.25, 24, 'ساعت هر شیفت');
        $days = $this->integer('working_days_per_week', 1, 7, 'روز کاری در هفته');

        return [
            'title' => $title,
            'facility_name' => $this->text('facility_name', 180, false),
            'industry' => $this->text('industry', 120, false),
            'site_location' => $this->text('site_location', 180, false),
            'scope_description' => $this->text('scope_description', 5000, false),
            'planning_period' => $period,
            'shifts_per_day' => $shifts,
            'hours_per_shift' => $hours,
            'working_days_per_week' => $days,
        ];
    }

    private function text(string $field, int $maxLength, bool $required = true): string
    {
        $value = trim((string) ($_POST[$field] ?? ''));
        $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
        if ($length > $maxLength) throw new \InvalidArgumentException('طول یکی از ورودی‌های متنی بیش از حد مجاز است.');
        if ($required && $value === '') throw new \InvalidArgumentException('عنوان پروژه الزامی است.');
        return $value;
    }

    private function integer(string $field, int $min, int $max, string $label): int
    {
        $value = fl_latin_digits(trim((string) ($_POST[$field] ?? '')));
        if ($value === '' || !preg_match('/^\d+$/', $value)) throw new \InvalidArgumentException($label . ' باید عدد صحیح باشد.');
        $number = (int) $value;
        if ($number < $min || $number > $max) throw new \InvalidArgumentException($label . " باید بین {$min} و {$max} باشد.");
        return $number;
    }

    private function decimal(string $field, float $min, float $max, string $label): float
    {
        $value = fl_latin_digits(trim((string) ($_POST[$field] ?? '')));
        if ($value === '' || !preg_match('/^\d+(?:\.\d{1,2})?$/', $value)) {
            throw new \InvalidArgumentException($label . ' باید عددی معتبر با حداکثر دو رقم اعشار باشد.');
        }
        $number = (float) $value;
        if (!is_finite($number) || $number < $min || $number > $max) {
            throw new \InvalidArgumentException($label . " باید بین {$min} و {$max} باشد.");
        }
        return round($number, 2);
    }

    private function userId(): int
    {
        $this->requireAuth();
        return (int) ($_SESSION['user_id'] ?? 0);
    }
}
