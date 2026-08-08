<?php
namespace App\Services;

use App\Models\Technique;
use App\Models\Task;
use App\Models\Project;
use App\Models\ProjectTask;

class RecommendationService
{
    private $techniqueModel;
    private $taskModel;
    private $projectModel;
    private $projectTaskModel;

    public function __construct()
    {
        $this->techniqueModel = new Technique();
        $this->taskModel = new Task();
        $this->projectModel = new Project();
        $this->projectTaskModel = new ProjectTask();
    }

    /**
     * پیشنهاد تکنیک‌ها برای یک وظیفه خاص با توجه به زمینه پروژه
     */
    public function recommendForTask($taskId, $context = [])
    {
        // 1. دریافت تکنیک‌های مرتبط با وظیفه
        $techniques = $this->techniqueModel->getForTask($taskId);
        
        // 2. امتیازدهی به تکنیک‌ها بر اساس زمینه
        foreach ($techniques as &$tech) {
            $score = $this->calculateScore($tech, $context);
            $tech['relevance_score'] = $score;
            $tech['reason'] = $this->getReason($tech, $context);
        }
        
        // 3. مرتب‌سازی بر اساس امتیاز
        usort($techniques, function($a, $b) {
            return $b['relevance_score'] - $a['relevance_score'];
        });
        
        return $techniques;
    }

    /**
     * پیشنهاد تکنیک‌های مناسب برای کل پروژه
     */
    public function recommendForProject($projectId)
    {
        $project = $this->projectModel->find($projectId);
        if (!$project) {
            return [];
        }
        
        // دریافت وظایف پروژه
        $projectTasks = $this->projectTaskModel->getByProject($projectId);
        $taskIds = array_column($projectTasks, 'task_id');
        
        $recommendations = [];
        foreach ($taskIds as $taskId) {
            $task = $this->taskModel->find($taskId);
            if ($task) {
                $techniques = $this->recommendForTask($taskId, [
                    'methodology' => $project['methodology'],
                    'phase' => $project['phase'],
                    'stakeholder_count' => $project['stakeholder_count']
                ]);
                
                $recommendations[] = [
                    'task' => $task,
                    'techniques' => $techniques
                ];
            }
        }
        
        return $recommendations;
    }

