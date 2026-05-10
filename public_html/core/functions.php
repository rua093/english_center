<?php
declare(strict_types=1);

require_once __DIR__ . '/response.php';
require_once __DIR__ . '/security.php';

function ui_pagination_per_page_options(): array
{
	return [10, 20, 50, 100];
}

function ui_pagination_resolve_per_page(string $queryKey, int $defaultPerPage = 10): int
{
	$allowed = ui_pagination_per_page_options();
	$requested = (int) ($_GET[$queryKey] ?? $defaultPerPage);

	if (in_array($requested, $allowed, true)) {
		return $requested;
	}

	return $defaultPerPage;
}

function ui_format_date(?string $value, string $fallback = '—'): string
{
	$raw = trim((string) $value);
	if ($raw === '') {
		return $fallback;
	}

	try {
		$dt = new DateTimeImmutable($raw);
		return $dt->format('d/m/Y');
	} catch (Throwable) {
		return $raw;
	}
}

function ui_format_datetime(?string $value, string $fallback = '—'): string
{
	$raw = trim((string) $value);
	if ($raw === '') {
		return $fallback;
	}

	try {
		$dt = new DateTimeImmutable($raw);
		return $dt->format('d/m/Y H:i');
	} catch (Throwable) {
		return $raw;
	}
}

function ui_format_date_range(?string $startDate, ?string $endDate, string $fallback = 'Không giới hạn'): string
{
	$start = trim((string) $startDate);
	$end = trim((string) $endDate);

	if ($start !== '' && $end !== '') {
		return ui_format_date($start, $start) . ' - ' . ui_format_date($end, $end);
	}

	if ($start !== '') {
		return 'Từ ' . ui_format_date($start, $start);
	}

	if ($end !== '') {
		return 'Đến ' . ui_format_date($end, $end);
	}

	return $fallback;
}

function ui_render_bbcode(?string $value): string
{
	$text = trim((string) $value);
	if ($text === '') {
		return '';
	}

	$text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	$replacements = [
		'#\[br\s*/?\]#i' => '<br>',
		'#\[b\](.*?)\[/b\]#is' => '<strong>$1</strong>',
		'#\[i\](.*?)\[/i\]#is' => '<em>$1</em>',
		'#\[u\](.*?)\[/u\]#is' => '<u>$1</u>',
		'#\[s\](.*?)\[/s\]#is' => '<s>$1</s>',
		'#\[quote\](.*?)\[/quote\]#is' => '<blockquote>$1</blockquote>',
		'#\[code\](.*?)\[/code\]#is' => '<code>$1</code>',
	];

	foreach ($replacements as $pattern => $replacement) {
		$text = preg_replace($pattern, $replacement, $text) ?? $text;
	}

	$text = preg_replace_callback(
		'#\[url=([^\]]+)\](.*?)\[/url\]#is',
		static function (array $matches): string {
			$url = trim(html_entity_decode($matches[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
			$label = $matches[2];
			if ($url === '') {
				return $label;
			}

			$escapedUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			return '<a href="' . $escapedUrl . '" target="_blank" rel="noopener noreferrer">' . $label . '</a>';
		},
		$text
	) ?? $text;

	$text = preg_replace_callback(
		'#\[url\](.*?)\[/url\]#is',
		static function (array $matches): string {
			$url = trim(html_entity_decode($matches[1], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
			if ($url === '') {
				return $matches[1];
			}

			$escapedUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
			return '<a href="' . $escapedUrl . '" target="_blank" rel="noopener noreferrer">' . $matches[1] . '</a>';
		},
		$text
	) ?? $text;

	$text = preg_replace_callback(
		'#\[(ul|ol)\](.*?)\[/\1\]#is',
		static function (array $matches): string {
			$tag = strtolower((string) $matches[1]);
			$inner = trim((string) $matches[2]);
			$items = preg_split('#\[li\]#i', $inner) ?: [];
			$listItems = [];
			foreach ($items as $item) {
				$item = trim(preg_replace('#\[/li\]#i', '', $item) ?? $item);
				if ($item === '') {
					continue;
				}
				$listItems[] = '<li>' . $item . '</li>';
			}

			if ($listItems === []) {
				return '';
			}

			return '<' . $tag . '>' . implode('', $listItems) . '</' . $tag . '>';
		},
		$text
	) ?? $text;

	return nl2br($text, false);
}
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/db_helper.php';
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/api_helpers.php';
require_once __DIR__ . '/file_storage.php';
require_once __DIR__ . '/page_routes.php';
