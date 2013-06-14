USE `movlib`;
BEGIN;
INSERT INTO `users` (
    `name`,
    `mail`,
    `pass`,
    `created`,
    `login`,
    `timezone`,
    `init`,
    `dyn_data`,
    `language_id`
  ) VALUES (
    'test',
    'test@example.com',
    'test',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP,
    'Universal',
    'test@old.com',
    '',
    41
);
COMMIT;