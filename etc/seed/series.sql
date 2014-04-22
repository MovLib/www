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
-- Series seed data.
--
-- @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

TRUNCATE TABLE `series`;
TRUNCATE TABLE `series_titles`;

-- START "Game of Thrones"

INSERT INTO `series` SET
  `dyn_synopses` = COLUMN_CREATE(
    'en', '&lt;p&gt;Game of Thrones is an American fantasy drama television series created for HBO by David Benioff and D. B. Weiss. It is an adaptation of A Song of Ice and Fire, George R. R. Martin&#039;s series of fantasy novels, the first of which is titled A Game of Thrones. Filmed in a Belfast studio and on location elsewhere in Northern Ireland, Malta, Scotland, Croatia, Iceland and Morocco, it premiered on HBO in the United States on April 17, 2011.&lt;/p&gt;',
    'de', '&lt;p&gt;Game of Thrones ist eine US-amerikanische Fantasy-Fernsehserie von David Benioff und D. B. Weiss für den US-Kabelsender HBO. Die von den Kritikern sehr gelobte und auch kommerziell erfolgreiche Serie basiert auf den Romanen Das Lied von Eis und Feuer von George R. R. Martin.&lt;/p&gt;'
  ),
  `dyn_wikipedia` = COLUMN_CREATE(
    'en', 'http://en.wikipedia.org/wiki/Game_of_Thrones',
    'de', 'http://de.wikipedia.org/wiki/Game_of_Thrones'
  ),
  `original_title`               = 'Game of Thrones',
  `original_title_language_code` = 'en',
  `start_year`                   = 2011,
  `status`                       = 2
;
SET @series_game_of_thrones = LAST_INSERT_ID();

INSERT INTO `series_titles` SET
  `series_id`     = @series_game_of_thrones,
  `display`       = true,
  `dyn_comments`  = '',
  `language_code` = 'de',
  `title`         = 'Das Lied von Eis und Feuer'
;

-- END "Game of Thrones"