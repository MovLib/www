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
-- Ratings (movies + series) seed data.
--
-- @author Richard Fussenegger <richard@fussenegger.info>
-- @copyright © 2013 MovLib
-- @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
-- @link https://movlib.org/
-- @since 0.0.1-dev
-- ---------------------------------------------------------------------------------------------------------------------

TRUNCATE TABLE `movies_ratings`;

INSERT INTO `movies_ratings` (`movie_id`, `user_id`, `rating`) VALUES
(1, 1, 5), -- "Roundhay Garden Scene" must have 5 ;)
(2, 1, 4), -- "Big Buck Bunny"
(3, 1, 4), -- "The Shawshank Redemption"
(4, 1, 4); -- "Ichi, The Killer"

UPDATE `movies` SET `rating` = 0, `mean_rating` = 5, `votes` = 1 WHERE `id` = 1;
UPDATE `movies` SET `rating` = 0, `mean_rating` = 4, `votes` = 1 WHERE `id` = 2;
UPDATE `movies` SET `rating` = 0, `mean_rating` = 4, `votes` = 1 WHERE `id` = 3;
UPDATE `movies` SET `rating` = 0, `mean_rating` = 4, `votes` = 1 WHERE `id` = 4;
