<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link https://movlib.org/ MovLib}.
 *
 * MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with MovLib.
 * If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */

/**
 * Generate forums seed data.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */

// We want to insert exactly as many forums into each table as we have defined in code.
$insert = null;
foreach (new \RegexIterator($this->fs->getRecursiveIterator("dr://src/MovLib/Data/Forum"), "/[0-9]+\.php$/") as $fileinfo) {
  $insert && ($insert .= ",");
  $insert .= "()";
}
$insert && ($insert = "INSERT INTO `de_forum` () VALUES {$insert};");

$firstPostCreated = (string) new \MovLib\Component\DateTime();

return <<<SQL
TRUNCATE TABLE `de_forum`;
TRUNCATE TABLE `de_topic`;
TRUNCATE TABLE `de_post`;

{$insert}

INSERT INTO `de_topic` (`created`, `first_post_id`, `first_post_username`, `forum_id`, `title`) VALUES
  ('{$firstPostCreated}', 1, 'Fleshgrinder', 1, 'MovLib Open Beta');

INSERT INTO `de_post` () VALUES ();

UPDATE `de_forum` SET
  `last_post_created`  = '{$firstPostCreated}',
  `last_post_id`       = 1,
  `last_post_username` = 'Fleshgrinder',
  `last_topic_id`      = 1,
  `last_topic_title`   = 'MovLib Open Beta'
;
SQL;
