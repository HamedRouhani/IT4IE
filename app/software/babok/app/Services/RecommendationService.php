<?php

namespace App\Software\Babok\Services;

use App\Software\Babok\Models\Task;
use App\Software\Babok\Models\Technique;
use App\Software\Babok\Models\TaskTechnique;
use App\Software\Babok\Models\Project;
use App\Software\Babok\Models\ProjectTask;

/**
 * سرویس پیشنهاد هوشمند تکنیک‌های BABOK و تحلیل ردیابی
 * 
 * این سرویس بر اساس موارد زیر امتیازدهی و پیشنهاد می‌دهد:
 * ۱. تکنیک‌های استاندارد مرتبط با هر وظیفه (از جدول task_techniques)
 * ۲. متدولوژی پروژه (waterfall, agile, hybrid)
 * ۳. فاز فعلی پروژه (initiation, planning, analysis, ...)
 * ۴. تعداد ذی‌نفعان و تکرار تکنیک در وظایف مختلف
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
            'collaborative' => 0.8, 'research' => 1.2, 'experimental' => 0.7,
            'management' => 1.3, 'strategic' => 1.1, 'modeling' => 1.2
        ],
        'agile' => [
            'collaborative' => 1.4, 'research' => 0.9, 'experimental' => 1.3,
            'management' => 0.9, 'strategic' => 0.8, 'modeling' => 1.0
        ],
        'hybrid' => [
            'collaborative' => 1.1, 'research' => 1.1, 'experimental' => 1.0,
            'management' => 1.1, 'strategic' => 1.0, 'modeling' => 1.1
        ]
    ];

    // ضرایب وزنی بر اساس فاز پروژه
    private $phaseWeights = [
        'initiation' => [
            'collaborative' => 1.3, 'research' => 1.1, 'experimental' => 0.7,
            'management' => 1.0, 'strategic' => 1.4, 'modeling' => 0.8
        ],
        'planning' => [
            'collaborative' => 1.1, 'research' => 1.0, 'experimental' => 0.8,
            'management' => 1.4, 'strategic' => 1.1, 'modeling' => 1.1
        ],
        'analysis' => [
            'collaborative' => 1.2, 'research' => 1.4, 'experimental' => 1.0,
            'management' => 1.0, 'strategic' => 0.9, 'modeling' => 1.3
        ],
        'design' => [
            'collaborative' => 1.0, 'research' => 1.0, 'experimental' => 1.4,
            'management' => 0.9, 'strategic' => 0.8, 'modeling' => 1.4
        ],
        'implementation' => [
            'collaborative' => 1.1, 'research' => 0.8, 'experimental' => 1.2,
            'management' => 1.3, 'strategic' => 0.7, 'modeling' => 1.0
        ],
        'evaluation' => [
            'collaborative' => 1.0, 'research' => 1.3, 'experimental' => 1.0,
            'management' => 1.3, 'strategic' => 1.1, 'modeling' => 0.9
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
     * 🌟 متد اصلی برای دریافت پیشنهادات هوشمند در صفحه جزئیات پروژه
     * 
     * @param int $projectId شناسه پروژه
     * @return array لیست ۵ تکنیک برتر پیشنهادی
     */
    public function getSmartRecommendations($projectId)
    {
        $project = $this->projectModel->find($projectId);
        if (!$project) {
            return [];
        }

        // دریافت پیشنهادات تجمیع‌شده برای کل پروژه
        $allRecommendations = $this->recommendForProject($projectId);

        // بازگرداندن ۵ مورد برتر برای نمایش در داشبورد
        return array_slice($allRecommendations, 0, 5);
    }

    /**
     * پیشنهاد تکنیک برای یک وظیفه خاص
     */
    public function recommendForTask($taskId, $context = [])
    {
        $task = $this->taskModel->find($taskId);
        if (!$task) {
            return [];
        }

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

        usort($ranked, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $ranked;
    }

    /**
     * پیشنهاد تکنیک برای کل پروژه (تجمیع امتیازات)
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

        $projectTasks = $this->projectTaskModel->getByProject($projectId);
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

        $allRecommendations = [];

        // محاسبه امتیاز نهایی و مرتب‌سازی
        foreach ($techniqueScores as $techId => $data) {
            $avgScore = $data['task_count'] > 0 ? $data['total_score'] / $data['task_count'] : 0;
            // پاداش فرکانس: اگر یک تکنیک برای چندین وظیفه پیشنهاد شود، امتیاز بیشتری می‌گیرد
            $frequencyBonus = min(20, $data['task_count'] * 3); 
            
            $allRecommendations[] = [
                'technique' => $data['technique'],
                'score' => round($avgScore + $frequencyBonus, 1),
                'task_count' => $data['task_count'],
                'reason' => implode(' | ', array_slice(array_unique($data['reasons']), 0, 2))
            ];
        }

        usort($allRecommendations, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $allRecommendations;
    }

    /**
     * محاسبه امتیاز یک تکنیک بر اساس context
     */
    private function calculateTechniqueScore($technique, $methodology, $phase, $stakeholderCount)
    {
        $score = 50; // امتیاز پایه
        $category = $technique['category'] ?? 'collaborative';

        // ۱. اعمال وزن متدولوژی
        $methodologyWeight = $this->methodologyWeights[$methodology][$category] ?? 1.0;
        $score *= $methodologyWeight;

        // ۲. اعمال وزن فاز
        $phaseWeight = $this->phaseWeights[$phase][$category] ?? 1.0;
        $score *= $phaseWeight;

        // ۳. امتیاز بر اساس تعداد ذی‌نفعان
        if ($stakeholderCount > 0) {
            if ($category === 'collaborative') {
                $score += min(15, $stakeholderCount * 1.5);
            }
            if ($category === 'management' && $stakeholderCount > 5) {
                $score += 10;
            }
        }

        // ۴. امتیاز برای تکنیک‌های پرکاربرد و کلیدی
        $popularTechniques = ['Interviews', 'Workshops', 'Brainstorming', 'Document Analysis', 'Prototyping'];
        if (in_array($technique['name'], $popularTechniques)) {
            $score += 5;
        }

        return min(100, $score);
    }

    /**
     * تولید دلیل پیشنهاد یک تکنیک به زبان فارسی
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

        $methodologyReasons = [
            'waterfall' => 'سازگار با رویکرد آبشاری و مستندسازی کامل',
            'agile' => 'سازگار با رویکرد چابک و تکرارپذیری',
            'hybrid' => 'قابل استفاده در رویکرد ترکیبی'
        ];

        if (isset($methodologyReasons[$methodology])) {
            $reasons[] = $methodologyReasons[$methodology];
        }

        return implode(' | ', array_slice($reasons, 0, 2));
    }

    /**
     * 🌟 پیشنهاد هوشمند ردیابی (Traceability) بین وظایف پروژه
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

        // وظایف تکمیل شده یا در حال انجام (منبع)
        $activeProjectTasks = array_filter($projectTasks, function($pt) {
            return in_array($pt['status'], ['completed', 'in_progress']);
        });

        foreach ($activeProjectTasks as $pt) {
            $currentTask = $taskDetails[$pt['task_id']] ?? null;
            if (!$currentTask || empty(trim($currentTask['outputs'] ?? ''))) continue;

            // 🌟 تبدیل encoding به UTF-8 قبل از پردازش
            $outputsRaw = mb_convert_encoding($currentTask['outputs'], 'UTF-8', 'auto');
            
            // ۱. پاک‌سازی و تبدیل خروجی‌ها به آرایه تمیز (با modifier /u برای UTF-8)
            $outputs = preg_split('/[،,]/u', $outputsRaw);
            $outputs = array_map(function($val) {
                return trim(mb_convert_encoding($val, 'UTF-8', 'auto'));
            }, $outputs);
            $outputs = array_filter($outputs, fn($val) => mb_strlen($val, 'UTF-8') > 2);

            // جستجو برای وظایف بعدی که هنوز شروع نشده‌اند (هدف)
            foreach ($projectTasks as $nextPt) {
                if ($nextPt['status'] === 'not_started') {
                    $nextTask = $taskDetails[$nextPt['task_id']] ?? null;
                    if (!$nextTask || empty(trim($nextTask['inputs'] ?? ''))) continue;

                    // 🌟 تبدیل encoding به UTF-8 قبل از پردازش
                    $inputsRaw = mb_convert_encoding($nextTask['inputs'], 'UTF-8', 'auto');
                    
                    // ۲. پاک‌سازی و تبدیل ورودی‌ها به آرایه تمیز
                    $inputs = preg_split('/[،,]/u', $inputsRaw);
                    $inputs = array_map(function($val) {
                        return trim(mb_convert_encoding($val, 'UTF-8', 'auto'));
                    }, $inputs);
                    $inputs = array_filter($inputs, fn($val) => mb_strlen($val, 'UTF-8') > 2);
                    
                    // ۳. یافتن اشتراک دقیق بین ورودی‌ها و خروجی‌ها
                    $matches = array_intersect($outputs, $inputs);

                    if (!empty($matches)) {
                        // حذف مقادیر تکراری و ساخت رشته نهایی
                        $sharedArtifacts = implode('، ', array_unique($matches));
                        
                        // 🌟 اطمینان از UTF-8 بودن خروجی نهایی
                        $sharedArtifacts = mb_convert_encoding($sharedArtifacts, 'UTF-8', 'auto');
                        
                        $suggestions[] = [
                            'source_task_name' => mb_convert_encoding($currentTask['name'], 'UTF-8', 'auto'),
                            'target_task_name' => mb_convert_encoding($nextTask['name'], 'UTF-8', 'auto'),
                            'shared_artifacts' => trim($sharedArtifacts) !== '' ? trim($sharedArtifacts) : 'نیاز به بررسی دستی',
                            'recommendation' => "خروجی «{$currentTask['name']}» می‌تواند به عنوان ورودی مستقیم برای «{$nextTask['name']}» استفاده شود."
                        ];
                    }
                }
            }
        }

        return $suggestions;
    }
}