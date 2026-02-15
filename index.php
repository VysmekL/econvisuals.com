<?php
/**
 * Front Controller - Public Website
 */

// Autoloader
require_once __DIR__ . '/src/autoload.php';

use App\Router;
use App\Post;
use App\Category;
use App\Tag;
use App\ImageHandler;

// Inicializovat upload složku
ImageHandler::ensureUploadDir();

// Vytvořit router
$router = new Router();

// ============================================================================
// FRONTEND ROUTES
// ============================================================================

// Homepage - seznam infografik
$router->get('/', function() {
    $postModel = new Post();
    $posts = $postModel->getAll(12, 0, true);
    $totalPosts = $postModel->count(true);

    require __DIR__ . '/src/templates/homepage.php';
});

// Detail článku
$router->get('/post/:slug', function($slug) {
    $postModel = new Post();
    $post = $postModel->findBySlug($slug);

    if (!$post) {
        http_response_code(404);
        echo '404 - Article Not Found';
        return;
    }

    require __DIR__ . '/src/templates/post-detail.php';
});

// Category
$router->get('/category/:slug', function($slug) {
    $categoryModel = new Category();
    $category = $categoryModel->findBySlug($slug);

    if (!$category) {
        http_response_code(404);
        echo '404 - Category Not Found';
        return;
    }

    $postModel = new Post();
    $posts = $postModel->getByCategory($category['id']);

    require __DIR__ . '/src/templates/category.php';
});

// Tag
$router->get('/tag/:slug', function($slug) {
    $tagModel = new Tag();
    $tag = $tagModel->findBySlug($slug);

    if (!$tag) {
        http_response_code(404);
        echo '404 - Tag Not Found';
        return;
    }

    $postModel = new Post();
    $posts = $postModel->getByTag($tag['id']);

    require __DIR__ . '/src/templates/tag.php';
});

// Search
$router->get('/search', function() {
    $query = trim($_GET['q'] ?? '');
    $postModel = new Post();

    if (empty($query)) {
        $posts = [];
    } else {
        // Search in title and content
        $db = Database::getInstance();
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug
                FROM posts p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE (p.title LIKE ? OR p.content LIKE ?)
                AND p.is_published = 1
                ORDER BY p.created_at DESC
                LIMIT 50";

        $searchTerm = '%' . $query . '%';
        $posts = $db->query($sql, [$searchTerm, $searchTerm])->fetchAll();

        // Load tags for each post
        foreach ($posts as &$post) {
            $tagQuery = "SELECT t.id, t.name, t.slug
                         FROM tags t
                         INNER JOIN post_tags pt ON t.id = pt.tag_id
                         WHERE pt.post_id = ?";
            $post['tags'] = $db->query($tagQuery, [$post['id']])->fetchAll();
        }
    }

    require __DIR__ . '/src/templates/search.php';
});

// ============================================================================
// SPUSTIT ROUTER
// ============================================================================
$router->run();
