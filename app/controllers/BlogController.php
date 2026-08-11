<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Post;
use App\Models\Category;

class BlogController extends Controller
{
    /**
     * لیست همه مطالب
     */
    public function index()
    {
        $postModel = new Post();
        $categoryModel = new Category();

        // دریافت پارامترهای فیلتر
        $categorySlug = $_GET['category'] ?? null;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 9;
        $offset = ($page - 1) * $perPage;

        // محاسبه تعداد کل برای صفحه‌بندی
        $totalPosts = 0;
        $currentCategory = null;

        if ($categorySlug) {
            // فیلتر بر اساس دسته
            $currentCategory = $categoryModel->findBySlug($categorySlug);

            if ($currentCategory) {
                $posts = $postModel->query(
                    "SELECT p.*, c.name AS category_name, c.slug AS category_slug
                     FROM posts p
                     LEFT JOIN categories c ON p.category_id = c.id
                     WHERE p.status = 'published' AND c.slug = ?
                     ORDER BY p.created_at DESC
                     LIMIT {$perPage} OFFSET {$offset}",
                    [$categorySlug]
                );

                $countResult = $postModel->queryOne(
                    "SELECT COUNT(*) as cnt 
                     FROM posts p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     WHERE p.status = 'published' AND c.slug = ?",
                    [$categorySlug]
                );
                $totalPosts = $countResult['cnt'] ?? 0;
            } else {
                $posts = [];
            }
        } else {
            // همه مطالب
            $posts = $postModel->query(
                "SELECT p.*, c.name AS category_name, c.slug AS category_slug
                 FROM posts p
                 LEFT JOIN categories c ON p.category_id = c.id
                 WHERE p.status = 'published'
                 ORDER BY p.created_at DESC
                 LIMIT {$perPage} OFFSET {$offset}"
            );

            $countResult = $postModel->queryOne(
                "SELECT COUNT(*) as cnt FROM posts WHERE status = 'published'"
            );
            $totalPosts = $countResult['cnt'] ?? 0;
        }

        $totalPages = max(1, ceil($totalPosts / $perPage));
        $categories = $categoryModel->getAllActive();

        // محاسبه تعداد پست هر دسته برای سایدبار
        $categoryCounts = [];
        foreach ($categories as $cat) {
            try {
                $count = $postModel->queryOne(
                    "SELECT COUNT(*) as cnt FROM posts WHERE category_id = ? AND status = 'published'",
                    [$cat['id']]
                );
                $categoryCounts[$cat['id']] = $count['cnt'] ?? 0;
            } catch (\Exception $e) {
                $categoryCounts[$cat['id']] = 0;
            }
        }

        $this->render('blog/index', [
            'title' => $currentCategory 
                ? ($currentCategory['name'] . ' - IT4IE') 
                : 'همه مطالب - IT4IE',
            'posts' => $posts,
            'categories' => $categories,
            'currentCategory' => $currentCategory,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalPosts' => $totalPosts,
            'categoryCounts' => $categoryCounts
        ]);
    }
}