<?php

declare(strict_types=1);

namespace App\Software\FactoryLayoutAnalyzer\Controllers;

use App\Core\Controller;
use App\Software\FactoryLayoutAnalyzer\Models\MaterialFlowRepository;

final class MaterialFlowController extends Controller
{
    private const PERIODS = ['day' => 'روزانه', 'week' => 'هفتگی', 'month' => 'ماهانه', 'year' => 'سالانه'];

    public function store(int $projectId): void
    {
        $userId = $this->userId();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('project', 'show', ['id' => $projectId, 'step' => 'flows']));
        }
        try {
            (new MaterialFlowRepository())->create($userId, $projectId, $this->createPayload());
            fl_flash_set('success', 'جریان جهت‌دار مواد ثبت شد.');
        } catch (\InvalidArgumentException $e) {
            fl_flash_set('danger', $e->getMessage());
        } catch (\Throwable $e) {
            error_log('Factory Layout material flow create failed: ' . $e->getMessage());
            fl_flash_set('danger', 'ثبت جریان مواد انجام نشد؛ اطلاعات را بررسی کنید.');
        }
        $this->redirect(fl_url('project', 'show', ['id' => $projectId, 'step' => 'flows']));
    }

    public function update(int $id): void
    {
        $userId = $this->userId();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('project', 'index'));
        }
        $repository = new MaterialFlowRepository();
        $flow = $repository->findForUser($userId, $id);
        if (!$flow) {
            http_response_code(404);
            fl_flash_set('danger', 'جریان پیدا نشد یا دسترسی به آن ندارید.');
            $this->redirect(fl_url('project', 'index'));
        }
        $projectId = (int) $flow['project_id'];
        try {
            $repository->update($userId, $id, $this->updatePayload());
            fl_flash_set('success', 'مقدار، دوره و مبنای جریان به‌روزرسانی شد.');
        } catch (\InvalidArgumentException $e) {
            fl_flash_set('danger', $e->getMessage());
        } catch (\Throwable $e) {
            error_log('Factory Layout material flow update failed: ' . $e->getMessage());
            fl_flash_set('danger', 'به‌روزرسانی جریان انجام نشد؛ اطلاعات را بررسی کنید.');
        }
        $this->redirect(fl_url('project', 'show', ['id' => $projectId, 'step' => 'flows']));
    }

    public function delete(int $id): void
    {
        $userId = $this->userId();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('project', 'index'));
        }
        $repository = new MaterialFlowRepository();
        $flow = $repository->findForUser($userId, $id);
        if (!$flow) {
            http_response_code(404);
            fl_flash_set('danger', 'جریان پیدا نشد یا دسترسی به آن ندارید.');
            $this->redirect(fl_url('project', 'index'));
        }
        $projectId = (int) $flow['project_id'];
        try {
            $repository->delete($userId, $id);
            fl_flash_set('success', 'جریان از تحلیل پروژه حذف شد.');
        } catch (\Throwable $e) {
            error_log('Factory Layout material flow delete failed: ' . $e->getMessage());
            fl_flash_set('danger', $e instanceof \InvalidArgumentException ? $e->getMessage() : 'حذف جریان انجام نشد.');
        }
        $this->redirect(fl_url('project', 'show', ['id' => $projectId, 'step' => 'flows']));
    }

    /** @return array{from_area:string,to_area:string,load_moves:string,flow_period:string,evidence:string} */
    private function createPayload(): array
    {
        $from = $this->postText('from_area');
        $to = $this->postText('to_area');
        if ($from === '' || $to === '' || $from === $to) {
            throw new \InvalidArgumentException('مبدأ و مقصد معتبر و متفاوت انتخاب کنید.');
        }
        return ['from_area' => $from, 'to_area' => $to] + $this->measurePayload();
    }

    /** @return array{load_moves:string,flow_period:string,evidence:string} */
    private function updatePayload(): array
    {
        return $this->measurePayload();
    }

    /** @return array{load_moves:string,flow_period:string,evidence:string} */
    private function measurePayload(): array
    {
        $quantity = fl_latin_digits($this->postText('load_moves'));
        if (!preg_match('/^(?:0|[1-9]\\d{0,9})(?:\\.\\d{1,4})?$/', $quantity) || (float) $quantity <= 0) {
            throw new \InvalidArgumentException('تعداد جابه‌جایی بار باید عددی مثبت با حداکثر چهار رقم اعشار باشد.');
        }
        $period = $this->postText('flow_period');
        if (!isset(self::PERIODS[$period])) {
            throw new \InvalidArgumentException('دورهٔ مشاهدهٔ جریان معتبر نیست.');
        }
        $evidence = $this->postText('evidence');
        $length = function_exists('mb_strlen') ? mb_strlen($evidence, 'UTF-8') : strlen($evidence);
        if ($evidence === '' || $length > 500) {
            throw new \InvalidArgumentException('منبع یا مبنای برآورد الزامی است و حداکثر ۵۰۰ نویسه دارد.');
        }
        return ['load_moves' => $quantity, 'flow_period' => $period, 'evidence' => $evidence];
    }

    private function postText(string $field): string
    {
        $value = $_POST[$field] ?? '';
        if (!is_string($value)) {
            throw new \InvalidArgumentException('ورودی درخواست معتبر نیست.');
        }
        return trim($value);
    }

    private function userId(): int
    {
        $this->requireAuth();
        return (int) ($_SESSION['user_id'] ?? 0);
    }
}
