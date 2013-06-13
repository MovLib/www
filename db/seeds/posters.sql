USE `movlib`;
BEGIN;
INSERT INTO `images` (`filename`, `user_id`, `width`, `height`, `size`, `ext`, `created`, `dyn_descriptions`) VALUES ("Roundhay Garden Scene", 1, 856, 482, 73462, "jpg", CURRENT_TIMESTAMP, '');
INSERT INTO `posters` (`movie_id`, `image_id`, `country_id`) VALUES (1, 1, 77);
COMMIT;