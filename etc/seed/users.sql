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
-- User seed data.
--
-- @author Richard Fussenegger <richard@fussenegger.info>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

TRUNCATE TABLE `sessions`;
TRUNCATE TABLE `users`;

-- START "Fleshgrinder"

INSERT INTO `users` SET
  `name`               = 'Fleshgrinder',
  `admin`              = TRUE,
  `birthdate`          = '1985-06-27',
  `country_code`       = 'AT',
  `created`            = '2013-04-22 19:15:35', -- initial commit
  `currency_code`      = 'EUR',
  `dyn_about_me`       = COLUMN_CREATE(
    'en', '&lt;p&gt;My English profile text.&lt;/p&gt;',
    'de', '&lt;p&gt;Mein deutscher Profiltext.&lt;/p&gt;'
  ),
  `email`              = 'richard@fussenegger.info',
  `image_cache_buster` = UNHEX('96edb4cb1a85927f151e74e687636d6b'),
  `image_extension`    = 'jpg',
  `password`           = '$2y$13$LFDTAUaaxs5D6XulZkDU4uKtYgJBuyjDBS2ax7k.oqsASEXstzQDu',
  `real_name`          = 'Richard Fussenegger',
  `sex`                = 1,
  `language_code`      = 'en',
  `timezone`           = 'Europe/Vienna',
  `website`            = 'http://richard.fussenegger.info/'
;

-- END "Fleshgrinder"

INSERT INTO `users` SET
  `name`               = 'Ravenlord',
  `admin`              = TRUE,
  `country_code`       = 'AT',
  `created`            = '2013-05-03 07:48:30', -- initial commit
  `currency_code`      = 'EUR',
  `dyn_about_me`       = '',
  `email`              = 'markus@deutschl.at',
  `image_cache_buster` = UNHEX('f5156ce289d48b9229a1b425f4a03356'),
  `image_extension`    = 'jpg',
  `password`           = '$2y$13$xtl5jmUnz3F/Tss5qXyzt.fJ1Rppz/d2HGitxd.ig1MUM7gkXQCPC',
  `real_name`          = 'Markus Deutschl',
  `sex`                = 1,
  `language_code`      = 'en',
  `timezone`           = 'Europe/Vienna',
  `private`            = 1
;

INSERT INTO `users` SET
  `name`               = 'ftorghele',
  `admin`              = TRUE,
  `country_code`       = 'AT',
  `created`            = '2013-05-27 01:29:57', -- initial commit
  `currency_code`      = 'EUR',
  `dyn_about_me`       = '',
  `email`              = 'franz@torghele.at',
  `image_cache_buster` = UNHEX('7ef0468e9f45ae317cfdd33cae75d4b7'),
  `image_extension`    = 'png',
  `password`           = '$2y$13$UZQYCsImiKIDQQu1OPfaTe9pZSsOd5OCgsEPVXgAVm98ygQLN0Mje',
  `real_name`          = 'Franz Torghele',
  `sex`                = 1,
  `language_code`      = 'en',
  `timezone`           = 'Europe/Vienna'
;
