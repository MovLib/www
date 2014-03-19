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
-- Licenses seed data.
--
-- @author Richard Fussenegger <richard@fussenegger.info>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

-- Copyrighted is always 1
INSERT INTO `licenses` SET
  `id`                = 1,
  `dyn_names`         = COLUMN_CREATE('en', 'Copyrighted'),
  `dyn_descriptions`  = '',
  `dyn_url`           = COLUMN_CREATE(
    'en', 'https://en.wikipedia.org/wiki/Copyright',
    'de', 'https://de.wikipedia.org/wiki/Urheberrecht'
  ),
  `abbreviation`      = '©',
  `icon_extension`    = 'svg',
  `icon_changed`      = CURRENT_TIMESTAMP
;

-- Public Domain is always 2
INSERT INTO `licenses` SET
  `id`                = 2,
  `dyn_names`         = COLUMN_CREATE('en', 'Public Domain', 'de', 'Gemeinfrei'),
  `dyn_descriptions`  = '',
  `dyn_url`           = COLUMN_CREATE(
    'en', 'https://en.wikipedia.org/wiki/Public_domain',
    'de', 'https://de.wikipedia.org/wiki/Gemeinfreiheit'
  ),
  `abbreviation`      = 'PD',
  `icon_extension`    = 'svg',
  `icon_changed`      = CURRENT_TIMESTAMP
;

-- Anything else has a dynamic identifier!

INSERT INTO `licenses` SET
  `dyn_names`         = COLUMN_CREATE('en', 'Creative Commons Zero 1.0 Universal'),
  `dyn_descriptions`  = COLUMN_CREATE(
    'en', '&lt;p&gt;The person who associated a work with this deed has dedicated the work to the public domain by waiving all of his or her rights to the work worldwide under copyright law, including all related and neighboring rights, to the extent allowed by law. You can copy, modify, distribute and perform the work, even for commercial purposes, all without asking permission.&lt;/p&gt;'
  ),
  `dyn_url`           = COLUMN_CREATE(
    'en', 'https://creativecommons.org/publicdomain/zero/1.0/deed.en',
    'de', 'https://creativecommons.org/publicdomain/zero/1.0/deed.de'
  ),
  `abbreviation`      = 'CC0 1.0',
  `icon_extension`    = 'svg',
  `icon_changed`      = CURRENT_TIMESTAMP
;

INSERT INTO `licenses` SET
  `dyn_names`         = COLUMN_CREATE('en', 'Creative Commons Attribution 3.0 Unported'),
  `dyn_descriptions`  = COLUMN_CREATE(
    'en', '&lt;p&gt;You are free:&lt;/p&gt;&lt;ul&gt;&lt;li&gt;&lt;b&gt;to share&lt;/b&gt; – to copy, distribute and transmit the work&lt;/li&gt;&lt;li&gt;&lt;b&gt;to remix&lt;/b&gt; – to adapt the work&lt;/li&gt;&lt;/ul&gt;&lt;p&gt;Under the following conditions:&lt;ul&gt;&lt;li&gt;&lt;b&gt;attribution&lt;/b&gt; – You must attribute the work in the manner specified by the author or licensor (but not in any way that suggests that they endorse you or your use of the work).&lt;/li&gt;&lt;/ul&gt;&lt;/p&gt;'
  ),
  `dyn_url`           = COLUMN_CREATE(
    'en', 'https://creativecommons.org/licenses/by/3.0/deed.en',
    'de', 'https://creativecommons.org/licenses/by/3.0/deed.de'
  ),
  `abbreviation`      = 'CC BY 3.0',
  `icon_extension`    = 'svg',
  `icon_changed`      = CURRENT_TIMESTAMP
;
