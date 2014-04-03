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
namespace MovLib\Console\Command\Install;

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Translation related insatll tasks.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Translation extends \MovLib\Console\Command\AbstractCommand {


  /**
   * Cleaning up translation files.
   *
   * Removes document root out of translation files.
   */
  protected function cleanTranslationFiles() {
    $this->writeVerbose("Cleaning translation files..", self::MESSAGE_TYPE_INFO);
    $paths = [
      $this->fs->realpath("dr://var/intl/messages.pot"),
      $this->fs->realpath("dr://var/intl/plural.pot"),
    ];
    foreach ($this->intl->systemLocales as $code => $locale) {
      if ($code != $this->intl->defaultLanguageCode) {
        $paths[] = $this->fs->realpath("dr://var/intl/{$locale}/messages.po");
      }
    }
    foreach ($paths as $path) {
      file_put_contents($path, str_replace($this->fs->documentRoot, "", file_get_contents($path)));
    }

    return 0;
  }

  /**
   * Compile all translations.
   */
  protected function compile() {
    $this->writeVerbose("Compiling translations...", self::MESSAGE_TYPE_INFO);

    foreach ($this->intl->systemLocales as $code => $locale) {
      if ($code != $this->intl->defaultLanguageCode) {
        $translations = "";
        $poPath = $this->fs->realpath("dr://var/intl/{$locale}/messages.po");
        $fh = fopen($poPath, "rb");
        $lineNumber = 0;
        $comments   = "";
        while ($line = fgets($fh)) {
          ++$lineNumber;
          if (substr($line, 0, 3) === "#: ") {
            $comments .= $line;
            continue;
          }

          if (substr($line, 0, 6) == "msgid ") {
            $line   = trim($line);
            $msgid  = substr($line, 7, strlen($line) - 8);
            $line   = trim(fgets($fh));
            $msgstr = substr($line, 8, strlen($line) - 9);

            if (strpos($msgstr, "'") !== false) {
              throw new \LogicException("\"'\" not alowed in {$poPath} on line {$lineNumber}");
            }
            if (strpos($msgstr, '"') !== false) {
              throw new \LogicException("'\"' not alowed in {$poPath} on line {$lineNumber}");
            }
            if (strpos($msgstr, '$') !== false && isset($msgstr[strpos($msgstr, '$') + 1]) && $msgstr[strpos($msgstr, '$') + 1]!== " ") {
              throw new \LogicException("No PHP variable allowed in {$poPath} on line {$lineNumber}");
            }

            if (strpos($msgid, "'") !== false) {
              throw new \LogicException("\"'\" not alowed in: \n{$comments}");
            }
            if (strpos($msgid, '"') !== false) {
              throw new \LogicException("'\"' not alowed not alowed in: \n{$comments}");
            }
            if (strpos($msgid, '$') !== false && isset($msgid[strpos($msgid, '$') + 1]) && $msgid[strpos($msgid, '$') + 1]!== " ") {
              throw new \LogicException("No PHP variable allowed in: \n{$comments}");
            }

            $comments = "";
            if (empty($msgstr) || $msgid == $msgstr) {
              continue;
            }
            $translations .= "\"{$msgid}\"=>\"{$msgstr}\",";
          }
        }
        fclose($fh);
        file_put_contents("dr://var/intl/{$locale}/messages.php", "<?php return[{$translations}];");
      }
    }
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("translation");
    $this->setDescription("Perform various translation related tasks.");
    $this->addArgument("task", InputArgument::OPTIONAL, "extract or compile");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    // Array containing the names of all tasks that could be executed.
    $tasks = [
      "extract",
      "compile",
    ];

    if ($task = $input->getArgument('task')) {
      if (method_exists($this, $task)) {
        $this->$task();
      }
      else {
        $this->write("There is no task called '{$task}'.", self::MESSAGE_TYPE_ERROR);
      }
    }
    else {
      foreach ($tasks as $task) {
        $this->$task();
      }
    }

    return 0;
  }

  /**
   * Extract translations to po template and update po files.
   */
  protected function extract() {
    $this->extractPlural();

    $this->writeVerbose("Getting all translation keys from php files...", self::MESSAGE_TYPE_INFO);
    $this->xGetText();

    $this->writeVerbose("Updating po files for all languages.", self::MESSAGE_TYPE_INFO);
    foreach ($this->intl->systemLocales as $code => $locale) {
      if ($code != $this->intl->defaultLanguageCode) {
        $poPath  = $this->fs->realpath("dr://var/intl/{$locale}/messages.po");
        $potPath = $this->fs->realpath("dr://var/intl/messages.pot");
        $command = "msgmerge --update --no-wrap {$poPath} {$potPath}";
        $this->exec($command);
      }
    }

    $this->cleanTranslationFiles();

    return 0;
  }

  /**
   * Extract plural translations.
   */
  protected function extractPlural() {
    $this->writeVerbose("Expand plural translations", self::MESSAGE_TYPE_INFO);

    $potPath = $this->fs->realpath("dr://var/intl/plural.pot");
    $this->xGetText("tp", $potPath);

    $tCalls = PHP_EOL;
    $fh = fopen($potPath, "rb");
    while ($line = fgets($fh)) {
      if (substr($line, 0, 6) == "msgid ") {
        $line   = trim($line);
        $msgid  = substr($line, 7, strlen($line) - 8);
        if (!empty($msgid)) {
          $tCalls .= "t(\"{@count, plural, one{{$msgid}} other{{$msgid}}}\");".PHP_EOL;
        }
      }
    }
    fclose($fh);
    file_put_contents("dr://src/plural.php", "<?php {$tCalls}");

    return 0;
  }

  /**
   * Wrapper around xgettext.
   *
   * @param string $key [optional]
   *   The method key (e.g. t or tp).
   * @param string $potPath [optional]
   *   The URI where the po template file should be stored.
   * @param string $searchPath [optional]
   *   The URI of the source folder.
   */
  protected function xGetText($key = "t", $potPath = "dr://var/intl/messages.pot", $searchPath = "dr://src") {
    $potPath    = $this->fs->realpath($potPath);
    $searchPath = $this->fs->realpath($searchPath);

    $command = "find {$searchPath} -iname '*.php' | xargs xgettext";
    foreach ([
      "output"    => $potPath,
      "language"  => "PHP",
      "from-code" => "UTF-8",
      "keyword"   => $key,
      "no-wrap"   => null
    ] as $option => $arg) {
      if (isset($arg)) {
        $command .= " --{$option}=" . escapeshellarg($arg);
      }
      else {
        $command .= " --{$option}";
      }
    }
    $this->exec($command);

    return 0;
  }

}