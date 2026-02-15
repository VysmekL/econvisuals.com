<?php
/**
 * Search Results Template
 */

$searchQuery = htmlspecialchars($_GET['q'] ?? '');
$pageTitle = !empty($searchQuery) ? "Search: $searchQuery | EconVisuals" : 'Search | EconVisuals';
$pageDescription = !empty($searchQuery) ? "Search results for '$searchQuery' - find infographics on EconVisuals" : 'Search infographics on EconVisuals';

include __DIR__ . '/header.php';
?>

<main class="container">
    <header class="page-header">
        <h1>Search Results</h1>
        <?php if (!empty($searchQuery)): ?>
            <p>Showing results for: <strong>"<?= $searchQuery ?>"</strong></p>
        <?php endif; ?>
        <a href="/" class="back-link">← Back to home</a>
    </header>

    <?php if (empty($searchQuery)): ?>
        <div class="empty-state">
            <p>Please enter a search term.</p>
        </div>
    <?php elseif (empty($posts)): ?>
        <div class="empty-state">
            <p>No results found for "<?= $searchQuery ?>".</p>
            <p>Try different keywords or browse our <a href="/">latest infographics</a>.</p>
        </div>
    <?php else: ?>
        <p class="search-count"><?= count($posts) ?> result<?= count($posts) !== 1 ? 's' : '' ?> found</p>

        <div class="infographic-grid">
            <?php foreach ($posts as $post): ?>
                <article class="infographic-card">
                    <a href="/post/<?= htmlspecialchars($post['slug']) ?>">
                        <figure class="infographic-image">
                            <picture>
                                <source srcset="<?= App\ImageHandler::getSrcset($post['image_filename']) ?>"
                                        sizes="(max-width: 600px) 100vw, (max-width: 1200px) 50vw, 33vw">
                                <img src="/uploads/infographics/<?= htmlspecialchars($post['image_filename']) ?>"
                                     alt="<?= htmlspecialchars($post['title']) ?>"
                                     loading="lazy"
                                     width="600"
                                     height="600">
                            </picture>
                        </figure>

                        <div class="card-content">
                            <h2><?= htmlspecialchars($post['title']) ?></h2>

                            <?php if ($post['category_name']): ?>
                                <span class="category-badge">
                                    <?= htmlspecialchars($post['category_name']) ?>
                                </span>
                            <?php endif; ?>

                            <div class="card-meta">
                                <time datetime="<?= $post['created_at'] ?>">
                                    <?= date('M d, Y', strtotime($post['created_at'])) ?>
                                </time>
                                <?php if ($post['views'] > 0): ?>
                                    <span class="views"><?= number_format($post['views']) ?> views</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/footer.php'; ?>
