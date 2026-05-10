<?php
declare(strict_types=1);

require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/mail.php';
require_once __DIR__ . '/mail_templates.php';

final class SmtpMailer
{
    /** @var resource|null */
    private $socket = null;
    private string $host;
    private int $port;
    private string $encryption;
    private string $username;
    private string $password;
    private string $authMode;
    private int $timeout;
    private bool $verifyPeer;

    public function __construct()
    {
        $this->host = trim((string) MAIL_HOST);
        $this->port = max(1, (int) MAIL_PORT);
        $this->encryption = strtolower(trim((string) MAIL_ENCRYPTION));
        $this->username = trim((string) MAIL_USERNAME);
        $this->password = (string) MAIL_PASSWORD;
        $this->authMode = strtolower(trim((string) MAIL_AUTH_MODE));
        $this->timeout = max(5, (int) MAIL_TIMEOUT);
        $this->verifyPeer = MAIL_VERIFY_PEER === true;
    }

    public function send(array $message): array
    {
        if (!mail_is_configured()) {
            throw new RuntimeException('Mail transport is not configured.');
        }

        $toEmail = strtolower(trim((string) ($message['to_email'] ?? '')));
        if ($toEmail === '' || filter_var($toEmail, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Invalid recipient email address.');
        }

        $toName = trim((string) ($message['to_name'] ?? ''));
        $subject = trim((string) ($message['subject'] ?? ''));
        $htmlBody = (string) ($message['html_body'] ?? '');
        $textBody = (string) ($message['text_body'] ?? strip_tags($htmlBody));
        $headers = is_array($message['headers'] ?? null) ? $message['headers'] : [];

        $this->connect();

        try {
            $this->expectCode([220], $this->readResponse());
            $this->command('EHLO ' . $this->ehloHost(), [250]);

            if ($this->encryption === 'tls') {
                $this->startTls();
                $this->command('EHLO ' . $this->ehloHost(), [250]);
            }

            if ($this->username !== '' || $this->password !== '') {
                $this->authenticate();
            }

            $this->command('MAIL FROM:<' . mail_from_address() . '>', [250]);
            $this->command('RCPT TO:<' . $toEmail . '>', [250, 251]);
            $this->command('DATA', [354]);

            $rawMessage = $this->buildMimeMessage($toEmail, $toName, $subject, $htmlBody, $textBody, $headers);
            fwrite($this->socket, $rawMessage . "\r\n.\r\n");
            $dataResponse = $this->readResponse();
            $this->expectCode([250], $dataResponse);
            $this->command('QUIT', [221]);

            preg_match('/250[ -].*?([A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+)/', $dataResponse, $matches);
            $providerMessageId = $matches[1] ?? '';

            return [
                'provider_message_id' => is_string($providerMessageId) ? $providerMessageId : '',
                'response' => $dataResponse,
            ];
        } finally {
            $this->disconnect();
        }
    }

    private function connect(): void
    {
        $transport = $this->encryption === 'ssl' ? 'ssl://' . $this->host : $this->host;
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => $this->verifyPeer,
                'verify_peer_name' => $this->verifyPeer,
                'allow_self_signed' => !$this->verifyPeer,
            ],
        ]);

        $socket = @stream_socket_client(
            $transport . ':' . $this->port,
            $errorNumber,
            $errorMessage,
            $this->timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!is_resource($socket)) {
            throw new RuntimeException('SMTP connect failed: ' . $errorMessage . ' (' . $errorNumber . ')');
        }

        stream_set_timeout($socket, $this->timeout);
        $this->socket = $socket;
    }

    private function startTls(): void
    {
        $this->command('STARTTLS', [220]);

        $result = @stream_socket_enable_crypto(
            $this->socket,
            true,
            STREAM_CRYPTO_METHOD_TLS_CLIENT
        );

        if ($result !== true) {
            throw new RuntimeException('Unable to start TLS encryption for SMTP connection.');
        }
    }

    private function authenticate(): void
    {
        if ($this->authMode === 'plain') {
            $this->command('AUTH PLAIN ' . base64_encode("\0" . $this->username . "\0" . $this->password), [235]);
            return;
        }

        $this->command('AUTH LOGIN', [334]);
        $this->command(base64_encode($this->username), [334]);
        $this->command(base64_encode($this->password), [235]);
    }

    private function buildMimeMessage(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        string $textBody,
        array $headers
    ): string {
        $boundary = 'b1_' . bin2hex(random_bytes(12));
        $messageId = '<' . bin2hex(random_bytes(12)) . '@' . preg_replace('/[^a-z0-9.\-]/i', '', $this->host) . '>';

        $headerLines = [
            'Date: ' . gmdate('D, d M Y H:i:s O'),
            'Message-ID: ' . $messageId,
            'From: ' . $this->formatAddress(mail_from_address(), mail_from_name()),
            'To: ' . $this->formatAddress($toEmail, $toName),
            'Reply-To: ' . $this->formatAddress(mail_reply_to_address(), mail_reply_to_name()),
            'Subject: ' . $this->encodeHeader($subject),
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            'X-Mailer: EnglishCenterSMTP/1.0',
        ];

        foreach ($headers as $name => $value) {
            $headerName = trim((string) $name);
            $headerValue = trim((string) $value);
            if ($headerName === '' || $headerValue === '') {
                continue;
            }

            $headerLines[] = $headerName . ': ' . $headerValue;
        }

        $normalizedText = str_replace(["\r\n", "\r"], "\n", $textBody);
        $normalizedHtml = str_replace(["\r\n", "\r"], "\n", $htmlBody);

        $bodyLines = [
            '--' . $boundary,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            '',
            chunk_split(base64_encode($normalizedText)),
            '--' . $boundary,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
            '',
            chunk_split(base64_encode($normalizedHtml)),
            '--' . $boundary . '--',
        ];

        $raw = implode("\r\n", $headerLines) . "\r\n\r\n" . implode("\r\n", $bodyLines);
        $raw = preg_replace("/(?m)^\./", '..', $raw) ?? $raw;

        return $raw;
    }

    private function formatAddress(string $email, string $name = ''): string
    {
        $normalizedEmail = trim($email);
        $normalizedName = trim($name);
        if ($normalizedName === '') {
            return '<' . $normalizedEmail . '>';
        }

        return $this->encodeHeader($normalizedName) . ' <' . $normalizedEmail . '>';
    }

    private function encodeHeader(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (preg_match('/^[\x20-\x7E]+$/', $value) === 1) {
            return $value;
        }

        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private function command(string $command, array $expectedCodes): string
    {
        fwrite($this->socket, $command . "\r\n");
        $response = $this->readResponse();
        $this->expectCode($expectedCodes, $response);
        return $response;
    }

    private function expectCode(array $expectedCodes, string $response): void
    {
        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $expectedCodes, true)) {
            throw new RuntimeException('Unexpected SMTP response [' . $code . ']: ' . trim($response));
        }
    }

    private function readResponse(): string
    {
        $response = '';
        while (is_resource($this->socket)) {
            $line = fgets($this->socket, 515);
            if ($line === false) {
                break;
            }

            $response .= $line;

            if (strlen($line) < 4) {
                break;
            }

            if (preg_match('/^\d{3}\s/', $line) === 1) {
                break;
            }
        }

        if ($response === '') {
            throw new RuntimeException('Empty SMTP response received.');
        }

        return $response;
    }

    private function ehloHost(): string
    {
        $host = trim((string) parse_url(mail_abs_url('/'), PHP_URL_HOST));
        return $host !== '' ? $host : 'localhost';
    }

    private function disconnect(): void
    {
        if (is_resource($this->socket)) {
            fclose($this->socket);
        }

        $this->socket = null;
    }
}
