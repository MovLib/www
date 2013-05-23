USE `movlib`;
BEGIN;
INSERT INTO `releases` (`countries_country_id`, `labels_label_id`) VALUES (840, 1);
INSERT INTO `movies_has_releases` (`movies_movie_id`, `releases_release_id`) VALUES (1, 1);
INSERT INTO `releases` (`release_title`, `countries_country_id`, `labels_label_id`) VALUES ("Roundhay Garden Scene remake", 40, 1);
INSERT INTO `movies_has_releases` (`movies_movie_id`, `releases_release_id`) VALUES (1, 2);
COMMIT;