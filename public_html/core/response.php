<?php
declare(strict_types=1);

function e(string $value): string
{
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function format_person_name_with_code(string $name, ?string $code = null): string
{
	$normalizedName = trim($name);
	$normalizedCode = trim((string) $code);

	if ($normalizedName === '') {
		return $normalizedCode !== '' ? $normalizedCode : '';
	}

	return $normalizedCode !== '' ? $normalizedName . ' (' . $normalizedCode . ')' : $normalizedName;
}

function format_person_dropdown_label(string $name, ?string $code = null): string
{
	$normalizedName = trim($name);
	$normalizedCode = trim((string) $code);

	if ($normalizedName === '') {
		return $normalizedCode !== '' ? $normalizedCode : '';
	}

	return $normalizedCode !== '' ? $normalizedName . ' - ' . $normalizedCode : $normalizedName;
}

function teacher_display_name(array $row, string $fallbackName = 'Giáo viên'): string
{
	$name = trim((string) ($row['teacher_name'] ?? $row['full_name'] ?? $fallbackName));
	$code = trim((string) ($row['teacher_code'] ?? ''));
	return format_person_name_with_code($name, $code);
}

function student_display_name(array $row, string $fallbackName = 'Học viên'): string
{
	$name = trim((string) ($row['student_name'] ?? $row['full_name'] ?? $fallbackName));
	$code = trim((string) ($row['student_code'] ?? ''));
	return format_person_name_with_code($name, $code);
}

function user_display_name(array $row, string $fallbackName = 'Người dùng'): string
{
	$name = trim((string) ($row['full_name'] ?? $row['student_name'] ?? $row['teacher_name'] ?? $fallbackName));
	$code = trim((string) ($row['teacher_code'] ?? $row['student_code'] ?? ''));
	return format_person_name_with_code($name, $code);
}

function teacher_dropdown_label(array $row, string $fallbackName = 'Giáo viên'): string
{
	$name = trim((string) ($row['teacher_name'] ?? $row['full_name'] ?? $fallbackName));
	$code = trim((string) ($row['teacher_code'] ?? ''));
	return format_person_dropdown_label($name, $code);
}

function student_dropdown_label(array $row, string $fallbackName = 'Học viên'): string
{
	$name = trim((string) ($row['student_name'] ?? $row['full_name'] ?? $fallbackName));
	$code = trim((string) ($row['student_code'] ?? ''));
	return format_person_dropdown_label($name, $code);
}

function user_dropdown_label(array $row, string $fallbackName = 'Người dùng'): string
{
	$name = trim((string) ($row['full_name'] ?? $row['student_name'] ?? $row['teacher_name'] ?? $fallbackName));
	$code = trim((string) ($row['teacher_code'] ?? $row['student_code'] ?? ''));
	return format_person_dropdown_label($name, $code);
}

function redirect(string $path): never
{
	header('Location: ' . $path);
	exit;
}

function set_flash(string $key, string $message): void
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	$_SESSION['flash'][$key] = function_exists('translate_legacy_flash_message')
		? translate_legacy_flash_message($message)
		: $message;
}

function get_flash(string $key): ?string
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	if (!isset($_SESSION['flash'][$key])) {
		return null;
	}

	$message = (string) $_SESSION['flash'][$key];
	unset($_SESSION['flash'][$key]);
	return $message;
}

function format_money(float $amount): string
{
	return number_format($amount, 0, ',', '.') . ' đ';
}

function ui_btn_primary_classes(string $size = 'md'): string
{
	$sizeClass = match ($size) {
		'sm' => 'px-3 py-1.5 text-xs',
		'md' => 'px-4 py-2.5 text-sm',
		default => 'px-4 py-2.5 text-sm',
	};

	return 'inline-flex items-center justify-center rounded-xl bg-blue-700 ' . $sizeClass . ' font-bold text-white transition hover:-translate-y-0.5 hover:bg-blue-800';
}

function ui_btn_secondary_classes(string $size = 'md'): string
{
	$sizeClass = match ($size) {
		'sm' => 'px-3 py-1.5 text-xs',
		'md' => 'px-4 py-2.5 text-sm',
		default => 'px-4 py-2.5 text-sm',
	};

	return 'inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white ' . $sizeClass . ' font-bold text-blue-700 transition hover:bg-slate-100';
}

function ui_btn_danger_classes(string $size = 'sm'): string
{
	$sizeClass = match ($size) {
		'sm' => 'px-3 py-1.5 text-xs',
		'md' => 'px-4 py-2.5 text-sm',
		default => 'px-3 py-1.5 text-xs',
	};

	return 'inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white ' . $sizeClass . ' font-bold text-rose-700 transition hover:bg-rose-50';
}

function ui_quick_action_link_classes(): string
{
	return 'inline-flex min-w-[120px] flex-col rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 leading-tight text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700';
}
