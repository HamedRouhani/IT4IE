<?php
namespace App\Software\Statlab\Controllers;

use App\Software\Statlab\Core\Controller;
use App\Software\Statlab\Helpers\DistributionLibrary as DL;

class DistributionController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $this->view('distributions/index', [
            'pageTitle'     => 'توزیع‌های احتمال',
            'currentPage'   => 'distribution',
            'distributions' => DL::list(),
        ]);
    }

    /**
     * ماشین‌حساب توزیع (AJAX)
     */
    public function calculate(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }

        try {
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $dist  = $payload['dist'] ?? 'normal';
            $meta  = DL::list();

            if (!is_string($dist) || !isset($meta[$dist])) {
                throw new \InvalidArgumentException('توزیع نامعتبر است.');
            }

            $rawParams = $payload['params'] ?? [];
            if (!is_array($rawParams)) {
                throw new \InvalidArgumentException('پارامترهای توزیع باید به‌صورت مجموعهٔ مقادیر ارسال شوند.');
            }

            // اعتبارسنجی و ساخت پارامترها
            $params = [];
            foreach ($meta[$dist]['params'] as $pm) {
                $raw = $rawParams[$pm['key']] ?? $pm['default'];
                $v = \App\Software\Statlab\Helpers\InputValidator::finiteNumber($raw, $pm['label']);
                if (isset($pm['min']) && $v < $pm['min']) {
                    throw new \InvalidArgumentException($pm['label'] . ' خارج از بازه مجاز است.');
                }
                if (isset($pm['max']) && $v > $pm['max']) {
                    throw new \InvalidArgumentException($pm['label'] . ' خارج از بازه مجاز است.');
                }
                $params[$pm['key']] = $v;
            }

            $mode = $payload['mode'] ?? 'left';
            if (!is_string($mode) || !in_array($mode, ['left', 'right', 'between', 'quantile'], true)) {
                throw new \InvalidArgumentException('نوع محاسبه نامعتبر است.');
            }
            $x = \App\Software\Statlab\Helpers\InputValidator::finiteNumber($payload['x'] ?? 0, 'x');
            $x2 = \App\Software\Statlab\Helpers\InputValidator::finiteNumber($payload['x2'] ?? 0, 'x2');
            $prob = \App\Software\Statlab\Helpers\InputValidator::finiteNumber($payload['p'] ?? 0.95, 'احتمال');

            $family = $meta[$dist]['family'];
            $result = [];

            if ($mode === 'quantile') {
                $q = DL::quantile($dist, $params, $prob);
                $result = [
                    'quantile' => $q,
                    'cdf_at_q' => $prob,
                    'text' => "P(X ≤ " . round($q, 4) . ") = " . round($prob, 4),
                ];
            } else {
                if ($mode === 'left') {
                    $probOut = DL::cdf($dist, $params, $x);
                    $region = [null, $x];
                    $text = "P(X ≤ " . $x . ") = " . round($probOut, 6);
                } elseif ($mode === 'right') {
                    $probOut = 1 - DL::cdf($dist, $params, $x);
                    $region = [$x, null];
                    $text = "P(X ≥ " . $x . ") = " . round($probOut, 6);
                } else {
                    if ($x2 < $x) [$x, $x2] = [$x2, $x];
                    $probOut = DL::cdf($dist, $params, $x2) - DL::cdf($dist, $params, $x);
                    $region = [$x, $x2];
                    $text = "P(" . $x . " ≤ X ≤ " . $x2 . ") = " . round($probOut, 6);
                }
                $result = ['probability' => $probOut, 'region' => $region, 'text' => $text];
            }

            // نقاط منحنی برای نمودار
            [$lo, $hi] = DL::support($dist, $params);
            $curve = [];
            if ($family === 'discrete') {
                for ($k = (int)$lo; $k <= (int)$hi; $k++) {
                    $curve[] = ['x' => $k, 'y' => DL::pdf($dist, $params, $k)];
                }
            } else {
                $n = 160;
                for ($i = 0; $i <= $n; $i++) {
                    $cx = $lo + ($hi - $lo) * $i / $n;
                    $curve[] = ['x' => $cx, 'y' => DL::pdf($dist, $params, $cx)];
                }
            }

            $this->json([
                'success'  => true,
                'family'   => $family,
                'result'   => $result,
                'curve'    => $curve,
                'mean'     => DL::mean($dist, $params),
                'variance' => DL::variance($dist, $params),
            ]);

        } catch (\Throwable $e) {
            error_log("StatLab Distribution Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], $e instanceof \InvalidArgumentException ? 422 : 500);
        }
    }
}
