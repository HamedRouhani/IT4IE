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

            if (!isset($meta[$dist])) {
                $this->json(['success' => false, 'error' => 'توزیع نامعتبر است.']);
                return;
            }

            // اعتبارسنجی و ساخت پارامترها
            $params = [];
            foreach ($meta[$dist]['params'] as $pm) {
                $v = (float)($payload['params'][$pm['key']] ?? $pm['default']);
                if (isset($pm['min']) && $v < $pm['min']) $v = $pm['min'];
                if (isset($pm['max']) && $v > $pm['max']) $v = $pm['max'];
                $params[$pm['key']] = $v;
            }

            $mode = $payload['mode'] ?? 'left';
            $x  = (float)($payload['x'] ?? 0);
            $x2 = (float)($payload['x2'] ?? 0);
            $prob = (float)($payload['p'] ?? 0.95);

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
                } else { // between
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
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}