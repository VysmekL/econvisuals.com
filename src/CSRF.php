<?php
/**
 * CSRF Protection
 *
 * Ochrana proti Cross-Site Request Forgery útokům.
 * Každý formulář musí obsahovat validní token.
 */

namespace App;

class CSRF
{
    private const TOKEN_NAME = 'csrf_token';
    private const TOKEN_LENGTH = 32;

    /**
     * Vygeneruje nový CSRF token a uloží do session
     */
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::TOKEN_NAME] = $token;

        return $token;
    }

    /**
     * Vrátí aktuální CSRF token ze session
     */
    public static function getToken(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION[self::TOKEN_NAME] ?? null;
    }

    /**
     * Ověří, zda poskytnutý token souhlasí s tokenem v session
     *
     * @param string|null $token Token k ověření
     * @return bool
     */
    public static function validateToken(?string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sessionToken = self::getToken();

        if ($sessionToken === null || $token === null) {
            return false;
        }

        // Časově konstantní porovnání (prevence timing attacks)
        return hash_equals($sessionToken, $token);
    }

    /**
     * Vygeneruje HTML hidden input s CSRF tokenem
     */
    public static function getTokenField(): string
    {
        $token = self::getToken() ?? self::generateToken();
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Ověří CSRF token z POST requestu, pokud neplatný -> ukončí skript
     */
    public static function validateRequest(): void
    {
        $token = $_POST[self::TOKEN_NAME] ?? null;

        if (!self::validateToken($token)) {
            http_response_code(403);
            die('Invalid CSRF token. Request rejected.');
        }
    }
}
