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

use \MovLib\Core\Intl;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Extract translation strings from source files with xgettext.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class TranslationExtract extends \MovLib\Console\Command\AbstractCommand {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "TranslationExtract";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("translation-extract");
    $this->setDescription("Extract translation strings from source files with xgettext.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $potPath = $this->fs->realpath("dr://var/intl/messages.pot");
    $srcPath = $this->fs->realpath("dr://tmp/src");

    $this->writeVerbose("Creating temporary copy of source files to work with...", self::MESSAGE_TYPE_INFO);
    $this->exec("cp -r {$this->fs->realpath("dr://src")} {$this->fs->realpath("dr://tmp")}");

    $this->writeVerbose("Fixing embedded translations...", self::MESSAGE_TYPE_INFO);
    /* @var $fileinfo \SplFileInfo */
    foreach (new \RegexIterator($this->fs->getRecursiveIterator($srcPath), "/\.php$/") as  $fileinfo) {
      $path = $fileinfo->getPathname();
      $this->writeDebug("Fixing embedded translations in <comment>{$path}</comment>");
      $content = file_get_contents($path);
      $count = [];
      for ($i = 0; $i < 4; ++$i) {
        $content = preg_replace('/\{(\$[a-z0-9\$_\->]+)\((.*)\)(\s*)\}/isU', '" . $1($2) . $3"', $content, -1, $count[]);
      }
      $countSum = array_sum($count);
      $this->writeDebug("Fixed {$countSum} patterns in file...");
      file_put_contents($path, $content);
    }

    $this->writeVerbose("Expanding tp calls...", self::MESSAGE_TYPE_INFO);
    /* @var $fileinfo \SplFileInfo */
    foreach (new \RegexIterator($this->fs->getRecursiveIterator($srcPath), "/\.php$/") as  $fileinfo) {
      $path    = $fileinfo->getPathname();
      $content = file_get_contents($path);
      if (strpos($content, "->tp(") !== false) {
        $this->writeDebug("Expanding tp calls in <comment>{$path}</comment>");
        $content = preg_replace_callback('/->tp\("([^"].+)"(, "([^"].+)")?.*\)/isU', function ($matches) {
          if (empty($matches[2])) {
            $matches[2] = $matches[1];
          }
          return '->t("{0,plural,one{' . $matches[1] . '}other{' . $matches[2] . '}}")->t("' . $matches[1] . '")->t("' . $matches[2] . '")';
        }, $content, -1, $count);
        $this->writeDebug("Expanded {$count} tp calls in file...");
        file_put_contents($path, $content);
      }
    }


    $this->writeVerbose("Getting all translation keys from php files...", self::MESSAGE_TYPE_INFO);
    $command = "find {$srcPath} -iname '*.php' | xargs xgettext";
    foreach ([
      "output"             => $potPath,
      "language"           => "PHP",
      "from-code"          => "UTF-8",
      "keyword"            => "t",
      "no-wrap"            => null,
      "add-comments"       => "/",
      "package-name"       => "{$this->config->sitename} Messages",
      "package-version"    => $this->config->version,
      "msgid-bugs-address" => "https://github.com/MovLib/www/issues?labels=translation",
    ] as $option => $arg) {
      $command .= " --{$option}";
      if (isset($arg)) {
        $command .= "=" . escapeshellarg($arg);
      }
    }
    $this->exec($command);

    $this->writeVerbose("Deleting temporary copy of source files...", self::MESSAGE_TYPE_INFO);
    $this->fs->registerFileForDeletion("dr://tmp/src", true);

    $this->writeVerbose("Updating po files for all languages...", self::MESSAGE_TYPE_INFO);
    foreach (Intl::$systemLanguages as $code => $locale) {
      if ($code !== Intl::DEFAULT_CODE) {
        $poPath = $this->fs->realpath("dr://var/intl/{$code}/messages.po");
        if (file_exists($poPath)) {
          $this->exec("msgmerge --backup='off' --no-wrap --previous --update {$poPath} {$potPath}");
        }
        else {
          $this->exec("msginit --locale='{$locale}' --no-translator --no-wrap -i {$potPath} -o {$poPath}");
        }
        file_put_contents($poPath, str_replace("{$this->fs->documentRoot}/tmp", "", file_get_contents($poPath)));
      }
    }
    file_put_contents($potPath, str_replace("{$this->fs->documentRoot}/tmp", "", file_get_contents($potPath)));

    return 0;
  }

}
