<?php
/**
 * Дані фільмів: перевірка вхідних значень і запити до бази.
 * Усі SQL-запити підготовлені (prepare/execute): значення від користувача
 * ніколи не вставляються в текст запиту.
 */

// ---------- Перевірка вхідних даних ----------

function toNumber(mixed $value): int|float|null
{
    if (is_int($value) || is_float($value)) {
        return $value;
    }
    if (is_string($value) && is_numeric(trim($value))) {
        return trim($value) + 0;
    }
    return null;
}

function parseId(mixed $value): ?int
{
    if (is_int($value) && $value > 0) {
        return $value;
    }
    if (is_string($value) && ctype_digit($value) && (int) $value > 0) {
        return (int) $value;
    }
    return null;
}

// Оцінка: число від 0 до 10 (масив, порожнє значення, -5 тощо — відхиляються)
function validateRating(mixed $value): ?float
{
    $number = toNumber($value);
    if ($number === null || $number < 0 || $number > 10) {
        return null;
    }
    return round((float) $number, 1);
}

// Повертає [помилки за полями, очищені значення]
function validateMovieInput(array $input): array
{
    $errors = [];
    $clean = [];

    foreach (['title' => 255, 'director' => 255, 'genre' => 100] as $field => $maxLength) {
        $value = $input[$field] ?? null;
        if (!is_string($value) || trim($value) === '') {
            $errors[$field] = 'Обов\'язкове непорожнє текстове поле';
        } elseif (mb_strlen(trim($value)) > $maxLength) {
            $errors[$field] = "Максимум {$maxLength} символів";
        } else {
            $clean[$field] = trim($value);
        }
    }

    $currentYear = (int) date('Y');
    $year = toNumber($input['year'] ?? null);
    if ($year === null || floor($year) != $year || $year < 1888 || $year > $currentYear) {
        $errors['year'] = "Ціле число від 1888 до {$currentYear}";
    } else {
        $clean['year'] = (int) $year;
    }

    $rating = validateRating($input['rating'] ?? null);
    if ($rating === null) {
        $errors['rating'] = 'Число від 0 до 10';
    } else {
        $clean['rating'] = $rating;
    }

    return [$errors, $clean];
}

// ---------- Запити до бази даних ----------

function getAllMovies(PDO $pdo): array
{
    return $pdo->query('SELECT * FROM movies ORDER BY id DESC')->fetchAll();
}

function findByGenre(PDO $pdo, string $genre): array
{
    $stmt = $pdo->prepare('SELECT * FROM movies WHERE genre = :genre ORDER BY id DESC');
    $stmt->execute([':genre' => $genre]);
    return $stmt->fetchAll();
}

function listGenres(PDO $pdo): array
{
    return $pdo->query('SELECT DISTINCT genre FROM movies ORDER BY genre')->fetchAll(PDO::FETCH_COLUMN);
}

function topRated(PDO $pdo, int $limit): array
{
    $stmt = $pdo->prepare('SELECT * FROM movies ORDER BY rating DESC, id DESC LIMIT :limit');
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function findMovieById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM movies WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $movie = $stmt->fetch();
    return $movie === false ? null : $movie;
}

function addMovie(PDO $pdo, array $movie): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO movies (title, director, year, genre, rating, ratings_count)
         VALUES (:title, :director, :year, :genre, :rating, 1)'
    );
    $stmt->execute([
        ':title' => $movie['title'],
        ':director' => $movie['director'],
        ':year' => $movie['year'],
        ':genre' => $movie['genre'],
        ':rating' => $movie['rating'],
    ]);
}

function updateMovie(PDO $pdo, int $id, array $movie): void
{
    $stmt = $pdo->prepare(
        'UPDATE movies
         SET title = :title, director = :director, year = :year, genre = :genre, rating = :rating
         WHERE id = :id'
    );
    $stmt->execute([
        ':title' => $movie['title'],
        ':director' => $movie['director'],
        ':year' => $movie['year'],
        ':genre' => $movie['genre'],
        ':rating' => $movie['rating'],
        ':id' => $id,
    ]);
}

function deleteMovie(PDO $pdo, int $id): bool
{
    $stmt = $pdo->prepare('DELETE FROM movies WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->rowCount() > 0;
}

// Додає оцінку й перераховує середній рейтинг одним атомарним запитом
function rateMovie(PDO $pdo, int $id, float $rating): bool
{
    $stmt = $pdo->prepare(
        'UPDATE movies
         SET rating = ROUND((rating * ratings_count + :new_rating) / (ratings_count + 1), 1),
             ratings_count = ratings_count + 1
         WHERE id = :id'
    );
    $stmt->execute([':new_rating' => $rating, ':id' => $id]);
    return $stmt->rowCount() > 0;
}
