<?php
/**
 * JEDNODUCHÁ INSTALACE - Vytvoří prvního administrátora
 *
 * DŮLEŽITÉ: Po vytvoření admina SMAŽ tento soubor!
 */

require_once __DIR__ . '/src/autoload.php';

use App\Database;
use App\Auth;

$success = false;
$error = null;
$passwordHash = null;

// Zpracování formuláře
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password2 = trim($_POST['password2'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Vyplňte všechna pole.';
    } elseif ($password !== $password2) {
        $error = 'Hesla se neshodují.';
    } elseif (strlen($password) < 8) {
        $error = 'Heslo musí mít alespoň 8 znaků.';
    } else {
        try {
            $db = Database::getInstance();

            // Zkontrolovat, zda už nějaký admin existuje
            $existing = $db->query("SELECT COUNT(*) as count FROM users")->fetch();

            if ($existing['count'] > 0) {
                $error = 'Administrátor již existuje! Smažte tento soubor.';
            } else {
                // Vytvořit hash hesla
                $passwordHash = Auth::hashPassword($password);

                // Vložit do databáze
                $db->query("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)", [
                    $username,
                    $passwordHash,
                    'admin'
                ]);

                $success = true;
            }
        } catch (Exception $e) {
            $error = 'Chyba: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalace - Infographic CMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            max-width: 500px;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <main class="container">
        <article>
            <header>
                <h1>🚀 Instalace CMS</h1>
                <p>Vytvoření prvního administrátora</p>
            </header>

            <?php if ($success): ?>
                <div class="success">
                    <h3>✅ Úspěch!</h3>
                    <p><strong>Administrátor byl vytvořen.</strong></p>
                    <p>Uživatelské jméno: <code><?= htmlspecialchars($_POST['username']) ?></code></p>
                </div>

                <div class="warning">
                    <h4>⚠️ DŮLEŽITÉ BEZPEČNOSTNÍ KROKY:</h4>
                    <ol>
                        <li><strong>IHNED SMAŽ soubor <code>install.php</code></strong> z FTP!</li>
                        <li>Přejmenuj složku <code>/jsilepsi</code> na něco unikátního (např. <code>/tajnyadmin123</code>)</li>
                        <li>Přihlas se do administrace</li>
                    </ol>
                </div>

                <a href="/jsilepsi" role="button">→ Přejít do administrace</a>

            <?php else: ?>

                <?php if ($error): ?>
                    <div class="error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <label for="username">
                        Uživatelské jméno
                        <input type="text"
                               id="username"
                               name="username"
                               required
                               autofocus
                               placeholder="admin">
                    </label>

                    <label for="password">
                        Heslo (min. 8 znaků)
                        <input type="password"
                               id="password"
                               name="password"
                               required
                               minlength="8"
                               placeholder="••••••••">
                    </label>

                    <label for="password2">
                        Heslo znovu
                        <input type="password"
                               id="password2"
                               name="password2"
                               required
                               minlength="8"
                               placeholder="••••••••">
                    </label>

                    <button type="submit">Vytvořit administrátora</button>
                </form>

                <footer>
                    <small>
                        <strong>Poznámka:</strong> Tento soubor vytvoří pouze prvního administrátora.
                        Po instalaci ho ihned smažte!
                    </small>
                </footer>

            <?php endif; ?>
        </article>
    </main>
</body>
</html>
