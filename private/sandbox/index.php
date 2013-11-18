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
 * Special file that can be used by developers to test stuff.
 *
 * This file (and files in this directory) can only be executed via the secured tools domain.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */

// Make sure any errors are displayed.
ini_set("display_errors", true);

// Include the composer autoloader for easy class loading.
/**/
$docRoot = dirname(dirname(__DIR__));
require "{$docRoot}/vendor/autoload.php";
//*/

// Converts all PHP errors to exception, you have to uncomment the autoloader for this to work!
/*/
new \MovLib\Exception\Handlers();
//*/

// Most of the time plain text output is better.
/**/
header("content-type: text/plain");
//*/

class Foo extends \MovLib\Data\Database {

  public function __construct() {
    $this->query(
      "SELECT
        `m1`.`movie_id` AS `id`,
        IFNULL(`title`.`title`, `m1`.`original_title`) AS `displayTitle`,
        `m1`.`original_title` AS `originalTitle`,
        `m1`.`year` AS `year`
      FROM `movies` AS `m1`
        LEFT JOIN `movies_titles` AS `title` ON `title`.`movie_id` = `m1`.`movie_id`
      WHERE
        `m1`.`deleted` = false
        AND `title`.`is_display_title` = true

      UNION ALL

      SELECT
        `image`.`ext` AS `imageExtension`,
        UNIX_TIMESTAMP(`image`.`changed`) AS `imageChanged`,
        `image`.`changed` IS NOT NULL AS `imageExists`
      FROM `movies` AS `m2`
        LEFT JOIN `movies_images` AS `image` ON `image`.`movie_id` = `m2`.`movie_id`
      WHERE
        `m2`.`deleted` = false
        AND `image`.`image_id` = (
          SELECT `i`.`image_id`
          FROM `movies_images` AS `i`
          WHERE `i`.`movie_id` = `m2`.`movie_id`
          ORDER BY `i`.`upvotes` DESC
          LIMIT 1
        )

      ORDER BY `m1`.`created` DESC
      LIMIT 0, 30"
    )->close();
  }

}

class Bar extends \MovLib\Data\Database {

  public function __construct() {
    $this->query(
      "SELECT
        `movie`.`movie_id` AS `id`,
        IFNULL(`title`.`title`, `movie`.`original_title`) AS `displayTitle`,
        `movie`.`original_title` AS `originalTitle`,
        `movie`.`year`
      FROM `movies` AS `movie`
        LEFT JOIN `movies_titles` AS `title` ON `title`.`movie_id` = `movie`.`movie_id`
      WHERE
        `movie`.`deleted` = false
        AND `title`.`is_display_title` = true
      ORDER BY `movie`.`created` DESC
      LIMIT 0, 30"
    )->close();
    $this->query(
      "SELECT
        `image`.`ext` AS `imageExtension`,
        UNIX_TIMESTAMP(`image`.`changed`) AS `imageChanged`,
        `image`.`changed` IS NOT NULL AS `imageExists`
      FROM `movies` AS `movie`
        LEFT JOIN `movies_images` AS `image` ON `image`.`movie_id` = `movie`.`movie_id`
      WHERE
        `movie`.`deleted` = false
        AND `image`.`image_id` = (
          SELECT `i`.`image_id`
          FROM `movies_images` AS `i`
          WHERE `i`.`movie_id` = `movie`.`movie_id`
          ORDER BY `i`.`upvotes` DESC
          LIMIT 1
        )
      ORDER BY `movie`.`created` DESC
      LIMIT 0, 30"
    );
  }

}

// You can use the following construct to create an A/B benchmark.
/**/
define('LOOP', 10);

function f1() {
  for ($i = 0; $i < LOOP; ++$i) {
    new Foo();
  }
}

function f2() {
  for ($i = 0; $i < LOOP; ++$i) {
    new Bar();
  }
}

$time1 = -microtime(true);
f1();
$time1 += microtime(true);

$time2 = -microtime(true);
//f2();
$time2 += microtime(true);

echo "\n\n{$time1}\t{$time2}\n\n";
//*/
