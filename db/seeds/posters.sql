USE `movlib`;
BEGIN;
INSERT INTO `posters`
  (
    `movie_id`,
    `poster_id`,
    `user_id`,
    `country_id`,
    `filename`,
    `width`,
    `height`,
    `size`,
    `ext`,
    `created`,
    `rating`,
    `dyn_descriptions`
  )
VALUES
  (
    1,
    1,
    1,
    77,
    "Roundhay Garden Scene",
    856,
    482,
    73462,
    "jpg",
    CURRENT_TIMESTAMP,
    0,
    ''
  )
;
COMMIT;
