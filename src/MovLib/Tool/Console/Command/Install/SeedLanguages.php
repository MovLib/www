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
 * Create language translations.
 *
 * The translated languages are used for audio formats of the various releases and contain two special language codes.
 * One for silent movies and one for other languages. The first one should be obvious and the later is meant for dead
 * languages or other special language, like <i>Hebrew</i> in <i>The Passion Of The Christ</i> or <i>Klingon</i> in
 * <i>Star Trek</i>. Users have to specify these special languages in the notes section of a release.
 *
 * We don't use ISO 639-2 in our application because nearly no software works together with them (W3C standards and
 * parsers built upon them, Intl ICU, ...).
 *
 * @see \MovLib\Data\StreamWrapper\I18nStreamWrapper
 * @see \MovLib\Presentation\Partial\Language
 * @see \MovLib\Stub\Data\Language
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class SeedLanguages extends AbstractIntlCommand {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * All available ISO 639-1 language codes.
   *
   * @var array
   */
  protected $codes = [
    "aa", "ab", "ae", "af", "ak", "am", "an", "ar", "as", "av", "ay", "az",
    "ba", "be", "bg", "bh", "bi", "bm", "bn", "bo", "br", "bs",
    "ca", "ce", "ch", "co", "cr", "cs", "cu", "cv", "cy",
    "da", "de", "dv", "dz",
    "ee", "el", "en", "eo", "es", "et", "eu",
    "fa", "ff", "fi", "fj", "fo", "fr", "fy",
    "ga", "gd", "gl", "gn", "gu", "gv",
    "ha", "he", "hi", "ho", "hr", "ht", "hu", "hy", "hz",
    "ia", "id", "ie", "ig", "ii", "ik", "io", "is", "it", "iu",
    "ja", "jv",
    "ka", "kg", "ki", "kj", "kk", "kl", "km", "kn", "ko", "kr", "ks", "ku", "kv", "kw", "ky",
    "la", "lb", "lg", "li", "ln", "lo", "lt", "lu", "lv",
    "mg", "mh", "mi", "mk", "ml", "mn", "mr", "ms", "mt", "my",
    "na", "nb", "nd", "ne", "ng", "nl", "nn", "no", "nr", "nv", "ny",
    "oc", "oj", "om", "or", "os",
    "pa", "pi", "pl", "ps", "pt",
    "qu",
    "rm", "rn", "ro", "ru", "rw",
    "sa", "sc", "sd", "se", "sg", "si", "sk", "sl", "sm", "sn", "so", "sq", "sr", "ss", "st", "su", "sv", "sw",
    "ta", "te", "tg", "th", "ti", "tk", "tl", "tn", "to", "tr", "ts", "tt", "tw", "ty",
    "ug", "uk", "ur", "uz",
    "ve", "vi", "vo",
    "wa", "wo",
    "xh",
    "yi", "yo",
    "za", "zh", "zu",
  ];


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setName("seed-languages");
    $this->setDescription("Create translations for all available languages.");
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function translate() {
    global $i18n;

    // Translate all available languages to the desired locale.
    $languages = [];
    foreach ($this->codes as $code) {
      $languages[$code] = Locale::getDisplayLanguage($code, $i18n->locale);
    }

    // Add the two special language codes.
    $languages[I18n::CODE_NON_LINGUISTIC] = $i18n->t("Silent");
    $languages[I18n::CODE_OTHER]          = $i18n->t("Other");

    // Prepare search array which helps us to identify which special language codes don't have a native translation.
    $noNative = [ I18n::CODE_NON_LINGUISTIC, I18n::CODE_OTHER ];

    // Sort the translated language according to their translated names.
    $i18n->getCollator()->asort($languages);

    $translations = null;
    foreach ($languages as $code => $name) {
      // Add the native language's name to the output if applicable.
      if (in_array($code, $noNative)) {
        $native = "null";
      }
      else {
        $native = Locale::getDisplayLanguage($code, $code);
      }

      // Put it together.
      $translations .= '"' . $code . '"=>(object)["code"=>"' . $code . '","name"=>"' . $name . '","native"=>"' . $native . '"],';
    }

    return $translations;
  }

}
