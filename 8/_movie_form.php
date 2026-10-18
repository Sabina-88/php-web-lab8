<?php
require_once __DIR__ . '/bootstrap.php';

// Спільна форма для add.php та edit.php.
// Очікує змінні: $formAction, $submitLabel, $values, $errors, а також $movieId (null для нового фільму).
if (!isset($formAction, $submitLabel, $values, $errors)) {
    abort(404, 'Сторінку не знайдено.');
}
?>
<form method="post" action="<?= e($formAction) ?>">
    <?= csrf_field() ?>
    <?php if (($movieId ?? null) !== null): ?>
        <input type="hidden" name="id" value="<?= (int) $movieId ?>">
    <?php endif; ?>

    <label for="title">Назва</label>
    <input type="text" id="title" name="title" maxlength="255" required value="<?= e($values['title']) ?>">
    <?= field_error($errors, 'title') ?>

    <label for="director">Режисер</label>
    <input type="text" id="director" name="director" maxlength="255" required value="<?= e($values['director']) ?>">
    <?= field_error($errors, 'director') ?>

    <label for="year">Рік</label>
    <input type="number" id="year" name="year" min="1888" max="<?= (int) date('Y') ?>" required value="<?= e($values['year']) ?>">
    <?= field_error($errors, 'year') ?>

    <label for="genre">Жанр</label>
    <input type="text" id="genre" name="genre" maxlength="100" required value="<?= e($values['genre']) ?>">
    <?= field_error($errors, 'genre') ?>

    <label for="rating">Рейтинг (0–10)</label>
    <input type="number" id="rating" name="rating" min="0" max="10" step="0.1" required value="<?= e($values['rating']) ?>">
    <?= field_error($errors, 'rating') ?>

    <button type="submit"><?= e($submitLabel) ?></button>
</form>
