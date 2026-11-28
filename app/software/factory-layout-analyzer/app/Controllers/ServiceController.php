<?php

declare(strict_types=1);

namespace App\Software\FactoryLayoutAnalyzer\Controllers;

use App\Core\Controller;
use App\Software\FactoryLayoutAnalyzer\Models\ProjectRepository;
use App\Software\FactoryLayoutAnalyzer\Models\SupportServiceRepository;

final class ServiceController extends Controller
{
    private const CATEGORIES = [
        'storage' => 'انبارش و انبار',
        'maintenance' => 'تعمیرات و نگهداری',
        'quality' => 'کنترل کیفیت و آزمایشگاه',
        'utilities' => 'تأسیسات و انرژی',
        'office' => 'اداری و سرپرستی',
        'welfare' => 'رفاهی و خدمات کارکنان',
        'safety' => 'ایمنی و محیط زیست',
        'shipping' => 'دریافت و ارسال',
        'other' => 'سایر خدمات',
    ];

    private const REQUIREMENTS = [
        'required' => 'الزامی',
        'preferred' => 'ترجیحی',
        'optional' => 'اختیاری',
    ];

    public function create(int $projectId): void
    {
        $project = (new ProjectRepository())->findForUser($this->userId(), $projectId);
        if (!$project) {
            http_response_code(404);
            fl_flash_set('danger', 'پروژه پیدا نشد یا دسترسی به آن ندارید.');
            $this->redirect(fl_url('project', 'index'));
        }
        $this->renderForm($project, null);
    }

