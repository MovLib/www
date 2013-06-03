#!/usr/bin/env php
<?php

/*!
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

/**
 * Translation extractor and importer console application.
 *
 * The Intl ICU software family is missing a program like xgettext for extracting translations from source code.
 * Evaluations have shown that we are not able to use xgettext to extract the strings from our sources because the
 * parser is not flexible enough to understand all kinds of nestings that are possible with PHP code. Therefor we've
 * decided to write our own parser.
 *
 * <b>IMPORTANT!</b> All calls to translatable methods must be made by using a variable that is called <var>$i18n</var>,
 * otherwise the extractor will not be able to find the calls in the source code (there are exceptions, have a look at
 * this file).
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */

/*DEBUG{{{*/
error_reporting(-1);
ini_set("display_errors", 1);
/*}}}DEBUG*/

/**
 * Extend the PHP platform with a recursive glob function.
 *
 * @see glob()
 * @link http://www.php.net/manual/en/function.glob.php#87221
 * @param string $pattern
 *   The pattern. No tilde expansion or parameter substitution is done.
 * @param string $path
 *   Absolute path to the directory in which should be searched for files that match the given pattern.
 * @return array
 *   Returns an array containing the matched files/directories, an empty array if no file matched or <tt>FALSE</tt> on error.
 */
function rglob($pattern, $path) {
  $files = glob($path . $pattern);
  foreach (glob("{$path}*", GLOB_MARK|GLOB_ONLYDIR|GLOB_NOSORT) as $p) {
    $files = array_merge($files, rglob($pattern, $p));
  }
  return $files;
}

/**
 * @todo Documentation
 */
class TranslationExtractor {

  /**
   * @todo Documentation
   */
  private $patterns = [ "viewA", "i18nR", "i18nT" ];

  /**
   * @todo Documentation
   */
  private $viewApattern = '$this->a(';

  /**
   * @todo Documentation
   */
  private $viewAposition = false;

  /**
   * @todo Documentation
   */
  private $i18nRpattern = '$this->r(';

  /**
   * @todo Documentation
   */
  private $i18nRposition = false;

  /**
   * @todo Documentation
   */
  private $i18nTpattern = '$this->t(';

  /**
   * @todo Documentation
   */
  private $i18nTposition = false;

  /**
   * @todo Documentation
   */
  private $fileContent;

  /**
   * @todo Documentation
   */
  public function __construct() {
    foreach (rglob("*.php", dirname(__DIR__) . "/src") as $file) {
      $this->fileContent = str_replace([ '$i18n->r(', '$i18n->t(' ], [ '$this->r(', '$this->t(' ], file_get_contents($file));
      do {
        $continue = false;
        foreach ($this->patterns as $pattern) {
          if (($this->{"{$pattern}position"} = strpos($this->fileContent, $this->{"{$pattern}pattern"})) !== false) {
            $this->extractAndCall($pattern, $this->{"{$pattern}position"});
            $this->{"{$pattern}position"} = false;
            $continue = true;
            break;
          }
        }
      } while ($continue);
    }
  }

  /**
   * @todo Documentation
   */
  private function extractAndCall($pattern, $position) {
    // Truncate the file's content.
    $this->fileContent = mb_substr($this->fileContent, $position);
    // Count the remaining characters.
    $contentLength = mb_strlen($this->fileContent);
    // We have one opening bracket.
    $openingBrackets = 1;
    // And no closing brackets.
    $closingBrackets = 0;
    // Skip the method pattern in the upcoming loop. No need for multi-byte function.
    $i = strlen($pattern);
    // Iterate over the files content; character by character.
    for (; $i < $contentLength; ++$i) {
      // Increase counters if we encounter any of their characters.
      $this->fileContent[$i] === "(" && ++$openingBrackets;
      $this->fileContent[$i] === ")" && ++$closingBrackets;
      // We are done parsing this call as soon as opening and closing brackets are equal.
      if ($openingBrackets === $closingBrackets) {
        // Add one to the current position within the files content, this is for the last closing bracket.
        $position = $i + 1;
        // Extract the call from the file.
        $call = mb_substr($this->fileContent, 0, $position);
        // Remove the args array from the i18n calls.
        if ($pattern !== $this->viewApattern && ($argsStart = strpos($this->fileContent, "[")) !== false) {
          $this->removeArgs($call, $argsStart);
        }
        // @todo Remove args arrays from calls to the view method arrays.
        else {

        }
        if ($this->fileContent[10] !== "$") {
//          eval("{$call};");
          echo "{$call};\n";
        }
        break;
      }
    }
    // Truncate the file's content again and remove the call we just handled.
    $this->fileContent = mb_substr($this->fileContent, $position);
    // Go and test the rest of the file.
    $this->testTruncateAndCall();
  }

  /**
   * @todo Documentation
   * @todo This is not working yet!
   */
  private function removeArgs(&$call, $start) {
      // We have one opening square bracket.
      $openingSquareBrackets = 1;
      // We have no closing square bracket.
      $closingSquareBrackets = 0;
      // Count the call characters.
      $callLength = mb_strlen($call);
      // Iterate over the call and collect all square brackets.
      for ($j = 0; $j < $callLength; ++$j) {
        $call[$j] === "[" && ++$openingSquareBrackets;
        $call[$j] === "]" && ++$closingSquareBrackets;
        if ($openingSquareBrackets === $closingSquareBrackets) {
          break;
        }
      }
      $start -= mb_strrpos(mb_substr($call, 0, $start), ",");
      echo mb_substr($call, 0, $start) . mb_substr($call, $j, $callLength) . PHP_EOL;
//      $call = mb_substr($call, 0, $argsStart) . mb_substr($call, $j, $callLength);
//      echo "{$call};\n";
  }

  /**
   * @todo Documentation
   */
  private function a($route, $text) {
    echo "Calling a()\n";
    var_dump($route);
    var_dump($text);
    echo "\n";
  }

  /**
   * @todo Documentation
   */
  private function r($route, $comment = null, $oldRoute = null) {
    echo "Calling r()\n";
    var_dump($route);
    var_dump($comment);
    var_dump($oldRoute);
    echo "\n";
  }

  /**
   * @todo Documentation
   */
  private function t($message, $comment = null, $oldMessage = null) {
    echo "Calling t()\n";
    var_dump($message);
    var_dump($comment);
    var_dump($oldMessage);
    echo "\n";
  }

}

// Start the application.
new TranslationExtractor();
