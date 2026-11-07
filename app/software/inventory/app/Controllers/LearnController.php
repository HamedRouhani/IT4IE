<?php

declare(strict_types=1);

namespace App\Software\Inventory\Controllers;

use App\Software\Inventory\Services\InventoryPolicyService;

/**
 * مرکز آموزش Inventory Analyzer: درس‌ها، واژه‌نامه و ماشین‌حساب‌های تعاملی.
 *
 * محتوای آموزشی ثابت است و به دادهٔ فضای کاری کاربر دسترسی ندارد؛ ابزارها نیز
 * فقط برای تمرین‌اند و چیزی ذخیره نمی‌کنند. فرمول‌ها همان روابط
 * InventoryPolicyService هستند تا نتیجه با بخش «تحلیل و سفارش» هم‌خوان بماند.
 */
final class LearnController extends InventoryController
{
    /** @var array<string,mixed>|null */
    private ?array $curriculum = null;

    public function index(): void
    {
        $curriculum = $this->curriculum();
        $levelFilter = (string) ($_GET['level'] ?? 'all');
        if (!array_key_exists($levelFilter, $curriculum['levels'])) $levelFilter = 'all';

        $lessons = $curriculum['lessons'];
        if ($levelFilter !== 'all') {
            $lessons = array_values(array_filter(
                $lessons,
                static fn(array $lesson): bool => $lesson['level'] === $levelFilter
            ));
        }

        $this->renderPage('learn/index', [
            'pageTitle' => 'مرکز آموزش کنترل موجودی',
            'title' => 'مرکز آموزش کنترل موجودی - Inventory Analyzer',
            'lessons' => $lessons,
            'levels' => $curriculum['levels'],
            'levelFilter' => $levelFilter,
            'totalLessons' => count($curriculum['lessons']),
            'glossaryCount' => count($curriculum['glossary']),
        ]);
    }

    public function show(): void
    {
        $curriculum = $this->curriculum();
        $lessonId = (string) ($_GET['lesson'] ?? '');
        $lesson = null;

        foreach ($curriculum['lessons'] as $candidate) {
            if ($candidate['id'] === $lessonId) {
                $lesson = $candidate;
                break;
            }
        }

        if ($lesson === null) {
            http_response_code(404);
            inv_flash_set('danger', 'درس درخواستی پیدا نشد.');
            $this->redirect(inv_url('learn', 'index'));
        }

        $ids = array_column($curriculum['lessons'], 'id');
        $position = array_search($lessonId, $ids, true);
        $previous = $position !== false && $position > 0 ? $curriculum['lessons'][$position - 1] : null;
        $next = $position !== false && $position < count($ids) - 1 ? $curriculum['lessons'][$position + 1] : null;

        $this->renderPage('learn/show', [
            'pageTitle' => $lesson['title'],
            'title' => $lesson['title'] . ' - آموزش کنترل موجودی',
            'lesson' => $lesson,
            'previous' => $previous,
            'next' => $next,
            'levelLabel' => $curriculum['levels'][$lesson['level']] ?? $lesson['level'],
        ]);
    }

    public function glossary(): void
    {
        $curriculum = $this->curriculum();

        $this->renderPage('learn/glossary', [
            'pageTitle' => 'واژه‌نامهٔ کنترل موجودی',
            'title' => 'واژه‌نامه - آموزش کنترل موجودی',
            'terms' => $curriculum['glossary'],
        ]);
    }

    public function tools(): void
    {
        $result = null;
        $values = [];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->verifyCsrf('درخواست معتبر نیست یا نشست منقضی شده است؛ صفحه را تازه کنید.');

            try {
                $tool = (string) ($_POST['tool'] ?? '');
                [$result, $values] = $this->runTool($tool);
            } catch (\InvalidArgumentException $e) {
                $result = ['tool' => (string) ($_POST['tool'] ?? ''), 'error' => $e->getMessage()];
                $values = $_POST;
            } catch (\Throwable $e) {
                error_log('Inventory learn tool failed: ' . $e->getMessage());
                $result = [
                    'tool' => (string) ($_POST['tool'] ?? ''),
                    'error' => 'محاسبه انجام نشد؛ ورودی‌ها را بازبینی کنید.',
                ];
                $values = $_POST;
            }
        }

