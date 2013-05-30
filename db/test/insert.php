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
$mysqli = new mysqli("localhost");

$synopsis = <<<EOD
Egestas nam mauris a purr sleep on your face, purr elit hiss bibendum enim ut chuf tail flick. Tincidunt a run catnip faucibus rip the couch hairball, tortor vestibulum iaculis zzz jump on the table. Stuck in a tree stretching vulputate egestas, lick sleep on your face fluffy fur judging you quis judging you. Rhoncus iaculis eat the grass elit rhoncus, tail flick et ac hiss ac. Aliquam accumsan tail flick justo, scratched libero chase the red dot neque pellentesque orci turpis. Mauris a amet hiss adipiscing, leap pellentesque tortor bibendum stretching rip the couch.

Etiam accumsan toss the mousie vel, sleep in the sink neque tail flick faucibus I don't like that food accumsan biting libero. Puking chase the red dot egestas puking consectetur lick, knock over the lamp give me fish litter box chase the red dot. Meow tincidunt a vestibulum sleep on your keyboard, feed me nam elit litter box chuf litter box lay down in your way. Ac fluffy fur catnip vestibulum, vehicula cras nec rip the couch sniff enim sleep in the sink attack your ankles. Quis nunc bat sleep on your keyboard give me fish, litter box accumsan sleep in the sink feed me neque bat nam. Egestas hiss tincidunt a bibendum leap, neque purr nullam mauris a kittens. Elit vulputate enim jump on the table, bibendum meow bibendum jump puking attack your ankles vestibulum sleep on your keyboard. Give me fish nam iaculis lick accumsan sleep in the sink, bat stuck in a tree run climb the curtains give me fish.

Vestibulum non zzz lick, iaculis nam leap tortor tempus suscipit. Rutrum puking enim consectetur pellentesque, judging you bat lay down in your way libero shed everywhere shed everywhere tail flick. Chase the red dot leap faucibus ac, suspendisse jump mauris a suscipit vestibulum tortor. Etiam meow elit purr ac, rip the couch tristique run sleep in the sink mauris a enim ut bibendum. Rhoncus etiam chuf meow, sleep in the sink faucibus amet zzz knock over the lamp meow. Vel accumsan sleep on your keyboard catnip, tail flick lick tortor vehicula sleep in the sink attack enim ut fluffy fur. Climb the curtains sniff etiam enim egestas, I don't like that food mauris a vel nunc I don't like that food rhoncus. Nam sollicitudin purr judging you adipiscing, puking vel et libero sollicitudin.
EOD;
$synopsis = $mysqli->real_escape_string($synopsis);

$languages = ["de", "en", "nl", "es", "fr", "ja", "ru", "xx", "xy", "xz"];


$jsonSynopsis = [];
foreach ($languages as $l) {
  $jsonSynopsis[$l] = $synopsis;
}
$jsonSynopsis = $mysqli->real_escape_string(json_encode($jsonSynopsis));
$mysqli->close();

$fileHandles = [ "json" => fopen("json.sql", "a+"), "dynamic" => fopen("dynamic.sql", "a+"), "columns" => fopen("columns.sql", "a+"), "inheritance" => fopen("inheritance.sql", "a+") ];

foreach ($fileHandles as $handle) {
  fwrite($handle, "BEGIN;\n");
}
for ($i = 0; $i < 1000; ++$i) {
  fwrite($fileHandles["json"], "INSERT INTO json (`synopsis`) VALUES ('$jsonSynopsis');\n");

  fwrite($fileHandles["columns"], "INSERT INTO columns (`de`, `en`, `nl`, `es`, `fr`, `ja`, `ru`, `xx`, `xy`, `xz`) VALUES ('$synopsis', '$synopsis', '$synopsis', '$synopsis', '$synopsis', '$synopsis', '$synopsis', '$synopsis', '$synopsis', '$synopsis');\n");

  $id = $i + 1;
  fwrite($fileHandles["inheritance"], "INSERT INTO parent () VALUES ();\n");
  foreach ($languages as $l) {
    fwrite($fileHandles["inheritance"], "INSERT INTO child_$l (`parent_id`, `synopsis`) VALUES ($id, '$synopsis');\n");
  }

  fwrite($fileHandles["dynamic"], "INSERT INTO dynamic (`synopsis`) VALUES (COLUMN_CREATE('de', '$synopsis', 'en', '$synopsis', 'nl', '$synopsis', 'es', '$synopsis', 'fr', '$synopsis', 'ja', '$synopsis', 'ru', '$synopsis', 'xx', '$synopsis', 'xy', '$synopsis', 'xz', '$synopsis'));\n");
  if ($i % 100 == 0) {
    foreach ($fileHandles as $handle) {
      fwrite($handle, "COMMIT;\n");
      fwrite($handle, "BEGIN;\n");
    }
  }
}
foreach ($fileHandles as $handle) {
  fwrite($handle, "COMMIT;\n");
}