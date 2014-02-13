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
$docRoot = dirname(dirname(__DIR__));
require "{$docRoot}/vendor/autoload.php";

// Most of the time plain text output is better.
header("content-type: text/plain");

// You can use the following construct to create an A/B benchmark.
define('LOOP', 10000);

function f1() {
  for ($i = 0; $i < LOOP; ++$i) {
    "";
  }
}

function f2() {
  for ($i = 0; $i < LOOP; ++$i) {
    '';
  }
}

$time1 = -microtime(true);
f1();
$time1 += microtime(true);

$time2 = -microtime(true);
f2();
$time2 += microtime(true);

echo "\n\n{$time1}\t{$time2}\n\n";
