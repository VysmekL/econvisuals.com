<?php
/**
 * Tag Model
 *
 * Správa štítků (tagů)
 */

namespace App;

class Tag
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Vytvoří nový tag
     */
    public function create(string $name, string $slug): int|false
    {
        try {
            $query = "INSERT INTO tags (name, slug) VALUES (?, ?)";
            $this->db->query($query, [$name, $slug]);

            return (int)$this->db->lastInsertId();
        } catch (\Exception $e) {
            error_log('Tag creation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Aktualizuje tag
     */
    public function update(int $id, string $name, string $slug): bool
    {
        try {
            $query = "UPDATE tags SET name = ?, slug = ? WHERE id = ?";
            $this->db->query($query, [$name, $slug, $id]);

            return true;
        } catch (\Exception $e) {
            error_log('Tag update failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Smaže tag
     */
    public function delete(int $id): bool
    {
        try {
            $query = "DELETE FROM tags WHERE id = ?";
            $this->db->query($query, [$id]);

            return true;
        } catch (\Exception $e) {
            error_log('Tag deletion failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Najde tag podle ID
     */
    public function findById(int $id): ?array
    {
        $query = "SELECT * FROM tags WHERE id = ? LIMIT 1";
        $result = $this->db->query($query, [$id])->fetch();

        return $result ?: null;
    }

    /**
     * Najde tag podle slugu
     */
    public function findBySlug(string $slug): ?array
    {
        $query = "SELECT * FROM tags WHERE slug = ? LIMIT 1";
        $result = $this->db->query($query, [$slug])->fetch();

        return $result ?: null;
    }

    /**
     * Najde nebo vytvoří tag podle názvu
     *
     * @param string $name
     * @return int Tag ID
     */
    public function findOrCreate(string $name): int
    {
        $slug = Post::generateSlug($name);

        // Zkusit najít existující
        $existing = $this->findBySlug($slug);

        if ($existing) {
            return (int)$existing['id'];
        }

        // Vytvořit nový
        return $this->create($name, $slug);
    }

    /**
     * Zpracuje tagy oddělené čárkou a vrátí jejich ID
     *
     * @param string $tagsString "tag1, tag2, tag3"
     * @return array [tag_id1, tag_id2, ...]
     */
    public function processTagString(string $tagsString): array
    {
        $tagNames = array_map('trim', explode(',', $tagsString));
        $tagNames = array_filter($tagNames); // Odstranit prázdné
        $tagIds = [];

        foreach ($tagNames as $tagName) {
            if (!empty($tagName)) {
                $tagIds[] = $this->findOrCreate($tagName);
            }
        }

        return $tagIds;
    }

    /**
     * Vrátí všechny tagy
     */
    public function getAll(): array
    {
        $query = "SELECT t.*, COUNT(pt.post_id) as post_count
                  FROM tags t
                  LEFT JOIN post_tags pt ON t.id = pt.tag_id
                  GROUP BY t.id
                  ORDER BY t.name ASC";

        return $this->db->query($query)->fetchAll();
    }

    /**
     * Vrátí nejpoužívanější tagy
     */
    public function getPopular(int $limit = 10): array
    {
        $query = "SELECT t.*, COUNT(pt.post_id) as post_count
                  FROM tags t
                  INNER JOIN post_tags pt ON t.id = pt.tag_id
                  GROUP BY t.id
                  HAVING post_count > 0
                  ORDER BY post_count DESC, t.name ASC
                  LIMIT ?";

        return $this->db->query($query, [$limit])->fetchAll();
    }
}
