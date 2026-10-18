-- Схема бази даних застосунку «Кінотека» (практикуми 4–8, варіант 5).
-- Виконайте файл у MySQL Workbench, щоб відтворити структуру з нуля.

CREATE DATABASE IF NOT EXISTS practicum4 CHARACTER SET utf8mb4;
USE practicum4;

CREATE TABLE IF NOT EXISTS movies (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(255) NOT NULL,
    director      VARCHAR(255) NOT NULL,
    year          INT          NOT NULL,
    genre         VARCHAR(100) NOT NULL,
    rating        DECIMAL(3,1) NOT NULL,
    ratings_count INT          NOT NULL DEFAULT 1,   -- скільки оцінок враховано в середньому рейтингу

    -- findByGenre():  WHERE genre = ?   та   listGenres():  SELECT DISTINCT genre
    INDEX idx_movies_genre (genre),
    -- topRated():     ORDER BY rating DESC, id DESC LIMIT n
    INDEX idx_movies_rating (rating)
);

-- Перевірка використання індексів (виконується на потрібній кількості даних):
-- EXPLAIN SELECT * FROM movies WHERE genre = 'драма';
-- EXPLAIN SELECT * FROM movies ORDER BY rating DESC, id DESC LIMIT 3;
