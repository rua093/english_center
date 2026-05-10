<?php
declare(strict_types=1);

api_guard_login();
api_require_post(page_url('home'));

$text = (string) ($_POST['text'] ?? '');
if (function_exists('mb_substr')) {
    $text = mb_substr($text, 0, 20000, 'UTF-8');
} else {
    $text = substr($text, 0, 20000);
}

api_success('OK', [
    'html' => bbcode_to_html($text),
]);
