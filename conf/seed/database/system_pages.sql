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
-- System pages seed data.
--
-- @author Richard Fussenegger <richard@fussenegger.info>
-- @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

TRUNCATE TABLE `system_pages`;

-- Contact

INSERT INTO `system_pages` SET
  `dyn_titles` = COLUMN_CREATE(
    'en', 'Contact',
    'de', 'Kontakt'
  ),
  `dyn_texts`  = COLUMN_CREATE(
    'en', '',
    'de', ''
  )
;

-- Imprint

INSERT INTO `system_pages` SET
  `dyn_titles` = COLUMN_CREATE(
    'en', 'Imprint',
    'de', 'Impressum'
  ),
  `dyn_texts`  = COLUMN_CREATE(
    'en', 'English imprint',
    'de', '&lt;p&gt;Ein &lt;strong&gt;Impressum&lt;/strong&gt; ist eine gesetzlich vorgeschriebene Angabe zu den Eigentümern und Urhebern einer Websites, die in Deutschland, und bestimmten anderen deutschsprachigen Ländern wie Österreich und der Schweiz, veröffentlicht wurde.&lt;/p&gt;&lt;dl class=&apos;horizontal&apos;&gt;&lt;dt&gt;Vereinsname&lt;/dt&gt;&lt;dd&gt;MovLib&lt;/dd&gt;&lt;/dl&gt;'
  )
;

-- Privacy Policy

INSERT INTO `system_pages` SET
  `dyn_titles` = COLUMN_CREATE(
    'en', 'Privacy Policy',
    'de', 'Datenschutzerklärung'
  ),
  `dyn_texts`  = COLUMN_CREATE(
    'en', '',
    'de', ''
  )
;

-- Team

INSERT INTO `system_pages` SET
  `dyn_titles` = COLUMN_CREATE(
    'en', 'Team',
    'de', 'Team'
  ),
  `dyn_texts`  = COLUMN_CREATE(
    'en', '',
    'de', ''
  )
;

-- Terms of Use

INSERT INTO `system_pages` SET
  `dyn_titles` = COLUMN_CREATE(
    'en', 'Terms of Use',
    'de', 'Nutzungsbedingungen'
  ),
  `dyn_texts`  = COLUMN_CREATE(
    'en', '',
    'de', ''
  )
;
