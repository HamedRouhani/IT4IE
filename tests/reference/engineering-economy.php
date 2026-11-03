<?php
/**
 * آزمون عددی مرجع ماژول اقتصاد مهندسی.
 * اجرا از ریشهٔ مخزن:  php tests/reference/engineering-economy.php
 * بدون دیتابیس، بدون فریم‌ورک و بدون وابستگی خارجی.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../app/software/engineering-economy/app/Services/EeMath.php';

use App\Software\EngineeringEconomy\Services\EeMath;

$failures = 0;
$checks = 0;

/**
 * مقایسه با تلورانس نسبی.
 */
function check(string $label, ?float $actual, ?float $expected, float $tolerance = 1.0e-6): void
{
    global $failures, $checks;
    $checks++;

    if ($actual === null || $expected === null) {
        $failures++;
        echo "FAIL  {$label}: مقدار null برگشت (انتظار: " . var_export($expected, true) . ")\n";

        return;
    }

    $difference = abs($actual - $expected);
    $scale = max(1.0, abs($expected));

    if (($difference / $scale) > $tolerance) {
        $failures++;
        printf("FAIL  %s: %s (انتظار %s)\n", $label, number_format($actual, 10), number_format($expected, 10));

        return;
    }

    printf("ok    %s = %s\n", $label, number_format($actual, 6));
}

/* ------------------------------------------------------------------
 * ۱) فاکتورهای ارزش زمانی پول (جداول استاندارد، i=10%, n=5)
 * ------------------------------------------------------------------ */
check('(P/A,10%,5)', EeMath::factorPA(0.10, 5), 3.790787, 1.0e-5);
check('(A/P,10%,5)', EeMath::factorAP(0.10, 5), 0.263797, 1.0e-5);
check('(P/F,10%,5)', EeMath::factorPF(0.10, 5), 0.620921, 1.0e-5);
check('(F/P,10%,5)', EeMath::factorFP(0.10, 5), 1.610510, 1.0e-5);
check('(F/A,10%,5)', EeMath::factorFA(0.10, 5), 6.105100, 1.0e-5);
check('(A/F,10%,5)', EeMath::factorAF(0.10, 5), 0.163797, 1.0e-5);
check('(P/G,10%,5)', EeMath::factorPG(0.10, 5), 6.861800, 1.0e-4);
check('(A/G,10%,5)', EeMath::factorAG(0.10, 5), 1.810100, 1.0e-4);
check('(P/A,0%,5) = n', EeMath::factorPA(0.0, 5), 5.0);

/* ------------------------------------------------------------------
 * ۲) جریان نقدی نمونه: -10000 و سپس 5 سال 3000 با MARR=10%
 * ------------------------------------------------------------------ */
$flows = [-10000.0, 3000.0, 3000.0, 3000.0, 3000.0, 3000.0];

check('NPV نمونه', EeMath::npv(0.10, $flows), 1372.36, 1.0e-3);
check('IRR نمونه', EeMath::irr($flows), 0.1524, 1.0e-3);
check('ERR نمونه (reinvest 10%)', EeMath::err($flows, 0.10), 0.1287, 2.0e-3);
check('MIRR نمونه', EeMath::mirr($flows, 0.10), 0.1287, 2.0e-3);
check('بازگشت ساده', EeMath::paybackSimple($flows), 3.3333, 1.0e-3);
check('شاخص سودآوری', EeMath::profitabilityIndex($flows, 0.10), 1.1372, 1.0e-4);
check('AW نمونه', EeMath::annualWorth(0.10, $flows, 5), 362.00, 0.05);

$roots = EeMath::irrRoots($flows);
printf("ok    تعداد ریشهٔ IRR = %d\n", count($roots));
check('تغییر علامت جریان متعارف', (float) EeMath::signChanges($flows), 1.0);

/* ------------------------------------------------------------------
 * ۳) ارزش تغییر: سال ۱ با −۵۰٫۳٪ تغییر، NPV را صفر می‌کند
 * ------------------------------------------------------------------ */
