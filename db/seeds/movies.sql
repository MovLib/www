USE `movlib`;
BEGIN;
INSERT INTO `movies` (`year`, `runtime`, `original_title`, `dyn_synopses`) VALUES (
  '1888',
  1,
  'Roundhay Garden Scene',
  COLUMN_CREATE('en', 'This is the first movie ever.')
);
INSERT INTO `movies_languages` (`movie_id`, `language_id`) VALUES (1, 185);
INSERT INTO `movies_genres` (`movie_id`, `genre_id`) VALUES (1, 7), (1, 18), (1, 19);
INSERT INTO `movies_countries` (`movie_id`, `country_id`) VALUES (1, 77);
COMMIT;