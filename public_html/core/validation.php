<?php
declare(strict_types=1);

function input_string(array $source, string $key, string $default = ''): string
{
	$value = $source[$key] ?? $default;
	return trim((string) $value);
}

function input_int(array $source, string $key, int $default = 0): int
{
	$value = $source[$key] ?? $default;
	return (int) $value;
}

function input_float(array $source, string $key, float $default = 0): float
{
	$value = $source[$key] ?? $default;
	return (float) $value;
}

function normalize_phone_string(mixed $value): string
{
	$normalized = trim((string) $value);
	if ($normalized === '') {
		return '';
	}

	$digits = preg_replace('/\D+/', '', $normalized);
	return is_string($digits) ? $digits : '';
}

function validate_required_fields(array $source, array $requiredMap): array
{
	$errors = [];

	foreach ($requiredMap as $field => $label) {
		$value = $source[$field] ?? null;
		$normalized = is_string($value) ? trim($value) : $value;
		if ($normalized === null || $normalized === '') {
			$errors[$field] = (string) $label;
		}
	}

	return $errors;
}
