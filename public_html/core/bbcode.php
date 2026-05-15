<?php
declare(strict_types=1);

use s9e\TextFormatter\Configurator;

const BBCODE_BUNDLE_CLASS = 'App\\BbcodeBundle';
const BBCODE_BUNDLE_VERSION = 's9e-text-formatter-v2';

function bbcode_cache_dir(): string
{
    return dirname(__DIR__) . '/storage/cache/bbcode';
}

function bbcode_bundle_filepath(): string
{
    return bbcode_cache_dir() . '/BbcodeBundle.php';
}

function bbcode_renderer_filepath(): string
{
    return bbcode_cache_dir() . '/BbcodeRenderer.php';
}

function bbcode_ensure_cache_dir(): void
{
    $cacheDir = bbcode_cache_dir();
    if (!app_ensure_directory($cacheDir)) {
        throw new RuntimeException('BBCode cache directory is not writable: ' . $cacheDir);
    }
}

function bbcode_is_safe_url(string $rawUrl): bool
{
    $url = trim(html_entity_decode($rawUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    if ($url === '' || strlen($url) > 2048) {
        return false;
    }

    if (preg_match('/[\x00-\x1F\x7F]/', $url) === 1) {
        return false;
    }

    if ($url[0] === '/') {
        return true;
    }

    return preg_match('#^https?://#i', $url) === 1;
}

function bbcode_is_safe_image_url(string $rawUrl): bool
{
    if (!bbcode_is_safe_url($rawUrl)) {
        return false;
    }

    $url = trim(html_entity_decode($rawUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    $path = (string) parse_url($url, PHP_URL_PATH);
    if ($path === '') {
        return false;
    }

    $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
    return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif'], true);
}

function bbcode_prepare_input(?string $text): string
{
    $normalized = str_replace(["\r\n", "\r"], "\n", (string) $text);

    $normalized = preg_replace_callback('~\[img\](.*?)\[/img\]~is', static function (array $matches): string {
        $url = trim((string) ($matches[1] ?? ''));
        if (bbcode_is_safe_image_url($url)) {
            return '[img]' . $url . '[/img]';
        }

        return '[code]' . (string) ($matches[0] ?? '') . '[/code]';
    }, $normalized) ?? $normalized;

    return $normalized;
}

function bbcode_generate_bundle(): void
{
    bbcode_ensure_cache_dir();

    if (!is_writable(bbcode_cache_dir())) {
        throw new RuntimeException('BBCode cache directory is not writable: ' . bbcode_cache_dir());
    }

    $configurator = new Configurator;
    $configurator->rendering->setEngine('PHP', bbcode_cache_dir());
    $configurator->rendering->engine->className = 'App_BbcodeRenderer';
    $configurator->rendering->engine->filepath = bbcode_renderer_filepath();
    $configurator->rootRules->enableAutoLineBreaks();

    foreach (['B', 'I', 'U', 'S', 'CODE', 'QUOTE', 'URL', 'IMG'] as $tagName) {
        $configurator->BBCodes->addFromRepository($tagName);
    }

    $configurator->tags['CODE']->template = '<pre class="bbcode-code"><code><xsl:apply-templates /></code></pre>';
    $configurator->tags['URL']->template = '<a href="{@url}" rel="nofollow ugc noopener noreferrer"><xsl:copy-of select="@title" /><xsl:apply-templates /></a>';
    $configurator->tags['IMG']->template = '<img src="{@src}" alt="{@alt}" loading="lazy" referrerpolicy="no-referrer"></img>';

    $bundleSource = "<?php\n"
        . "declare(strict_types=1);\n"
        . "// " . BBCODE_BUNDLE_VERSION . "\n"
        . $configurator->bundleGenerator->generate(BBCODE_BUNDLE_CLASS);

    file_put_contents(bbcode_bundle_filepath(), $bundleSource);
}

function bbcode_ensure_bundle(): string
{
    $bundlePath = bbcode_bundle_filepath();
    $mustGenerate = !is_file($bundlePath);

    if (!$mustGenerate) {
        $bundleSource = (string) file_get_contents($bundlePath);
        $mustGenerate = !str_contains($bundleSource, BBCODE_BUNDLE_VERSION);
    }

    if ($mustGenerate) {
        bbcode_generate_bundle();
    }

    require_once $bundlePath;
    return BBCODE_BUNDLE_CLASS;
}

function bbcode_to_html(?string $text): string
{
    $content = trim((string) $text);
    if ($content === '') {
        return '';
    }

    try {
        $bundleClass = bbcode_ensure_bundle();
        $xml = $bundleClass::parse(bbcode_prepare_input($content));
        return $bundleClass::render($xml);
    } catch (Throwable $exception) {
        app_log('error', 'BBCode render failed', [
            'error' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);

        return nl2br(e($content), false);
    }
}

function bbcode_to_plain_text(?string $text): string
{
    $html = bbcode_to_html($text);
    if ($html === '') {
        return '';
    }

    $plain = str_replace(['<br>', '<br/>', '<br />'], "\n", $html);
    $plain = html_entity_decode(strip_tags($plain), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $plain = preg_replace("/\n{3,}/", "\n\n", $plain) ?? $plain;
    return trim($plain);
}

function bbcode_truncate_plain_text(?string $text, int $limit = 120): string
{
    $plain = bbcode_to_plain_text($text);
    if ($plain === '') {
        return '';
    }

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($plain, 'UTF-8') <= $limit) {
            return $plain;
        }

        return rtrim(mb_substr($plain, 0, max(0, $limit - 1), 'UTF-8')) . '…';
    }

    if (strlen($plain) <= $limit) {
        return $plain;
    }

    return rtrim(substr($plain, 0, max(0, $limit - 1))) . '…';
}

function bbcode_editor_attributes(array $attributes): string
{
    $parts = [];
    foreach ($attributes as $key => $value) {
        if ($value === null || $value === false) {
            continue;
        }

        if ($value === true) {
            $parts[] = $key;
            continue;
        }

        $parts[] = $key . '="' . e((string) $value) . '"';
    }

    return implode(' ', $parts);
}

function render_bbcode_editor(string $name, ?string $value = '', array $options = []): string
{
    $id = (string) ($options['id'] ?? preg_replace('/[^a-z0-9_-]+/i', '-', $name) ?? $name);
    $rows = max(3, (int) ($options['rows'] ?? 4));
    $placeholder = (string) ($options['placeholder'] ?? '');
    $required = !empty($options['required']);
    $wrapperClass = trim((string) ($options['wrapper_class'] ?? ''));
    $textareaClass = trim((string) ($options['textarea_class'] ?? ''));

    $textareaAttributes = bbcode_editor_attributes([
        'id' => $id,
        'name' => $name,
        'rows' => $rows,
        'placeholder' => $placeholder !== '' ? $placeholder : null,
        'required' => $required,
        'data-bbcode-input' => '1',
        'data-bbcode-field' => '1',
        'class' => $textareaClass !== '' ? $textareaClass : null,
    ]);

    return
        '<div class="bbcode-editor' . ($wrapperClass !== '' ? ' ' . e($wrapperClass) : '') . '" data-bbcode-editor="1">' .
            '<textarea ' . $textareaAttributes . '>' . e((string) $value) . '</textarea>' .
            '<p class="bbcode-hint">Soạn thảo trực quan, dữ liệu vẫn được lưu ở dạng BBCode an toàn. Ảnh chỉ nhận URL http/https hoặc đường dẫn nội bộ, có đuôi jpg/jpeg/png/gif/webp/avif.</p>' .
        '</div>';
}
