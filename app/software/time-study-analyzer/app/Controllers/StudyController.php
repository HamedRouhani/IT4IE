<?php
namespace App\Software\TimeStudyAnalyzer\Controllers;

use App\Software\TimeStudyAnalyzer\Models\StudyRepository;
use App\Software\TimeStudyAnalyzer\Services\StudyCalculator;
use DateTimeImmutable;
use DateTimeZone;

final class StudyController extends StudyControllerBase
{
    public function index(): void
    {
        $userId = $this->userId();
        $repo = new StudyRepository();
        $this->renderPage('study/index', [
            'pageTitle' => 'مطالعات زمان‌سنجی',
            'studies' => $repo->list($userId, ($_GET['archived'] ?? '') === '1'),
            'showArchived' => ($_GET['archived'] ?? '') === '1',
        ]);
    }

    public function create(): void
    {
        $this->userId();
        $method = (string) ($_GET['method'] ?? 'stopwatch');
        if (!in_array($method, ['stopwatch', 'work_sampling'], true)) $method = 'stopwatch';
        $this->renderPage('study/form', ['pageTitle' => 'ایجاد مطالعهٔ جدید', 'study' => null, 'method' => $method]);
    }

    public function store(): void
    {
        $userId = $this->userId();
        $this->postOnly();
        try {
            $method = (string) ($_POST['method'] ?? '');
            $data = $this->studyPayload($method);
            $children = $this->childrenPayload($method);
            $id = (new StudyRepository())->create($userId, $data, $children);
            ts_flash_set('success', 'مطالعه ذخیره شد. پیش از ثبت داده، روش و شرایط کار را بازبینی و مطالعه را آغاز کنید.');
            $this->redirect(ts_url('study', 'show', ['id' => $id]));
        } catch (\InvalidArgumentException $e) {
            ts_flash_set('danger', $e->getMessage());
            $this->redirect(ts_url('study', 'create', ['method' => (string) ($_POST['method'] ?? 'stopwatch')]));
        } catch (\Throwable $e) {
            error_log('Time Study create failed: ' . $e->getMessage());
            ts_flash_set('danger', 'ثبت مطالعه انجام نشد؛ ورودی‌ها را بازبینی کنید.');
            $this->redirect(ts_url('study', 'create'));
        }
    }

    public function edit(int $id): void
    {
        $userId = $this->userId();
        $study = (new StudyRepository())->find($userId, $id);
        if (!$study || $study['status'] !== 'draft') {
            ts_flash_set('warning', 'فقط پیش‌نویس‌ها قابل ویرایش هستند.');
            $this->redirect(ts_url('study', 'show', ['id' => $id]));
        }
        $this->renderPage('study/form', ['pageTitle' => 'ویرایش پیش‌نویس', 'study' => $study, 'method' => $study['method']]);
    }

    public function update(int $id): void
    {
        $userId = $this->userId();
        $this->postOnly();
        try {
            $repo = new StudyRepository();
            $existing = $repo->find($userId, $id);
            if (!$existing) throw new \InvalidArgumentException('مطالعه پیدا نشد.');
            $method = $existing['method'];
            $data = $this->studyPayload($method);
            $children = $this->childrenPayload($method);
            $repo->updateDraft($userId, $id, $data, $children);
            ts_flash_set('success', 'پیش‌نویس به‌روزرسانی شد.');
        } catch (\InvalidArgumentException $e) {
            ts_flash_set('danger', $e->getMessage());
        } catch (\Throwable $e) {
            error_log('Time Study draft update failed: ' . $e->getMessage());
            ts_flash_set('danger', 'به‌روزرسانی پیش‌نویس انجام نشد.');
        }
        $this->redirect(ts_url('study', 'show', ['id' => $id]));
    }

    public function show(int $id): void
    {
        $userId = $this->userId();
        $study = (new StudyRepository())->find($userId, $id);
        if (!$study) {
            http_response_code(404);
            ts_flash_set('danger', 'مطالعه پیدا نشد.');
            $this->redirect(ts_url('study'));
        }
        $result = $study['method'] === 'stopwatch'
            ? StudyCalculator::stopwatch($study, $study['elements'], $study['cycles'], $study['observations'])
            : StudyCalculator::workSampling($study, $study['categories'], $study['slots']);
        $this->renderPage('study/show', ['pageTitle' => $study['title'], 'study' => $study, 'result' => $result]);
    }

