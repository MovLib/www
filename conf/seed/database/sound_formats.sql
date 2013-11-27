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
-- Sound Format seed data.
--
-- @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

INSERT INTO `sound_formats` SET
  `id`					= 1,
  `dyn_names`  			= COLUMN_CREATE('en', 'Dolby Digital 5.1'),
  `dyn_descriptions`	= ''
;

INSERT INTO `sound_formats` SET
  `id`					= 2,
  `dyn_names`  			= COLUMN_CREATE('en', 'Dolby Digital 2.0 Stereo'),
  `dyn_descriptions`	= ''
;

INSERT INTO `sound_formats` SET
  `id`					= 3,
  `dyn_names`  			= COLUMN_CREATE('en', 'DTS-HD Master Audio 5.1'),
  `dyn_descriptions`	= ''
;

INSERT INTO `sound_formats` SET
  `id`					= 4,
  `dyn_names`  			= COLUMN_CREATE('en', 'DTS 2.0 Stereo'),
  `dyn_descriptions`	= ''
;

INSERT INTO `sound_formats` SET
  `id`					= 5,
  `dyn_names`  			= COLUMN_CREATE('en', 'Dolby Digital 5.1 EX'),
  `dyn_descriptions`	= ''
;

INSERT INTO `sound_formats` SET
  `id`					= 6,
  `dyn_names`  			= COLUMN_CREATE('en', 'Stereo'),
  `dyn_descriptions`	= ''
;
