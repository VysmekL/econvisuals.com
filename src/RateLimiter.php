<?php
/**
 * Rate Limiter
 *
 * Ochrana proti brute-force útokům.
 * Implementuje exponenciální zpomalování a blokování po překročení limitu.
 */

namespace App;

class RateLimiter
{
    private Database $db;
    private const MAX_ATTEMPTS = 5;
    private const TIME_WINDOW = 900; // 15 minut v sekundách
    private const LOCKOUT_THRESHOLD = 10;
    private const HARD_LIMIT = 20;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Zaznamená neúspěšný pokus o přihlášení
     *
     * @param string|null $username
     * @param string $ipAddress
     */
    public function recordFailedAttempt(?string $username, string $ipAddress): void
    {
        $ipInt = ip2long($ipAddress);

        if ($ipInt === false) {
            return;
        }

        $query = "INSERT INTO failed_logins (username, ip_address, attempted_at)
                  VALUES (?, ?, NOW())";

        $this->db->query($query, [$username, $ipInt]);
    }

    /**
     * Zkontroluje, zda IP adresa není zablokována
     *
     * @param string $ipAddress
     * @return array ['allowed' => bool, 'attempts' => int, 'delay' => int]
     */
    public function checkRateLimit(string $ipAddress): array
    {
        $ipInt = ip2long($ipAddress);

        if ($ipInt === false) {
            return ['allowed' => true, 'attempts' => 0, 'delay' => 0];
        }

        // Spočítat pokusy za posledních X minut
        $query = "SELECT COUNT(*) as attempt_count
                  FROM failed_logins
                  WHERE ip_address = ?
                  AND attempted_at > DATE_SUB(NOW(), INTERVAL ? SECOND)";

        $result = $this->db->query($query, [$ipInt, self::TIME_WINDOW])->fetch();
        $attempts = (int)$result['attempt_count'];

        // Hard limit - úplná blokace
        if ($attempts >= self::HARD_LIMIT) {
            return [
                'allowed' => false,
                'attempts' => $attempts,
                'delay' => 0,
                'message' => 'Too many failed attempts. Account locked for 15 minutes.'
            ];
        }

        // Exponenciální zpomalování
        $delay = 0;
        if ($attempts >= self::LOCKOUT_THRESHOLD) {
            $delay = pow(2, $attempts - self::LOCKOUT_THRESHOLD) * 100; // milisekundy
        }

        return [
            'allowed' => true,
            'attempts' => $attempts,
            'delay' => $delay
        ];
    }

    /**
     * Vyčistí staré záznamy (starší než TIME_WINDOW)
     * Mělo by se volat pravidelně (např. cron job)
     */
    public function cleanupOldRecords(): void
    {
        $query = "DELETE FROM failed_logins
                  WHERE attempted_at < DATE_SUB(NOW(), INTERVAL ? SECOND)";

        $this->db->query($query, [self::TIME_WINDOW]);
    }

    /**
     * Resetuje počítadlo pokusů pro danou IP (po úspěšném přihlášení)
     *
     * @param string $ipAddress
     */
    public function resetAttempts(string $ipAddress): void
    {
        $ipInt = ip2long($ipAddress);

        if ($ipInt === false) {
            return;
        }

        $query = "DELETE FROM failed_logins WHERE ip_address = ?";
        $this->db->query($query, [$ipInt]);
    }

    /**
     * Aplikuje zpomalení (delay) pokud je potřeba
     *
     * @param int $delayMs Zpoždění v milisekundách
     */
    public static function applyDelay(int $delayMs): void
    {
        if ($delayMs > 0) {
            usleep($delayMs * 1000); // usleep používá mikrosekundy
        }
    }
}
