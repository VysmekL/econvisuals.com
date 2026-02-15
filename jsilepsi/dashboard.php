<?php
/**
 * Admin Dashboard
 *
 * Seznam všech příspěvků s možností editace a smazání.
 */

require_once __DIR__ . '/../src/autoload.php';

use App\Auth;
use App\Post;
use App\ImageHandler;
use App\CSRF;

// Zakázat indexování vyhledávači
header('X-Robots-Tag: noindex, nofollow, noarchive');

$auth = new Auth();
$auth->requireAuth();

$postModel = new Post();

// Zpracování smazání příspěvku
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    CSRF::validateRequest();

    $postId = (int)$_POST['post_id'];
    $post = $postModel->findById($postId);

    if ($post) {
        // Smazat obrázek
        $imageHandler = new ImageHandler();
        $imageHandler->deleteImage($post['image_filename']);

        // Smazat příspěvek
        $postModel->delete($postId);

        header('Location: dashboard.php?deleted=1');
        exit;
    }
}

// Načíst všechny příspěvky
$posts = $postModel->getAll(100, 0, false);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Dashboard - Administrace</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <style>
        .post-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }

        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .actions a,
        .actions button {
            margin: 0;
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-published {
            background: var(--pico-ins-color);
            color: white;
        }

        .status-draft {
            background: var(--pico-muted-color);
            color: white;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <main class="container">
        <nav>
            <div>
                <h1>Dashboard</h1>
                <p>Vítejte, <?= htmlspecialchars($_SESSION['username']) ?></p>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="editor.php" role="button">+ Nový příspěvek</a>
                <a href="logout.php" role="button" class="secondary">Odhlásit se</a>
            </div>
        </nav>

        <?php if (isset($_GET['deleted'])): ?>
            <div role="alert">Příspěvek byl úspěšně smazán.</div>
        <?php endif; ?>

        <?php if (isset($_GET['saved'])): ?>
            <div role="alert">Příspěvek byl úspěšně uložen.</div>
        <?php endif; ?>

        <article>
            <header>
                <h2>Všechny příspěvky (<?= count($posts) ?>)</h2>
            </header>

            <?php if (empty($posts)): ?>
                <p>Zatím žádné příspěvky. <a href="editor.php">Vytvořte první!</a></p>
            <?php else: ?>
                <figure>
                    <table role="grid">
                        <thead>
                            <tr>
                                <th>Náhled</th>
                                <th>Titulek</th>
                                <th>Kategorie</th>
                                <th>Status</th>
                                <th>Zobrazení</th>
                                <th>Vytvořeno</th>
                                <th>Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($posts as $post): ?>
                                <tr>
                                    <td>
                                        <img src="../uploads/infographics/<?= htmlspecialchars($post['image_filename']) ?>"
                                             alt="<?= htmlspecialchars($post['title']) ?>"
                                             class="post-image">
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($post['title']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($post['category_name'] ?? 'Bez kategorie') ?></td>
                                    <td>
                                        <?php if ($post['is_published']): ?>
                                            <span class="status-badge status-published">Publikováno</span>
                                        <?php else: ?>
                                            <span class="status-badge status-draft">Koncept</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($post['views']) ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="editor.php?id=<?= $post['id'] ?>" role="button" class="secondary">Upravit</a>

                                            <form method="POST" style="margin: 0;" onsubmit="return confirm('Opravdu smazat tento příspěvek?')">
                                                <?= CSRF::getTokenField() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                                <button type="submit" class="contrast">Smazat</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </figure>
            <?php endif; ?>
        </article>
    </main>
</body>
</html>
