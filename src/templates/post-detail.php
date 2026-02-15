<?php
/**
 * Post Detail Template
 *
 * Detail of infographic with content and sources
 */

$pageTitle = htmlspecialchars($post['title']);
$pageDescription = htmlspecialchars($post['meta_description'] ?? mb_substr(strip_tags($post['content'] ?? ''), 0, 160));
$ogImage = 'https://' . $_SERVER['HTTP_HOST'] . '/uploads/infographics/' . $post['image_filename'];

include __DIR__ . '/header.php';
?>

<main class="container">
    <article class="post-detail">
        <header class="post-header">
            <h1><?= htmlspecialchars($post['title']) ?></h1>

            <div class="post-meta">
                <?php if ($post['category_name']): ?>
                    <a href="/category/<?= htmlspecialchars($post['category_slug']) ?>" class="category-badge">
                        <?= htmlspecialchars($post['category_name']) ?>
                    </a>
                <?php endif; ?>

                <time datetime="<?= $post['created_at'] ?>">
                    <?= date('F j, Y', strtotime($post['created_at'])) ?>
                </time>

                <?php if ($post['views'] > 0): ?>
                    <span class="views"><?= number_format($post['views']) ?> views</span>
                <?php endif; ?>
            </div>

            <?php if (!empty($post['tags'])): ?>
                <div class="post-tags">
                    <?php foreach ($post['tags'] as $tag): ?>
                        <a href="/tag/<?= htmlspecialchars($tag['slug']) ?>" class="tag">
                            #<?= htmlspecialchars($tag['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </header>

        <figure class="infographic-container">
            <picture>
                <source srcset="<?= App\ImageHandler::getSrcset($post['image_filename']) ?>"
                        sizes="(max-width: 800px) 100vw, 800px">
                <img src="/uploads/infographics/<?= htmlspecialchars($post['image_filename']) ?>"
                     alt="<?= htmlspecialchars($post['title']) ?>"
                     width="1200"
                     height="1200">
            </picture>
        </figure>

        <?php if ($post['content']): ?>
            <section class="post-content">
                <details open>
                    <summary>Description and Sources</summary>
                    <div class="content-text">
                        <?= $post['content'] ?>
                    </div>
                </details>
            </section>
        <?php endif; ?>

        <footer class="post-footer">
            <a href="/" class="back-link">← Back to overview</a>
        </footer>
    </article>
</main>

<?php include __DIR__ . '/footer.php'; ?>
