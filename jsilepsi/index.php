<?php
/**
 * Admin Login
 *
 * Skrytá administrační sekce s honeypot ochranou a rate limiting.
 */

require_once __DIR__ . '/../src/autoload.php';

use App\Auth;
use App\CSRF;

// Zakázat indexování vyhledávači
header('X-Robots-Tag: noindex, nofollow, noarchive');

$auth = new Auth();

// Pokud už je přihlášen, redirect na dashboard
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = null;

// Zpracování přihlášení
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF ochrana
    CSRF::validateRequest();

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // HONEYPOT ochrana - pokud je vyplněno "email2", je to bot
    $honeypot = $_POST['email2'] ?? '';

    if (!empty($honeypot)) {
        // Bot detekován - potichu odmítnout bez chybové hlášky
        sleep(2); // Simulovat zpracování
        error_log('Honeypot triggered: ' . $_SERVER['REMOTE_ADDR']);
        $error = 'Invalid credentials.';
    } else {
        // Pokusit se přihlásit
        if ($auth->login($username, $password)) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Nesprávné přihlašovací údaje.';
        }
    }
}

// Vygenerovat CSRF token
$csrfToken = CSRF::generateToken();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Přihlášení - Administrace</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <style>
        /* Honeypot - skrýt pole mimo obrazovku */
        .hp-field {
            position: absolute;
            left: -9999px;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .login-container {
            max-width: 400px;
            width: 100%;
        }
    </style>
</head>
<body>
    <main class="login-container">
        <article>
            <header>
                <h1>Administrace</h1>
            </header>

            <?php if ($error): ?>
                <div role="alert" style="color: var(--pico-del-color);">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?= CSRF::getTokenField() ?>

                <label for="username">
                    Uživatelské jméno
                    <input type="text" id="username" name="username" required autocomplete="username">
                </label>

                <label for="password">
                    Heslo
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </label>

                <!-- HONEYPOT FIELD - MUSÍ být skrytý CSS, NE display:none -->
                <label class="hp-field" for="email2" aria-hidden="true">
                    Email (nechte prázdné)
                    <input type="text" id="email2" name="email2" tabindex="-1" autocomplete="off">
                </label>

                <button type="submit">Přihlásit se</button>
            </form>
        </article>
    </main>
</body>
</html>
