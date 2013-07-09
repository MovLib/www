-- Léon
BEGIN;
INSERT INTO `persons` (
  `name`,
  `birthdate`,
  `country`,
  `city`,
  `gender`,
  `dyn_aliases`,
  `dyn_biographies`,
  `dyn_links`
)
VALUES (
  'Luc Besson',
  '1959-03-18',
  'FR',
  'Paris',
  'male',
  '',
  '',
  ''
);
INSERT INTO `persons_photos` (
  `photo_id`,
  `person_id`,
  `user_id`,
  `filename`,
  `width`,
  `height`,
  `size`,
  `ext`,
  `created`,
  `rating`,
  `dyn_descriptions`,
  `hash`
) VALUES (
1,
1,
1,
'Luc-Besson.1.en',
858,
1087,
239919,
'jpg',
CURRENT_TIMESTAMP,
0,
'',
'hash'
);
INSERT INTO `persons` (
  `name`,
  `born_name`,
  `birthdate`,
  `country`,
  `city`,
  `gender`,
  `dyn_aliases`,
  `dyn_biographies`,
  `dyn_links`
)
VALUES (
  'Jean Reno',
  'Juan Moreno y Herrera-Jiménez',
  '1948-07-30',
  'MA',
  'Casablanca',
  'male',
  '',
  '',
  ''
);
INSERT INTO `persons` (
  `name`,
  `birthdate`,
  `country`,
  `city`,
  `gender`,
  `dyn_aliases`,
  `dyn_biographies`,
  `dyn_links`
)
VALUES (
  'Natalie Portman',
  '1981-06-09',
  'IL',
  'Jerusalem',
  'female',
  '',
  '',
  ''
);
INSERT INTO `persons` (
  `name`,
  `birthdate`,
  `country`,
  `city`,
  `gender`,
  `dyn_aliases`,
  `dyn_biographies`,
  `dyn_links`
)
VALUES (
  'Gary Oldman',
  '1958-03-21',
  'GB',
  'London',
  'male',
  '',
  '',
  ''
);
COMMIT;
