<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/table_model_utils.php';

final class ExtracurricularActivitiesTableModel
{
    use TableModelUtils;
    public function listWithRegistrationCount(): array
    {
        $sql = "SELECT a.id, a.title AS activity_name, a.description, a.start_date, a.start_date AS end_date,
                '' AS location, 0 AS max_participants, a.status, a.fee, COUNT(r.id) AS registered
            FROM extracurricular_activities a
            LEFT JOIN activity_registrations r ON r.activity_id = a.id
            GROUP BY a.id, a.title, a.description, a.start_date, a.status, a.fee
            ORDER BY a.start_date DESC";
        return $this->fetchAll($sql);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT id, title AS activity_name, description, content, image_thumbnail, fee, start_date, start_date AS end_date, status FROM extracurricular_activities WHERE id = :id LIMIT 1',
            ['id' => $id]
        );
    }

    public function save(array $data): void
    {
        $activityTitle = (string) ($data['activity_name'] ?? $data['title'] ?? 'Untitled Activity');
        $activityStatus = (string) ($data['status'] ?? 'upcoming');
        $allowedStatus = ['upcoming', 'ongoing', 'finished'];
        if (!in_array($activityStatus, $allowedStatus, true)) {
            $activityStatus = 'upcoming';
        }
        $startDateRaw = (string) ($data['start_date'] ?? date('Y-m-d'));
        $startDate = substr(str_replace('T', ' ', $startDateRaw), 0, 10);

        if ((int) ($data['id'] ?? 0) > 0) {
            $sql = 'UPDATE extracurricular_activities SET title = :title, description = :description, content = :content,
                image_thumbnail = :image_thumbnail, fee = :fee, start_date = :start_date, status = :status WHERE id = :id';
            $this->executeStatement($sql, [
                'id' => (int) $data['id'],
                'title' => $activityTitle,
                'description' => (string) ($data['description'] ?? ''),
                'content' => (string) ($data['content'] ?? ''),
                'image_thumbnail' => (string) ($data['image_thumbnail'] ?? ''),
                'fee' => (float) ($data['fee'] ?? 0),
                'start_date' => $startDate,
                'status' => $activityStatus,
            ]);
            return;
        }

        $sql = 'INSERT INTO extracurricular_activities (title, description, content, image_thumbnail, fee, start_date, status)
            VALUES (:title, :description, :content, :image_thumbnail, :fee, :start_date, :status)';
        $this->executeStatement($sql, [
            'title' => $activityTitle,
            'description' => (string) ($data['description'] ?? ''),
            'content' => (string) ($data['content'] ?? ''),
            'image_thumbnail' => (string) ($data['image_thumbnail'] ?? ''),
            'fee' => (float) ($data['fee'] ?? 0),
            'start_date' => $startDate,
            'status' => $activityStatus,
        ]);
    }

    public function deleteById(int $id): void
    {
        $this->executeStatement('DELETE FROM extracurricular_activities WHERE id = :id', ['id' => $id]);
    }
}