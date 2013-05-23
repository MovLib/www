USE `movlib`;
BEGIN;
INSERT INTO `movies` (`year`) VALUES (1888);
INSERT INTO `movie_titles` (`title`, `languages_language_id`, `movies_movie_id`, `is_original_title`) VALUES ("Roundhay Garden Scene", 22, 1, TRUE);
INSERT INTO `movies_has_languages` (`movies_movie_id`, `languages_language_id`) VALUES (1, 97);
INSERT INTO `movies_has_genres` (`movies_movie_id`, `genres_genre_id`) VALUES (1, 7), (1, 19), (1, 20);
INSERT INTO `movies_has_countries` (`movies_movie_id`, `countries_country_id`) VALUES (1, 826);
INSERT INTO `movies_en` (`movies_movie_id`, `display_title_id`, `synopsis`) VALUES (1, 1, "This is the first movie ever.");
COMMIT;