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
namespace MovLib\Partial;

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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new language partial.
   *
   * @param string $code
   *   The ISO 639-1 code of the language.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the element.
   * @param string $tag [optional]
   *   The tag that should be used to wrap this language, defaults to <code>"span"</code>.
   */
  public function __construct($code, array $attributes = null, $tag = "span") {
    $this->attributes             = $attributes;
    $this->attributes[]           = "itemscope";
    $this->attributes["itemtype"] = "http://schema.org/Language";
    $this->language               = $this->intl->getTranslations("languages")[$code];
    $this->tag                    = $tag;

    // The special code xx isn't valid if we use it as lang attribute, but the ISO 639-2 code zxx is, make sure we use
    // the right language code.
    if ($this->language->code != $this->intl->languageCode) {
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
   * Get select form element to select a language.
   *
   * @param string $value
   *   The form element's value.
   * @param array $attributes [optional]
   *   The form element's additional attributes.
   * @param string $id [optional]
   *   The form element's unique identifier, defaults to <code>"language"</code>.
   * @param string $label [optional]
   *   The form element's translated label, default to <code>$this->intl->t("Language")</code>.
   * @return \MovLib\Presentation\Partial\FormElement\Select
   *   The select form element to select a language.
   */
  public static function getSelectFormElement(&$value, array $attributes = null, $id = "language", $label = null) {
    return new Select($id, $label ?: $this->intl->t("Language"), self::getLanguages(), $value, $attributes);
  }

}
