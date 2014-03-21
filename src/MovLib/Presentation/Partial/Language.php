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

use \MovLib\Data\FileSystem;
use \MovLib\Presentation\Partial\FormElement\RadioGroup;
use \MovLib\Presentation\Partial\FormElement\Select;

/**
 * Represents a single language in HTML and provides an interface to all available languages.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Language extends \MovLib\Presentation\AbstractBase {


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
   * The HTML tag to wrap the language.
   *
   * @var string
   */
  protected $tag;

  /**
   * Array containing all available languages.
   *
   * @var array
   */
  protected static $languages;


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
    $this->attributes["itemtype"] = "http://schema.org/Language";
    $this->language               = new \MovLib\Data\Language($code);
    $this->tag                    = $tag;

    // The special code xx isn't valid if we use it as lang attribute, but the ISO 639-2 code zxx is, make sure we use
    // the right language code.
    if ($this->language->code != $i18n->languageCode) {
      $this->attributes["lang"] = $this->language->code == "xx" ? "zxx" : $this->language->code;
    }
  }

  /**
   * Get the string representation of the language.
   *
   * @return string
   */
  public function __toString() {
    return "<{$this->tag}{$this->expandTagAttributes($this->attributes)}><span itemprop='name'>{$this->language->name}</span><meta itemprop='alternateName' content='{$this->language->native}'></{$this->tag}>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get language instance.
   *
   * @param string $code
   *   The ISO 639-1 code of the language. Additionally you can pass <code>"xx"</code> which is a custom code for the
   *   ISO 639-2 code <code>"zxx"</code> and declares the <em>absence of linguistic information</em>.
   * @return \MovLib\Stub\Data\Language
   *   The desired language.
   */
  public static function get($code) {
    if (!self::$languages) {
      return self::getLanguages()[$code];
    }
    return self::$languages[$code];
  }

  /**
   * Get all supported and translated languages.
   *
   * @return array
   *   All supported and translated languages.
   */
  public static function getLanguages() {
    if (!self::$languages) {
      self::$languages = require FileSystem::realpath("i18n://languages");
    }
    return self::$languages;
  }

  /**
   * Get select form element to select a language.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param string $value
   *   The form element's value.
   * @param array $attributes [optional]
   *   The form element's additional attributes.
   * @param string $id [optional]
   *   The form element's unique identifier, defaults to <code>"language"</code>.
   * @param string $label [optional]
   *   The form element's translated label, default to <code>$i18n->t("Language")</code>.
   * @return \MovLib\Presentation\Partial\FormElement\Select
   *   The select form element to select a language.
   */
  public static function getSelectFormElement(&$value, array $attributes = null, $id = "language", $label = null) {
    global $i18n;
    return new Select($id, $label ?: $i18n->t("Language"), self::getLanguages(), $value, $attributes);
  }

}
