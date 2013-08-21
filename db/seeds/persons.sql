-- Léon
BEGIN;
-- id: 1
INSERT INTO `persons` (
  `name`,
  `birthdate`,
  `country`,
  `city`,
  `sex`,
  `dyn_aliases`,
  `dyn_biographies`,
  `dyn_links`
)
VALUES (
  'Luc Besson',
  '1959-03-18',
  'FR',
  'Paris',
  1,
  '',
  '',
  ''
);
INSERT INTO `persons_photos` (
  `person_id`,
  `section_id`,
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
-- id: 2
INSERT INTO `persons` (
  `name`,
  `born_name`,
  `birthdate`,
  `country`,
  `city`,
  `sex`,
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
  1,
  '',
  '',
  ''
);
-- id: 3
INSERT INTO `persons` (
  `name`,
  `birthdate`,
  `country`,
  `city`,
  `sex`,
  `dyn_aliases`,
  `dyn_biographies`,
  `dyn_links`
)
VALUES (
  'Natalie Portman',
  '1981-06-09',
  'IL',
  'Jerusalem',
  2,
  '',
  '',
  ''
);
-- id: 4
INSERT INTO `persons` (
  `name`,
  `birthdate`,
  `country`,
  `city`,
  `sex`,
  `dyn_aliases`,
  `dyn_biographies`,
  `dyn_links`
)
VALUES (
  'Gary Oldman',
  '1958-03-21',
  'GB',
  'London',
  1,
  '',
  '',
  ''
);
COMMIT;

-- The Shawshank Redemption
BEGIN;
-- id: 5
INSERT INTO `persons` (
  `name`,
  `birthdate`,
  `country`,
  `city`,
  `sex`,
  `dyn_aliases`,
  `dyn_biographies`,
  `dyn_links`
)
VALUES (
  'Frank Darabont',
  '1959-01-28',
  'FR',
  'Montbéliard',
  1,
  '',
  '',
  ''
);
INSERT INTO `persons_photos` (
  `person_id`,
  `section_id`,
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
5,
1,
1,
'Frank-Darabont.1.en',
348,
394,
109183,
'jpg',
CURRENT_TIMESTAMP,
0,
'',
'hash'
);
-- id: 6
INSERT INTO `persons` (
  `name`,
  `birthdate`,
  `country`,
  `city`,
  `sex`,
  `dyn_aliases`,
  `dyn_biographies`,
  `dyn_links`
)
VALUES (
  'Tim Robbins',
  '1958-10-16',
  'US',
  'West Covina, CA',
  1,
  '',
  '',
  ''
);
-- id: 7
INSERT INTO `persons` (
  `name`,
  `birthdate`,
  `country`,
  `city`,
  `sex`,
  `dyn_aliases`,
  `dyn_biographies`,
  `dyn_links`
)
VALUES (
  'Morgan Freeman',
  '1937-06-01',
  'US',
  'Memphis, TN',
  1,
  '',
  '',
  ''
);
COMMIT;
