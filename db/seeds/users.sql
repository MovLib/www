USE `movlib`;
BEGIN;
INSERT INTO `users` (
  `language_id`,
  `name`,
  `email`,
  `password`,
  `created`,
  `login`,
  `timezone`,
  `dyn_profile`,
  `sex`,
  `country_id`,
  `real_name`,
  `birthday`,
  `website`,
  `avatar_extension`,
  `avatar_name`
) VALUES (
  (SELECT `language_id` FROM `languages` WHERE `iso_alpha-2` = 'en' LIMIT 1),
  'Fleshgrinder',
  'richard@fussenegger.info',
  '$2y$10$N/kvo2/A9vAv.8Mkgb4ky.llucBnDaPi5pdW7HPP2OCHV9yDQyjbG', -- Hashed password "test1234"
  CURRENT_TIMESTAMP,
  CURRENT_TIMESTAMP,
  'Europe/Vienna',
  COLUMN_CREATE('en', 'Richard’s English profile text.', 'de', 'Richards deutscher Profiltext.'),
  1,
  (SELECT `country_id` FROM `countries` WHERE `iso_alpha-2` = 'at' LIMIT 1),
  'Richard Fussenegger',
  '1985-6-27',
  'http://richard.fussenegger.info/',
  'jpg',
  '3696208974'
);
COMMIT;
