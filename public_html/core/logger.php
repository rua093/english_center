<?php
declare(strict_types=1);

function app_log(string $level, string $message, array $context = []): void
{
	$allowedLevels = ['debug', 'info', 'warning', 'error'];
	$normalizedLevel = in_array(strtolower($level), $allowedLevels, true) ? strtolower($level) : 'info';

	$logDir = defined('APP_LOG_DIR') ? (string) APP_LOG_DIR : (BASE_PATH . '/storage/logs');
	if (!app_ensure_directory($logDir)) {
		error_log(sprintf('[%s] %s', strtoupper($normalizedLevel), $message));
		return;
	}

	$logFile = $logDir . '/' . date('Y-m-d') . '.log';
	$maxBytes = defined('APP_LOG_MAX_BYTES') ? (int) APP_LOG_MAX_BYTES : 2097152;

	if (is_file($logFile) && filesize($logFile) !== false && filesize($logFile) > $maxBytes) {
		$archiveFile = $logDir . '/' . date('Y-m-d') . '.log.1';
		if (is_file($archiveFile)) {
			@unlink($archiveFile);
		}
		@rename($logFile, $archiveFile);
	}

	$line = sprintf(
		"[%s] [%s] %s",
		date('Y-m-d H:i:s'),
		strtoupper($normalizedLevel),
		$message
	);

	$sanitizedContext = app_log_sanitize_context($context);
	if (!empty($sanitizedContext)) {
		$encodedContext = json_encode($sanitizedContext, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		if (is_string($encodedContext)) {
			$line .= ' | ' . $encodedContext;
		}
	}

	$line .= PHP_EOL;
	@file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

function app_log_sanitize_context(array $context): array
{
	$sensitiveKeys = ['password', 'pass', 'token', 'authorization', 'cookie', '_csrf'];
	$sanitized = [];

	foreach ($context as $key => $value) {
		$normalizedKey = strtolower((string) $key);
		if (in_array($normalizedKey, $sensitiveKeys, true)) {
			$sanitized[$key] = '[redacted]';
			continue;
		}

		if (is_array($value)) {
			$sanitized[$key] = app_log_sanitize_context($value);
			continue;
		}

		if (is_scalar($value) || $value === null) {
			$sanitized[$key] = $value;
			continue;
		}

		$sanitized[$key] = '[' . gettype($value) . ']';
	}

	return $sanitized;
}
