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
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Compile translation strings from PO to PHP files.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class TranslationCompile extends \MovLib\Console\Command\AbstractCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("translation-compile");
    $this->setDescription("Compile translation strings from PO to PHP files.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->writeVerbose("Compiling translations...", self::MESSAGE_TYPE_INFO);
    foreach ($this->intl->systemLocales as $code => $locale) {
      $matches = null;
      if ($code != $this->intl->defaultLanguageCode) {
        $c = preg_match_all('/msgid\s+((?:".*(?<!\\\\)"\s*)+)\s+msgstr\s+((?:".*(?<!\\\\)"\s*)+)/', file_get_contents("dr://var/intl/{$locale}/messages.po"), $matches);
        $translations = "<?php return[";
        for ($i = 0; $i < $c; ++$i) {
          $msgid = $this->prepare($matches[1][$i]);
          if (empty($msgid)) {
            continue;
          }
          $msgstr = $this->prepare($matches[2][$i]);
          if (empty($msgstr)) {
            continue;
          }
          if ($msgid == $msgstr) {
            continue;
          }
          $translations .= "\"{$msgid}\"=>\"{$msgstr}\",";
        }
        file_put_contents("dr://var/intl/{$locale}/messages.php", rtrim($translations, ",") . "];");
      }
    }
    return 0;
  }

  /**
   * Prepare a PO file string for PHP array conversion.
   *
   * @staticvar array $patterns
   *   Array containing regular expression patterns to replace.
   * @staticvar array $replacements
   *   Array containing replacements patterns.
   * @param string $string
   *   The PO file string to prepare.
   * @return string
   *   The prepared PO file string for inclusion in a PHP file.
   * @throws \LogicException
   */
  protected function prepare($string) {
    static $patterns = [ '/"\s+"/', '/\\\\n/', '/\\\\r/', '/\\\\t/', '/\\\\"/', '/\\\\\\\\/' ];
    static $replacements = [ "", "\n", "\r", "\t", '"', "\\" ];
    $prepared = (string) preg_replace($patterns, $replacements, substr(rtrim($string), 1, -1));
    if (strip_tags($prepared) != $prepared) {
      throw new \LogicException("HTML isn't allowed in translations ({$prepared}).");
    }
    if (strpos($prepared, "'") !== false || strpos($prepared, '"') !== false) {
      throw new \LogicException("Quotes (double and single) aren't allowed in translations ({$prepared}).");
    }
    if (($pos = strpos($prepared, "$")) !== false && isset($prepared{++$pos}) && $prepared{$pos} != " " && !is_numeric($prepared{$pos})) {
      throw new \LogicException("PHP variables aren't allowed in translations ({$prepared}).");
    }
    return $prepared;
  }

}
