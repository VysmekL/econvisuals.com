<?php
/**
 * Database Configuration Example
 *
 * DŮLEŽITÉ: Zkopírujte tento soubor jako db.php a vyplňte své údaje!
 *
 * cp db.example.php db.php
 */

return [
    'host' => 'localhost',              // nebo md394.wedos.net
    'dbname' => 'your_database_name',   // název databáze
    'username' => 'your_username',      // uživatelské jméno
    'password' => 'your_password',      // heslo k databázi
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false, // Prevence SQL Injection
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]
];
