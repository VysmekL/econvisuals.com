<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $pageDescription ?? 'Infografiky založené na datech' ?>">

    <title><?= $pageTitle ?? 'Infografiky' ?></title>

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?= isset($post) ? 'article' : 'website' ?>">
    <meta property="og:url" content="https://<?= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>">
    <meta property="og:title" content="<?= $pageTitle ?? 'Infografiky' ?>">
    <meta property="og:description" content="<?= $pageDescription ?? 'Infografiky založené na datech' ?>">
    <?php if (isset($ogImage)): ?>
        <meta property="og:image" content="<?= $ogImage ?>">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="1200">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $pageTitle ?? 'Infografiky' ?>">
    <meta name="twitter:description" content="<?= $pageDescription ?? 'Infografiky založené na datech' ?>">
    <?php if (isset($ogImage)): ?>
        <meta name="twitter:image" content="<?= $ogImage ?>">
    <?php endif; ?>

    <!-- Canonical URL -->
    <link rel="canonical" href="https://<?= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>">

    <!-- Favicon -->
    <link rel="icon" href="/assets/img/favicon.ico">

    <!-- Main CSS -->
    <link rel="stylesheet" href="/assets/css/main.css">

    <!-- Preload kritické assety -->
    <link rel="preload" href="/assets/css/main.css" as="style">
</head>
<body>
    <nav class="main-nav">
        <div class="container">
            <a href="/" class="logo">Infografiky</a>

            <ul class="nav-menu">
                <li><a href="/">Přehled</a></li>
                <?php
                // Načíst kategorie pro menu
                $categoryModel = new App\Category();
                $navCategories = $categoryModel->getAll();
                foreach (array_slice($navCategories, 0, 5) as $cat):
                ?>
                    <li><a href="/kategorie/<?= htmlspecialchars($cat['slug']) ?>">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </nav>
