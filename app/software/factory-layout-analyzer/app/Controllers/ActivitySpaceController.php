<?php

declare(strict_types=1);

namespace App\Software\FactoryLayoutAnalyzer\Controllers;

use App\Core\Controller;
use App\Software\FactoryLayoutAnalyzer\Models\ProductionAreaSpaceRepository;

final class ActivitySpaceController extends Controller
{
    public function store(int $projectId): void
    {
        $userId = $this->userId();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('project', 'show', ['id' => $projectId, 'step' => 'space']));
        }
        unset($_SESSION['fl_old_space']);

        try {
            (new ProductionAreaSpaceRepository())->save($userId, $projectId, $this->payload());
            fl_flash_set('success', 'نیاز فضای بخش تولیدی ثبت شد.');
        } catch (\InvalidArgumentException $e) {
            fl_flash_set('danger', $e->getMessage());
            $this->rememberSubmittedValues();
        } catch (\Throwable $e) {
            error_log('Factory Layout production area space save failed: ' . $e->getMessage());
            fl_flash_set('danger', 'ثبت نیاز فضا انجام نشد؛ ورودی‌ها را بررسی کنید.');
            $this->rememberSubmittedValues();
        }
        $this->redirect(fl_url('project', 'show', ['id' => $projectId, 'step' => 'space']));
    }

    public function delete(int $id): void
    {
        $userId = $this->userId();
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->redirect(fl_url('project', 'index'));
        }

        try {
            $projectId = (new ProductionAreaSpaceRepository())->delete($userId, $id);
            fl_flash_set('success', 'برآورد فضای بخش تولیدی حذف شد.');
        } catch (\InvalidArgumentException $e) {
            fl_flash_set('danger', $e->getMessage());
            $this->redirect(fl_url('project', 'index'));
        } catch (\Throwable $e) {
            error_log('Factory Layout production area space delete failed: ' . $e->getMessage());
            fl_flash_set('danger', 'حذف برآورد فضا انجام نشد.');
            $this->redirect(fl_url('project', 'index'));
        }
        $this->redirect(fl_url('project', 'show', ['id' => $projectId, 'step' => 'space']));
    }

    /** @return array{area_name:string,area_m2:string,area_basis:string,notes:string} */
    private function payload(): array
    {
        $areaName = $this->textField('area_name');
        $nameLength = function_exists('mb_strlen') ? mb_strlen($areaName, 'UTF-8') : strlen($areaName);
        if ($areaName === '' || $nameLength > 180) {
            throw new \InvalidArgumentException('نام بخش معتبر نیست.');
        }

        $rawArea = fl_latin_digits($this->textField('area_m2'));
        if (!preg_match('/^\d+(?:\.\d{1,3})?$/', $rawArea)) {
            throw new \InvalidArgumentException('مساحت موردنیاز باید عددی مثبت و حداکثر با سه رقم اعشار باشد.');
        }
        [$whole, $fraction] = array_pad(explode('.', $rawArea, 2), 2, '');
        $whole = ltrim($whole, '0');
        if (strlen($whole) > 9) {
            throw new \InvalidArgumentException('مساحت واردشده از بیشینهٔ قابل‌ذخیره بزرگ‌تر است.');
        }
        if (trim($whole . $fraction, '0') === '') {
            throw new \InvalidArgumentException('مساحت باید بزرگ‌تر از صفر باشد.');
        }

        $basis = $this->textField('area_basis');
        $basisLength = function_exists('mb_strlen') ? mb_strlen($basis, 'UTF-8') : strlen($basis);
        if ($basis === '' || $basisLength > 500) {
            throw new \InvalidArgumentException('مبنای برآورد یا منبع مساحت الزامی است و حداکثر ۵۰۰ نویسه دارد.');
        }
        $notes = $this->textField('notes');
        $notesLength = function_exists('mb_strlen') ? mb_strlen($notes, 'UTF-8') : strlen($notes);
        if ($notesLength > 1000) {
            throw new \InvalidArgumentException('یادداشت حداکثر ۱۰۰۰ نویسه دارد.');
        }

        return [
            'area_name' => $areaName,
            'area_m2' => ($whole === '' ? '0' : $whole) . '.' . str_pad($fraction, 3, '0'),
            'area_basis' => $basis,
            'notes' => $notes,
        ];
    }

    private function textField(string $name): string
    {
        $value = $_POST[$name] ?? '';
        if (!is_string($value)) {
            throw new \InvalidArgumentException('یکی از ورودی‌های متنی قالب معتبر ندارد.');
        }
        return trim($value);
    }

    private function rememberSubmittedValues(): void
    {
        $_SESSION['fl_old_space'] = [
            'at' => time(),
            'data' => array_intersect_key($_POST, array_flip(['area_name', 'area_m2', 'area_basis', 'notes'])),
        ];
    }

    private function userId(): int
    {
        $this->requireAuth();
        return (int) ($_SESSION['user_id'] ?? 0);
    }
}
