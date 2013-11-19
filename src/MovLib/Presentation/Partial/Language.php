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
namespace MovLib\Presentation\Partial;

/**
 * Represents a single language in HTML and provides an interface to all available languages.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Language extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The attributes array.
   *
   * @var array
   */
  protected $attributes;

  /**
   * The language to present.
   *
   * @var \MovLib\Data\Language
   */
  protected $language;

  /**
   * The HTML tag to wrap the country.
   *
   * @var string
   */
  protected $tag;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new language partial.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param string $code
   *   The ISO 639-1 code of the language.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the element.
   * @param string $tag [optional]
   *   The tag that should be used to wrap this language, defaults to <code>"span"</code>.
   */
  public function __construct($code, array $attributes = null, $tag = "span") {
    global $i18n;
    $this->attributes             = $attributes;
    $this->attributes[]           = "itemscope";
    $this->attributes["itemtype"] = "http://schem.org/Language";
    $this->language               = new \MovLib\Data\Language($code);
    $this->tag                    = $tag;

    // The special code xx isn't valid if we use it as lang attribute, but the ISO 639-2 code zxx is, make sure we use
    // the right language code.
    if ($this->language->code != $i18n->languageCode) {
      $attributes["lang"] = $this->language->code == "xx" ? "zxx" : $this->language->code;
    }
  }

  /**
   * Get the string representation of the language.
   *
   * @return string
   */
  public function __toString() {
    return "<{$this->tag}{$this->expandTagAttributes($this->attributes)}><span itemprop='name'>{$this->country->name}</span></{$this->tag}>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get all supported and translated languages.
   *
   * @global \MovLib\Data\I18n $i18n
   * @staticvar array $languages
   *   Associative array used for caching.
   * @return array
   *   All supported and translated languages.
   */
  public static function getLanguages() {
    global $i18n;
    static $languages = null;

    // If we haven't built the array for this locale build it.
    if (!isset($languages[$i18n->locale])) {
      // @todo We can't use the native name as title because it has a different language and we have no possiblity to
      //       indicate that to the user agent. On the other hand this isn't only used for form elements and in those
      //       other use cases the native name might be from interest.
      foreach (\MovLib\Data\Language::getLanguages() as $code => $language) {
        $languages[$i18n->locale][$code] = $language["name"];
      }
    }

    return $languages[$i18n->locale];
  }

}
