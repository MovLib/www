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
    '$2y$10$zxXKIGS8N9z6vk6iAPiR1u5h2Eypz7kWOQRRfa1uXZ1igkjQ1F8Ga', -- Hashed password "test"
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP,
    'UTC',
    'test@old.com',
    '',
    41
);
COMMIT;