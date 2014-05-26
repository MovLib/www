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

use \MovLib\Partial\FormElement\Select;

/**
 * Represents a single language in HTML and provides an interface to all available languages.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Language extends \MovLib\Core\Presentation\DependencyInjectionBase {


 // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * All available languages in the current locale.
   *
   * @var array
   */
  public $languages;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function __construct(\MovLib\Core\HTTP\Container $container) {
    parent::__construct($container);
    $this->languages = $this->intl->getTranslations("languages");
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Format a language.
   *
   * @param string $languageCode
   *   The language's ISO alpha-2 code.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the element. Note that the <code>"typeof"</code> attribute is
   *   always overwritten.
   * @param string $tag [optional]
   *   The tag that should be used to wrap the language, defaults to <code>"span"</code>.
   * @return string
   *   The formatted language.
   * @throws \ErrorException
   *   If the language code is invalid.
   */
  public function format($languageCode, array $attributes = [], $tag = "span") {
    $language = $this->languages[$languageCode];
    $attributes["typeof"] = "Language";
    return "<{$tag}{$this->expandTagAttributes($attributes)}><span property='name'>{$language->name}</span><meta itemprop='alternateName' content='{$this->language->native}'></{$tag}>";
  }

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
  public function getSelectFormElement(&$value, array $attributes = null, $id = "language", $label = null) {
    $options = [];
    /* @var $language \MovLib\Stub\Data\Language */
    foreach ($this->languages as $language) {
      $options[$language->code] = $language->name;
    }
    return new Select($this->container, $id, $label ?: $this->intl->t("Language"), $options, $value, $attributes);
  }

}