    public function start(int $id): void
    {
        $userId = $this->userId();
        $this->postOnly();
        try {
            (new StudyRepository())->start($userId, $id);
            ts_flash_set('success', 'مطالعه آغاز شد؛ روش و عناصر آن از این مرحله به بعد ثابت می‌مانند.');
        } catch (\Throwable $e) {
            ts_flash_set('danger', $e instanceof \InvalidArgumentException ? $e->getMessage() : 'آغاز مطالعه انجام نشد.');
        }
        $this->redirect(ts_url('study', 'show', ['id' => $id]));
    }

    public function addCycle(int $id): void
    {
        $userId = $this->userId();
        $this->postOnly();
        try {
            $raw = $_POST['observations'] ?? null;
            if (!is_array($raw)) throw new \InvalidArgumentException('داده‌های چرخه معتبر نیستند.');
            $observations = [];
            foreach ($raw as $elementId => $row) {
                $elementId = filter_var($elementId, FILTER_VALIDATE_INT);
                if (!$elementId || !is_array($row)) throw new \InvalidArgumentException('شناسهٔ عنصر یا دادهٔ مشاهده معتبر نیست.');
                $seconds = $this->decimal((string) ($row['seconds'] ?? ''), 'زمان مشاهده‌شده', 0.0001, 36000, 4);
                $rating = $this->decimal((string) ($row['rating'] ?? '100'), 'ارزیابی عملکرد', 50, 150, 2);
                $valid = (string) ($row['valid'] ?? '1');
                if (!in_array($valid, ['0', '1'], true)) throw new \InvalidArgumentException('وضعیت اعتبار مشاهده معتبر نیست.');
                $reason = isset($row['reason']) && is_string($row['reason']) ? trim($row['reason']) : '';
                if ($valid === '0' && $reason === '') throw new \InvalidArgumentException('علت کنارگذاری مشاهده را وارد کنید.');
                $observations[(int) $elementId] = [
                    'seconds' => $seconds, 'rating' => $rating, 'valid' => $valid === '1',
                    'reason' => $reason ?: null,
                    'note' => isset($row['note']) && is_string($row['note']) ? self::clip(trim($row['note']), 1000) : null,
                ];
            }
            $cycleStatus = (string) ($_POST['cycle_status'] ?? 'valid');
            if (!in_array($cycleStatus, ['valid', 'excluded'], true)) throw new \InvalidArgumentException('وضعیت چرخه معتبر نیست.');
            $reason = $this->inputString('cycle_exclusion_reason', 'علت کنارگذاری چرخه', 500);
            $observer = $this->inputString('observer_name', 'نام مشاهده‌گر', 120);
            $note = $this->inputString('cycle_note', 'یادداشت چرخه', 1000);
            (new StudyRepository())->addCycle($userId, $id, $observations, $cycleStatus, $reason, $observer, $note);
            ts_flash_set('success', 'چرخه ثبت شد؛ مقادیر خام و وضعیت اعتبار آن نگهداری شدند.');
        } catch (\InvalidArgumentException $e) {
            ts_flash_set('danger', $e->getMessage());
        } catch (\Throwable $e) {
            error_log('Time Study cycle registration failed: ' . $e->getMessage());
            ts_flash_set('danger', 'ثبت چرخه انجام نشد.');
        }
        $this->redirect(ts_url('study', 'show', ['id' => $id]));
    }

