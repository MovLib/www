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

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "TranslationCompile";
  // @codingStandardsIgnoreEnd

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
    // Let the user now that compilation started.
    $this->writeVerbose("Compiling message translations...", self::MESSAGE_TYPE_INFO);

    // Go through all available system languages and compile the messages.
    foreach (Intl::$systemLanguages as $code => $locale) {
      // Build the absolute URI to the file that contains the translated messages.
      $po = "dr://var/intl/{$code}/messages.po";

      // Skip this language if no messages are available that contain translations.
      if (!file_exists($po)) {
        $this->writeVerbose("Skipping <comment>{$code}</comment> no PO file found...");
        continue;
      }

      // Extract all translations from the messages file.
      $this->writeVerbose("Compiling <comment>{$code}</comment>...");
      $c = preg_match_all('/msgid\s+((?:".*(?<!\\\\)"\s*)+)\s+msgstr\s+((?:".*(?<!\\\\)"\s*)+)/', file_get_contents($po), $matches);

      // Start building the PHP array.
      $translations = null;
      for ($i = 0; $i < $c; ++$i) {
        // Prepare the message's source string and check if it's empty. The source string is empty after preparation if
        // it contains the PO file header.
        $msgid = $this->prepare($matches[1][$i]);
        if (empty($msgid)) {
          continue;
        }

        // Prepare the message's translation string and skip this translation if the string is empty or equals the
        // source string. There's no need to include this translation in the compiled PHP array if it equals the source
        // string because we can directly use the source string itself to format the message.
        $msgstr = $this->prepare($matches[2][$i]);
        if (empty($msgstr) || $msgid === $msgstr) {
          continue;
        }

        // Append array entry separator if we already have translations and append this translation.
        $translations && ($translations .= ",");
        $translations .= "\"{$msgid}\"=>\"{$msgstr}\"";
      }

      // Only export the compiled translations if at least a single message differs from the source.
      if ($translations) {
        file_put_contents("dr://var/intl/{$code}/messages.php", "<?php return[{$translations}];");
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

    // Replace all strings that might be within the translated string because of the PO file format.
    $prepared = (string) preg_replace($patterns, $replacements, substr(rtrim($string), 1, -1));

    // Make sure that the translated message doesn't contain HTML tags.
    if (strip_tags($prepared) !== $prepared) {
      throw new \LogicException("HTML isn't allowed in translations ({$prepared}).");
    }

    // Make sure that the translated message doesn't contain computer quotes.
    if (strpos($prepared, "'") !== false || strpos($prepared, '"') !== false) {
      throw new \LogicException("Quotes (double and single) aren't allowed in translations ({$prepared}).");
    }

    // Make sure that the translated message doesn't contain PHP variables.
    if (($pos = strpos($prepared, "$")) !== false && isset($prepared{++$pos}) && $prepared{$pos} !== " " && !is_numeric($prepared{$pos})) {
      throw new \LogicException("PHP variables aren't allowed in translations ({$prepared}).");
    }

    return $prepared;
  }

}
