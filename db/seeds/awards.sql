-- id 1: Japanese Academy Prize
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('Japan Academy Prize', COLUMN_CREATE('ja', '日本アカデミー賞', 'de', 'Japanese Academy Award'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  1,
  1,
  'Best Foreign Film',
  COLUMN_CREATE('de', 'Bester Ausländischer Film'),
  ''
);
COMMIT;

-- id 2: Czech Lion
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('Czech Lion', COLUMN_CREATE('cs', 'Český lev', 'de', 'Tschechischer Löwe'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  2,
  1,
  'Best Foreign Language Film',
  COLUMN_CREATE('de', 'Bester Fremdsprachiger Film', 'cs', 'Nejlepsí zahranicní film'),
  ''
);
COMMIT;

-- id 3: César Award
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('César Award', COLUMN_CREATE('fr', 'César', 'de', 'César'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  3,
  1,
  'Best Actor',
  COLUMN_CREATE('de', 'Bester Hauptdarsteller', 'fr', 'Meilleur acteur'),
  ''
);
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  3,
  2,
  'Best Cinematography',
  COLUMN_CREATE('de', 'Beste Kameraführung', 'fr', 'Meilleure photographie'),
  ''
);
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  3,
  3,
  'Best Director',
  COLUMN_CREATE('de', 'Bester Regisseur', 'fr', 'Meilleur réalisateur'),
  ''
);
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  3,
  4,
  'Best Editing',
  COLUMN_CREATE('de', 'Bester Schnitt', 'fr', 'Meilleur montage'),
  ''
);

INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  3,
  5,
  'Best Film',
  COLUMN_CREATE('de', 'Bester Film', 'fr', 'Meilleur film'),
  ''
);
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  3,
  6,
  'Best Music',
  COLUMN_CREATE('de', 'Beste Filmmusik', 'fr', 'Meilleur musique'),
  ''
);
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  3,
  7,
  'Best Sound',
  COLUMN_CREATE('de', 'Bester Ton', 'fr', 'Meilleur son'),
  ''
);
COMMIT;

-- id 4: Golden Reel Award (Motion Picture Sound Editors)
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('Golden Reel Award (Motion Picture Sound Editors)', COLUMN_CREATE('en', 'Golden Reel Award (Motion Picture Sound Editors)', 'de', 'Golden Reel Award (Motion Picture Sound Editors)'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  4,
  1,
  'Best Sound Editing - Foreign Feature',
  COLUMN_CREATE('de', 'Beste Tonbearbeitung - Ausländischer Film'),
  ''
);
COMMIT;

-- id 5: Oscar
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('Oscar', COLUMN_CREATE('de', 'Oscar'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  5,
  1,
  'Best Actor in a Leading Role',
  COLUMN_CREATE('de', 'Bester Hauptdarsteller'),
  ''
);
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  5,
  2,
  'Best Cinematography',
  COLUMN_CREATE('de', 'Beste Kameraführung'),
  ''
);
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  5,
  3,
  'Best Film Editing',
  COLUMN_CREATE('de', 'Bester Schnitt'),
  ''
);
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  5,
  4,
  'Best Music, Original Score',
  COLUMN_CREATE('de', 'Beste Filmmusik'),
  ''
);
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  5,
  5,
  'Best Picture',
  COLUMN_CREATE('de', 'Bester Film'),
  ''
);
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  5,
  6,
  'Best Sound',
  COLUMN_CREATE('de', 'Bester Ton'),
  ''
);
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  5,
  7,
  'Best Writing (Adapted Screenplay)',
  COLUMN_CREATE('de', 'Bestes adaptiertes Drehbuch'),
  ''
);
COMMIT;

-- id 6: Saturn Award
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('Saturn Award', COLUMN_CREATE('de', 'Saturn Award'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  6,
  1,
  'Best Action/Adventure/Thriller Film',
  COLUMN_CREATE('de', 'Bester Action/Adventure/Thriller-Film'),
  ''
);
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  6,
  2,
  'Best Writin',
  COLUMN_CREATE('de', 'Bestes Drehbuch'),
  ''
);
COMMIT;

-- id 7: Eddie Awards
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('Eddie', COLUMN_CREATE('de', 'Eddie'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  7,
  1,
  'Best Edited Feature Film',
  COLUMN_CREATE('de', 'Bester Filmschnitt'),
  ''
);
COMMIT;

-- id 8: ASC Awards
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('ASC Award', COLUMN_CREATE('de', 'ASC Award'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  8,
  1,
  'Outstanding Achievement in Cinematography in Theatrical Releases',
  '',
  ''
);
COMMIT;

-- id 9: Camerimage
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('Plus Camerimage', COLUMN_CREATE('de', 'Plus Camerimage'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  9,
  1,
  'Bronze Frog',
  COLUMN_CREATE('de', 'Bronzener Frosch'),
  ''
);
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  9,
  2,
  'Silver Frog',
  COLUMN_CREATE('de', 'Silberner Frosch'),
  ''
);
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  9,
  3,
  'Golden Frog',
  COLUMN_CREATE('de', 'Goldener Frosch'),
  ''
);
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  9,
  4,
  'Golden Frog (Lifetime Achievement)',
  COLUMN_CREATE('de', 'Goldener Frosch (Lebenswerk)'),
  ''
);
COMMIT;

