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

INSERT INTO `system_pages` (`dyn_titles`, `dyn_texts`) VALUES
  (COLUMN_CREATE('en', 'Contact', 'de', 'Kontakt'), '' ),
  (COLUMN_CREATE('en', 'Imprint', 'de', 'Impressum'), COLUMN_CREATE('en', 'English imprint', 'de', 'Deutsches Impressum')),
  (COLUMN_CREATE('en', 'Privacy Policy', 'de', 'Datenschutzerklärung'), ''),
  (COLUMN_CREATE('en', 'Team', 'de', 'Team'), ''),
  (COLUMN_CREATE('en', 'Terms of Use', 'de', 'Nutzungsbedingungen'), '')
;
