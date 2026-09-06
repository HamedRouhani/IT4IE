<?php
namespace App\Software\Statlab\Controllers;

use App\Software\Statlab\Core\Controller;
use App\Software\Statlab\Helpers\RegressionEngine as RE;

class RegressionController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $pm = new \App\Software\Statlab\Models\Project();
        $projects = $pm->query(
            "SELECT id, name FROM `{$pm->getTableName()}` WHERE user_id = :uid ORDER BY updated_at DESC LIMIT 50",
            ['uid' => $this->currentUserId]
        );
        $this->view('regression/index', [
            'pageTitle'   => 'رگرسیون و همبستگی',
            'currentPage' => 'regression',
            'projects'    => $projects,
        ]);
    }

    public function analyze(): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }

        try {
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $mode  = $payload['mode'] ?? 'simple';
            $alpha = (float)($payload['alpha'] ?? 0.05);

            // ─── دریافت Y و X ها (می‌توانند از payload یا dataset id باشند) ───
            $Y = $this->parseNumbers($payload['y'] ?? '');
            $n = count($Y);
            if ($n < 3) throw new \Exception('حداقل ۳ مشاهده برای Y لازم است.');

            $k = ($mode === 'multiple') ? max(1, min(5, (int)($payload['k'] ?? 1))) : 1;
            $Xs = [];
            for ($j = 1; $j <= $k; $j++) {
                $xj = $this->parseNumbers($payload['x' . $j] ?? '');
                if (count($xj) !== $n) throw new \Exception("تعداد داده‌های X$j باید برابر Y باشد ($n).");
                $Xs[] = $xj;
            }
            if ($mode === 'multiple' && $n <= $k + 1) {
                throw new \Exception('تعداد مشاهدات باید بیشتر از (تعداد پیشبین‌ها + ۱) باشد.');
            }

            // ─── اجرا ───
            if ($mode === 'correlation')     $result = RE::correlation($Xs[0], $Y, $alpha);
            elseif ($mode === 'simple')      $result = RE::simple($Xs[0], $Y, $alpha);
            else                             $result = RE::multiple($Y, $Xs, $alpha);

            // ─── ذخیره در پروژه (ایجاد خودکار در صورت نیاز) ───
            $pm = new \App\Software\Statlab\Models\Project();
            $dm = new \App\Software\Statlab\Models\Dataset();
            $rm = new \App\Software\Statlab\Models\Result();

            $projectId = (int)($payload['project_id'] ?? 0);

            if ($projectId > 0) {
                $proj = $pm->find($projectId);
                if (!$proj || (int)$proj['user_id'] !== (int)$this->currentUserId) {
                    $this->json(['success' => false, 'error' => 'دسترسی به پروژه مجاز نیست.'], 403);
                    return;
                }
            } else {
                // ✅ اگر پروژه مقصد نبود، یکی جدید ایجاد کن
                $titles = [
                    'correlation' => 'تحلیل همبستگی',
                    'simple'      => 'رگرسیون خطی ساده',
                    'multiple'    => 'رگرسیون چندگانه'
                ];
                $projectId = $pm->create([
                    'user_id'            => $this->currentUserId,
                    'name'               => trim($payload['project_name'] ?? '') ?: ($titles[$mode] . ' - ' . date('Y-m-d H:i')),
                    'description'        => 'تحلیل رگرسیون/همبستگی',
                    'category_code'      => 'regression',
                    'analysis_type_code' => $mode,
                    'objective'          => 'predict',
                    'significance_level' => $alpha,
                    'status'             => 'completed',
                ]);
                $this->logActivity('create_project', 'project', $projectId);
            }

            // ─── ذخیره داده‌ها (فقط اگر داده مستقیماً از فرم آمده باشد) ───
            // اگر داده‌ها از dataset موجود می‌آیند، نیازی به ایجاد dataset جدید نیست
            $saveDatasets = (bool)($payload['save_datasets'] ?? true);
            if ($saveDatasets) {
                $dm->create([
                    'project_id' => $projectId, 'user_id' => $this->currentUserId,
                    'name' => 'Y (وابسته)', 'source' => 'manual',
                    'data_json' => json_encode($Y), 'sample_size' => $n, 'variables_count' => 1,
                ]);
                foreach ($Xs as $j => $xj) {
                    $dm->create([
                        'project_id' => $projectId, 'user_id' => $this->currentUserId,
                        'name' => 'X' . ($j + 1), 'source' => 'manual',
                        'data_json' => json_encode($xj), 'sample_size' => $n, 'variables_count' => 1,
                    ]);
                }
            }

            $rm->create([
                'project_id'         => $projectId,
                'analysis_type_code' => 'regression',
                'test_code'          => $mode,
                'method_name'        => ['correlation' => 'همبستگی پیرسون/اسپیرمن', 'simple' => 'رگرسیون خطی ساده', 'multiple' => 'رگرسیون چندگانه (OLS)'][$mode],
                'input_params'       => json_encode(['alpha' => $alpha, 'k' => $k], JSON_UNESCAPED_UNICODE),
                'output_data'        => json_encode($result, JSON_UNESCAPED_UNICODE),
                'p_value'            => min(1, max(0, $result['p_slope'] ?? $result['p_F'] ?? $result['pearson_p'] ?? 1)),
                'interpretation_fa'  => $result['interpretation_fa'],
            ]);

            $pm->update($projectId, ['status' => 'completed']);
            $this->logActivity('analyze_regression', 'project', $projectId);

            $this->json([
                'success' => true, 'mode' => $mode, 'result' => $result,
                'project_id' => $projectId, 'project_name' => $pm->find($projectId)['name'] ?? '',
            ]);

        } catch (\Throwable $e) {
            error_log("StatLab Regression Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function parseNumbers($text): array
    {
        $out = [];
        $text = str_replace(
            ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹','٠','١','٢','٣','٤','٥','٦','٧','٨','٩'],
            ['0','1','2','3','4','5','6','7','8','9','0','1','2','3','4','5','6','7','8','9'],
            (string)$text
        );
        foreach (preg_split('/[\s,;\n\r\t|]+/', $text) as $part) {
            $part = trim($part);
            if ($part !== '' && is_numeric($part)) $out[] = (float)$part;
        }
        return $out;
    }
}