    public function store(int $projectId): void
    {
        $userId = $this->userId();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('project', 'show', ['id' => $projectId, 'step' => 'services']));
        }
        try {
            (new SupportServiceRepository())->create($userId, $projectId, $this->payload());
            fl_flash_set('success', 'خدمت پشتیبان به پروژه افزوده شد.');
        } catch (\InvalidArgumentException $e) {
            fl_flash_set('danger', $e->getMessage());
            $_SESSION['fl_old_service'] = ['data' => $_POST, 'at' => time()];
            $this->redirect(fl_url('service', 'create', ['project_id' => $projectId]));
        } catch (\Throwable $e) {
            error_log('Factory Layout support service create failed: ' . $e->getMessage());
            fl_flash_set('danger', 'ثبت خدمت پشتیبان انجام نشد؛ اطلاعات را بررسی کنید.');
            $_SESSION['fl_old_service'] = ['data' => $_POST, 'at' => time()];
            $this->redirect(fl_url('service', 'create', ['project_id' => $projectId]));
        }
        $this->redirect(fl_url('project', 'show', ['id' => $projectId, 'step' => 'services']));
    }

    public function edit(int $id): void
    {
        $userId = $this->userId();
        $service = (new SupportServiceRepository())->findForUser($userId, $id);
        if (!$service) {
            http_response_code(404);
            fl_flash_set('danger', 'خدمت پشتیبان پیدا نشد یا دسترسی به آن ندارید.');
            $this->redirect(fl_url('project', 'index'));
        }
        $project = (new ProjectRepository())->findForUser($userId, (int) $service['project_id']);
        if (!$project) {
            http_response_code(404);
            $this->redirect(fl_url('project', 'index'));
        }
        $this->renderForm($project, $service);
    }

    public function update(int $id): void
    {
        $userId = $this->userId();
        $repository = new SupportServiceRepository();
        $service = $repository->findForUser($userId, $id);
        if (!$service) {
            http_response_code(404);
            fl_flash_set('danger', 'خدمت پشتیبان پیدا نشد یا دسترسی به آن ندارید.');
            $this->redirect(fl_url('project', 'index'));
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('service', 'edit', ['id' => $id]));
        }
        try {
            $repository->update($userId, $id, $this->payload());
            fl_flash_set('success', 'مشخصات خدمت پشتیبان به‌روزرسانی شد.');
        } catch (\InvalidArgumentException $e) {
            fl_flash_set('danger', $e->getMessage());
            $_SESSION['fl_old_service'] = ['data' => $_POST, 'at' => time()];
            $this->redirect(fl_url('service', 'edit', ['id' => $id]));
        } catch (\Throwable $e) {
            error_log('Factory Layout support service update failed: ' . $e->getMessage());
            fl_flash_set('danger', 'به‌روزرسانی خدمت پشتیبان انجام نشد؛ اطلاعات را بررسی کنید.');
            $_SESSION['fl_old_service'] = ['data' => $_POST, 'at' => time()];
            $this->redirect(fl_url('service', 'edit', ['id' => $id]));
        }
        $this->redirect(fl_url('project', 'show', ['id' => (int) $service['project_id'], 'step' => 'services']));
    }

    public function delete(int $id): void
    {
        $userId = $this->userId();
        $repository = new SupportServiceRepository();
        $service = $repository->findForUser($userId, $id);
        if (!$service) {
            http_response_code(404);
            fl_flash_set('danger', 'خدمت پشتیبان پیدا نشد یا دسترسی به آن ندارید.');
            $this->redirect(fl_url('project', 'index'));
        }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('project', 'show', ['id' => (int) $service['project_id'], 'step' => 'services']));
        }
        try {
            $projectId = $repository->delete($userId, $id);
            fl_flash_set('success', 'خدمت پشتیبان از پروژه حذف شد.');
        } catch (\Throwable $e) {
            error_log('Factory Layout support service delete failed: ' . $e->getMessage());
            fl_flash_set('danger', 'حذف خدمت پشتیبان انجام نشد.');
            $projectId = (int) $service['project_id'];
        }
        $this->redirect(fl_url('project', 'show', ['id' => $projectId, 'step' => 'services']));
    }

    /** @param array<string,mixed>|null $service */
    private function renderForm(array $project, ?array $service): void
    {
        $oldState = $_SESSION['fl_old_service'] ?? null;
        unset($_SESSION['fl_old_service']);
        $old = is_array($oldState) && (time() - (int) ($oldState['at'] ?? 0)) < 900 && is_array($oldState['data'] ?? null)
            ? $oldState['data']
            : [];
        $this->renderSoftware('service/form', [
            'pageTitle' => $service ? 'ویرایش خدمت پشتیبان' : 'افزودن خدمت پشتیبان',
            'title' => ($service ? 'ویرایش خدمت پشتیبان' : 'افزودن خدمت پشتیبان') . ' - چیدمان کارخانه',
            'softwareName' => 'Factory Layout Analyzer',
            'project' => $project,
            'service' => $service,
            'source' => array_merge($service ?? [], $old),
            'serviceCategories' => self::CATEGORIES,
            'requirements' => self::REQUIREMENTS,
            'flash' => fl_flash_get(),
            'csrfField' => $this->csrfField(),
        ], 'factory-layout-analyzer');
    }

    /** @return array{name:string,category:string,requirement_level:string,area_m2:?string,area_basis:string,notes:string} */
    private function payload(): array
    {
        $name = $this->text('name', 180);
        $category = (string) ($_POST['category'] ?? '');
        if (!isset(self::CATEGORIES[$category])) throw new \InvalidArgumentException('نوع خدمت انتخاب‌شده معتبر نیست.');
        $requirement = (string) ($_POST['requirement_level'] ?? '');
        if (!isset(self::REQUIREMENTS[$requirement])) throw new \InvalidArgumentException('سطح الزام خدمت معتبر نیست.');

        $rawArea = fl_latin_digits(trim((string) ($_POST['area_m2'] ?? '')));
        $area = null;
        if ($rawArea !== '') {
            if (!preg_match('/^\d+(?:\.\d{1,3})?$/', $rawArea)) {
                throw new \InvalidArgumentException('مساحت باید عددی مثبت و حداکثر با سه رقم اعشار باشد.');
            }
            [$whole, $fraction] = array_pad(explode('.', $rawArea, 2), 2, '');
            $whole = ltrim($whole, '0');
            if (strlen($whole) > 9) throw new \InvalidArgumentException('مساحت واردشده از بیشینهٔ قابل‌ذخیره بزرگ‌تر است.');
            if (trim($whole . $fraction, '0') === '') throw new \InvalidArgumentException('مساحت باید بزرگ‌تر از صفر باشد.');
            $area = ($whole === '' ? '0' : $whole) . '.' . str_pad($fraction, 3, '0');
        }
        $areaBasis = $this->text('area_basis', 500, false);
        if ($area !== null && $areaBasis === '') {
            throw new \InvalidArgumentException('برای مساحت ثبت‌شده، مبنای برآورد یا منبع آن را هم وارد کنید.');
        }

        return [
            'name' => $name,
            'category' => $category,
            'requirement_level' => $requirement,
            'area_m2' => $area,
            'area_basis' => $areaBasis,
            'notes' => $this->text('notes', 1000, false),
        ];
    }

    private function text(string $field, int $maxLength, bool $required = true): string
    {
        $value = trim((string) ($_POST[$field] ?? ''));
        $length = function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
        if ($length > $maxLength) throw new \InvalidArgumentException('طول یکی از ورودی‌های متنی بیش از حد مجاز است.');
        if ($required && $value === '') throw new \InvalidArgumentException('نام خدمت پشتیبان الزامی است.');
        return $value;
    }

    private function userId(): int
    {
        $this->requireAuth();
        return (int) ($_SESSION['user_id'] ?? 0);
    }
}
