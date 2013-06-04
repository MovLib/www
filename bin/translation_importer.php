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
  private $patterns = [ "viewApattern", "i18nRpattern", "i18nTpattern" ];

  /**
   * @todo Documentation
   */
  private $viewApattern = '$this->a(';

  /**
   * @todo Documentation
   */
  private $i18nRpattern = '$this->r(';

  /**
   * @todo Documentation
   */
  private $i18nTpattern = '$this->t(';

  /**
   * @todo Documentation
   */
  private $fileContent;

  /**
   * @todo Documentation
   */
  public function __construct() {
    // Go through all source files.
    foreach (rglob("*.php", dirname(__DIR__) . "/src") as $file) {
      // Get the content of this file without comments and unnecessary whitespaces.
      $this->fileContent = php_strip_whitespace($file);
      // Replace the i18n calls with calls to our own class. This has the nice sideffect hat calls within the i18n to
      // itself are also catched.
      $this->fileContent = str_replace([ '$i18n->r(', '$i18n->t(' ], [ '$this->r(', '$this->t(' ], $this->fileContent);
      // We use a break to end this loop.
      while (true) {
        // Initially we have no pattern an invalid position.
        $pattern = null;
        $position = PHP_INT_MAX;
        // Iterate over all patterns we have.
        foreach ($this->patterns as $tmpPattern) {
          // Check if this pattern occurres somewhere within this files content and store its position if it does.
          if (($tmpPosition = strpos($this->fileContent, $this->{$tmpPattern})) !== false && $tmpPosition < $position) {
            $position = $tmpPosition;
            $pattern = $tmpPattern;
          }
        }
        if ($position < PHP_INT_MAX) {
          // If we have a valid position extract it and start over again with the remaining file content.
          $this->extractAndCall($pattern, $position);
        } else {
          // If we have no position after checking all patterns, break out of the while loop.
          break;
        }
      }
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
    $i = strlen($this->{$pattern});
    // The minimum method call is the pattern plus two brackets.
    $callMinLength = $i + 2;
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
        switch ($pattern) {
          case "viewApattern":
            // @todo Remove args arrays from parameters.
            // $this->a([ "/user/{0,number,integer}", [ $userId ], "comment", "oldRoute" ], [ "Hello {0}", [ $userName ], "comment", "oldMessage" ], $anchorArgs);
            break;

          case "i18nRpattern":
          case "i18nTpattern":
            // If we have any arguments, remove them from the method call.
            // @todo What if the [ is in the comment?
            if (( $argsStart = strpos($call, "[")) !== false) {
              $tmpCall = mb_substr($call, 0, $argsStart);
              $tmpCall = mb_substr($tmpCall, 0, mb_strrpos($tmpCall, ","));
              $call = $tmpCall . mb_substr($call, mb_strrpos($call, "]") + 1);
            }
            // Is there any method left to call?
            if (mb_strlen($call) > $callMinLength) {
              $tokens = token_get_all("<?php {$call}");
              if (count($tokens) > 5 && is_array($tokens[5]) && $tokens[5][0] === T_CONSTANT_ENCAPSED_STRING) {
                eval("{$call};");
              }
            }
            break;
        }
        // Truncate the file's content again and remove the call we just handled.
        $this->fileContent = mb_substr($this->fileContent, $position);
        // This call was handled, break and return to search for the next pattern.
        break;
      }
    }
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
