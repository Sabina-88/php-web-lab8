<?php
/**
 * Спільна ініціалізація для всіх сторінок:
 * налаштування помилок, сесія, CSRF-захист та допоміжні функції.
 */

// Помилки PHP користувачу не показуємо — лише пишемо в лог
ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');

// Будь-який необроблений виняток (у тому числі PDOException): деталі — в лог, користувачу — загальний текст
set_exception_handler(function (Throwable $e): void {
    error_log(get_class($e) . ': ' . $e->getMessage() . ' у ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo 'Сталася внутрішня помилка. Спробуйте пізніше.';
});

ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax', // додатковий захист: cookie не надсилається з міжсайтових POST-запитів
]);
session_start();

// ---------- Виведення ----------

// Екранування для виведення в HTML (захист від XSS)
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function field_error(array $errors, string $field): string
{
    return isset($errors[$field]) ? '<div class="error">' . e($errors[$field]) . '</div>' : '';
}

// Коротка сторінка з повідомленням і кодом статусу
function abort(int $status, string $message): never
{
    http_response_code($status);
    echo '<!DOCTYPE html><html lang="uk"><head><meta charset="UTF-8"><title>Помилка</title>'
        . '<link rel="stylesheet" href="style.css"></head><body>'
        . '<p>' . e($message) . '</p><p><a href="index.php">← Назад до списку</a></p></body></html>';
    exit;
}

// ---------- Захист від CSRF ----------

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

// Перевірка токена при обробці POST; без токена або з чужим — 403
function csrf_verify(): void
{
    $expected = $_SESSION['csrf_token'] ?? '';
    $sent = $_POST['csrf_token'] ?? '';

    if (!is_string($expected) || $expected === '' || !is_string($sent) || !hash_equals($expected, $sent)) {
        abort(403, 'Недійсний CSRF-токен. Оновіть сторінку й спробуйте ще раз.');
    }
}

// Дії, що змінюють дані, дозволені лише методом POST
function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');
        abort(405, 'Ця дія доступна лише через POST.');
    }
}

// Значення полів форми з $_POST для повторного показу (лише рядки)
function posted_values(array $defaults): array
{
    $values = [];
    foreach (array_keys($defaults) as $key) {
        $value = $_POST[$key] ?? '';
        $values[$key] = is_string($value) ? $value : '';
    }
    return $values;
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/movie_functions.php';
