<?php
/**
 * Admin Editor
 *
 * Vytváření a editace příspěvků.
 */

require_once __DIR__ . '/../src/autoload.php';

use App\Auth;
use App\Post;
use App\Category;
use App\Tag;
use App\ImageHandler;
use App\CSRF;

// Zakázat indexování vyhledávači
header('X-Robots-Tag: noindex, nofollow, noarchive');

$auth = new Auth();
$auth->requireAuth();

$postModel = new Post();
$categoryModel = new Category();
$tagModel = new Tag();
$imageHandler = new ImageHandler();

// Editace existujícího příspěvku?
$editMode = isset($_GET['id']);
$postId = $editMode ? (int)$_GET['id'] : null;
$post = $editMode ? $postModel->findById($postId) : null;

$error = null;
$success = false;

// Zpracování formuláře
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validateRequest();

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $metaDescription = trim($_POST['meta_description'] ?? '');
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $tagsString = trim($_POST['tags'] ?? '');
    $isPublished = isset($_POST['is_published']) ? 1 : 0;

    // Validace
    if (empty($title)) {
        $error = 'Titulek je povinný.';
    } else {
        // Generovat slug
        $slug = Post::generateSlug($title);

        // Zpracovat upload obrázku (pokud je nový)
        $imageFilename = $post['image_filename'] ?? null;

        if (!empty($_FILES['image']['name'])) {
            $uploadResult = $imageHandler->processUpload($_FILES['image']);

            if ($uploadResult['success']) {
                // Smazat starý obrázek pokud existuje
                if ($editMode && $post['image_filename']) {
                    $imageHandler->deleteImage($post['image_filename']);
                }

                $imageFilename = $uploadResult['filename'];
            } else {
                $error = $uploadResult['error'];
            }
        }

        // Kontrola, zda máme obrázek
        if (!$editMode && !$imageFilename) {
            $error = 'Obrázek je povinný.';
        }

        if (!$error) {
            // Zpracovat tagy
            $tagIds = !empty($tagsString) ? $tagModel->processTagString($tagsString) : [];

            $data = [
                'title' => $title,
                'slug' => $slug,
                'image_filename' => $imageFilename,
                'content' => $content,
                'meta_description' => $metaDescription,
                'category_id' => $categoryId,
                'tags' => $tagIds,
                'is_published' => $isPublished
            ];

            if ($editMode) {
                // Aktualizovat
                if ($postModel->update($postId, $data)) {
                    header('Location: dashboard.php?saved=1');
                    exit;
                } else {
                    $error = 'Chyba při ukládání příspěvku.';
                }
            } else {
                // Vytvořit nový
                $newId = $postModel->create($data);
                if ($newId) {
                    header('Location: dashboard.php?saved=1');
                    exit;
                } else {
                    $error = 'Chyba při vytváření příspěvku.';
                }
            }
        }
    }
}

// Načíst kategorie
$categories = $categoryModel->getAll();