        $this->renderPage('learn/tools', [
            'pageTitle' => 'ماشین‌حساب‌های آموزشی موجودی',
            'title' => 'ماشین‌حساب‌ها - آموزش کنترل موجودی',
            'result' => $result,
            'values' => $values,
            'serviceLevels' => InventoryPolicyService::serviceLevels(),
        ]);
    }

    /* ---------------------------------------------------------------
     * ماشین‌حساب‌ها
     * --------------------------------------------------------------- */

    /**
     * @return array{0:array<string,mixed>,1:array<string,mixed>}
     */
    private function runTool(string $tool): array
    {
        return match ($tool) {
            'eoq' => $this->toolEoq(),
            'safety' => $this->toolSafety(),
            'abc' => $this->toolAbc(),
            'policy' => $this->toolPolicy(),
            'kpis' => $this->toolKpis(),
            default => throw new \InvalidArgumentException('ماشین‌حساب درخواستی شناخته نشد.'),
        };
    }

    private function toolEoq(): array
    {
        $demand = $this->amount('demand', 'تقاضای سالانه');
        $orderCost = $this->amount('order_cost', 'هزینهٔ هر سفارش');
        $unitCost = $this->amount('unit_cost', 'بهای واحد');
        $rate = $this->percent('holding_rate', 'نرخ نگهداری');

        $analysis = InventoryPolicyService::analyze($this->policyItem([
            'annual_demand' => $demand,
            'unit_cost' => $unitCost,
            'order_cost' => $orderCost,
            'holding_rate' => $rate,
        ]));

        $eoq = $analysis['eoq'];
        if ($eoq === null) {
            throw new \InvalidArgumentException('برای محاسبهٔ EOQ، تقاضا، هزینهٔ سفارش و هزینهٔ نگهداری باید مثبت باشند.');
        }

        $holdingPerUnit = (float) $analysis['annual_holding_cost_per_unit'];
        $orders = $demand / $eoq;
        $cycleDays = $orders > 0 ? 365.0 / $orders : null;
        $annualHolding = ($eoq / 2.0) * $holdingPerUnit;
        $annualOrdering = $orders * $orderCost;
        $relevantTotal = $annualHolding + $annualOrdering;

        $rows = [];
        foreach ([0.5, 1.0, 1.5, 2.0] as $factor) {
            $quantity = $eoq * $factor;
            $holding = ($quantity / 2.0) * $holdingPerUnit;
            $ordering = ($demand / $quantity) * $orderCost;
            $rows[] = [
                inv_num($quantity, 2) . ($factor === 1.0 ? ' (EOQ)' : ''),
                inv_money($holding),
                inv_money($ordering),
                inv_money($holding + $ordering),
            ];
        }

        return [[
            'tool' => 'eoq',
            'title' => 'مقدار اقتصادی سفارش و حساسیت آن',
            'columns' => ['اندازهٔ سفارش', 'هزینهٔ نگهداری', 'هزینهٔ سفارش', 'جمع'],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'EOQ', 'value' => inv_num($eoq, 2) . ' واحد'],
                ['label' => 'تعداد سفارش در سال', 'value' => inv_num($orders, 2) . ' بار'],
                ['label' => 'فاصلهٔ سفارش', 'value' => $cycleDays === null ? '—' : inv_num($cycleDays, 1) . ' روز'],
                ['label' => 'هزینهٔ نگهداری سالانه', 'value' => inv_money($annualHolding)],
                ['label' => 'هزینهٔ سفارش سالانه', 'value' => inv_money($annualOrdering)],
                ['label' => 'جمع هزینهٔ سفارش و نگهداری', 'value' => inv_money($relevantTotal)],
                ['label' => 'هزینهٔ خرید سالانه (اطلاعی)', 'value' => inv_money($demand * $unitCost)],
            ],
            'note' => 'در نقطهٔ EOQ هزینهٔ سفارش و نگهداری با هم برابر می‌شوند. جدول بالا نشان می‌دهد انحراف از EOQ چه اثری بر هزینهٔ کل دارد.',
        ], [
            'demand' => $demand,
            'order_cost' => $orderCost,
            'unit_cost' => $unitCost,
            'holding_rate' => $rate * 100,
        ]];
    }

    private function toolSafety(): array
    {
        $dailyMean = $this->amount('daily_demand', 'میانگین تقاضای روزانه');
        $dailyStd = $this->amount('daily_std', 'انحراف معیار تقاضای روزانه');
        $leadTime = $this->amount('lead_time', 'زمان تأمین');
        $serviceLevel = $this->serviceLevel();

        $analysis = InventoryPolicyService::analyze($this->policyItem([
            'annual_demand' => $dailyMean * 365.0,
            'daily_demand_std' => $dailyStd,
            'lead_time_days' => $leadTime,
            'service_level' => $serviceLevel,
        ]));

        $sigmaLeadTime = $dailyStd * sqrt($leadTime);
        $rows = [];
        foreach (InventoryPolicyService::serviceLevels() as $level => $label) {
            $z = InventoryPolicyService::zValue((string) $level);
            $safety = $z * $dailyStd * sqrt($leadTime);
            $reorder = $dailyMean * $leadTime + $safety;
            $rows[] = [
                $label . ((string) $level === $serviceLevel ? ' (انتخاب‌شده)' : ''),
                inv_num($z, 4),
                inv_num($safety, 2),
                inv_num($reorder, 2),
            ];
        }

        return [[
            'tool' => 'safety',
            'title' => 'موجودی اطمینان و نقطهٔ سفارش',
            'columns' => ['سطح خدمت', 'ضریب z', 'موجودی اطمینان', 'نقطهٔ سفارش'],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'ضریب z', 'value' => inv_num($analysis['z_value'], 4)],
                ['label' => 'انحراف معیار تقاضای زمان تأمین', 'value' => inv_num($sigmaLeadTime, 2) . ' واحد'],
                ['label' => 'تقاضای میانگین زمان تأمین', 'value' => inv_num($dailyMean * $leadTime, 2) . ' واحد'],
                ['label' => 'موجودی اطمینان', 'value' => inv_num($analysis['safety_stock'], 2) . ' واحد'],
                ['label' => 'نقطهٔ سفارش', 'value' => inv_num($analysis['reorder_point'], 2) . ' واحد'],
            ],
            'note' => 'فرض: زمان تأمین ثابت و تقاضای روزانه مستقل است. اگر زمان تأمین هم نوسان دارد، این تقریب کافی نیست.',
        ], [
            'daily_demand' => $dailyMean,
            'daily_std' => $dailyStd,
            'lead_time' => $leadTime,
            'service_level' => $serviceLevel,
        ]];
    }

    private function toolAbc(): array
    {
        $raw = trim((string) ($_POST['items'] ?? ''));
        if ($raw === '') {
            throw new \InvalidArgumentException('دست‌کم یک قلم با ارزش مصرف سالانه وارد کنید (هر قلم در یک خط).');
        }

        $lines = preg_split('/[\r\n]+/', $raw) ?: [];
        $items = [];
        $index = 1;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;
            if (count($items) >= 500) {
                throw new \InvalidArgumentException('حداکثر ۵۰۰ قلم در هر محاسبه قابل طبقه‌بندی است.');
            }

            [$name, $valueRaw] = $this->splitAbcLine($line);

            if ($name === '') $name = 'قلم ' . $index;
            $value = $this->parseAmountString($valueRaw, 'ارزش مصرف سالانهٔ «' . $name . '»');
            if ($value <= 0) {
                throw new \InvalidArgumentException('ارزش مصرف سالانهٔ «' . $name . '» باید بزرگ‌تر از صفر باشد.');
            }

            $items[] = $this->policyItem([
                'name' => $name,
                'annual_demand' => $value,
                'unit_cost' => 1.0,
            ]);
            $index++;
        }

        if ($items === []) {
            throw new \InvalidArgumentException('هیچ قلم معتبری برای طبقه‌بندی پیدا نشد.');
        }

        $classified = InventoryPolicyService::classifyAbc($items);
        $total = 0.0;
        foreach ($classified as $item) {
            $total += (float) $item['_analysis']['annual_usage_value'];
        }

        $rows = [];
        $cumulative = 0.0;
        $counts = ['A' => 0, 'B' => 0, 'C' => 0];
        foreach ($classified as $item) {
            $analysis = $item['_analysis'];
            $cumulative += (float) $analysis['annual_usage_share'];
            $class = (string) $analysis['abc_class'];
            $counts[$class] = ($counts[$class] ?? 0) + 1;
            $rows[] = [
                (string) $item['name'],
                inv_money($analysis['annual_usage_value']),
                inv_pct($analysis['annual_usage_share']),
                inv_pct($cumulative),
                $class,
            ];
        }

        return [[
            'tool' => 'abc',
            'title' => 'طبقه‌بندی ABC بر اساس ارزش مصرف سالانه',
            'columns' => ['قلم', 'ارزش مصرف سالانه', 'سهم', 'سهم تجمعی', 'کلاس'],
            'rows' => $rows,
            'metrics' => [
                ['label' => 'ارزش کل', 'value' => inv_money($total)],
                ['label' => 'تعداد اقلام', 'value' => (string) count($classified)],
                ['label' => 'کلاس A', 'value' => (string) ($counts['A'] ?? 0) . ' قلم'],
                ['label' => 'کلاس B', 'value' => (string) ($counts['B'] ?? 0) . ' قلم'],
                ['label' => 'کلاس C', 'value' => (string) ($counts['C'] ?? 0) . ' قلم'],
            ],
            'note' => 'آستانه‌ها: پیش از افزودن هر قلم، سهم تجمعی کمتر از ۸۰٪ یعنی A، بین ۸۰٪ و کمتر از ۹۵٪ یعنی B و از ۹۵٪ به بالا یعنی C. این آستانه‌ها سیاست پیش‌فرض‌اند، نه قانون جهانی.',
        ], [
            'items' => $raw,
        ]];
    }

    private function toolPolicy(): array
    {
        $annualDemand = $this->amount('annual_demand', 'تقاضای سالانه');
        $dailyStd = $this->amount('daily_std', 'انحراف معیار تقاضای روزانه');
        $leadTime = $this->amount('lead_time', 'زمان تأمین');
        $onHand = $this->amount('on_hand', 'موجودی در دسترس');
        $onOrder = $this->amount('on_order', 'سفارش در راه', true);
        $unitCost = $this->amount('unit_cost', 'بهای واحد');
        $orderCost = $this->amount('order_cost', 'هزینهٔ هر سفارش');
        $rate = $this->percent('holding_rate', 'نرخ نگهداری');
        $serviceLevel = $this->serviceLevel();

        $analysis = InventoryPolicyService::analyze($this->policyItem([
            'annual_demand' => $annualDemand,
            'daily_demand_std' => $dailyStd,
            'lead_time_days' => $leadTime,
            'service_level' => $serviceLevel,
            'on_hand' => $onHand,
            'on_order' => $onOrder,
            'unit_cost' => $unitCost,
            'order_cost' => $orderCost,
            'holding_rate' => $rate,
        ]));

        $eoq = $analysis['eoq'];
        $shouldOrder = (bool) $analysis['should_order'];
        $suggested = (float) $analysis['suggested_order'];

        return [[
            'tool' => 'policy',
            'title' => 'سیاست سفارش یک قلم',
            'columns' => [],
            'rows' => [],
            'metrics' => [
                ['label' => 'میانگین تقاضای روزانه', 'value' => inv_num($analysis['average_daily_demand'], 2) . ' واحد'],
                ['label' => 'ضریب z', 'value' => inv_num($analysis['z_value'], 4)],
                ['label' => 'موجودی اطمینان', 'value' => inv_num($analysis['safety_stock'], 2) . ' واحد'],
                ['label' => 'نقطهٔ سفارش', 'value' => inv_num($analysis['reorder_point'], 2) . ' واحد'],
                ['label' => 'موقعیت موجودی', 'value' => inv_num($analysis['inventory_position'], 2) . ' واحد'],
                ['label' => 'EOQ', 'value' => $eoq === null ? 'قابل محاسبه نیست' : inv_num($eoq, 2) . ' واحد'],
                ['label' => 'کسری تا نقطهٔ سفارش', 'value' => inv_num($analysis['reorder_gap'], 2) . ' واحد'],
                ['label' => 'مقدار پیشنهادی سفارش', 'value' => $shouldOrder ? inv_num($suggested, 2) . ' واحد' : '—'],
                ['label' => 'وضعیت', 'value' => $shouldOrder ? 'نیازمند سفارش' : 'کافی'],
                ['label' => 'هزینهٔ نگهداری هر واحد در سال', 'value' => inv_money($analysis['annual_holding_cost_per_unit'])],
                ['label' => 'ارزش مصرف سالانه', 'value' => inv_money($analysis['annual_usage_value'])],
            ],
            'note' => $shouldOrder
                ? 'موقعیت موجودی به نقطهٔ سفارش رسیده است. ماژول مقدار پیشنهادی را بزرگ‌ترِ EOQ و کسری تا نقطهٔ سفارش می‌گذارد؛ بنابراین ممکن است پس از دریافت، موقعیت موجودی از ROP بالاتر برود.'
                : 'موقعیت موجودی بالاتر از نقطهٔ سفارش است و سفارشی پیشنهاد نمی‌شود. با کاهش موجودی یا افزایش سفارش در راه، این وضعیت بازبینی می‌شود.',
        ], [
            'annual_demand' => $annualDemand,
            'daily_std' => $dailyStd,
            'lead_time' => $leadTime,
            'on_hand' => $onHand,
            'on_order' => $onOrder,
            'unit_cost' => $unitCost,
            'order_cost' => $orderCost,
            'holding_rate' => $rate * 100,
            'service_level' => $serviceLevel,
        ]];
    }

    private function toolKpis(): array
    {
        $cogs = $this->amount('cogs', 'هزینهٔ کالای فروش‌رفته');
        $averageInventory = $this->amount('average_inventory', 'موجودی متوسط');
        $unitsShipped = $this->amount('units_shipped', 'واحدهای تأمین‌شده');
        $unitsDemanded = $this->amount('units_demanded', 'واحدهای درخواستی');

        if ($averageInventory <= 0) {
            throw new \InvalidArgumentException('برای محاسبهٔ گردش موجودی، موجودی متوسط باید بزرگ‌تر از صفر باشد.');
        }
        if ($unitsShipped > $unitsDemanded && $unitsDemanded > 0) {
            throw new \InvalidArgumentException('واحدهای تأمین‌شده نمی‌توانند از واحدهای درخواستی بیشتر باشند.');
        }

        $turnover = $cogs / $averageInventory;
        $days = $turnover > 0 ? 365.0 / $turnover : null;
        $fillRate = $unitsDemanded > 0 ? $unitsShipped / $unitsDemanded : null;

        return [[
            'tool' => 'kpis',
            'title' => 'شاخص‌های عملکرد موجودی',
            'columns' => [],
            'rows' => [],
            'metrics' => [
                ['label' => 'گردش موجودی', 'value' => inv_num($turnover, 2) . ' بار در سال'],
                ['label' => 'روزهای موجودی', 'value' => $days === null ? '—' : inv_num($days, 1) . ' روز'],
                ['label' => 'نرخ پرشدگی', 'value' => $fillRate === null ? '—' : inv_pct($fillRate)],
                ['label' => 'نرخ کمبود', 'value' => $fillRate === null ? '—' : inv_pct(max(0.0, 1.0 - $fillRate))],
                ['label' => 'هزینهٔ کالای فروش‌رفته', 'value' => inv_money($cogs)],
                ['label' => 'موجودی متوسط', 'value' => inv_money($averageInventory)],
            ],
            'note' => 'گردش را با COGS و موجودی متوسط به بهای تمام‌شده حساب کنید، نه با ارزش فروش. نرخ پرشدگی واحدمحور است و با سطح خدمت چرخه‌ای (احتمال نبود کمبود در هر چرخه) یکی نیست.',
        ], [
            'cogs' => $cogs,
            'average_inventory' => $averageInventory,
            'units_shipped' => $unitsShipped,
            'units_demanded' => $unitsDemanded,
        ]];
    }

    /* ---------------------------------------------------------------
     * کمکی‌ها
     * --------------------------------------------------------------- */

    /** @return array<string,mixed> */
    private function curriculum(): array
    {
        if ($this->curriculum === null) {
            $this->curriculum = require MODULAR_APP_PATH . '/app/Content/Curriculum.php';
        }

        return $this->curriculum;
    }

    /** @return array<string,mixed> */
    private function policyItem(array $overrides = []): array
    {
        return array_merge([
            'name' => '',
            'annual_demand' => 0.0,
            'daily_demand_std' => 0.0,
            'lead_time_days' => 0.0,
            'service_level' => '95',
            'on_hand' => 0.0,
            'on_order' => 0.0,
            'unit_cost' => 0.0,
            'order_cost' => 0.0,
            'holding_rate' => 0.0,
        ], $overrides);
    }

    /**
     * تفکیک خط «نام | ارزش» در ابزار ABC.
     *
     * جداکننده‌های قوی (خط عمودی، سمی‌کولون، دونقطه، تب) بر ویرگول اولویت دارند؛
     * چون ویرگول در اعداد فارسی و لاتین جداکنندهٔ هزارگان است. اگر جداکنندهٔ قوی
     * نبود، نخستین ویرگولی انتخاب می‌شود که سمت راست آن یک عدد معتبر باشد.
     *
     * @return array{0:string,1:string}
     */
    private function splitAbcLine(string $line): array
    {
        if ($this->isAmountToken($line)) {
            return ['', $line];
        }

        if (preg_match('/^(.*)[|;:\t]\s*([^|;:\t]+)$/u', $line, $match)) {
            return [trim($match[1]), trim($match[2])];
        }

        if (preg_match_all('/[,،]/u', $line, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $token = $match[0];
                $offset = $match[1];
                $left = trim(substr($line, 0, $offset));
                $right = trim(substr($line, $offset + strlen($token)));
                if ($left !== '' && $right !== '' && $this->isAmountToken($right)) {
                    return [$left, $right];
                }
            }
        }

        return ['', $line];
    }

    private function isAmountToken(string $raw): bool
    {
        $normalized = $this->latin(trim($raw));

        return $normalized !== '' && is_numeric($normalized);
    }

    private function latin(string $value): string
    {
        $value = strtr($value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '٫' => '.', '٬' => '', '،' => '',
        ]);

        return str_replace([',', ' ', "\u{200c}"], '', $value);
    }

    private function parseAmountString(string $raw, string $label): float
    {
        $normalized = $this->latin(trim($raw));
        if ($normalized === '' || !is_numeric($normalized)) {
            throw new \InvalidArgumentException($label . ' باید عدد باشد.');
        }

        $value = (float) $normalized;
        if (!is_finite($value) || $value < 0) {
            throw new \InvalidArgumentException($label . ' نمی‌تواند منفی باشد.');
        }

        return $value;
    }

    private function amount(string $field, string $label, bool $optional = false): float
    {
        $raw = trim((string) ($_POST[$field] ?? ''));
        if ($raw === '') {
            if ($optional) return 0.0;
            throw new \InvalidArgumentException($label . ' الزامی است.');
        }

        return $this->parseAmountString($raw, $label);
    }

    /** درصد از ورودی کاربر؛ خروجی کسری (0.25) */
    private function percent(string $field, string $label): float
    {
        $raw = trim((string) ($_POST[$field] ?? ''));
        if ($raw === '') {
            throw new \InvalidArgumentException($label . ' الزامی است.');
        }

        $normalized = $this->latin(str_replace('%', '', $raw));
        if ($normalized === '' || !is_numeric($normalized)) {
            throw new \InvalidArgumentException($label . ' باید عدد باشد.');
        }

        $value = (float) $normalized;
        if ($value < 0 || $value > 100) {
            throw new \InvalidArgumentException($label . ' باید بین ۰ و ۱۰۰ درصد باشد.');
        }

        return $value / 100.0;
    }

    private function serviceLevel(): string
    {
        $level = trim((string) ($_POST['service_level'] ?? '95'));

        return array_key_exists($level, InventoryPolicyService::serviceLevels()) ? $level : '95';
    }
}
