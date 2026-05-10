<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseTableModel.php';

final class EmailOutboxTableModel extends BaseTableModel
{
    public function queue(array $payload): int
    {
        $this->executeStatement(
            'INSERT INTO email_outbox (
                template_key,
                to_email,
                to_name,
                subject,
                html_body,
                text_body,
                payload_json,
                headers_json,
                meta_json,
                status,
                attempts,
                available_at,
                created_at,
                updated_at
             ) VALUES (
                :template_key,
                :to_email,
                :to_name,
                :subject,
                :html_body,
                :text_body,
                :payload_json,
                :headers_json,
                :meta_json,
                :status,
                0,
                NOW(),
                NOW(),
                NOW()
             )',
            [
                'template_key' => trim((string) ($payload['template_key'] ?? 'raw')),
                'to_email' => strtolower(trim((string) ($payload['to_email'] ?? ''))),
                'to_name' => $this->nullIfEmpty($payload['to_name'] ?? null),
                'subject' => trim((string) ($payload['subject'] ?? '')),
                'html_body' => (string) ($payload['html_body'] ?? ''),
                'text_body' => (string) ($payload['text_body'] ?? ''),
                'payload_json' => $this->encodeJson($payload['payload'] ?? []),
                'headers_json' => $this->encodeJson($payload['headers'] ?? []),
                'meta_json' => $this->encodeJson($payload['meta'] ?? []),
                'status' => trim((string) ($payload['status'] ?? 'pending')) ?: 'pending',
            ]
        );

        return (int) $this->pdo->lastInsertId();
    }

    public function claimPendingBatch(int $limit, int $maxAttempts): array
    {
        $limit = $this->clampLimit($limit, 20, 200);
        $maxAttempts = max(1, $maxAttempts);

        $rows = $this->fetchAll(
            'SELECT id, template_key, to_email, to_name, subject, html_body, text_body,
                    payload_json, headers_json, meta_json, attempts
             FROM email_outbox
             WHERE status IN ("pending", "retrying")
               AND attempts < :max_attempts
               AND available_at <= NOW()
               AND (locked_at IS NULL OR locked_at < DATE_SUB(NOW(), INTERVAL 20 MINUTE))
             ORDER BY available_at ASC, id ASC
             LIMIT ' . $limit,
            ['max_attempts' => $maxAttempts]
        );

        $claimed = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $updated = $this->executeStatement(
                'UPDATE email_outbox
                 SET status = "sending",
                     attempts = attempts + 1,
                     locked_at = NOW(),
                     updated_at = NOW()
                 WHERE id = :id
                   AND status IN ("pending", "retrying")
                   AND attempts < :max_attempts
                   AND available_at <= NOW()
                   AND (locked_at IS NULL OR locked_at < DATE_SUB(NOW(), INTERVAL 20 MINUTE))',
                [
                    'id' => $id,
                    'max_attempts' => $maxAttempts,
                ]
            );

            if ($updated !== 1) {
                continue;
            }

            $row['payload'] = $this->decodeJson((string) ($row['payload_json'] ?? ''));
            $row['headers'] = $this->decodeJson((string) ($row['headers_json'] ?? ''));
            $row['meta'] = $this->decodeJson((string) ($row['meta_json'] ?? ''));
            $row['attempts'] = (int) ($row['attempts'] ?? 0) + 1;
            $claimed[] = $row;
        }

        return $claimed;
    }

    public function markSent(int $id, string $providerMessageId = '', string $providerResponse = ''): void
    {
        $this->executeStatement(
            'UPDATE email_outbox
             SET status = "sent",
                 locked_at = NULL,
                 sent_at = NOW(),
                 provider_message_id = :provider_message_id,
                 provider_response = :provider_response,
                 last_error = NULL,
                 updated_at = NOW()
             WHERE id = :id',
            [
                'id' => $id,
                'provider_message_id' => $this->nullIfEmpty($providerMessageId),
                'provider_response' => $this->nullIfEmpty($providerResponse),
            ]
        );
    }

    public function markFailed(int $id, string $errorMessage, int $attempts, int $maxAttempts): void
    {
        $attempts = max(1, $attempts);
        $maxAttempts = max(1, $maxAttempts);
        $nextDelayMinutes = min(60, max(2, $attempts * 5));
        $status = $attempts >= $maxAttempts ? 'failed' : 'retrying';
        $availableAtSql = $status === 'failed'
            ? 'available_at'
            : 'DATE_ADD(NOW(), INTERVAL ' . $nextDelayMinutes . ' MINUTE)';

        $this->executeStatement(
            'UPDATE email_outbox
             SET status = :status,
                 locked_at = NULL,
                 last_error = :last_error,
                 available_at = ' . $availableAtSql . ',
                 updated_at = NOW()
             WHERE id = :id',
            [
                'id' => $id,
                'status' => $status,
                'last_error' => function_exists('mb_substr') ? mb_substr($errorMessage, 0, 2000) : substr($errorMessage, 0, 2000),
            ]
        );
    }

    public function recentByRecipient(string $email, int $limit = 20): array
    {
        return $this->fetchAll(
            'SELECT id, template_key, to_email, subject, status, attempts, sent_at, created_at
             FROM email_outbox
             WHERE to_email = :to_email
             ORDER BY id DESC
             LIMIT ' . $this->clampLimit($limit, 20, 200),
            ['to_email' => strtolower(trim($email))]
        );
    }

    private function encodeJson(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return is_string($json) ? $json : null;
    }

    private function decodeJson(string $raw): array
    {
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $normalized = trim((string) $value);
        return $normalized !== '' ? $normalized : null;
    }
}
