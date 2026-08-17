<?php

namespace App\Software\Babok\Services;

use App\Software\Babok\Models\Task;
use App\Software\Babok\Models\Technique;
use App\Software\Babok\Models\TaskTechnique;
use App\Software\Babok\Models\Project;
use App\Software\Babok\Models\ProjectTask;

/**
 * سرویس پیشنهاد هوشمند تکنیک‌های BABOK
 * 
 * این سرویس بر اساس:
 * - تکنیک‌های استاندارد مرتبط با هر وظیفه (از جدول task_techniques)
 * - متدولوژی پروژه (waterfall, agile, hybrid)
 * - فاز فعلی پروژه
 * - تعداد ذی‌نفعان
 * تکنیک‌های مناسب را پیشنهاد می‌دهد.
 */
class RecommendationService
{
    private $taskModel;
    private $techniqueModel;
    private $taskTechniqueModel;
    private $projectModel;
    private $projectTaskModel;

    // ضرایب وزنی بر اساس متدولوژی
    private $methodologyWeights = [
        'waterfall' => [
            'collaborative' => 0.8,
            'research' => 1.2,
            'experimental' => 0.7,
            'management' => 1.3,
            'strategic' => 1.1,
            'modeling' => 1.2
        ],
        'agile' => [
            'collaborative' => 1.4,
            'research' => 0.9,
            'experimental' => 1.3,
            'management' => 0.9,
            'strategic' => 0.8,
            'modeling' => 1.0
        ],
        'hybrid' => [
            'collaborative' => 1.1,
            'research' => 1.1,
            'experimental' => 1.0,
            'management' => 1.1,
            'strategic' => 1.0,
            'modeling' => 1.1
        ]
    ];

    // ضرایب وزنی بر اساس فاز پروژه
    private $phaseWeights = [
        'initiation' => [
            'collaborative' => 1.3,
            'research' => 1.1,
            'experimental' => 0.7,
            'management' => 1.0,
            'strategic' => 1.4,
            'modeling' => 0.8
        ],
        'planning' => [
            'collaborative' => 1.1,
            'research' => 1.0,
            'experimental' => 0.8,
            'management' => 1.4,
            'strategic' => 1.1,
            'modeling' => 1.1
        ],
        'analysis' => [
            'collaborative' => 1.2,
            'research' => 1.4,
            'experimental' => 1.0,
            'management' => 1.0,
            'strategic' => 0.9,
            'modeling' => 1.3
        ],
        'design' => [
            'collaborative' => 1.0,
            'research' => 1.0,
            'experimental' => 1.4,
            'management' => 0.9,
            'strategic' => 0.8,
            'modeling' => 1.4
        ],
        'implementation' => [
            'collaborative' => 1.1,
            'research' => 0.8,
            'experimental' => 1.2,
            'management' => 1.3,
            'strategic' => 0.7,
            'modeling' => 1.0
        ],
        'evaluation' => [
            'collaborative' => 1.0,
            'research' => 1.3,
            'experimental' => 1.0,
            'management' => 1.3,
            'strategic' => 1.1,
            'modeling' => 0.9
        ]
    ];

    public function __construct()
    {
        $this->taskModel = new Task();
        $this->techniqueModel = new Technique();
        $this->taskTechniqueModel = new TaskTechnique();
        $this->projectModel = new Project();
        $this->projectTaskModel = new ProjectTask();
    }

