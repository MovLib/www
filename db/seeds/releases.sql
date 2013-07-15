USE `movlib`;
-- The Shawshank Redemption
BEGIN;
-- Master Release (id: 1)
INSERT INTO `master_releases` (`title`, `country_id`, `dyn_notes`, `release_date`) VALUES ('Die Verurteilten', 57, '', '2007-11-15');
-- Release - Steelbook DVD (EuroVideo) (id: 1)
INSERT INTO `releases` (
  `master_release_id`,
  `is_cut`,
  `ean`,
  `length`,
  `length_credits`,
  `length_bonus`,
  `dyn_extras`,
  `dyn_notes`,
  `aspect_ratio_id`,
  `packaging_id`,
  `type`,
  `bin_type_data`
)
VALUES (
  1,
  false,
  '4009750255773',
  '2:12:38',
  '2:16:44',
  '1:16',
  '',
  '',
  1, -- 1,78:1 (anamorph / 16:9)
  1, -- Steelbook
  'DVD',
  ''
);
-- Sound Formats
-- Dolby Digital 5.1 German
INSERT INTO `releases_sound_formats` (`release_id`, `sound_format_id`, `language_id`, `dyn_comments`) VALUES (1, 1, 52, '');
-- Dolby Digital 2.0 Stereo English
INSERT INTO `releases_sound_formats` (`release_id`, `sound_format_id`, `language_id`, `dyn_comments`) VALUES (1, 2, 41, '');
-- Subtitles
-- German
INSERT INTO `releases_subtitles` (`release_id`, `language_id`, `is_hearing_impaired`, `dyn_comments`) VALUES (1, 52, false, '');
-- German (hearing impaired)
INSERT INTO `releases_subtitles` (`release_id`, `language_id`, `is_hearing_impaired`, `dyn_comments`) VALUES (1, 52, true, '');
-- English (hearing impaired)
INSERT INTO `releases_subtitles` (`release_id`, `language_id`, `is_hearing_impaired`, `dyn_comments`) VALUES (1, 41, true, '');

-- Master Release (id: 2)
INSERT INTO `master_releases` (`title`, `country_id`, `dyn_notes`, `release_date`) VALUES ('Die Verurteilten', 57, '', '2003-01-16');
-- Release - Keep Case DVD Cut (EuroVideo) (id: 2)
INSERT INTO `releases` (
  `master_release_id`,
  `is_cut`,
  `ean`,
  `dyn_extras`,
  `dyn_notes`,
  `aspect_ratio_id`,
  `packaging_id`,
  `type`,
  `bin_type_data`
)
VALUES (
  2,
  true,
  '4009750216279',
  '',
  COLUMN_CREATE('en', 'The movie has been accidentaly cut by 2-3 minutes due to a mastering mistake.', 'de', 'Der Film wurde aufgrund eines Masteringfehlers unabsichtlich um 2-3 Minuten gekürzt.'),
  3, -- 1,85:1 (anamorph / 16:9)
  2, -- Keep Case (Amaray)
  'DVD',
  ''
);
-- Sound Formats
-- Dolby Digital 5.1 EX German
INSERT INTO `releases_sound_formats` (`release_id`, `sound_format_id`, `language_id`, `dyn_comments`) VALUES (2, 5, 52, '');
-- Dolby Digital 2.0 Stereo German
INSERT INTO `releases_sound_formats` (`release_id`, `sound_format_id`, `language_id`, `dyn_comments`) VALUES (2, 2, 52, '');
-- Dolby Digital 2.0 Stereo English
INSERT INTO `releases_sound_formats` (`release_id`, `sound_format_id`, `language_id`, `dyn_comments`) VALUES (2, 2, 41, '');
-- Subtitles
-- German
INSERT INTO `releases_subtitles` (`release_id`, `language_id`, `is_hearing_impaired`, `dyn_comments`) VALUES (2, 52, false, '');
-- English
INSERT INTO `releases_subtitles` (`release_id`, `language_id`, `is_hearing_impaired`, `dyn_comments`) VALUES (2, 41, false, '');

-- Master Release (id: 3)
INSERT INTO `master_releases` (`title`, `country_id`, `dyn_notes`, `release_date`) VALUES ('Die Verurteilten', 57, '', '2000-09-28');
-- Release - Video (EuroVideo) (id: 3)
INSERT INTO `releases` (
  `master_release_id`,
  `is_cut`,
  `ean`,
  `length_credits`,
  `dyn_extras`,
  `dyn_notes`,
  `aspect_ratio_id`,
  `packaging_id`,
  `type`,
  `bin_type_data`
)
VALUES (
  3,
  false,
  '4012909054233',
  '2:17:00',
  '',
  COLUMN_CREATE('en', 'The credits start over the moving movie image.', 'de', 'Abspann beginnt über bewegtem Filmbild.'),
  2, -- 1,33:1
  4, -- Video Box
  'Video',
  ''
);
-- Sound Formats
-- Stereo German
INSERT INTO `releases_sound_formats` (`release_id`, `sound_format_id`, `language_id`, `dyn_comments`) VALUES (3, 4, 52, '');
COMMIT;
