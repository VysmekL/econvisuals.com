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
        echo '404 - Článek nenalezen';
        return;
    }

    require __DIR__ . '/src/templates/post-detail.php';
});

// Kategorie
$router->get('/kategorie/:slug', function($slug) {
    $categoryModel = new Category();
    $category = $categoryModel->findBySlug($slug);

    if (!$category) {
        http_response_code(404);
        echo '404 - Kategorie nenalezena';
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
        echo '404 - Tag nenalezen';
        return;
    }

    $postModel = new Post();
    $posts = $postModel->getByTag($tag['id']);

    require __DIR__ . '/src/templates/tag.php';
});

// ============================================================================
// SPUSTIT ROUTER
// ============================================================================
$router->run();