    /**
     * پیشنهاد تکنیک برای یک وظیفه خاص
     * 
     * @param int $taskId شناسه وظیفه
     * @param array $context شامل methodology, phase, stakeholder_count
     * @return array لیست تکنیک‌های پیشنهادی با امتیاز
     */
    public function recommendForTask($taskId, $context = [])
    {
        $task = $this->taskModel->find($taskId);
        if (!$task) {
            return [];
        }

        // دریافت تکنیک‌های استاندارد مرتبط با وظیفه
        $standardTechniques = $this->taskTechniqueModel->getTechniquesByTask($taskId);

        $methodology = $context['methodology'] ?? 'hybrid';
        $phase = $context['phase'] ?? 'analysis';
        $stakeholderCount = $context['stakeholder_count'] ?? 0;

        $ranked = [];
        $maxPossibleScore = 100;

        foreach ($standardTechniques as $technique) {
            $score = $this->calculateTechniqueScore($technique, $methodology, $phase, $stakeholderCount);
            
            $ranked[] = [
                'technique' => $technique,
                'score' => round($score, 1),
                'score_percent' => min(100, round(($score / $maxPossibleScore) * 100, 1)),
                'reason' => $this->generateReason($technique, $methodology, $phase)
            ];
        }

        // مرتب‌سازی بر اساس امتیاز (نزولی)
        usort($ranked, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $ranked;
    }

    /**
     * پیشنهاد تکنیک برای کل پروژه
     * 
     * @param int $projectId شناسه پروژه
     * @return array لیست تکنیک‌های پیشنهادی برای تمام وظایف پروژه
     */
    public function recommendForProject($projectId)
    {
        $project = $this->projectModel->find($projectId);
        if (!$project) {
            return [];
        }

        $context = [
            'methodology' => $project['methodology'],
            'phase' => $project['phase'],
            'stakeholder_count' => $project['stakeholder_count']
        ];

        // دریافت وظایف پروژه
        $projectTasks = $this->projectTaskModel->getByProject($projectId);
        
        $allRecommendations = [];
        $techniqueScores = [];

        // جمع‌آوری امتیازات تکنیک‌ها برای تمام وظایف پروژه
        foreach ($projectTasks as $projectTask) {
            $taskRecommendations = $this->recommendForTask($projectTask['task_id'], $context);
            
            foreach ($taskRecommendations as $rec) {
                $techId = $rec['technique']['id'];
                
                if (!isset($techniqueScores[$techId])) {
                    $techniqueScores[$techId] = [
                        'technique' => $rec['technique'],
                        'total_score' => 0,
                        'task_count' => 0,
                        'reasons' => []
                    ];
                }
                
                $techniqueScores[$techId]['total_score'] += $rec['score'];
                $techniqueScores[$techId]['task_count']++;
                $techniqueScores[$techId]['reasons'][] = $rec['reason'];
            }
        }

        // محاسبه امتیاز نهایی و مرتب‌سازی
        foreach ($techniqueScores as $techId => $data) {
            $avgScore = $data['task_count'] > 0 ? $data['total_score'] / $data['task_count'] : 0;
            $frequencyBonus = min(20, $data['task_count'] * 3); // امتیاز برای تکرار در وظایف متعدد
            
            $allRecommendations[] = [
                'technique' => $data['technique'],
                'score' => round($avgScore + $frequencyBonus, 1),
                'task_count' => $data['task_count'],
                'reason' => implode(' | ', array_slice(array_unique($data['reasons']), 0, 2))
            ];
        }

        // مرتب‌سازی بر اساس امتیاز (نزولی)
        usort($allRecommendations, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($allRecommendations, 0, 10);
    }

    /**
     * پیشنهاد بر اساس تحلیل متن نیازمندی
     * 
     * @param string $requirementText متن نیازمندی
     * @return array لیست تکنیک‌های پیشنهادی
     */
    public function recommendForRequirements($requirementText)
    {
        // استفاده از RequirementService برای تحلیل متن
        $requirementService = new RequirementService();
        $analysis = $requirementService->process($requirementText);
        
        return $analysis['techniques'] ?? [];
    }

    /**
     * محاسبه امتیاز یک تکنیک بر اساس context
     */
    private function calculateTechniqueScore($technique, $methodology, $phase, $stakeholderCount)
    {
        $score = 50; // امتیاز پایه
        $category = $technique['category'] ?? 'collaborative';

        // اعمال وزن متدولوژی
        $methodologyWeight = $this->methodologyWeights[$methodology][$category] ?? 1.0;
        $score *= $methodologyWeight;

        // اعمال وزن فاز
        $phaseWeight = $this->phaseWeights[$phase][$category] ?? 1.0;
        $score *= $phaseWeight;

        // امتیاز بر اساس تعداد ذی‌نفعان
        if ($stakeholderCount > 0) {
            // تکنیک‌های همکاری برای پروژه‌های با ذی‌نفعان زیاد مناسب‌ترند
            if ($category === 'collaborative') {
                $score += min(15, $stakeholderCount * 1.5);
            }
            // تکنیک‌های مدیریتی برای پروژه‌های بزرگ مناسب‌ترند
            if ($category === 'management' && $stakeholderCount > 5) {
                $score += 10;
            }
        }

        // امتیاز برای تکنیک‌های پرکاربرد
        $popularTechniques = ['Interviews', 'Workshops', 'Brainstorming', 'Document Analysis', 'Prototyping'];
        if (in_array($technique['name'], $popularTechniques)) {
            $score += 5;
        }

        return min(100, $score);
    }

    /**
     * تولید دلیل پیشنهاد یک تکنیک
     */
    private function generateReason($technique, $methodology, $phase)
    {
        $reasons = [];
        $category = $technique['category'] ?? 'collaborative';

        $categoryNames = [
            'collaborative' => 'مناسب برای کارهای گروهی و تعامل با ذی‌نفعان',
            'research' => 'مناسب برای تحقیقات و تحلیل داده‌ها',
            'experimental' => 'مناسب برای آزمایش و نمونه‌سازی',
            'management' => 'مناسب برای مدیریت و کنترل فرآیندها',
            'strategic' => 'مناسب برای تحلیل استراتژیک و تصمیم‌گیری',
            'modeling' => 'مناسب برای مدل‌سازی و طراحی'
        ];

        if (isset($categoryNames[$category])) {
            $reasons[] = $categoryNames[$category];
        }

        // دلیل بر اساس متدولوژی
        $methodologyReasons = [
            'waterfall' => 'سازگار با رویکرد آبشاری و مستندسازی کامل',
            'agile' => 'سازگار با رویکرد چابک و تکرارپذیری',
            'hybrid' => 'قابل استفاده در رویکرد ترکیبی'
        ];

        if (isset($methodologyReasons[$methodology])) {
            $reasons[] = $methodologyReasons[$methodology];
        }

        // دلیل بر اساس فاز
        $phaseReasons = [
            'initiation' => 'مناسب برای فاز شروع و تعریف پروژه',
            'planning' => 'مناسب برای فاز برنامه‌ریزی',
            'analysis' => 'مناسب برای فاز تحلیل نیازمندی‌ها',
            'design' => 'مناسب برای فاز طراحی راه‌حل',
            'implementation' => 'مناسب برای فاز پیاده‌سازی',
            'evaluation' => 'مناسب برای فاز ارزیابی و بهبود'
        ];

        if (isset($phaseReasons[$phase])) {
            $reasons[] = $phaseReasons[$phase];
        }

        return implode(' | ', array_slice($reasons, 0, 2));
    }

    /**
     * پیشنهاد هوشمند ردیابی (Traceability) بین وظایف پروژه
     * بر اساس تطابق خروجی (Output) یک وظیفه با ورودی (Input) وظیفه دیگر
     * 
     * @param int $projectId شناسه پروژه
     * @return array لیست پیشنهادات ردیابی
     */
    public function getTraceabilitySuggestions($projectId)
    {
        $projectTasks = $this->projectTaskModel->getByProject($projectId);
        $suggestions = [];

        // دریافت جزئیات تمام وظایف استاندارد BABOK
        $allTasks = $this->taskModel->getAll();
        $taskDetails = [];
        foreach ($allTasks as $task) {
            $taskDetails[$task['id']] = $task;
        }

        // وظایف تکمیل شده یا در حال انجام
        $activeProjectTasks = array_filter($projectTasks, function($pt) {
            return in_array($pt['status'], ['completed', 'in_progress']);
        });

        foreach ($activeProjectTasks as $pt) {
            $currentTask = $taskDetails[$pt['task_id']] ?? null;
            if (!$currentTask || empty($currentTask['outputs'])) continue;

            // فرض بر این است که خروجی‌ها با ویرگول فارسی یا انگلیسی جدا شده‌اند
            $outputs = preg_split('/[،,]/', $currentTask['outputs']);
            
            // جستجو برای وظایف بعدی که هنوز شروع نشده‌اند
            foreach ($projectTasks as $nextPt) {
                if ($nextPt['status'] === 'not_started') {
                    $nextTask = $taskDetails[$nextPt['task_id']] ?? null;
                    if (!$nextTask || empty($nextTask['inputs'])) continue;

                    $inputs = preg_split('/[،,]/', $nextTask['inputs']);
                    
                    // بررسی اشتراک بین خروجی‌ها و ورودی‌ها
                    $matches = array_intersect(array_map('trim', $outputs), array_map('trim', $inputs));
                    if (!empty($matches)) {
                        $suggestions[] = [
                            'source_task_code' => $currentTask['code'],
                            'source_task_name' => $currentTask['name'],
                            'target_task_code' => $nextTask['code'],
                            'target_task_name' => $nextTask['name'],
                            'shared_artifact' => trim($matches[0]),
                            'recommendation' => "خروجی «{$currentTask['name']}» می‌تواند به عنوان ورودی مستقیم برای «{$nextTask['name']}» استفاده شود. پیشنهاد می‌شود این ارتباط در ماتریس ردیابی ثبت شود."
                        ];
                    }
                }
            }
        }

        return $suggestions;
    }
}