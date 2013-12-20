-- ---------------------------------------------------------------------------------------------------------------------
-- This file is part of {@link https://github.com/MovLib MovLib}.
--
-- Copyright © 2013-present {@link https://movlib.org/ MovLib}.
--
-- MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
-- License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
-- version.
--
-- MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
-- of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
--
-- You should have received a copy of the GNU Affero General Public License along with MovLib.
-- If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
-- ---------------------------------------------------------------------------------------------------------------------

-- ---------------------------------------------------------------------------------------------------------------------
-- Movie seed data.
--
-- @author Richard Fussenegger <richard@fussenegger.info>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

TRUNCATE TABLE `movies_ratings`;
TRUNCATE TABLE `sessions`;
TRUNCATE TABLE `users`;

-- START "Fleshgrinder"

INSERT INTO `users` SET
  `name`                 = 'Fleshgrinder',
  `access`               = CURRENT_TIMESTAMP,
  `admin`                = TRUE,
  `birthday`             = '1985-06-27',
  `country_code`         = 'AT',
  `created`              = '2013-04-22 19:15:35', -- initial commit
  `currency_code`        = 'EUR',
  `dyn_about_me`         = COLUMN_CREATE(
    'en', '&lt;p&gt;My English profile text.&lt;/p&gt;',
    'de', '&lt;p&gt;Mein deutscher Profiltext.&lt;/p&gt;'
  ),
  `email`                = 'richard@fussenegger.info',
  `image_changed`        = '2013-04-22 19:15:35', -- initial commit
  `image_extension`      = 'jpg',
  `password`             = '$2y$13$LFDTAUaaxs5D6XulZkDU4uKtYgJBuyjDBS2ax7k.oqsASEXstzQDu',
  `real_name`            = 'Richard Fussenegger',
  `sex`                  = 1,
  `system_language_code` = 'en',
  `time_zone_identifier` = 'Europe/Vienna',
  `website`              = 'http://richard.fussenegger.info/'
;

INSERT INTO `movies_ratings` (`movie_id`, `user_id`, `rating`) VALUES
(1, 1, 5), -- "Roundhay Garden Scene" must have 5 ;)
(2, 1, 4), -- "Big Buck Bunny"
(3, 1, 4); -- "The Shawshank Redemption"

UPDATE `movies` SET `rating` = (1 / (1 + 100)) * 5 + (100 / (1 + 100)), `mean_rating` = 5, `votes` = `votes` + 1 WHERE `id` = 1;
UPDATE `movies` SET `rating` = (1 / (1 + 100)) * 4 + (100 / (1 + 100)), `mean_rating` = 4, `votes` = `votes` + 1 WHERE `id` = 2;
UPDATE `movies` SET `rating` = (1 / (1 + 100)) * 4 + (100 / (1 + 100)), `mean_rating` = 4, `votes` = `votes` + 1 WHERE `id` = 3;

-- END "Fleshgrinder"

INSERT INTO `users` SET
  `name`                 = 'Ravenlord',
  `access`               = CURRENT_TIMESTAMP,
  `admin`                = TRUE,
  `country_code`         = 'AT',
  `created`              = '2013-05-03 07:48:30', -- initial commit
  `currency_code`        = 'EUR',
  `dyn_about_me`         = '',
  `email`                = 'markus@deutschl.at',
  `password`             = '$2y$13$xtl5jmUnz3F/Tss5qXyzt.fJ1Rppz/d2HGitxd.ig1MUM7gkXQCPC',
  `real_name`            = 'Markus Deutschl',
  `sex`                  = 1,
  `system_language_code` = 'en',
  `time_zone_identifier` = 'Europe/Vienna'
;


INSERT INTO `users` SET
  `name`                 = 'ftorghele',
  `access`               = CURRENT_TIMESTAMP,
  `admin`                = TRUE,
  `country_code`         = 'AT',
  `created`              = '2013-05-27 01:29:57', -- initial commit
  `currency_code`        = 'EUR',
  `dyn_about_me`         = '',
  `email`                = 'franz@torghele.at',
  `image_changed`        = '2013-05-27 01:29:57', -- initial commit
  `image_extension`      = 'jpg',
  `password`             = '$2y$13$UZQYCsImiKIDQQu1OPfaTe9pZSsOd5OCgsEPVXgAVm98ygQLN0Mje',
  `real_name`            = 'Franz Torghele',
  `sex`                  = 1,
  `system_language_code` = 'en',
  `time_zone_identifier` = 'Europe/Vienna'
;