// Připravit tagy pro editaci
$currentTags = '';
if ($editMode && $post) {
    $tagNames = array_map(fn($t) => $t['name'], $post['tags']);
    $currentTags = implode(', ', $tagNames);
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= $editMode ? 'Upravit příspěvek' : 'Nový příspěvek' ?> - Administrace</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <style>
        .preview-container {
            margin-top: 1rem;
        }

        .preview-image {
            max-width: 300px;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            border-radius: 8px;
        }

        .file-upload {
            border: 2px dashed var(--pico-muted-border-color);
            padding: 2rem;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .file-upload:hover {
            border-color: var(--pico-primary);
            background: var(--pico-background-color);
        }

        .char-counter {
            font-size: 0.875rem;
            color: var(--pico-muted-color);
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
            <h1><?= $editMode ? 'Upravit příspěvek' : 'Nový příspěvek' ?></h1>
            <a href="dashboard.php" role="button" class="secondary">← Zpět na dashboard</a>
        </nav>

        <?php if ($error): ?>
            <div role="alert" style="color: var(--pico-del-color);">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <article>
            <form method="POST" enctype="multipart/form-data">
                <?= CSRF::getTokenField() ?>

                <!-- Titulek -->
                <label for="title">
                    Titulek <span style="color: red;">*</span>
                    <input type="text"
                           id="title"
                           name="title"
                           value="<?= htmlspecialchars($post['title'] ?? '') ?>"
                           required
                           placeholder="Zadejte titulek infografiky">
                </label>

                <!-- Obrázek -->
                <label for="image">
                    Infografika (čtvercový formát) <?= !$editMode ? '<span style="color: red;">*</span>' : '' ?>
                    <input type="file"
                           id="image"
                           name="image"
                           accept="image/jpeg,image/png,image/webp"
                           <?= !$editMode ? 'required' : '' ?>>
                    <small>Povolené formáty: JPG, PNG, WebP. Maximální velikost: 10MB.</small>
                </label>

                <?php if ($editMode && $post['image_filename']): ?>
                    <div class="preview-container">
                        <label>Aktuální obrázek:</label>
                        <img src="../uploads/infographics/<?= htmlspecialchars($post['image_filename']) ?>"
                             alt="<?= htmlspecialchars($post['title']) ?>"
                             class="preview-image">
                    </div>
                <?php endif; ?>

                <!-- Kategorie -->
                <label for="category_id">
                    Kategorie
                    <select id="category_id" name="category_id">
                        <option value="">Bez kategorie</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"
                                    <?= ($post['category_id'] ?? null) == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <!-- Štítky -->
                <label for="tags">
                    Štítky
                    <input type="text"
                           id="tags"
                           name="tags"
                           value="<?= htmlspecialchars($currentTags) ?>"
                           placeholder="zdraví, ekonomika, covid-19">
                    <small>Oddělené čárkou. Pokud tag neexistuje, bude automaticky vytvořen.</small>
                </label>

                <!-- Obsah/Zdroje -->
                <label for="content">
                    Obsah článku a zdroje
                    <textarea id="content"
                              name="content"
                              rows="10"
                              placeholder="Zde můžete napsat popis infografiky a citace zdrojů..."><?= htmlspecialchars($post['content'] ?? '') ?></textarea>
                    <small>Můžete použít HTML tagy pro formátování odkazů a seznamů.</small>
                </label>

                <!-- Meta Description (SEO) -->
                <label for="meta_description">
                    Meta popis (SEO)
                    <input type="text"
                           id="meta_description"
                           name="meta_description"
                           value="<?= htmlspecialchars($post['meta_description'] ?? '') ?>"
                           maxlength="160"
                           placeholder="Krátký popis pro vyhledávače">
                    <small class="char-counter">
                        <span id="char-count">0</span>/160 znaků
                    </small>
                </label>

                <!-- Publikovat -->
                <label for="is_published">
                    <input type="checkbox"
                           id="is_published"
                           name="is_published"
                           <?= ($post['is_published'] ?? 1) ? 'checked' : '' ?>>
                    Publikovat okamžitě
                </label>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit"><?= $editMode ? 'Uložit změny' : 'Vytvořit příspěvek' ?></button>
                    <a href="dashboard.php" role="button" class="secondary">Zrušit</a>
                </div>
            </form>
        </article>
    </main>

    <script>
        // Počítadlo znaků pro meta description
        const metaInput = document.getElementById('meta_description');
        const charCount = document.getElementById('char-count');

        function updateCharCount() {
            charCount.textContent = metaInput.value.length;
        }

        metaInput.addEventListener('input', updateCharCount);
        updateCharCount();

        // Preview obrázku při výběru
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = document.querySelector('.preview-container');
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.className = 'preview-container';
                        document.querySelector('input[type="file"]').parentNode.appendChild(preview);
                    }
                    preview.innerHTML = '<label>Náhled:</label><img src="' + e.target.result + '" class="preview-image" alt="Náhled">';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
