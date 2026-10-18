<?php
require_once 'bootstrap.php';

require_post();   // видалення лише через POST (GET-посилання не працює)
csrf_verify();

$id = parseId($_POST['id'] ?? null);
if ($id === null) {
    abort(400, 'Некоректний id фільму.');
}

if (!deleteMovie($pdo, $id)) {
    abort(404, 'Фільм не знайдено.');
}

header('Location: index.php');
exit;
