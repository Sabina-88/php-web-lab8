<?php
require_once 'bootstrap.php';

$errors = [];
$values = ['title' => '', 'director' => '', 'year' => '', 'genre' => '', 'rating' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    [$errors, $clean] = validateMovieInput($_POST);

    if (!$errors) {
        addMovie($pdo, $clean);
        header('Location: index.php');
        exit;
    }
    $values = posted_values($values);
}

$pageTitle = 'Додати фільм';
$formAction = 'add.php';
$submitLabel = 'Додати';
$movieId = null;
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
