<?php
/**
 * Standalone numerical regression references for OR, StatLab and Quality.
 * Run from the repository root with: php tests/reference/run.php
 * No database, framework boot, network, or external test package is required.
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$files = [
    'app/software/statlab/app/Helpers/InputValidator.php',
    'app/software/statlab/app/Helpers/DistributionLibrary.php',
    'app/software/statlab/app/Helpers/StatEngine.php',
    'app/software/statlab/app/Helpers/HypothesisTester.php',
    'app/software/statlab/app/Helpers/RegressionEngine.php',
    'app/software/statlab/app/Helpers/SmartStatistician.php',
    'app/software/or/app/Helpers/AssignmentSolver.php',
    'app/software/or/app/Helpers/DualSimplexEngine.php',
    'app/software/or/app/Helpers/SimplexSolver.php',
    'app/software/or/app/Helpers/ILPEngine.php',
    'app/software/or/app/Helpers/GameTheoryEngine.php',
    'app/software/or/app/Helpers/MarkovEngine.php',
    'app/software/or/app/Helpers/MonteCarloEngine.php',
    'app/software/or/app/Helpers/QueueingEngine.php',
    'app/software/or/app/Helpers/ShortestPathSolver.php',
    'app/software/or/app/Helpers/TransportationSolver.php',
    'app/software/or/app/Helpers/TransshipmentSolver.php',
    'app/software/or/app/Helpers/SensitivityAnalyzer.php',
    'app/software/quality/app/Services/CapabilityService.php',
    'app/software/quality/app/Services/ControlChartService.php',
    'app/software/quality/app/Services/SamplingService.php',
    'app/software/quality/app/Services/NelsonRulesService.php',
    'app/software/quality/app/Services/MsaService.php',
    'app/software/quality/app/Services/ParetoService.php',
    'app/software/mcdm/app/Helpers/AhpCalculator.php',
    'app/software/mcdm/app/Helpers/SawCalculator.php',
    'app/software/mcdm/app/Helpers/TopsisCalculator.php',
    'app/software/mcdm/app/Helpers/VikorCalculator.php',
    'app/software/pdm/app/Models/BaseModel.php',
    'app/software/pdm/app/Models/KPI.php',
];
foreach ($files as $file) {
    require_once $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
}

use App\Software\Or\Helpers\AssignmentSolver;
use App\Software\Or\Helpers\DualSimplexEngine;
use App\Software\Or\Helpers\GameTheoryEngine;
use App\Software\Or\Helpers\ILPEngine;
use App\Software\Or\Helpers\MarkovEngine;
use App\Software\Or\Helpers\MonteCarloEngine;
use App\Software\Or\Helpers\QueueingEngine;
use App\Software\Or\Helpers\SensitivityAnalyzer;
use App\Software\Or\Helpers\ShortestPathSolver;
use App\Software\Or\Helpers\SimplexSolver;
use App\Software\Or\Helpers\TransportationSolver;
use App\Software\Or\Helpers\TransshipmentSolver;
use App\Software\Quality\Services\CapabilityService;
use App\Software\Quality\Services\ControlChartService;
use App\Software\Quality\Services\MsaService;
use App\Software\Quality\Services\NelsonRulesService;
use App\Software\Quality\Services\ParetoService;
use App\Software\Quality\Services\SamplingService;
use App\Software\Mcdm\Helpers\AhpCalculator;
use App\Software\Mcdm\Helpers\SawCalculator;
use App\Software\Mcdm\Helpers\TopsisCalculator;
use App\Software\Mcdm\Helpers\VikorCalculator;
use App\Software\Pdm\Models\KPI as PdmKpi;
use App\Software\Statlab\Helpers\DistributionLibrary as DL;
use App\Software\Statlab\Helpers\HypothesisTester;
use App\Software\Statlab\Helpers\InputValidator;
use App\Software\Statlab\Helpers\RegressionEngine;
use App\Software\Statlab\Helpers\SmartStatistician;
use App\Software\Statlab\Helpers\StatEngine;

$passed = 0;
$failed = [];
$assert = static function (string $name, bool $condition, string $detail = '') use (&$passed, &$failed): void {
    if ($condition) {
        $passed++;
        return;
    }
    $failed[] = $name . ($detail === '' ? '' : ': ' . $detail);
};
$close = static function (string $name, $actual, float $expected, float $tol = 1e-6) use ($assert): void {
    $assert($name, is_numeric($actual) && is_finite((float)$actual) && abs((float)$actual - $expected) <= $tol,
        'expected ' . $expected . ', got ' . var_export($actual, true));
};
$throws = static function (string $name, callable $fn) use ($assert): void {
    try { $fn(); $assert($name, false, 'expected InvalidArgumentException'); }
    catch (InvalidArgumentException $e) { $assert($name, true); }
};

// StatLab input/data summaries; expected values are hand-computable.
$close('StatLab sample mean', StatEngine::mean([1, 2, 3, 4]), 2.5);
$close('StatLab sample variance', StatEngine::variance([1, 2, 3, 4]), 5 / 3);
$close('StatLab population variance', StatEngine::variance([1, 2, 3, 4], false), 1.25);
$quartiles = StatEngine::quartiles([1, 2, 3, 4]);
$close('StatLab Type 7 first quartile', $quartiles['q1'] ?? null, 1.75);
$close('StatLab Type 7 third quartile', $quartiles['q3'] ?? null, 3.25);
$assert('StatLab parser normalizes Persian digits and decimal mark', InputValidator::parseNumbers('۱٫۵ ۲٫۵') === [1.5, 2.5]);
$assert('StatLab rejects nonnumeric observations', (static function () { try { StatEngine::mean([1, 'bad']); return false; } catch (InvalidArgumentException $e) { return true; } })());
$throws('StatLab rejects nonrectangular matrix', static fn() => InputValidator::parseMatrix("1 2\n3"));

// Distribution references: standard normal symmetry, exponential CDF, exact moments.
$close('Normal CDF at mean', DL::cdf('normal', ['mu' => 0, 'sigma' => 1], 0), 0.5, 1e-7);
$close('Normal median quantile', DL::quantile('normal', ['mu' => 0, 'sigma' => 1], 0.5), 0, 1e-7);
$close('Shifted normal upper quantile with negative support', DL::quantile('normal', ['mu' => -100, 'sigma' => 2], 0.975), -100 + 2 * 1.95996398454, 0.002);
$close('Cauchy lower-tail quantile beyond initial support', DL::quantile('t', ['df' => 1], 0.001), tan(M_PI * (0.001 - 0.5)), 0.1);
$close('Exponential CDF', DL::cdf('exponential', ['lambda' => 2], 0.5), 1 - exp(-1), 1e-7);
$close('Binomial mean', DL::mean('binomial', ['n' => 10, 'p' => 0.3]), 3);
$close('Binomial variance', DL::variance('binomial', ['n' => 10, 'p' => 0.3]), 2.1);
$throws('Distribution rejects invalid quantile probability', static fn() => DL::quantile('normal', ['mu' => 0, 'sigma' => 1], 1));

// Inferential/statistical engines.
$ht = HypothesisTester::run('one_sample_t', ['data1' => [2, 4, 6], 'mu0' => 4], 0.05);
$close('One-sample t statistic under H0', $ht['statistic'] ?? null, 0, 1e-6);
$close('One-sample t p-value under H0', $ht['p_value'] ?? null, 1, 1e-6);
$reg = RegressionEngine::simple([1, 2, 3, 4, 5], [3, 5, 7, 9, 11], 0.05);
$close('OLS slope', $reg['slope'] ?? null, 2, 1e-6);
$close('OLS intercept', $reg['intercept'] ?? null, 1, 1e-6);
$close('OLS R squared on exact line', $reg['r2'] ?? null, 1, 1e-6);
$multi = RegressionEngine::multiple([1, 3, 5, 7, 9], [[1, 2, 3, 4, 5]], 0.05);
$close('Multiple OLS intercept', $multi['coefficients'][0]['beta'] ?? null, -1, 1e-5);
$close('Multiple OLS slope', $multi['coefficients'][1]['beta'] ?? null, 2, 1e-5);
$close('Multiple OLS exact-fit R squared', $multi['r2'] ?? null, 1, 1e-6);
$throws('Regression rejects unequal samples', static fn() => RegressionEngine::correlation([1, 2, 3], [1, 2], 0.05));
$smart = (new SmartStatistician())->analyze('آزمون t یک نمونه برای مقایسه میانگین این نمونه با مقدار فرض شده');
$assert('Smart Statistician identifies the one-sample test', ($smart['detected_test'] ?? '') === 'one_sample_t');
$assert('Smart Statistician exposes heuristic-score semantics', ($smart['score_semantics'] ?? '') === 'exploratory_keyword_match_share');
$close('Legacy confidence alias equals score share', $smart['confidence'] ?? null, (float)($smart['match_share'] ?? NAN), 1e-9);

// OR optimization: LPs share a tiny hand-solvable model with optimum (2,2), Z=10.
$c = [3, 2]; $A = [[1, 1], [1, 0], [0, 1]]; $b = [4, 2, 3]; $types = ['<=', '<=', '<='];
$simplex = SimplexSolver::solve($c, $A, $b, $types, 'maximize');
$assert('Two-phase simplex finds optimum', ($simplex['status'] ?? '') === 'optimal');
$close('Two-phase simplex objective', $simplex['optimal_value'] ?? null, 10, 1e-4);
$dual = DualSimplexEngine::solve($c, $A, $b, $types, 'maximize');
$assert('Big-M simplex finds optimum', ($dual['status'] ?? '') === 'optimal');
$close('Big-M simplex objective', $dual['objective_value'] ?? null, 10, 1e-4);
$ilp = ILPEngine::solve($c, $A, $b, $types, [0, 1], 'maximize');
$assert('Branch-and-bound finds integer optimum', ($ilp['status'] ?? '') === 'optimal');
$close('Branch-and-bound objective', $ilp['objective_value'] ?? null, 10, 1e-4);
$assignment = AssignmentSolver::solve([[4, 1], [2, 3]]);
$assert('Hungarian assignment succeeds', ($assignment['status'] ?? '') === 'success');
$close('Hungarian minimum cost', $assignment['total_cost'] ?? null, 3, 1e-6);
$game = GameTheoryEngine::solve('zero_sum', ['H', 'T'], ['H', 'T'], [[1, -1], [-1, 1]]);
$assert('Matching-pennies mixed strategy detected', ($game['status'] ?? '') === 'ok' && ($game['type'] ?? '') === 'mixed');
$close('Matching-pennies game value', $game['value_of_game'] ?? null, 0, 1e-6);
$close('Matching-pennies strategy probability', $game['p1_probs'][0] ?? null, 0.5, 1e-6);

// Network/flow and stochastic models.
$nodes = [['id'=>1,'name'=>'A'], ['id'=>2,'name'=>'B'], ['id'=>3,'name'=>'C']];
$edges = [
    ['source_id'=>1,'destination_id'=>2,'cost'=>1],
    ['source_id'=>2,'destination_id'=>3,'cost'=>2],
    ['source_id'=>1,'destination_id'=>3,'cost'=>8],
];
$dijkstra = ShortestPathSolver::dijkstra($nodes, $edges, 1);
$dijkstraToC = array_values(array_filter($dijkstra['paths'], static fn($path) => $path['destination'] === 'C'))[0] ?? [];
$close('Dijkstra shortest A to C', $dijkstraToC['distance'] ?? null, 3, 1e-6);
$floyd = ShortestPathSolver::floydWarshall($nodes, $edges);
$floydToC = array_values(array_filter($floyd['paths'], static fn($path) => $path['from'] === 'A' && $path['to'] === 'C'))[0] ?? [];
$close('Floyd-Warshall shortest A to C', $floydToC['distance'] ?? null, 3, 1e-6);
$markov = MarkovEngine::solve(['A','B'], [[0.9,0.1],[0.5,0.5]], [1,0], 1);
$assert('Markov chain accepted', ($markov['status'] ?? '') === 'ok');
$close('Markov one-step probability', $markov['final_state'][0] ?? null, 0.9, 1e-8);
$close('Markov stationary probability', $markov['steady_state'][0] ?? null, 5/6, 1e-6);
$mc = MonteCarloEngine::simulate([['name'=>'x1','dist'=>'binomial','params'=>['n'=>1,'p'=>0.5]]], 'x1', 1000, 12345);
$assert('Monte Carlo reports all valid samples', ($mc['status'] ?? '') === 'ok' && ($mc['valid_samples'] ?? 0) === 1000);
$assert('Monte Carlo seeded output reproducible', $mc['stats']['mean'] === MonteCarloEngine::simulate([['name'=>'x1','dist'=>'binomial','params'=>['n'=>1,'p'=>0.5]]], 'x1', 1000, 12345)['stats']['mean']);
$lognormal = MonteCarloEngine::simulate([['name'=>'x1','dist'=>'lognormal','params'=>['mean'=>100,'std'=>20]]], 'x1', 20000, 20261026);
$assert('Lognormal simulation preserves arithmetic mean', abs($lognormal['var_stats']['x1']['mean'] - 100) < 1.0,
    'expected a seeded sample mean within 1.0 of 100, got ' . ($lognormal['var_stats']['x1']['mean'] ?? 'missing'));
$assert('Lognormal simulation preserves arithmetic standard deviation', abs($lognormal['var_stats']['x1']['std'] - 20) < 1.0,
    'expected a seeded sample SD within 1.0 of 20, got ' . ($lognormal['var_stats']['x1']['std'] ?? 'missing'));
$throws('Lognormal rejects nonpositive arithmetic mean', static fn() => MonteCarloEngine::simulate([['name'=>'x1','dist'=>'lognormal','params'=>['mean'=>0,'std'=>1]]], 'x1', 100, 1));

// Queueing formulas use λ=2, μ=3: for M/M/1, ρ=2/3, L=2, Lq=4/3, W=1/1.
$mm1 = QueueingEngine::solve('MM1', ['lambda'=>2,'mu'=>3]);
$close('M/M/1 utilization', $mm1['rho'] ?? null, 2/3, 1e-6);
$close('M/M/1 L', $mm1['L'] ?? null, 2, 1e-4);
$close('M/M/1 Lq', $mm1['Lq'] ?? null, 4/3, 1e-4);
$mmc = QueueingEngine::solve('MMc', ['lambda'=>2,'mu'=>3,'servers'=>2]);
$assert('M/M/c stable result', ($mmc['status'] ?? '') === 'ok');
$mmcA = 2/3;
$mmcP0 = 1 / (1 + $mmcA + (($mmcA ** 2) / 2) / (1 - 1/3));
$close('M/M/2 Erlang-C wait probability', $mmc['Pw'] ?? null, (($mmcA ** 2) / 2) / (1 - 1/3) * $mmcP0, 1e-6);
$mm1k = QueueingEngine::solve('MM1K', ['lambda'=>2,'mu'=>3,'capacity'=>4]);
$close('M/M/1/K probabilities normalize', array_sum($mm1k['Pn'] ?? []), 1, 1e-5);
$mm1kRho = 2/3; $mm1kP0 = (1 - $mm1kRho) / (1 - $mm1kRho ** 5);
$close('M/M/1/K blocking probability', $mm1k['extra']['احتمال رد (P_K)'] ?? null, $mm1kRho ** 4 * $mm1kP0, 1e-6);
$mmck = QueueingEngine::solve('MMcK', ['lambda'=>2,'mu'=>3,'servers'=>2,'capacity'=>4]);
$close('M/M/c/K probabilities normalize', array_sum($mmck['Pn'] ?? []), 1, 1e-5);
$mmckWeights = [1, 2/3, (2/3)**2/2, (2/3)**3/4, (2/3)**4/8];
$close('M/M/2/4 blocking probability', $mmck['extra']['احتمال رد (P_K)'] ?? null, end($mmckWeights)/array_sum($mmckWeights), 1e-6);
$mg1 = QueueingEngine::solve('MG1', ['lambda'=>2,'mu'=>3,'service_std'=>0]);
$md1 = QueueingEngine::solve('MD1', ['lambda'=>2,'mu'=>3]);
$close('M/G/1 deterministic-service matches M/D/1 Lq', $mg1['Lq'] ?? null, $md1['Lq'] ?? null, 1e-4);

$transport = TransportationSolver::solve([[2,3],[5,4]], [3,2], [2,3], 'VAM', true);
$assert('Transportation solution balances supply', ($transport['status'] ?? '') === 'success' && array_sum(array_map('array_sum', $transport['allocation'])) === 5);
$close('Transportation optimal cost', $transport['optimal_cost'] ?? null, 15, 1e-4);
$transshipment = TransshipmentSolver::solve(
    [
        ['id'=>1,'name'=>'S','type'=>'source','capacity'=>5],
        ['id'=>2,'name'=>'T','type'=>'transshipment','capacity'=>5],
        ['id'=>3,'name'=>'D','type'=>'destination','capacity'=>5],
    ],
    [
        ['source_id'=>1,'destination_id'=>2,'cost'=>1,'is_prohibited'=>false],
        ['source_id'=>2,'destination_id'=>2,'cost'=>0,'is_prohibited'=>false],
        ['source_id'=>2,'destination_id'=>3,'cost'=>2,'is_prohibited'=>false],
        ['source_id'=>1,'destination_id'=>3,'cost'=>8,'is_prohibited'=>false],
    ]
);
$assert('Transshipment solver returns a feasible result', ($transshipment['status'] ?? '') === 'success');
$close('Transshipment path flow cost', $transshipment['total_cost'] ?? null, 15, 1e-4);
$sensitivity = SensitivityAnalyzer::analyze('LP', ['shadow_prices'=>[2], 'objective_ranges'=>[]], []);
$assert('LP sensitivity result preserves shadow prices', ($sensitivity['status'] ?? '') === 'success' && ($sensitivity['shadow_prices'][0] ?? null) === 2);

// Quality mathematical services.
$cap = (new CapabilityService())->compute([[9,10,11],[9,10,11]], 0, 20, 10);
$close('Capability grand mean', $cap['mean'] ?? null, 10, 1e-6);
$close('Capability Cp from Rbar/d2', $cap['cp'] ?? null, 20/(6*(2/1.128)), 1e-4);
$chart = (new ControlChartService())->compute('xbar_r', [[1,2],[2,3],[3,4]]);
$close('Xbar-R center line', $chart['center_line'] ?? null, 2.5, 1e-6);
$close('Xbar-R upper limit', $chart['ucl'] ?? null, 2.5 + 1.88, 1e-6);
$sampling = new SamplingService();
$close('Acceptance plan exact probability', $sampling->acceptanceProbability(5, 0, 0.1), 0.9**5, 1e-10);
$nelson = (new NelsonRulesService())->detect([0,0,0,4], 0, 1);
$assert('Nelson rule 1 flags out-of-control point', ($nelson[0]['rule'] ?? null) === 1 && ($nelson[0]['points'][0] ?? null) === 3);
$msa = (new MsaService())->computeAnova([
    'parts'=>2,'operators'=>2,'trials'=>2,
    'values'=>[
        [[10,11],[20,21]],
        [[11,12],[21,22]],
    ],
]);
$assert('Gage R&R returns finite study variation', isset($msa['pct_grr']) && is_finite((float)$msa['pct_grr']));
$close('Gage R&R ANOVA total sum of squares decomposition', $msa['anova']['ss_part'] + $msa['anova']['ss_op'] + $msa['anova']['ss_interaction'] + $msa['anova']['ss_error'], $msa['anova']['ss_total'], 1e-6);
$paretoService = (new ReflectionClass(ParetoService::class))->newInstanceWithoutConstructor();
$pareto = $paretoService->calculate([
    ['category_name'=>'A','value'=>50],['category_name'=>'B','value'=>30],['category_name'=>'C','value'=>20],
]);
$close('Pareto total', $pareto['total_value'] ?? null, 100, 1e-6);
$assert('Pareto vital-few count respects threshold crossing', ($pareto['vital_few_count'] ?? null) === 2);

// Multi-criteria decision methods and PdM reliability KPIs.
$ahp = AhpCalculator::analyze([[1,2],[0.5,1]]);
$assert('AHP pairwise matrix accepted', ($ahp['status'] ?? '') === 'success');
$close('AHP consistent pairwise weight', $ahp['weights'][0] ?? null, 2/3, 1e-6);
$close('AHP consistency ratio for consistent matrix', $ahp['consistency_metrics']['CR'] ?? null, 0, 1e-6);
$decision = [[1],[2],[3]]; $weights = [1]; $types = ['benefit'];
$saw = SawCalculator::calculate($decision, $weights, $types);
$topsis = TopsisCalculator::calculate($decision, $weights, $types);
$vikor = VikorCalculator::calculate($decision, $weights, $types, 0.5);
$assert('SAW ranks highest benefit first', ($saw['ranking'][0]['alternative_index'] ?? null) === 2);
$assert('TOPSIS ranks highest benefit first', ($topsis['ranking'][0]['alternative_index'] ?? null) === 2);
$assert('VIKOR ranks highest benefit first', ($vikor['ranking'][0]['alternative_index'] ?? null) === 2);
$close('TOPSIS ideal alternative score', $topsis['ranking'][0]['score'] ?? null, 1, 1e-6);
$pdmKpi = (new ReflectionClass(PdmKpi::class))->newInstanceWithoutConstructor();
$calculateKpi = new ReflectionMethod(PdmKpi::class, 'calculateKPI');
$availability = $calculateKpi->invoke($pdmKpi, ['code'=>'Availability'], ['mtbf'=>200.0,'mttr'=>10.0]);
$failureRate = $calculateKpi->invoke($pdmKpi, ['code'=>'FailureRate'], ['mtbf'=>200.0,'mttr'=>10.0]);
$reliability = $calculateKpi->invoke($pdmKpi, ['code'=>'Reliability'], ['mtbf'=>200.0,'mttr'=>10.0]);
$close('PdM availability from MTBF and MTTR', $availability, 95.24, 1e-6);
$close('PdM failure rate from MTBF', $failureRate, 0.005, 1e-6);
$close('PdM exponential reliability at stated 100h horizon', $reliability, exp(-0.5)*100, 0.01);
fwrite(STDOUT, sprintf("Reference checks: %d passed, %d failed\n", $passed, count($failed)));
foreach ($failed as $failure) fwrite(STDERR, "FAIL: {$failure}\n");
exit($failed === [] ? 0 : 1);