    public function generateSchedule(int $id): void
    {
        $userId = $this->userId();
        $this->postOnly();
        try {
            $date = $this->jalaliDate((string) ($_POST['sample_date'] ?? ''), 'روز نمونه‌برداری');
            $start = self::latinDigits((string) ($_POST['shift_start'] ?? ''));
            $end = self::latinDigits((string) ($_POST['shift_end'] ?? ''));
            if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/D', $start) || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/D', $end)) throw new \InvalidArgumentException('ساعت شروع و پایان را به‌شکل ساعت:دقیقه وارد کنید.');
            $zone = new DateTimeZone('Asia/Tehran');
            $startAt = new DateTimeImmutable($date . ' ' . $start . ':00', $zone);
            $endAt = new DateTimeImmutable($date . ' ' . $end . ':00', $zone);
            if ($endAt <= $startAt) $endAt = $endAt->modify('+1 day');
            $span = $endAt->getTimestamp() - $startAt->getTimestamp();
            if ($span < 60) throw new \InvalidArgumentException('پایان شیفت باید پس از شروع باشد و بازه دست‌کم یک دقیقه طول بکشد.');
            $rawCount = filter_var(self::latinDigits((string) ($_POST['sample_count'] ?? '')), FILTER_VALIDATE_INT);
            if (!$rawCount || $rawCount < 1 || $rawCount > 500 || $rawCount > $span) throw new \InvalidArgumentException('تعداد نمونه باید بین ۱ تا ۵۰۰ و کمتر از تعداد ثانیه‌های بازه باشد.');
            $offsets = [];
            while (count($offsets) < $rawCount) $offsets[random_int(0, $span - 1)] = true;
            $offsets = array_keys($offsets);
            sort($offsets, SORT_NUMERIC);
            $dateTimes = [];
            foreach ($offsets as $offset) $dateTimes[] = $startAt->modify('+' . $offset . ' seconds')->format('Y-m-d H:i:s');
            $inserted = (new StudyRepository())->generateSamplingSlots($userId, $id, $dateTimes);
            if ($inserted === 0) throw new \InvalidArgumentException('هیچ زمان تازه‌ای اضافه نشد؛ زمان‌های تولیدشده با برنامه‌های قبلی تکراری بودند. بازه یا تاریخ دیگری انتخاب کنید.');
            $skipped = count($dateTimes) - $inserted;
            ts_flash_set('success', $inserted . ' زمان تصادفی به برنامه افزوده شد.' . ($skipped ? ' ' . $skipped . ' زمان تکراری با برنامه‌های قبلی حذف شد.' : '') . ' برنامه را پیش از اجرای شیفت در اختیار مشاهده‌گر بگذارید.');
        } catch (\InvalidArgumentException $e) {
            ts_flash_set('danger', $e->getMessage());
        } catch (\Throwable $e) {
            error_log('Time Study sampling schedule failed: ' . $e->getMessage());
            ts_flash_set('danger', 'ساخت برنامهٔ نمونه‌برداری انجام نشد.');
        }
        $this->redirect(ts_url('study', 'show', ['id' => $id]));
    }

    public function recordSample(int $id, int $slotId): void
    {
        $userId = $this->userId();
        $this->postOnly();
        try {
            $status = (string) ($_POST['slot_status'] ?? 'observed');
            $category = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);
            $observer = $this->inputString('observer_name', 'نام مشاهده‌گر', 120);
            $note = $this->inputString('note', 'یادداشت مشاهده', 1000);
            (new StudyRepository())->recordSamplingSlot($userId, $id, $slotId, $category ?: null, $status, $observer, $note);
            ts_flash_set('success', 'نتیجهٔ زمان نمونه تعیین تکلیف شد.');
        } catch (\InvalidArgumentException $e) {
            ts_flash_set('danger', $e->getMessage());
        } catch (\Throwable $e) {
            error_log('Time Study sample record failed: ' . $e->getMessage());
            ts_flash_set('danger', 'ثبت نتیجهٔ مشاهده انجام نشد.');
        }
        $this->redirect(ts_url('study', 'show', ['id' => $id]));
    }

    public function complete(int $id): void
    {
        $userId = $this->userId();
        $this->postOnly();
        try {
            (new StudyRepository())->complete($userId, $id);
            ts_flash_set('success', 'مطالعه نهایی شد و داده‌های آن قفل شدند.');
        } catch (\Throwable $e) {
            ts_flash_set('warning', $e instanceof \InvalidArgumentException ? $e->getMessage() : 'نهایی‌سازی مطالعه انجام نشد.');
        }
        $this->redirect(ts_url('study', 'show', ['id' => $id]));
    }

    public function archive(int $id): void
    {
        $userId = $this->userId();
        $this->postOnly();
        try {
            (new StudyRepository())->archive($userId, $id);
            ts_flash_set('success', 'مطالعه بایگانی شد؛ گزارش و داده‌ها حفظ شدند.');
        } catch (\Throwable $e) {
            ts_flash_set('danger', $e instanceof \InvalidArgumentException ? $e->getMessage() : 'بایگانی مطالعه انجام نشد.');
        }
        $this->redirect(ts_url('study', 'index', ['archived' => 1]));
    }
}
