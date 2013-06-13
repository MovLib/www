USE `movlib`;
BEGIN;
INSERT INTO `users` (`name`, `mail`, `pass`, `created`, `login`, `timezone`, `init`) VALUES ('test', 'test@example.com', 'test', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'Universal', 'test@old.com');
COMMIT;