<?php
/**
 * Підключення до бази даних. Дані підключення беруться з config.local.php,
 * який НЕ потрапляє в репозиторій (зразок — config.example.php).
 * Помилки підключення обробляє загальний обробник у bootstrap.php:
 * деталі пишуться в лог, користувач бачить лише загальне повідомлення.
 */
$configFile = __DIR__ . '/config.local.php';
if (!is_file($configFile)) {
    throw new RuntimeException('Не знайдено config.local.php: скопіюйте config.example.php і впишіть дані підключення');
}
$config = require $configFile;

$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $config['host'], $config['dbname']);

$pdo = new PDO($dsn, $config['username'], $config['password'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false, // справжні підготовлені запити на боці СУБД
]);
