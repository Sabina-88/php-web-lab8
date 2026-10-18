<?php
require_once 'bootstrap.php';

require_post();
csrf_verify();

$id = parseId($_POST['id'] ?? null);
if ($id === null) {
    abort(400, 'Некоректний id фільму.');
}

$rating = validateRating($_POST['rating'] ?? null);
if ($rating === null) {
    abort(400, 'Оцінка має бути числом від 0 до 10.');
}

if (!rateMovie($pdo, $id, $rating)) {
    abort(404, 'Фільм не знайдено.');
}

header('Location: index.php');
exit;
