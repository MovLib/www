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
namespace MovLib\Tool\Console\Command\Install;

use \Locale;
use \MovLib\Data\I18n;

/**
 * Create subtitle translations.
 *
 * @see \MovLib\Data\StreamWrapper\I18nStreamWrapper
 * @see \MovLib\Stub\Data\Subtitle
 * @see \MovLib\Presentation\Partial\Subtitle
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class SeedSubtitles extends SeedLanguages {

  /**
   * @inheritdoc
   * @global \MovLib\Tool\Kernel $kernel
   */
  protected function configure() {
    $this->setName("seed-subtitles");
    $this->setDescription("Create translations for all available subtitles.");
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function translate() {
    global $i18n;

    // Translate all available languages to the desired locale.
    $languages = [];
    foreach ($this->languageCodes as $languageCode) {
      $languages[$languageCode] = Locale::getDisplayLanguage($languageCode, $i18n->locale);
    }

    // Add the two special language codes.
    $languages[I18n::CODE_COMMENTARY] = $i18n->t("Commentary");
    $languages[I18n::CODE_FACT]       = $i18n->t("Facts");
    $languages[I18n::CODE_OTHER]      = $i18n->t("Other");

    // Prepare search array which helps us to identify which special language codes don't have a native translation.
    $noNative = [ I18n::CODE_COMMENTARY, I18n::CODE_FACT, I18n::CODE_OTHER ];

    // Sort the translated language according to their translated names.
    $i18n->getCollator()->asort($languages);

    $translations = null;
    foreach ($languages as $languageCode => $name) {
      // Add the native language's name to the output if applicable.
      if (in_array($languageCode, $noNative)) {
        $native = "null";
      }
      else {
        $native = Locale::getDisplayLanguage($languageCode, $languageCode);
      }

      // Put it together.
      $translations .= '"' . $languageCode . '"=>(object)["code"=>"' . $languageCode . '","name"=>"' . $name . '","native"=>"' . $native . '","closed"=>null,"forced"=>true],';
    }

    return $translations;
  }

}
