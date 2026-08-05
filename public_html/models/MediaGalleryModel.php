<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/functions.php';

class MediaGalleryModel
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /**
     * Lấy danh sách các chủ đề/danh mục
     */
    public function getCategories(bool $onlyActive = false): array
    {
        $sql = 'SELECT c.*, COUNT(m.id) AS item_count 
                FROM media_categories c 
                LEFT JOIN media_items m ON c.id = m.category_id ' . ($onlyActive ? 'AND m.is_active = 1' : '') . ' 
                WHERE 1=1 ' . ($onlyActive ? 'AND c.is_active = 1' : '') . ' 
                GROUP BY c.id 
                ORDER BY c.display_order ASC, c.id ASC';
        
        $stmt = $this->db->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function findCategoryById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare('SELECT * FROM media_categories WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function findCategoryBySlug(string $slug): ?array
    {
        $slug = trim($slug);
        if ($slug === '') {
            return null;
        }

        $stmt = $this->db->prepare('SELECT * FROM media_categories WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function saveCategory(array $data): int
    {
        $id = (int) ($data['id'] ?? 0);
        $name = trim((string) ($data['name'] ?? ''));
        $slug = trim((string) ($data['slug'] ?? ''));
        if ($slug === '' && $name !== '') {
            $slug = slugify($name);
        }
        $description = trim((string) ($data['description'] ?? ''));
        $displayOrder = (int) ($data['display_order'] ?? 0);
        $isActive = isset($data['is_active']) ? (int) (bool) $data['is_active'] : 1;

        if ($id > 0) {
            $stmt = $this->db->prepare(
                'UPDATE media_categories 
                 SET name = :name, slug = :slug, description = :description, display_order = :display_order, is_active = :is_active 
                 WHERE id = :id'
            );
            $stmt->execute([
                'name' => $name,
                'slug' => $slug,
                'description' => $description !== '' ? $description : null,
                'display_order' => $displayOrder,
                'is_active' => $isActive,
                'id' => $id,
            ]);
            return $id;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO media_categories (name, slug, description, display_order, is_active) 
             VALUES (:name, :slug, :description, :display_order, :is_active)'
        );
        $stmt->execute([
            'name' => $name,
            'slug' => $slug,
            'description' => $description !== '' ? $description : null,
            'display_order' => $displayOrder,
            'is_active' => $isActive,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function deleteCategory(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM media_categories WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Lấy danh sách Media Item (Ảnh & Video) phân trang & lọc
     */
    public function getMediaItems(
        ?int $categoryId = null,
        ?string $mediaType = null,
        int $page = 1,
        int $perPage = 12,
        bool $onlyActive = true,
        ?string $search = null
    ): array {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;

        $where = ['1=1'];
        $params = [];

        if ($onlyActive) {
            $where[] = 'm.is_active = 1';
        }

        if ($categoryId !== null && $categoryId > 0) {
            $where[] = 'm.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        if ($mediaType !== null && in_array($mediaType, ['image', 'video', 'youtube'], true)) {
            $where[] = 'm.media_type = :media_type';
            $params['media_type'] = $mediaType;
        }

        if ($search !== null && trim($search) !== '') {
            $where[] = '(m.title LIKE :search OR m.description LIKE :search)';
            $params['search'] = '%' . trim($search) . '%';
        }

        $whereSql = implode(' AND ', $where);

        // Count total
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM media_items m WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        // Fetch records
        $sql = "SELECT m.*, c.name AS category_name, c.slug AS category_slug 
                FROM media_items m 
                JOIN media_categories c ON m.category_id = c.id 
                WHERE {$whereSql} 
                ORDER BY m.is_featured DESC, m.created_at DESC 
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalPages = (int) ceil($total / $perPage);

        return [
            'items' => is_array($items) ? $items : [],
            'total' => $total,
            'pages' => max(1, $totalPages),
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    public function findMediaItemById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare('
            SELECT m.*, c.name AS category_name, c.slug AS category_slug 
            FROM media_items m 
            JOIN media_categories c ON m.category_id = c.id 
            WHERE m.id = :id LIMIT 1
        ');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function saveMediaItem(array $data): int
    {
        $id = (int) ($data['id'] ?? 0);
        $categoryId = (int) ($data['category_id'] ?? 0);
        $title = trim((string) ($data['title'] ?? ''));
        $mediaType = trim((string) ($data['media_type'] ?? 'image'));
        if (!in_array($mediaType, ['image', 'video', 'youtube'], true)) {
            $mediaType = 'image';
        }
        $filePathOrUrl = trim((string) ($data['file_path_or_url'] ?? ''));
        $thumbnailUrl = trim((string) ($data['thumbnail_url'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $isFeatured = isset($data['is_featured']) ? (int) (bool) $data['is_featured'] : 0;
        $isActive = isset($data['is_active']) ? (int) (bool) $data['is_active'] : 1;
        $createdBy = (int) ($data['created_by'] ?? 0);

        if ($id > 0) {
            $stmt = $this->db->prepare(
                'UPDATE media_items 
                 SET category_id = :category_id, 
                     title = :title, 
                     media_type = :media_type, 
                     file_path_or_url = :file_path_or_url, 
                     thumbnail_url = :thumbnail_url, 
                     description = :description, 
                     is_featured = :is_featured, 
                     is_active = :is_active 
                 WHERE id = :id'
            );
            $stmt->execute([
                'category_id' => $categoryId,
                'title' => $title,
                'media_type' => $mediaType,
                'file_path_or_url' => $filePathOrUrl,
                'thumbnail_url' => $thumbnailUrl !== '' ? $thumbnailUrl : null,
                'description' => $description !== '' ? $description : null,
                'is_featured' => $isFeatured,
                'is_active' => $isActive,
                'id' => $id,
            ]);
            return $id;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO media_items (category_id, title, media_type, file_path_or_url, thumbnail_url, description, is_featured, is_active, created_by) 
             VALUES (:category_id, :title, :media_type, :file_path_or_url, :thumbnail_url, :description, :is_featured, :is_active, :created_by)'
        );
        $stmt->execute([
            'category_id' => $categoryId,
            'title' => $title,
            'media_type' => $mediaType,
            'file_path_or_url' => $filePathOrUrl,
            'thumbnail_url' => $thumbnailUrl !== '' ? $thumbnailUrl : null,
            'description' => $description !== '' ? $description : null,
            'is_featured' => $isFeatured,
            'is_active' => $isActive,
            'created_by' => $createdBy > 0 ? $createdBy : null,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function deleteMediaItem(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM media_items WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