-- id 10: Artios Awards
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('Artios Award', COLUMN_CREATE('de', 'Artios Award'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  10,
  1,
  'Best Casting for Feature Film, Drama',
  '',
  ''
);
COMMIT;

-- id 11: CFCA Awards
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('CFCA Award', COLUMN_CREATE('de', 'CFCA Award'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  11,
  1,
  'Best Picture',
  COLUMN_CREATE('de', 'Bester Film'),
  ''
);
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  11,
  2,
  'Best Supporting Actor',
  COLUMN_CREATE('de', 'Bester Nebendarsteller'),
  ''
);
COMMIT;

-- id 12: Chlotrudis Awards
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('Chlotrudis Award', COLUMN_CREATE('de', 'Chlotrudis Award'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  12,
  1,
  'Best Actor',
  COLUMN_CREATE('de', 'Bester Hauptdarsteller'),
  ''
);
COMMIT;

-- id 13: DFWFCA Awards
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('DFWFCA Award', COLUMN_CREATE('de', 'DFWFCA Award'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  13,
  1,
  'Best Actor',
  COLUMN_CREATE('de', 'Bester Hauptdarsteller'),
  ''
);
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  13,
  2,
  'Best Picture',
  COLUMN_CREATE('de', 'Bester Film'),
  ''
);
COMMIT;

-- id 14: DGA Awards
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('DGA Award', COLUMN_CREATE('de', 'DGA Award'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  14,
  1,
  'Outstanding Directorial Achievement in Feature Film',
  '',
  ''
);
COMMIT;

-- id 15: Golden Globe Awards
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('Golden Globe', COLUMN_CREATE('de', 'Golden Globe'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  15,
  1,
  'Best Actor – Motion Picture Drama',
  COLUMN_CREATE('de', 'Bester Hauptdarsteller - Drama'),
  ''
);
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  15,
  2,
  'Best Screenplay - Motion Picture',
  COLUMN_CREATE('de', 'Bestes Filmdrehbuch'),
  ''
);
COMMIT;

-- id 16: Grammy Awards
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('Grammy', COLUMN_CREATE('de', 'Grammy'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  16,
  1,
  'Best Instrumental Composition Written for a Motion Picture or for Television',
  '',
  ''
);
COMMIT;

-- id 17: Studio Crystal Heart Award
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('Studio Crystal Heart Award', COLUMN_CREATE('de', 'Studio Crystal Heart Award'), '');
COMMIT;

-- id 18: Hochi Film Award
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('Hochi Film Award', COLUMN_CREATE('de', 'Hochi Film Award', 'ja', '報知映画賞'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  18,
  1,
  'Best International Picture',
  COLUMN_CREATE('de', 'Bester Internationaler Film'),
  ''
);
COMMIT;

-- id 19: Humanitas Prize
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('Humanitas Prize', COLUMN_CREATE('de', 'Humanitas-Preis'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  19,
  1,
  'Feature Film',
  COLUMN_CREATE('de', 'Film'),
  ''
);
COMMIT;

-- id 20: Kinema Junpo Awards
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('Kinema Junpo', COLUMN_CREATE('de', 'Kinema Junpo', 'ja', 'キネマ旬報'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  20,
  1,
  'Best Foreign Language Film',
  COLUMN_CREATE('de', 'Bester Fremdsprachiger Film'),
  ''
);
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  20,
  2,
  'Reader&apos;s Choice',
  COLUMN_CREATE('de', 'Leserpreis'),
  ''
);
COMMIT;

-- id 21: Manichi Film Awards
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('Mainichi Eiga Concours', COLUMN_CREATE('de', 'Mainichi Eiga Concours', 'ja', '毎日映画コンクール'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  21,
  1,
  'Best Foreign Language Film',
  COLUMN_CREATE('de', 'Bester Fremdsprachiger Film'),
  ''
);
COMMIT;

-- id 22: NBR Awards
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('NBR Award', COLUMN_CREATE('de', 'NBR Award'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  22,
  1,
  'Top Ten Films',
  COLUMN_CREATE('de', 'Top-Ten-Filme'),
  ''
);
COMMIT;

-- id 23: PEN Center USA West Literary Awards
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('PEN Center USA West Literary Awards', COLUMN_CREATE('de', 'PEN Center USA West Literary Awards'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  23,
  1,
  'Screenplay',
  COLUMN_CREATE('de', 'Drehbuch'),
  ''
);
COMMIT;

-- id 24: Screen Actors Guild Awards
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('Screen Actors Guild Award', COLUMN_CREATE('de', 'Screen Actors Guild Award'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  24,
  1,
  'Outstanding Performance by a Male Actor in a Leading Role',
  COLUMN_CREATE('de', 'Bester Hauptdarsteller'),
  ''
);
COMMIT;

-- id 25: USC Scripter Award
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('USC Scripter Award', COLUMN_CREATE('de', 'USC Scripter Award'), '');
COMMIT;

-- id 26: WGA Award
BEGIN;
INSERT INTO `awards` (`name`, `dyn_names`, `dyn_descriptions`) VALUES ('WGA Award', COLUMN_CREATE('de', 'WGA Award'), '');
INSERT INTO `awards_categories` (
  `award_id`,
  `award_category_id`,
  `name`,
  `dyn_names`,
  `dyn_descriptions`
) VALUES (
  26,
  1,
  'Best Adapted Screenplay',
  COLUMN_CREATE('de', 'Bestes adaptiertes Drehbuch'),
  ''
);
COMMIT;
