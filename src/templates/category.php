<?php
/**
 * Category Template
 */

$pageTitle = htmlspecialchars($category['name']) . ' - Kategorie';
$pageDescription = 'Infografiky v kategorii ' . htmlspecialchars($category['name']);

include __DIR__ . '/header.php';
?>

<main class="container">
    <header class="page-header">
        <h1><?= htmlspecialchars($category['name']) ?></h1>
        <a href="/" class="back-link">← Zpět na přehled</a>
    </header>

    <?php if (empty($posts)): ?>
        <div class="empty-state">
            <p>V této kategorii zatím nejsou žádné infografiky.</p>
        </div>
    <?php else: ?>
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

                            <div class="card-meta">
                                <time datetime="<?= $post['created_at'] ?>">
                                    <?= date('d.m.Y', strtotime($post['created_at'])) ?>
                                </time>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/footer.php'; ?>
