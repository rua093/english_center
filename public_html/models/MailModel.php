<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../core/bbcode.php';
require_once __DIR__ . '/../core/mailer.php';
require_once __DIR__ . '/../core/mail_templates.php';
require_once __DIR__ . '/tables/EmailOutboxTableModel.php';
require_once __DIR__ . '/tables/PasswordResetTokensTableModel.php';
require_once __DIR__ . '/tables/UsersTableModel.php';

final class MailModel
{
    private EmailOutboxTableModel $outboxTable;
    private PasswordResetTokensTableModel $passwordResetTokensTable;
    private UsersTableModel $usersTable;
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
        $this->outboxTable = new EmailOutboxTableModel($this->pdo);
        $this->passwordResetTokensTable = new PasswordResetTokensTableModel($this->pdo);
        $this->usersTable = new UsersTableModel($this->pdo);
    }

    public function queueTemplate(string $templateKey, string $toEmail, string $toName = '', array $data = [], array $meta = []): ?int
    {
        $normalizedEmail = strtolower(trim($toEmail));
        if ($normalizedEmail === '' || filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL) === false) {
            return null;
        }

        $rendered = mail_template_render($templateKey, $data);

        return $this->outboxTable->queue([
            'template_key' => $templateKey,
            'to_email' => $normalizedEmail,
            'to_name' => trim($toName),
            'subject' => (string) ($rendered['subject'] ?? ''),
            'html_body' => (string) ($rendered['html'] ?? ''),
            'text_body' => (string) ($rendered['text'] ?? ''),
            'payload' => $data,
            'meta' => $meta,
            'headers' => [
                'X-App-Template' => $templateKey,
            ],
            'status' => 'pending',
        ]);
    }

    public function processEmailOutbox(int $limit = MAIL_BATCH_SIZE): array
    {
        $result = [
            'processed' => 0,
            'sent' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        if (!mail_is_enabled()) {
            return $result;
        }

        if (!mail_is_configured()) {
            throw new RuntimeException('Mail is enabled but SMTP configuration is incomplete.');
        }

        $mailer = new SmtpMailer();
        $messages = $this->outboxTable->claimPendingBatch($limit, (int) MAIL_MAX_ATTEMPTS);

        foreach ($messages as $message) {
            $result['processed']++;
            $messageId = (int) ($message['id'] ?? 0);

            try {
                $sendResult = $mailer->send($message);
                $this->outboxTable->markSent(
                    $messageId,
                    (string) ($sendResult['provider_message_id'] ?? ''),
                    (string) ($sendResult['response'] ?? '')
                );
                $result['sent']++;
            } catch (Throwable $exception) {
                $this->outboxTable->markFailed(
                    $messageId,
                    $exception->getMessage(),
                    (int) ($message['attempts'] ?? 1),
                    (int) MAIL_MAX_ATTEMPTS
                );
                app_log('error', 'Email outbox send failed', [
                    'outbox_id' => $messageId,
                    'to_email' => (string) ($message['to_email'] ?? ''),
                    'template_key' => (string) ($message['template_key'] ?? ''),
                    'error' => $exception->getMessage(),
                ]);
                $result['failed']++;
            }
        }

        return $result;
    }

    public function requestPasswordReset(string $email, string $requestedIp = '', string $requestedUserAgent = ''): array
    {
        $normalizedEmail = strtolower(trim($email));
        $flowToken = bin2hex(random_bytes(24));
        $genericResponse = [
            'accepted' => true,
            'flow_token' => $flowToken,
        ];

        if ($normalizedEmail === '' || filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL) === false) {
            return $genericResponse;
        }

        if ($this->passwordResetTokensTable->hasActiveCooldown($normalizedEmail, $requestedIp, 60)) {
            return $genericResponse;
        }

        if ($this->passwordResetTokensTable->countRecentRequests($normalizedEmail, $requestedIp, 30) >= 5) {
            return $genericResponse;
        }

        $user = $this->usersTable->findActiveByEmail($normalizedEmail);
        if (!$user) {
            return $genericResponse;
        }

        $flowToken = bin2hex(random_bytes(24));
        $otpCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = new DateTimeImmutable('+10 minutes');

        $this->passwordResetTokensTable->createRequest(
            (int) ($user['id'] ?? 0),
            $normalizedEmail,
            $flowToken,
            $otpCode,
            $expiresAt,
            $requestedIp,
            $requestedUserAgent
        );

        $this->queueTemplate('password_reset_otp', $normalizedEmail, (string) ($user['full_name'] ?? ''), [
            'user_name' => (string) ($user['full_name'] ?? $user['username'] ?? 'bạn'),
            'otp_code' => $otpCode,
            'expires_in_minutes' => 10,
            'reset_url' => mail_forgot_password_url(),
        ], [
            'type' => 'password_reset',
            'user_id' => (int) ($user['id'] ?? 0),
        ]);

        return [
            'accepted' => true,
            'flow_token' => $flowToken,
        ];
    }

    public function verifyPasswordResetOtp(string $flowToken, string $otpCode): bool
    {
        $normalizedOtp = preg_replace('/\D+/', '', $otpCode);
        if (!is_string($normalizedOtp) || strlen($normalizedOtp) !== 6) {
            return false;
        }

        return $this->passwordResetTokensTable->verifyOtp(trim($flowToken), $normalizedOtp) !== null;
    }

    public function completePasswordReset(string $flowToken, string $newPassword): bool
    {
        $row = $this->passwordResetTokensTable->findVerifiedByPublicToken(trim($flowToken));
        if (!$row) {
            return false;
        }

        $userId = (int) ($row['user_id'] ?? 0);
        if ($userId <= 0) {
            return false;
        }

        $user = $this->usersTable->findActiveById($userId);
        if (!$user) {
            return false;
        }

        $this->usersTable->updatePassword($userId, $newPassword);
        $this->passwordResetTokensTable->markConsumed((int) ($row['id'] ?? 0));

        $email = strtolower(trim((string) ($user['email'] ?? '')));
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            $this->queueTemplate('password_reset_success', $email, (string) ($user['full_name'] ?? ''), [
                'user_name' => (string) ($user['full_name'] ?? $user['username'] ?? 'bạn'),
                'login_url' => mail_login_url(),
            ], [
                'type' => 'password_reset_success',
                'user_id' => $userId,
            ]);
        }

        return true;
    }

    public function queueLeadEmails(array $leadData): void
    {
        $parentEmail = strtolower(trim((string) ($leadData['parent_email'] ?? '')));
        if ($parentEmail !== '' && filter_var($parentEmail, FILTER_VALIDATE_EMAIL) !== false) {
            $this->queueTemplate('lead_confirmation', $parentEmail, (string) ($leadData['parent_name'] ?? ''), $leadData, [
                'type' => 'lead_confirmation',
            ]);
        }

        foreach ($this->resolveInternalLeadRecipients() as $recipient) {
            $this->queueTemplate(
                'lead_internal_notification',
                (string) ($recipient['email'] ?? ''),
                (string) ($recipient['name'] ?? ''),
                $leadData,
                ['type' => 'lead_internal_notification']
            );
        }
    }

    public function queueWelcomeAccountEmail(array $userData, string $plainPassword, string $roleLabel): void
    {
        $email = strtolower(trim((string) ($userData['email'] ?? '')));
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return;
        }

        $this->queueTemplate('user_welcome_account', $email, (string) ($userData['full_name'] ?? ''), [
            'full_name' => (string) ($userData['full_name'] ?? ''),
            'username' => (string) ($userData['username'] ?? ''),
            'plain_password' => $plainPassword,
            'role_label' => $roleLabel,
            'login_url' => mail_login_url(),
        ], [
            'type' => 'welcome_account',
            'user_id' => (int) ($userData['id'] ?? 0),
        ]);
    }

    public function queuePasswordChangedEmail(array $userData): void
    {
        $email = strtolower(trim((string) ($userData['email'] ?? '')));
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return;
        }

        $this->queueTemplate('user_password_changed', $email, (string) ($userData['full_name'] ?? ''), [
            'full_name' => (string) ($userData['full_name'] ?? ''),
            'login_url' => mail_login_url(),
        ], [
            'type' => 'user_password_changed',
            'user_id' => (int) ($userData['id'] ?? 0),
        ]);
    }

    public function queueTuitionOverdueEmail(int $userId, array $data): void
    {
        $user = $this->usersTable->findActiveById($userId);
        if (!$user) {
            return;
        }

        $email = strtolower(trim((string) ($user['email'] ?? '')));
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return;
        }

        $this->queueTemplate('tuition_overdue', $email, (string) ($user['full_name'] ?? ''), $data, [
            'type' => 'tuition_overdue',
            'user_id' => $userId,
        ]);
    }

    public function queueSystemNotificationEmails(string $targetType, ?int $targetId, string $title, string $message, array $meta = []): int
    {
        $recipients = $this->resolveNotificationRecipients($targetType, $targetId);
        $queuedCount = 0;

        foreach ($recipients as $recipient) {
            $queuedId = $this->queueTemplate(
                'system_notification',
                (string) ($recipient['email'] ?? ''),
                (string) ($recipient['name'] ?? ''),
                [
                    'title' => $title,
                    'message' => bbcode_to_plain_text($message),
                    'message_html' => bbcode_to_html($message),
                ],
                $meta + [
                    'type' => 'system_notification',
                    'target_type' => strtoupper(trim($targetType)),
                    'target_id' => $targetId,
                    'user_id' => (int) ($recipient['id'] ?? 0),
                ]
            );

            if ($queuedId !== null) {
                $queuedCount++;
            }
        }

        return $queuedCount;
    }

    private function resolveNotificationRecipients(string $targetType, ?int $targetId): array
    {
        $normalizedType = strtoupper(trim($targetType));
        $params = [];
        $sql = '';

        if ($normalizedType === 'ALL') {
            $sql = 'SELECT DISTINCT u.id, u.full_name, u.email
                    FROM users u
                    WHERE u.deleted_at IS NULL
                      AND u.status = "active"
                      AND u.email IS NOT NULL
                      AND u.email <> ""';
        } elseif ($normalizedType === 'USER' && (int) $targetId > 0) {
            $sql = 'SELECT u.id, u.full_name, u.email
                    FROM users u
                    WHERE u.id = :target_id
                      AND u.deleted_at IS NULL
                      AND u.status = "active"
                      AND u.email IS NOT NULL
                      AND u.email <> ""';
            $params['target_id'] = (int) $targetId;
        } elseif ($normalizedType === 'ROLE' && (int) $targetId > 0) {
            $sql = 'SELECT u.id, u.full_name, u.email
                    FROM users u
                    WHERE u.role_id = :target_id
                      AND u.deleted_at IS NULL
                      AND u.status = "active"
                      AND u.email IS NOT NULL
                      AND u.email <> ""';
            $params['target_id'] = (int) $targetId;
        } elseif ($normalizedType === 'CLASS' && (int) $targetId > 0) {
            $sql = 'SELECT DISTINCT u.id, u.full_name, u.email
                    FROM users u
                    WHERE u.deleted_at IS NULL
                      AND u.status = "active"
                      AND u.email IS NOT NULL
                      AND u.email <> ""
                      AND (
                            u.id IN (
                                SELECT cs.student_id
                                FROM class_students cs
                                WHERE cs.class_id = :target_id_student
                            )
                            OR u.id = (
                                SELECT c.teacher_id
                                FROM classes c
                                WHERE c.id = :target_id_teacher
                                LIMIT 1
                            )
                      )';
            $params['target_id_student'] = (int) $targetId;
            $params['target_id_teacher'] = (int) $targetId;
        }

        if ($sql === '') {
            return [];
        }

        $rows = $this->pdo->prepare($sql);
        $rows->execute($params);
        $items = $rows->fetchAll() ?: [];

        $recipients = [];
        $seenEmails = [];
        foreach ($items as $item) {
            $email = strtolower(trim((string) ($item['email'] ?? '')));
            if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false || isset($seenEmails[$email])) {
                continue;
            }

            $seenEmails[$email] = true;
            $recipients[] = [
                'id' => (int) ($item['id'] ?? 0),
                'name' => (string) ($item['full_name'] ?? ''),
                'email' => $email,
            ];
        }

        return $recipients;
    }

    private function resolveInternalLeadRecipients(): array
    {
        $configuredEmails = mail_internal_notification_recipients();
        if ($configuredEmails !== []) {
            return array_map(static fn (string $email): array => [
                'email' => $email,
                'name' => '',
            ], $configuredEmails);
        }

        $rows = $this->pdo->query(
            'SELECT DISTINCT u.id, u.full_name, u.email
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.deleted_at IS NULL
               AND u.status = "active"
               AND u.email IS NOT NULL
               AND u.email <> ""
               AND r.role_name IN ("admin", "staff")'
        )->fetchAll() ?: [];

        $recipients = [];
        foreach ($rows as $row) {
            $email = strtolower(trim((string) ($row['email'] ?? '')));
            if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                continue;
            }

            $recipients[] = [
                'email' => $email,
                'name' => (string) ($row['full_name'] ?? ''),
            ];
        }

        return $recipients;
    }
}
