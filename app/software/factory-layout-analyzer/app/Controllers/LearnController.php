<?php

declare(strict_types=1);

namespace App\Software\FactoryLayoutAnalyzer\Controllers;

use App\Core\Controller;
use App\Software\FactoryLayoutAnalyzer\Content\Curriculum;

final class LearnController extends Controller
{
    public function index(): void
    {
        $lessons = Curriculum::lessons();
        $levels = Curriculum::levels();
        $level = (string) ($_GET['level'] ?? 'all');
        if ($level !== 'all' && !isset($levels[$level])) $level = 'all';
        if ($level !== 'all') {
            $lessons = array_values(array_filter($lessons, static fn(array $lesson): bool => $lesson['level'] === $level));
        }

        $this->renderSoftware('learn/index', [
            'pageTitle' => 'مرکز آموزش چیدمان کارخانه',
            'title' => 'مرکز آموزش چیدمان کارخانه - IT4IE',
            'softwareName' => 'Factory Layout Analyzer',
            'lessons' => $lessons,
            'levels' => $levels,
            'level' => $level,
            'totalLessons' => count(Curriculum::lessons()),
        ], 'factory-layout-analyzer');
    }

    public function show(): void
    {
        $id = (string) ($_GET['lesson'] ?? '');
        $lessons = Curriculum::lessons();
        $ids = array_column($lessons, 'id');
        $position = array_search($id, $ids, true);
        if ($position === false) {
            http_response_code(404);
            $this->renderSoftware('learn/not-found', [
                'pageTitle' => 'درس پیدا نشد',
                'title' => 'درس پیدا نشد - چیدمان کارخانه',
                'softwareName' => 'Factory Layout Analyzer',
            ], 'factory-layout-analyzer');
            return;
        }

        $lesson = $lessons[$position];
        $this->renderSoftware('learn/show', [
            'pageTitle' => $lesson['title'],
            'title' => $lesson['title'] . ' - آموزش چیدمان کارخانه',
            'softwareName' => 'Factory Layout Analyzer',
            'lesson' => $lesson,
            'previous' => $position > 0 ? $lessons[$position - 1] : null,
            'next' => $position < count($lessons) - 1 ? $lessons[$position + 1] : null,
            'levelLabel' => Curriculum::levels()[$lesson['level']] ?? $lesson['level'],
        ], 'factory-layout-analyzer');
    }
}