check('ارزش تغییر سال ۱', EeMath::switchingValue($flows, 0.10, 1), -0.5032, 2.0e-3);

/* ------------------------------------------------------------------
 * ۴) استهلاک: سرمایه 10000، اسقاط 2000، عمر 5
 * ------------------------------------------------------------------ */
$straight = EeMath::depreciationStraightLine(10000.0, 2000.0, 5);
check('استهلاک مستقیم سال ۱', $straight[0]['depreciation'], 1600.0);
check('ارزش دفتری پایان سال ۵ (مستقیم)', $straight[4]['bookValue'], 2000.0);

$soyd = EeMath::depreciationSumOfYears(10000.0, 2000.0, 5);
check('مجموع سنوات سال ۱', $soyd[0]['depreciation'], 2666.6667, 1.0e-4);
check('مجموع سنوات سال ۵', $soyd[4]['depreciation'], 533.3333, 1.0e-4);

$declining = EeMath::depreciationDeclining(10000.0, 2000.0, 5, 0.20);
check('نزولی ۲۰٪ سال ۱', $declining[0]['depreciation'], 2000.0);
check('نزولی: ارزش دفتری پایان سال ۵', $declining[4]['bookValue'], 2000.0, 1.0e-6);

/* ------------------------------------------------------------------
 * ۵) EUAC و عمر اقتصادی
 * ------------------------------------------------------------------ */
check('EUAC (P=10000, S=0, n=5, i=10%)', (float) EeMath::euac(0.10, 10000.0, 0.0, 0.0, 5), 2637.97, 1.0e-2);
check('هزینهٔ سرمایه‌شده (A=1000, i=10%)', (float) EeMath::capitalizedCost(0.10, 5000.0, 1000.0), 15000.0);

$euacRows = EeMath::euacByYear(10000.0, [1 => 2000.0, 2 => 2500.0, 3 => 3000.0, 4 => 3500.0], [1 => 7000.0, 2 => 5000.0, 3 => 3000.0, 4 => 1000.0], 0.10);
printf("ok    عمر اقتصادی = %s سال\n", (string) EeMath::economicLife($euacRows));

/* ------------------------------------------------------------------
 * ۶) تورم و نرخ مؤثر
 * ------------------------------------------------------------------ */
check('نرخ اسمی از حقیقی ۱۰٪ و تورم ۲۰٪', EeMath::marketRateFromReal(0.10, 0.20), 0.32);
check('نرخ حقیقی از اسمی ۳۲٪ و تورم ۲۰٪', EeMath::realRateFromMarket(0.32, 0.20), 0.10, 1.0e-9);
check('نرخ مؤثر سالانه (۱۲٪، مرکب ماهانه)', EeMath::effectiveAnnualRate(0.12, 12), 0.126825, 1.0e-5);

/* ------------------------------------------------------------------
 * ۷) مالیات و برآورد هزینه
 * ------------------------------------------------------------------ */
$afterTax = EeMath::afterTaxFlows([1 => 5000.0], [1 => 2000.0], [1 => 1600.0], 0.25);
check('جریان پس از مالیات سال ۱', $afterTax[1]['netCash'], 2650.0);
check('قاعدهٔ شش‌دهم (ظرفیت دو برابر)', (float) EeMath::capacityCost(1000.0, 100.0, 200.0), 1515.72, 1.0e-2);

/* ------------------------------------------------------------------
 * ۸) بررسی اعتبار ورودی
 * ------------------------------------------------------------------ */
$issues = EeMath::validate([-1000.0, 500.0, -500.0, 900.0]);
printf("ok    هشدارهای جریان غیرمتعارف = %d مورد\n", count($issues));
check('جریان متعارف؟', EeMath::isConventional([-1000.0, 500.0, 500.0]) ? 1.0 : 0.0, 1.0);

/* ------------------------------------------------------------------ */
echo "\n" . str_repeat('-', 46) . "\n";
printf("نتیجه: %d بررسی، %d خطا\n", $checks, $failures);
exit($failures === 0 ? 0 : 1);
