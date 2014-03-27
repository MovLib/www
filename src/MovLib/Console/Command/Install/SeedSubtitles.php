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

/**
 * Seed subtitles.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class SeedSubtitles extends \MovLib\Console\Command\Install\SeedLanguages {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("seed-subtitles");
    $this->setDescription("Seed subtitles");
  }

  /**
   * {@inheritdoc}
   */
  protected function translate() {
    // Translate all available languages to the desired locale.
    $languages = [];
    foreach ($this->codes as $code) {
      $languages[$code] = \Locale::getDisplayLanguage($code, $this->intl->locale);
    }

    // Add the two special language codes.
    $languages[Intl::CODE_COMMENTARY] = $this->intl->t("Commentary");
    $languages[Intl::CODE_FACT]       = $this->intl->t("Facts");
    $languages[Intl::CODE_OTHER]      = $this->intl->t("Other");

    // Prepare search array which helps us to identify which special language codes don't have a native translation.
    $noNative = [ Intl::CODE_COMMENTARY, Intl::CODE_FACT, Intl::CODE_OTHER ];

    // Sort the translated language according to their translated names.
    $this->intl->getCollator()->asort($languages);

    $translations = null;
    foreach ($languages as $code => $name) {
      // Add the native language's name to the output if applicable.
      if (in_array($code, $noNative)) {
        $native = "null";
      }
      else {
        $native = \Locale::getDisplayLanguage($code, $code);
      }

      // Put it together.
      $translations .= '"' . $code . '"=>(object)["code"=>"' . $code . '","name"=>"' . $name . '","native"=>"' . $native . '","closed"=>null,"forced"=>true],';
    }

    return $translations;
  }

}
