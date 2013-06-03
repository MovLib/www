#!/usr/bin/env php
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

ini_set('display_errors',1);
error_reporting(-1);

class I18n {
  public function r($pattern, $args = null, $comment = null) {
    echo "Calling i18n->r:" . PHP_EOL;
    echo "Pattern: $pattern" . PHP_EOL . "Args: " . var_dump($args) . PHP_EOL . $comment . PHP_EOL;
  }

  public function t($pattern, $args = null, $comment = null) {

  }

}

global $i18n;
$i18n  = new I18n();

class TranslationImporter {

  public function a($route, $text, $titleOrAttributes = false) {
    echo "Calling this->a:" . PHP_EOL . "Route: $route" . PHP_EOL . "Text: $text" . PHP_EOL . "TitleOrAttr: " . var_dump($titleOrAttributes) . PHP_EOL;
  }

  public function import() {
    global $i18n;
    $file = dirname(__DIR__) . "/src/MovLib/View/HTML/Movie/TranslationTestView.php";
//    foreach ($this->rglob("*.php", dirname(__DIR__) . "/src") as $file) {
      $file = file_get_contents($file);
      $i18nTPos = strpos($file, "\$i18n->t(");
      $i18nRPos = strpos($file, "\$i18n->r(");
      $aPos = strpos($file, "\$this->a(");
      while ($i18nTPos !== false || $i18nRPos !== false || $aPos !== false) {
        $pos = min([ $i18nTPos, $i18nRPos, $aPos ]);
        $file = mb_substr($file, $pos);
        $lp = 1;
        $rp = 0;
        $fileArray = str_split($file);
        foreach ($fileArray as $delta => $char) {
          if ($delta < 9) {
            continue;
          }
          if ($char == "(") {
            $lp++;
          }
          if ($char == ")") {
            $rp++;
          }
          if ($lp === $rp) {
            $pos = $delta;
            eval(mb_substr($file, 0, $pos + 1) . ";");
            break;
          }
        }

        $file = mb_substr($file, $pos + 1);
        $i18nTPos = strpos($file, "\$i18n->t(");
        $i18nRPos = strpos($file, "\$i18n->r(");
        $aPos = strpos($file, "\$this->a(");
        break;
      }
//    }

  }

  private function rglob($pattern, $path) {
    $files = glob($path . $pattern);
    foreach (glob("{$path}*", GLOB_MARK|GLOB_ONLYDIR|GLOB_NOSORT) as $p) {
      $files = array_merge($files, $this->rglob($pattern, $p));
    }
    return $files;
  }
}


$translationImporter = new TranslationImporter();
$translationImporter->import();