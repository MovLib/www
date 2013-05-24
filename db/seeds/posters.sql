BEGIN;
INSERT INTO `images` (`file_name`, `users_user_id`, `width`, `height`, `extension`) VALUES ("Roundhay Garden Scene", 1, 856, 482, "jpg");
INSERT INTO `posters` (`movies_movie_id`, `images_file_id`, `languages_language_id`) VALUES (1, 1, 22);
COMMIT;