    /**
     * پیشنهاد هوشمند بر اساس تحلیل نیازمندی‌ها
     */
    public function recommendForRequirements($requirementText)
    {
        $keywords = $this->extractKeywords($requirementText);
        $techniques = $this->techniqueModel->getAll();
        
        $ranked = [];
        foreach ($techniques as $technique) {
            $score = $this->calculateRelevance($technique, $keywords);
            if ($score > 0) {
                $ranked[] = [
                    'technique' => $technique,
                    'score' => $score,
                    'matched_keywords' => $this->getMatchedKeywords($technique, $keywords)
                ];
            }
        }
        
        // مرتب‌سازی بر اساس امتیاز
        usort($ranked, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        return array_slice($ranked, 0, 10);
    }

    /**
     * دریافت وظایف پیشنهادی برای پروژه
     */
    public function getRecommendedTasks($projectId)
    {
        $project = $this->projectModel->find($projectId);
        if (!$project) {
            return [];
        }

        // دریافت همه وظایف
        $allTasks = $this->taskModel->getAll();
        
        // دریافت وظایف فعلی پروژه
        $currentTasks = $this->projectTaskModel->getByProject($projectId);
        $currentTaskIds = array_column($currentTasks, 'task_id');
        
        // فیلتر و امتیازدهی
        $recommended = [];
        foreach ($allTasks as $task) {
            if (!in_array($task['id'], $currentTaskIds)) {
                $score = $this->calculateTaskScore($task, $project);
                if ($score > 0) {
                    $task['score'] = $score;
                    $task['reason'] = $this->getTaskReason($task, $project);
                    $recommended[] = $task;
                }
            }
        }
        
        // مرتب‌سازی بر اساس امتیاز
        usort($recommended, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        return $recommended;
    }

    private function calculateScore($technique, $context)
    {
        $score = 50; // امتیاز پایه
        
        // امتیاز بر اساس متدلوژی
        if (isset($context['methodology'])) {
            if ($context['methodology'] === 'agile') {
                $agileFriendly = ['User Stories', 'Backlog Management', 'Collaborative Games', 'Prototyping'];
                if (in_array($technique['name'], $agileFriendly)) {
                    $score += 30;
                }
            } elseif ($context['methodology'] === 'waterfall') {
                $waterfallFriendly = ['Data Flow Diagrams', 'Data Modeling', 'Sequence Diagrams', 'State Modeling'];
                if (in_array($technique['name'], $waterfallFriendly)) {
                    $score += 30;
                }
            }
        }
        
        // امتیاز بر اساس فاز پروژه
        if (isset($context['phase'])) {
            $phaseMapping = [
                'initiation' => ['Brainstorming', 'Stakeholder List, Map, or Personas', 'Business Model Canvas'],
                'planning' => ['Estimation', 'Risk Analysis and Management', 'SWOT Analysis'],
                'analysis' => ['Interviews', 'Workshops', 'Process Modeling', 'Data Modeling'],
                'design' => ['Prototyping', 'Use Cases and Scenarios', 'Data Flow Diagrams'],
                'implementation' => ['Reviews', 'Acceptance and Evaluation Criteria'],
                'evaluation' => ['Metrics and Key Performance Indicators (KPIs)', 'Lessons Learned']
            ];
            
            $phaseTechniques = $phaseMapping[$context['phase']] ?? [];
            if (in_array($technique['name'], $phaseTechniques)) {
                $score += 20;
            }
        }
        
        // امتیاز بر اساس تعداد ذی‌نفعان
        if (isset($context['stakeholder_count'])) {
            if ($context['stakeholder_count'] > 10 && $technique['category'] === 'collaborative') {
                $score += 10;
            } elseif ($context['stakeholder_count'] < 5 && $technique['category'] === 'research') {
                $score += 10;
            }
        }
        
        return min($score, 100);
    }

    private function getReason($technique, $context)
    {
        $reasons = [];
        
        if (isset($context['methodology'])) {
            if ($context['methodology'] === 'agile') {
                $agileFriendly = ['User Stories', 'Backlog Management', 'Collaborative Games', 'Prototyping'];
                if (in_array($technique['name'], $agileFriendly)) {
                    $reasons[] = 'مناسب برای متدلوژی چابک';
                }
            } elseif ($context['methodology'] === 'waterfall') {
                $waterfallFriendly = ['Data Flow Diagrams', 'Data Modeling', 'Sequence Diagrams', 'State Modeling'];
                if (in_array($technique['name'], $waterfallFriendly)) {
                    $reasons[] = 'مناسب برای متدلوژی آبشاری';
                }
            }
        }
        
        if (isset($context['phase'])) {
            $reasons[] = "مناسب برای فاز " . ucfirst($context['phase']);
        }
        
        return implode('، ', $reasons) ?: 'تکنیک استاندارد';
    }

    private function calculateTaskScore($task, $project)
    {
        $score = 0;
        
        // بر اساس متدلوژی
        if ($project['methodology'] === 'agile') {
            $agileTasks = ['5.1', '5.2', '5.3', '4.2', '4.4'];
            if (in_array($task['code'], $agileTasks)) {
                $score += 20;
            }
        } elseif ($project['methodology'] === 'waterfall') {
            $waterfallTasks = ['3.1', '3.2', '3.3', '7.1', '7.2', '7.3'];
            if (in_array($task['code'], $waterfallTasks)) {
                $score += 20;
            }
        }
        
        // بر اساس فاز
        $phaseMapping = [
            'initiation' => ['6.1', '6.2', '6.3', '6.4'],
            'planning' => ['3.1', '3.2', '3.3', '3.4'],
            'analysis' => ['4.1', '4.2', '4.3', '7.1', '7.2', '7.3'],
            'design' => ['7.4', '7.5', '7.6'],
            'implementation' => ['5.4', '5.5'],
            'evaluation' => ['8.1', '8.2', '8.3', '8.4', '8.5']
        ];
        
        $phaseTasks = $phaseMapping[$project['phase']] ?? [];
        if (in_array($task['code'], $phaseTasks)) {
            $score += 30;
        }
        
        return $score;
    }

    private function getTaskReason($task, $project)
    {
        $reasons = [];
        
        if ($project['methodology'] === 'agile') {
            $agileTasks = ['5.1', '5.2', '5.3', '4.2', '4.4'];
            if (in_array($task['code'], $agileTasks)) {
                $reasons[] = 'مناسب برای پروژه‌های چابک';
            }
        }
        
        $reasons[] = "مناسب برای فاز " . ucfirst($project['phase']);
        
        return implode('، ', $reasons) ?: 'وظیفه استاندارد';
    }

    private function extractKeywords($text)
    {
        // حذف علائم نگارشی
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text);
        $text = strtolower($text);
        $words = array_unique(explode(' ', $text));
        return array_filter($words, function($w) { return strlen($w) > 2; });
    }

    private function calculateRelevance($technique, $keywords)
    {
        $score = 0;
        $text = strtolower(
            ($technique['name'] ?? '') . ' ' . 
            ($technique['description'] ?? '') . ' ' . 
            ($technique['purpose'] ?? '')
        );
        
        foreach ($keywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                $score++;
            }
        }
        
        return $score;
    }

    private function getMatchedKeywords($technique, $keywords)
    {
        $matched = [];
        $text = strtolower(
            ($technique['name'] ?? '') . ' ' . 
            ($technique['description'] ?? '') . ' ' . 
            ($technique['purpose'] ?? '')
        );
        
        foreach ($keywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                $matched[] = $keyword;
            }
        }
        
        return $matched;
    }
}