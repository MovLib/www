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
-- @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

TRUNCATE TABLE `protected_pages_routes`;
TRUNCATE TABLE `protected_pages`;

-- START "Contact"

INSERT INTO `protected_pages` SET
  `dyn_titles` = COLUMN_CREATE('en', 'Contact', 'de', 'Kontakt'),
  `dyn_texts`  = ''
;

SET @contact_id = LAST_INSERT_ID();

INSERT INTO `protected_pages_routes` SET
  `route`                 = 'contact',
  `protected_page_id`     = @contact_id,
  `system_language_code`  = 'en'
;

INSERT INTO `protected_pages_routes` SET
  `route`                 = 'kontakt',
  `protected_page_id`     = @contact_id,
  `system_language_code`  = 'de'
;

-- END "Contact"

-- ---------------------------------------------------------------------------------------------------------------------

-- START "Imprint"

INSERT INTO `protected_pages` SET
  `dyn_titles` = COLUMN_CREATE('en', 'Imprint', 'de', 'Impressum'),
  `dyn_texts`  = COLUMN_CREATE('en', 'English imprint', 'de', 'Deutsches Impressum')
;

SET @imprint_id = LAST_INSERT_ID();

INSERT INTO `protected_pages_routes` SET
  `route`                 = 'imprint',
  `protected_page_id`     = @imprint_id,
  `system_language_code`  = 'en'
;

INSERT INTO `protected_pages_routes` SET
  `route`                 = 'impressum',
  `protected_page_id`     = @imprint_id,
  `system_language_code`  = 'de'
;

-- END "Imprint"

-- ---------------------------------------------------------------------------------------------------------------------

-- START "Privacy Policy"

INSERT INTO `protected_pages` SET
  `dyn_titles` = COLUMN_CREATE('en', 'Privacy Policy', 'de', 'Datenschutzerklärung'),
  `dyn_texts`  = ''
;

SET @privacy_policy_id = LAST_INSERT_ID();

INSERT INTO `protected_pages_routes` SET
  `route`                 = 'privacy-policy',
  `protected_page_id`     = @privacy_policy_id,
  `system_language_code`  = 'en'
;

INSERT INTO `protected_pages_routes` SET
  `route`                 = 'datenschutzerklärung',
  `protected_page_id`     = @privacy_policy_id,
  `system_language_code`  = 'de'
;

-- END "Privacy Policy"

-- ---------------------------------------------------------------------------------------------------------------------

-- START "Team"

INSERT INTO `protected_pages` SET
  `dyn_titles` = COLUMN_CREATE('en', 'Team', 'de', 'Team'),
  `dyn_texts`  = ''
;

SET @team_id = LAST_INSERT_ID();

INSERT INTO `protected_pages_routes` SET
  `route`                 = 'team',
  `protected_page_id`     = @team_id,
  `system_language_code`  = 'en'
;

INSERT INTO `protected_pages_routes` SET
  `route`                 = 'team',
  `protected_page_id`     = @team_id,
  `system_language_code`  = 'de'
;

-- END "Team"

-- ---------------------------------------------------------------------------------------------------------------------

-- START "Terms of Use"

INSERT INTO `protected_pages` SET
  `dyn_titles` = COLUMN_CREATE('en', 'Terms of Use', 'de', 'Nutzungsbedingungen'),
  `dyn_texts`  = ''
;

SET @terms_of_use_id = LAST_INSERT_ID();

INSERT INTO `protected_pages_routes` SET
  `route`                 = 'terms-of-use',
  `protected_page_id`     = @terms_of_use_id,
  `system_language_code`  = 'en'
;

INSERT INTO `protected_pages_routes` SET
  `route`                 = 'nutzungsbedingungen',
  `protected_page_id`     = @terms_of_use_id,
  `system_language_code`  = 'de'
;

-- END "Terms of Use"
