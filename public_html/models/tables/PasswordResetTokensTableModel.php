<?php
declare(strict_types=1);

require_once __DIR__ . '/BaseTableModel.php';
require_once __DIR__ . '/../../core/mail.php';

final class PasswordResetTokensTableModel extends BaseTableModel
{
    public function createRequest(
        int $userId,
        string $email,
        string $publicToken,
        string $otpCode,
        DateTimeImmutable $expiresAt,
        string $requestedIp = '',
        string $requestedUserAgent = ''
    ): int {
        $this->executeInTransaction(function () use ($userId, $email, $publicToken, $otpCode, $expiresAt, $requestedIp, $requestedUserAgent): void {
            $this->executeStatement(
                'UPDATE password_reset_tokens
                 SET consumed_at = NOW(),
                     updated_at = NOW()
                 WHERE user_id = :user_id
                   AND consumed_at IS NULL',
                ['user_id' => $userId]
            );

            $this->executeStatement(
                'INSERT INTO password_reset_tokens (
                    user_id,
                    email,
                    token_hash,
                    otp_hash,
                    requested_ip,
                    requested_user_agent,
                    expires_at,
                    created_at,
                    updated_at
                 ) VALUES (
                    :user_id,
                    :email,
                    :token_hash,
                    :otp_hash,
                    :requested_ip,
                    :requested_user_agent,
                    :expires_at,
                    NOW(),
                    NOW()
                 )',
                [
                    'user_id' => $userId,
                    'email' => strtolower(trim($email)),
                    'token_hash' => $this->hashToken($publicToken),
                    'otp_hash' => password_hash($otpCode, PASSWORD_DEFAULT),
                    'requested_ip' => $this->nullIfEmpty($requestedIp),
                    'requested_user_agent' => $this->nullIfEmpty(
                        function_exists('mb_substr')
                            ? mb_substr($requestedUserAgent, 0, 500)
                            : substr($requestedUserAgent, 0, 500)
                    ),
                    'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                ]
            );
        });

        return (int) $this->pdo->lastInsertId();
    }

    public function countRecentRequests(string $email, string $requestedIp, int $minutes = 30): int
    {
        $filters = [];
        $params = [];

        $normalizedEmail = strtolower(trim($email));
        if ($normalizedEmail !== '') {
            $filters[] = 'email = :email';
            $params['email'] = $normalizedEmail;
        }

        $normalizedIp = trim($requestedIp);
        if ($normalizedIp !== '') {
            $filters[] = 'requested_ip = :requested_ip';
            $params['requested_ip'] = $normalizedIp;
        }

        if ($filters === []) {
            return 0;
        }

        return (int) $this->fetchScalar(
            'SELECT COUNT(*) AS total
             FROM password_reset_tokens
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL ' . max(1, $minutes) . ' MINUTE)
               AND (' . implode(' OR ', $filters) . ')',
            $params,
            'total',
            0
        );
    }

    public function hasActiveCooldown(string $email, string $requestedIp, int $seconds = 60): bool
    {
        $seconds = max(30, $seconds);
        return (int) $this->fetchScalar(
            'SELECT COUNT(*) AS total
             FROM password_reset_tokens
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL ' . $seconds . ' SECOND)
               AND (
                    email = :email
                    OR (:requested_ip <> "" AND requested_ip = :requested_ip)
               )',
            [
                'email' => strtolower(trim($email)),
                'requested_ip' => trim($requestedIp),
            ],
            'total',
            0
        ) > 0;
    }

    public function findActiveByPublicToken(string $publicToken): ?array
    {
        return $this->fetchOne(
            'SELECT *
             FROM password_reset_tokens
             WHERE token_hash = :token_hash
               AND consumed_at IS NULL
               AND expires_at > NOW()
             ORDER BY id DESC
             LIMIT 1',
            ['token_hash' => $this->hashToken($publicToken)]
        );
    }

    public function verifyOtp(string $publicToken, string $otpCode): ?array
    {
        $row = $this->findActiveByPublicToken($publicToken);
        if (!$row) {
            return null;
        }

        $otpHash = (string) ($row['otp_hash'] ?? '');
        if ($otpHash === '' || !password_verify($otpCode, $otpHash)) {
            return null;
        }

        $this->executeStatement(
            'UPDATE password_reset_tokens
             SET verified_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id',
            ['id' => (int) ($row['id'] ?? 0)]
        );

        return $this->findActiveByPublicToken($publicToken);
    }

    public function findVerifiedByPublicToken(string $publicToken): ?array
    {
        return $this->fetchOne(
            'SELECT *
             FROM password_reset_tokens
             WHERE token_hash = :token_hash
               AND consumed_at IS NULL
               AND verified_at IS NOT NULL
               AND expires_at > NOW()
             ORDER BY id DESC
             LIMIT 1',
            ['token_hash' => $this->hashToken($publicToken)]
        );
    }

    public function markConsumed(int $id): void
    {
        if ($id <= 0) {
            return;
        }

        $this->executeStatement(
            'UPDATE password_reset_tokens
             SET consumed_at = NOW(),
                 updated_at = NOW()
             WHERE id = :id',
            ['id' => $id]
        );
    }

    public function purgeExpired(int $retentionDays = 7): int
    {
        $retentionDays = max(1, $retentionDays);
        return $this->executeStatement(
            'DELETE FROM password_reset_tokens
             WHERE expires_at < DATE_SUB(NOW(), INTERVAL ' . $retentionDays . ' DAY)'
        );
    }

    private function hashToken(string $publicToken): string
    {
        return hash_hmac('sha256', $publicToken, api_secret_key());
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $normalized = trim((string) $value);
        return $normalized !== '' ? $normalized : null;
    }
}
