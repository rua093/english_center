<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/functions.php';

class FaqModel
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connection();
    }

    /**
     * Lấy danh sách các danh mục FAQ
     */
    public function getCategories(bool $onlyActive = false): array
    {
        $where = $onlyActive ? 'WHERE is_active = 1' : '';
        $sql = "SELECT category, COUNT(id) AS total_items 
                FROM faqs 
                {$where} 
                GROUP BY category 
                ORDER BY category ASC";

        $stmt = $this->db->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Lấy danh sách FAQ với bộ lọc phân trang / tìm kiếm
     */
    public function getAllFaqs(
        ?string $category = null,
        bool $onlyActive = false,
        ?string $search = null,
        int $page = 1,
        int $perPage = 50
    ): array {
        $where = ['1=1'];
        $params = [];

        if ($onlyActive) {
            $where[] = 'is_active = 1';
        }

        if ($category !== null && trim($category) !== '' && trim($category) !== 'ALL') {
            $where[] = 'category = :category';
            $params['category'] = trim($category);
        }

        if ($search !== null && trim($search) !== '') {
            $where[] = '(question LIKE :search OR answer LIKE :search OR category LIKE :search)';
            $params['search'] = '%' . trim($search) . '%';
        }

        $whereSql = implode(' AND ', $where);

        // Count total
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM faqs WHERE {$whereSql}");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $page = max(1, $page);
        $perPage = max(1, min(200, $perPage));
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM faqs 
                WHERE {$whereSql} 
                ORDER BY sort_order ASC, id ASC 
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'items' => is_array($items) ? $items : [],
            'total' => $total,
            'pages' => max(1, (int) ceil($total / $perPage)),
            'page' => $page,
            'per_page' => $perPage,
        ];
    }

    /**
     * Tìm 1 câu hỏi theo ID
     */
    public function findFaqById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $stmt = $this->db->prepare('SELECT * FROM faqs WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    /**
     * Thêm mới hoặc cập nhật FAQ
     */
    public function saveFaq(array $data): int
    {
        $id = (int) ($data['id'] ?? 0);
        $category = trim((string) ($data['category'] ?? 'Hỏi đáp chung'));
        if ($category === '') {
            $category = 'Hỏi đáp chung';
        }

        $question = trim((string) ($data['question'] ?? ''));
        $answer = trim((string) ($data['answer'] ?? ''));
        $sortOrder = (int) ($data['sort_order'] ?? 0);
        $isActive = isset($data['is_active']) ? (int) (bool) $data['is_active'] : 1;

        if ($id > 0) {
            $stmt = $this->db->prepare(
                'UPDATE faqs 
                 SET category = :category, 
                     question = :question, 
                     answer = :answer, 
                     sort_order = :sort_order, 
                     is_active = :is_active 
                 WHERE id = :id'
            );
            $stmt->execute([
                'category' => $category,
                'question' => $question,
                'answer' => $answer,
                'sort_order' => $sortOrder,
                'is_active' => $isActive,
                'id' => $id,
            ]);
            return $id;
        }

        $stmt = $this->db->prepare(
            'INSERT INTO faqs (category, question, answer, sort_order, is_active) 
             VALUES (:category, :question, :answer, :sort_order, :is_active)'
        );
        $stmt->execute([
            'category' => $category,
            'question' => $question,
            'answer' => $answer,
            'sort_order' => $sortOrder,
            'is_active' => $isActive,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Bật/Tắt hiển thị FAQ
     */
    public function toggleFaqActive(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $stmt = $this->db->prepare('UPDATE faqs SET is_active = NOT is_active WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Xóa FAQ
     */
    public function deleteFaq(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM faqs WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
