USE `movlib`;
BEGIN;
INSERT INTO `users` (
  `language_id`,
  `name`,
  `mail`,
  `pass`,
  `created`,
  `login`,
  `timezone`,
  `init`,
  `dyn_profile`,
  `sex`,
  `country_id`,
  `real_name`,
  `birthday`,
  `website`,
  `avatar_extension`,
  `avatar_hash`
) VALUES (
  (SELECT `language_id` FROM `languages` WHERE `iso_alpha-2` = 'en' LIMIT 1),
  'Fleshgrinder',
  'richard@fussenegger.info',
  '$2y$10$zxXKIGS8N9z6vk6iAPiR1u5h2Eypz7kWOQRRfa1uXZ1igkjQ1F8Ga', -- Hashed password "test"
  CURRENT_TIMESTAMP,
  CURRENT_TIMESTAMP,
  'Europe/Vienna',
  'richard@fussenegger.info',
  COLUMN_CREATE('en', 'Richard’s English profile text.', 'de', 'Richard’s deutscher Profiltext.'),
  1,
  (SELECT `country_id` FROM `countries` WHERE `iso_alpha-2` = 'at' LIMIT 1),
  'Richard Fussenegger',
  '1985-6-27',
  'http://richard.fussenegger.info/',
  'jpg',
  '3696208974'
);
COMMIT;
