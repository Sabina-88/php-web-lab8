<?php
require_once 'bootstrap.php';

$genre = $_GET['genre'] ?? '';
$genre = is_string($genre) ? trim($genre) : '';

$movies = $genre === '' ? getAllMovies($pdo) : findByGenre($pdo, $genre);
$genres = listGenres($pdo);
$top = topRated($pdo, 3);
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Кінотека</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Кінотека</h1>

<p><a href="add.php">+ Додати фільм</a></p>

<form method="get" action="index.php">
    <label for="genre">Фільтр за жанром:</label>
    <select name="genre" id="genre" onchange="this.form.submit()">
        <option value="">Усі жанри</option>
        <?php foreach ($genres as $g): ?>
            <option value="<?= e($g) ?>" <?= $g === $genre ? 'selected' : '' ?>><?= e($g) ?></option>
        <?php endforeach; ?>
    </select>
</form>

<table>
    <tr>
        <th>Назва</th><th>Режисер</th><th>Рік</th><th>Жанр</th><th>Рейтинг</th><th>Оцінити</th><th>Дії</th>
    </tr>
    <?php foreach ($movies as $movie): ?>
        <tr>
            <td><?= e($movie['title']) ?></td>
            <td><?= e($movie['director']) ?></td>
            <td><?= e($movie['year']) ?></td>
            <td><?= e($movie['genre']) ?></td>
            <td><?= e($movie['rating']) ?></td>
            <td>
                <form method="post" action="rate.php" class="inline">
                    <input type="hidden" name="id" value="<?= (int) $movie['id'] ?>">
                    <?= csrf_field() ?>
                    <input type="number" name="rating" min="0" max="10" step="0.1" required placeholder="0–10" class="rating-input">
                    <button type="submit">OK</button>
                </form>
            </td>
            <td>
                <a href="edit.php?id=<?= (int) $movie['id'] ?>">Редагувати</a>
                <form method="post" action="delete.php" class="inline" onsubmit="return confirm('Видалити цей фільм?')">
                    <input type="hidden" name="id" value="<?= (int) $movie['id'] ?>">
                    <?= csrf_field() ?>
                    <button type="submit">Видалити</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<div class="top">
    <strong>Топ-3 фільми за рейтингом:</strong>
    <ol>
        <?php foreach ($top as $movie): ?>
            <li><?= e($movie['title']) ?> — <?= e($movie['rating']) ?></li>
        <?php endforeach; ?>
    </ol>
</div>

</body>
</html>
