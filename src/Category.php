<?php
/**
 * Category Model
 *
 * Správa kategorií
 */

namespace App;

class Category
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Vytvoří novou kategorii
     */
    public function create(string $name, string $slug): int|false
    {
        try {
            $query = "INSERT INTO categories (name, slug) VALUES (?, ?)";
            $this->db->query($query, [$name, $slug]);

            return (int)$this->db->lastInsertId();
        } catch (\Exception $e) {
            error_log('Category creation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Aktualizuje kategorii
     */
    public function update(int $id, string $name, string $slug): bool
    {
        try {
            $query = "UPDATE categories SET name = ?, slug = ? WHERE id = ?";
            $this->db->query($query, [$name, $slug, $id]);

            return true;
        } catch (\Exception $e) {
            error_log('Category update failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Smaže kategorii
     */
    public function delete(int $id): bool
    {
        try {
            $query = "DELETE FROM categories WHERE id = ?";
            $this->db->query($query, [$id]);

            return true;
        } catch (\Exception $e) {
            error_log('Category deletion failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Najde kategorii podle ID
     */
    public function findById(int $id): ?array
    {
        $query = "SELECT * FROM categories WHERE id = ? LIMIT 1";
        $result = $this->db->query($query, [$id])->fetch();

        return $result ?: null;
    }

    /**
     * Najde kategorii podle slugu
     */
    public function findBySlug(string $slug): ?array
    {
        $query = "SELECT * FROM categories WHERE slug = ? LIMIT 1";
        $result = $this->db->query($query, [$slug])->fetch();

        return $result ?: null;
    }

    /**
     * Vrátí všechny kategorie
     */
    public function getAll(): array
    {
        $query = "SELECT c.*, COUNT(p.id) as post_count
                  FROM categories c
                  LEFT JOIN posts p ON c.id = p.category_id AND p.is_published = 1
                  GROUP BY c.id
                  ORDER BY c.name ASC";

        return $this->db->query($query)->fetchAll();
    }

    /**
     * Ověří, zda slug již existuje
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $query = "SELECT COUNT(*) as count FROM categories WHERE slug = ? AND id != ?";
            $result = $this->db->query($query, [$slug, $excludeId])->fetch();
        } else {
            $query = "SELECT COUNT(*) as count FROM categories WHERE slug = ?";
            $result = $this->db->query($query, [$slug])->fetch();
        }

        return $result['count'] > 0;
    }
}
