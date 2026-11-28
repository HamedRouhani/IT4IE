<?php

declare(strict_types=1);

namespace App\Software\FactoryLayoutAnalyzer\Controllers;

use App\Core\Controller;
use App\Software\FactoryLayoutAnalyzer\Models\ActivityRelationshipRepository;

final class RelationshipController extends Controller
{
    private const CODES = ['A', 'E', 'I', 'O', 'U', 'X'];

    public function store(int $projectId): void
    {
        $userId = $this->userId();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('project', 'show', ['id' => $projectId]));
        }

        try {
            (new ActivityRelationshipRepository())->create($userId, $projectId, $this->payload());
            fl_flash_set('success', 'رابطهٔ فعالیت‌ها با دلیل ثبت شد.');
        } catch (\InvalidArgumentException $e) {
            fl_flash_set('danger', $e->getMessage());
        } catch (\Throwable $e) {
            error_log('Factory Layout activity relationship create failed: ' . $e->getMessage());
            fl_flash_set('danger', 'ثبت رابطه انجام نشد؛ اطلاعات را بررسی کنید.');
        }
        $this->redirect(fl_url('project', 'show', ['id' => $projectId]));
    }

    public function delete(int $id): void
    {
        $userId = $this->userId();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('project', 'index'));
        }

        try {
            $projectId = (new ActivityRelationshipRepository())->delete($userId, $id);
            fl_flash_set('success', 'رابطه از پروژه حذف شد.');
        } catch (\Throwable $e) {
            error_log('Factory Layout activity relationship delete failed: ' . $e->getMessage());
            fl_flash_set('danger', $e instanceof \InvalidArgumentException ? $e->getMessage() : 'حذف رابطه انجام نشد.');
            $projectId = 0;
        }
        $this->redirect($projectId > 0 ? fl_url('project', 'show', ['id' => $projectId]) : fl_url('project', 'index'));
    }

    public function update(int $id): void
    {
        $userId = $this->userId();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('project', 'index'));
        }

        $repository = new ActivityRelationshipRepository();
        $relationship = $repository->findForUser($userId, $id);
        if (!$relationship) {
            http_response_code(404);
            fl_flash_set('danger', 'رابطه پیدا نشد یا دسترسی به آن ندارید.');
            $this->redirect(fl_url('project', 'index'));
        }
        $projectId = (int) $relationship['project_id'];
        try {
            $code = strtoupper($this->postText('closeness_code'));
            $reason = $this->postText('reason');
            $length = function_exists('mb_strlen') ? mb_strlen($reason, 'UTF-8') : strlen($reason);
            if (!in_array($code, self::CODES, true)) {
                throw new \InvalidArgumentException('کد نزدیکی انتخاب‌شده معتبر نیست.');
            }
            if ($reason === '' || $length > 500) {
                throw new \InvalidArgumentException('دلیل رابطه الزامی است و حداکثر ۵۰۰ نویسه دارد.');
            }
            $repository->update($userId, $id, [
                'closeness_code' => $code,
                'reason' => $reason,
            ]);
            fl_flash_set('success', 'کد و دلیل رابطهٔ فعالیت‌ها به‌روزرسانی شد.');
        } catch (\Throwable $e) {
            error_log('Factory Layout activity relationship update failed: ' . $e->getMessage());
            fl_flash_set('danger', $e instanceof \InvalidArgumentException ? $e->getMessage() : 'به‌روزرسانی رابطه انجام نشد.');
        }
        $this->redirect(fl_url('project', 'show', ['id' => $projectId]));
    }

    /** @return array{area_a:string,area_b:string,closeness_code:string,reason:string} */
    private function payload(): array
    {
        $areaA = $this->postText('area_a');
        $areaB = $this->postText('area_b');
        $code = strtoupper($this->postText('closeness_code'));
        $reason = $this->postText('reason');
        $areaALength = function_exists('mb_strlen') ? mb_strlen($areaA, 'UTF-8') : strlen($areaA);
        $areaBLength = function_exists('mb_strlen') ? mb_strlen($areaB, 'UTF-8') : strlen($areaB);
        if ($areaA === '' || $areaB === '' || $areaALength > 180 || $areaBLength > 180) {
            throw new \InvalidArgumentException('دو فعالیت معتبر انتخاب کنید.');
        }
        if (!in_array($code, self::CODES, true)) {
            throw new \InvalidArgumentException('کد نزدیکی انتخاب‌شده معتبر نیست.');
        }
        $length = function_exists('mb_strlen') ? mb_strlen($reason, 'UTF-8') : strlen($reason);
        if ($reason === '' || $length > 500) {
            throw new \InvalidArgumentException('دلیل رابطه الزامی است و حداکثر ۵۰۰ نویسه دارد.');
        }

        return ['area_a' => $areaA, 'area_b' => $areaB, 'closeness_code' => $code, 'reason' => $reason];
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
