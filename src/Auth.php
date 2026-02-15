<?php
/**
 * Authentication System
 *
 * Zajišťuje bezpečnou autentizaci s Argon2id hashing,
 * session management a rate limiting.
 */

namespace App;

class Auth
{
    private Database $db;
    private RateLimiter $rateLimiter;
    private const SESSION_LIFETIME = 3600; // 60 minut

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->rateLimiter = new RateLimiter();
        $this->initSession();
    }

    /**
     * Inicializuje bezpečnou session
     */
    private function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Bezpečnostní nastavení session
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_samesite', 'Strict');

            // Na produkci s HTTPS:
            // ini_set('session.cookie_secure', '1');

            session_start();

            // Automatické odhlášení po timeout
            if (isset($_SESSION['LAST_ACTIVITY'])) {
                if (time() - $_SESSION['LAST_ACTIVITY'] > self::SESSION_LIFETIME) {
                    $this->logout();
                }
            }

            $_SESSION['LAST_ACTIVITY'] = time();
        }
    }

    /**
     * Přihlášení uživatele
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function login(string $username, string $password): bool
    {
        $ipAddress = $this->getClientIp();

        // Kontrola rate limiting
        $rateCheck = $this->rateLimiter->checkRateLimit($ipAddress);

        if (!$rateCheck['allowed']) {
            return false;
        }

        // Aplikovat zpoždění pokud je potřeba
        if ($rateCheck['delay'] > 0) {
            RateLimiter::applyDelay($rateCheck['delay']);
        }

        // Načíst uživatele z databáze
        $query = "SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1";
        $result = $this->db->query($query, [$username])->fetch();

        if (!$result) {
            // Uživatel neexistuje - zaznamenat neúspěšný pokus
            $this->rateLimiter->recordFailedAttempt($username, $ipAddress);
            return false;
        }

        // Ověření hesla
        if (!password_verify($password, $result['password_hash'])) {
            // Špatné heslo - zaznamenat neúspěšný pokus
            $this->rateLimiter->recordFailedAttempt($username, $ipAddress);
            return false;
        }

        // Úspěšné přihlášení
        $this->createSession($result);
        $this->updateLastLogin($result['id']);
        $this->rateLimiter->resetAttempts($ipAddress);

        return true;
    }

    /**
     * Vytvoří session pro přihlášeného uživatele
     */
    private function createSession(array $user): void
    {
        // Regenerovat session ID (prevence session fixation)
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $_SESSION['ip_address'] = $this->getClientIp();
        $_SESSION['LAST_ACTIVITY'] = time();
    }

    /**
     * Aktualizuje čas posledního přihlášení
     */
    private function updateLastLogin(int $userId): void
    {
        $query = "UPDATE users SET last_login = NOW() WHERE id = ?";
        $this->db->query($query, [$userId]);
    }

    /**
     * Odhlášení uživatele
     */
    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * Kontrola, zda je uživatel přihlášen
     */
    public function isLoggedIn(): bool
    {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        // Ověření User-Agent (prevence session hijacking)
        $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $currentUserAgent) {
            $this->logout();
            return false;
        }

        return true;
    }

    /**
     * Vrátí ID přihlášeného uživatele
     */
    public function getUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Vyžaduje přihlášení - pokud není, redirect na login
     */
    public function requireAuth(): void
    {
        if (!$this->isLoggedIn()) {
            Router::redirect('/jsilepsi');
        }
    }

    /**
     * Získá IP adresu klienta
     */
    private function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Pokud je více IP (proxy chain), vzít první
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Vytvoří hash hesla (pro seed script)
     *
     * @param string $password
     * @return string
     */
    public static function hashPassword(string $password): string
    {
        // Použití Argon2id (nejbezpečnější algoritmus)
        if (defined('PASSWORD_ARGON2ID')) {
            return password_hash($password, PASSWORD_ARGON2ID);
        }

        // Fallback na Bcrypt
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}
