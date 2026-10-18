<?php
require_once 'bootstrap.php';

$id = parseId($_GET['id'] ?? $_POST['id'] ?? null);
if ($id === null) {
    abort(400, 'Некоректний id фільму.');
}

$movie = findMovieById($pdo, $id);
if ($movie === null) {
    abort(404, 'Фільм не знайдено.');
}

$errors = [];
$values = [
    'title' => (string) $movie['title'],
    'director' => (string) $movie['director'],
    'year' => (string) $movie['year'],
    'genre' => (string) $movie['genre'],
    'rating' => (string) $movie['rating'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    [$errors, $clean] = validateMovieInput($_POST);

    if (!$errors) {
        updateMovie($pdo, $id, $clean);
        header('Location: index.php');
        exit;
    }
    $values = posted_values($values);
}

$pageTitle = 'Редагувати фільм';
$formAction = 'edit.php';
$submitLabel = 'Зберегти';
$movieId = $id;
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="narrow">

<h1><?= e($pageTitle) ?></h1>

<?php require __DIR__ . '/_movie_form.php'; ?>

<p><a href="index.php">← Назад до списку</a></p>

</body>
</html>
