<?php
/**
 * Post Model
 *
 * CRUD operace pro příspěvky (infografiky)
 */

namespace App;

class Post
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Vytvoří nový příspěvek
     *
     * @param array $data ['title', 'slug', 'image_filename', 'content', 'meta_description', 'category_id', 'tags', 'is_published']
     * @return int|false ID nového příspěvku nebo false
     */
    public function create(array $data)
    {
        try {
            $this->db->beginTransaction();

            // Vložit příspěvek
            $query = "INSERT INTO posts (title, slug, image_filename, content, meta_description, category_id, is_published)
                      VALUES (?, ?, ?, ?, ?, ?, ?)";

            $this->db->query($query, [
                $data['title'],
                $data['slug'],
                $data['image_filename'],
                $data['content'] ?? null,
                $data['meta_description'] ?? null,
                $data['category_id'] ?? null,
                $data['is_published'] ?? 1
            ]);

            $postId = (int)$this->db->lastInsertId();

            // Přidat tagy pokud existují
            if (!empty($data['tags']) && is_array($data['tags'])) {
                $this->attachTags($postId, $data['tags']);
            }

            $this->db->commit();

            return $postId;
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log('Post creation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Aktualizuje příspěvek
     */
    public function update(int $id, array $data): bool
    {
        try {
            $this->db->beginTransaction();

            $query = "UPDATE posts
                      SET title = ?,
                          slug = ?,
                          image_filename = ?,
                          content = ?,
                          meta_description = ?,
                          category_id = ?,
                          is_published = ?
                      WHERE id = ?";

            $this->db->query($query, [
                $data['title'],
                $data['slug'],
                $data['image_filename'],
                $data['content'] ?? null,
                $data['meta_description'] ?? null,
                $data['category_id'] ?? null,
                $data['is_published'] ?? 1,
                $id
            ]);

            // Aktualizovat tagy - nejprve smazat staré
            $this->detachAllTags($id);

            // Přidat nové
            if (!empty($data['tags']) && is_array($data['tags'])) {
                $this->attachTags($id, $data['tags']);
            }

            $this->db->commit();

            return true;
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log('Post update failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Smaže příspěvek
     */
    public function delete(int $id): bool
    {
        try {
            // Cascade delete se postará o vazební tabulky
            $query = "DELETE FROM posts WHERE id = ?";
            $this->db->query($query, [$id]);

            return true;
        } catch (\Exception $e) {
            error_log('Post deletion failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Najde příspěvek podle ID
     */
    public function findById(int $id): ?array
    {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug
                  FROM posts p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.id = ?
                  LIMIT 1";

        $post = $this->db->query($query, [$id])->fetch();

        if (!$post) {
            return null;
        }

        // Načíst tagy
        $post['tags'] = $this->getPostTags($id);

        return $post;
    }

    /**
     * Najde příspěvek podle slugu
     */
    public function findBySlug(string $slug): ?array
    {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug
                  FROM posts p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.slug = ? AND p.is_published = 1
                  LIMIT 1";

        $post = $this->db->query($query, [$slug])->fetch();

        if (!$post) {
            return null;
        }

        // Načíst tagy
        $post['tags'] = $this->getPostTags($post['id']);

        // Inkrementovat views
        $this->incrementViews($post['id']);

        return $post;
    }

    /**
     * Vrátí všechny příspěvky s paginací
     */
    public function getAll(int $limit = 10, int $offset = 0, bool $publishedOnly = false): array
    {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug
                  FROM posts p
                  LEFT JOIN categories c ON p.category_id = c.id";

        if ($publishedOnly) {
            $query .= " WHERE p.is_published = 1";
        }

        $query .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";

        $posts = $this->db->query($query, [$limit, $offset])->fetchAll();

        // Načíst tagy pro každý příspěvek
        foreach ($posts as &$post) {
            $post['tags'] = $this->getPostTags($post['id']);
        }

        return $posts;
    }

    /**
     * Vrátí příspěvky podle kategorie
     */
    public function getByCategory(int $categoryId, int $limit = 10, int $offset = 0): array
    {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug
                  FROM posts p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.category_id = ? AND p.is_published = 1
                  ORDER BY p.created_at DESC
                  LIMIT ? OFFSET ?";

        $posts = $this->db->query($query, [$categoryId, $limit, $offset])->fetchAll();

        foreach ($posts as &$post) {
            $post['tags'] = $this->getPostTags($post['id']);
        }

        return $posts;
    }

    /**
     * Vrátí příspěvky podle tagu
     */
    public function getByTag(int $tagId, int $limit = 10, int $offset = 0): array
    {
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug
                  FROM posts p
                  LEFT JOIN categories c ON p.category_id = c.id
                  INNER JOIN post_tags pt ON p.id = pt.post_id
                  WHERE pt.tag_id = ? AND p.is_published = 1
                  ORDER BY p.created_at DESC
                  LIMIT ? OFFSET ?";

        $posts = $this->db->query($query, [$tagId, $limit, $offset])->fetchAll();

        foreach ($posts as &$post) {
            $post['tags'] = $this->getPostTags($post['id']);
        }

        return $posts;
    }

    /**
     * Spočítá celkový počet příspěvků
     */
    public function count(bool $publishedOnly = false): int
    {
        $query = "SELECT COUNT(*) as total FROM posts";

        if ($publishedOnly) {
            $query .= " WHERE is_published = 1";
        }

        $result = $this->db->query($query)->fetch();

        return (int)$result['total'];
    }

    /**
     * Vygeneruje slug z titulku
     */
    public static function generateSlug(string $title): string
    {
        // Převést na malá písmena
        $slug = mb_strtolower($title, 'UTF-8');

        // Nahradit česká diakritická znaménka
        $transliteration = [
            'á' => 'a', 'č' => 'c', 'ď' => 'd', 'é' => 'e', 'ě' => 'e',
            'í' => 'i', 'ň' => 'n', 'ó' => 'o', 'ř' => 'r', 'š' => 's',
            'ť' => 't', 'ú' => 'u', 'ů' => 'u', 'ý' => 'y', 'ž' => 'z'
        ];
        $slug = strtr($slug, $transliteration);

        // Odstranit všechny znaky kromě písmen, čísel, pomlček a mezer
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);

        // Nahradit mezery a více pomlček jednou pomlčkou
        $slug = preg_replace('/[\s-]+/', '-', $slug);

        // Odstranit pomlčky ze začátku a konce
        $slug = trim($slug, '-');

        return $slug;
    }

    /**
     * Inkrementuje počet zobrazení
     */
    private function incrementViews(int $postId): void
    {
        $query = "UPDATE posts SET views = views + 1 WHERE id = ?";
        $this->db->query($query, [$postId]);
    }

    /**
     * Načte tagy pro příspěvek
     */
    private function getPostTags(int $postId): array
    {
        $query = "SELECT t.id, t.name, t.slug
                  FROM tags t
                  INNER JOIN post_tags pt ON t.id = pt.tag_id
                  WHERE pt.post_id = ?
                  ORDER BY t.name";

        return $this->db->query($query, [$postId])->fetchAll();
    }

    /**
     * Připojí tagy k příspěvku
     */
    private function attachTags(int $postId, array $tagIds): void
    {
        foreach ($tagIds as $tagId) {
            $query = "INSERT IGNORE INTO post_tags (post_id, tag_id) VALUES (?, ?)";
            $this->db->query($query, [$postId, (int)$tagId]);
        }
    }

    /**
     * Odpojí všechny tagy od příspěvku
     */
    private function detachAllTags(int $postId): void
    {
        $query = "DELETE FROM post_tags WHERE post_id = ?";
        $this->db->query($query, [$postId]);
    }
}
