<?php

/*
 *  This file is part of {@link https://github.com/MovLib MovLib}.
 *
 *  Copyright © 2013-present {@link http://movlib.org/ MovLib}.
 *
 *  MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 *  License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 *  version.
 *
 *  MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 *  of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License along with MovLib.
 *  If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */

ini_set('display_errors', 1);
error_reporting(-1);

$mysqli = new mysqli("localhost");
$mysqli->select_db("test");

$languages = ["de", "en", "nl", "es", "fr", "ja", "ru", "xx", "xy", "xz"];
function getRandomLanguage($languages) {
  return $languages[mt_rand(0, 9)];
}

$times = [ "columns" => [], "dynamic" => [], "inheritance" => [], "json" => [] ];

for ($i = 1; $i <= 1001; ++$i) {
  $lang = getRandomLanguage($languages);

  // Columns
  $start = microtime(true);
  $stmt = $mysqli->prepare("SELECT `id`, `{$lang}` FROM columns WHERE id = {$i} LIMIT 1");
  $stmt->execute();
  $result = $stmt->get_result();
  $result->fetch_assoc();
  $times["columns"][] = microtime(true) - $start;

  // Dynamic
  $start = microtime(true);
  $stmt = $mysqli->prepare("SELECT `id`, COLUMN_GET(synopsis, '{$lang}' as BINARY) AS synopsis FROM dynamic WHERE id = {$i} LIMIT 1");
  $stmt->execute();
  $result = $stmt->get_result();
  $result->fetch_assoc();
  $times["dynamic"][] = microtime(true) - $start;

  // Inheritance
  $start = microtime(true);
  $stmt = $mysqli->prepare("SELECT p.`id`, c.`synopsis` FROM parent p INNER JOIN child_{$lang} c ON p.id = c.parent_id WHERE p.id = {$i} LIMIT 1");
  $stmt->execute();
  $result = $stmt->get_result();
  $result->fetch_assoc();
  $times["inheritance"][] = microtime(true) - $start;

  // JSON
  $start = microtime(true);
  $stmt = $mysqli->prepare("SELECT `id`, `synopsis` FROM json WHERE id = {$i} LIMIT 1");
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_assoc();
  $data["synopsis"] =  json_decode($data["synopsis"], true);
  $times["json"][] = microtime(true) - $start;
}

echo "Benchmark results:\n\n";
echo "Columns:\n\tMin: " . min($times["columns"]) . "\n\tMax: " . max($times["columns"]) . "\n\tAvg: " . array_sum($times["columns"]) / count($times["columns"]) . "\n\n";
echo "Dynamic:\n\tMin: " . min($times["dynamic"]) . "\n\tMax: " . max($times["dynamic"]) . "\n\tAvg: " . array_sum($times["dynamic"]) / count($times["dynamic"]) . "\n\n";
echo "Inheritance:\n\tMin: " . min($times["inheritance"]) . "\n\tMax: " . max($times["inheritance"]) . "\n\tAvg: " . array_sum($times["inheritance"]) / count($times["inheritance"]) . "\n\n";
echo "JSON:\n\tMin: " . min($times["json"]) . "\n\tMax: " . max($times["json"]) . "\n\tAvg: " . array_sum($times["json"]) / count($times["json"]) . "\n\n";