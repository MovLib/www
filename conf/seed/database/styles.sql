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
-- Styles seed data.
--
-- @author Richard Fussenegger <richard@fussenegger.info>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO `styles` (`dyn_names`, `dyn_descriptions`) VALUES
(
  COLUMN_CREATE('en', 'Heroic Bloodshed', 'de', 'Heroic Bloodshed'),
  COLUMN_CREATE(
    'en', '&lt;p&gt;&lt;strong&gt;Heroic Bloodshed&lt;/strong&gt; is a style of the &lt;a href="/genre/1"&gt;action film genre&lt;/a&gt;.&lt;/p&gt;',
    'de', '&lt;p&gt;&lt;strong&gt;Heroic Bloodshed&lt;/strong&gt; ist ein Stil des &lt;a href="/genre/1"&gt;Actionfilm Genres&lt;/a&gt;.&lt;/p&gt;'
  )
),
(
  COLUMN_CREATE('en', 'Martial Arts', 'de', 'Martial-Arts'),
  COLUMN_CREATE(
    'en', '&lt;p&gt;&lt;strong&gt;Marial arts&lt;/strong&gt; is a style of the &lt;a href="/genre/1"&gt;action film genres&lt;/a&gt;.&lt;/p&gt;',
    'de', '&lt;p&gt;&lt;strong&gt;Marial Arts&lt;/strong&gt; ist ein Stil des &lt;a href="/genre/1"&gt;Actionfilm Genres&lt;/a&gt;.&lt;/p&gt;'
  )
);
