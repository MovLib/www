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
namespace MovLib\Partial\FormElement;

/**
 * Input Wikipedia form element.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputWikipedia extends \MovLib\Partial\FormElement\InputURL {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Error code for wrong locale.
   *
   * @var integer
   */
  const ERROR_WRONG_LOCALE = 8;

  /**
   * Error code for wrong hostname.
   *
   * @var integer
   */
  const ERROR_WRONG_HOSTNAME = 9;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Validate the submitted URL.
   *
   * @param string $url
   *   The user submitted url to validate.
   * @param null|array $errors
   *   Parameter to collect error messages.
   * @return string
   *   The valid URL.
   */
  protected function validateValue($url, &$errors) {
    $this->attributes["data-allow-external"] = true;
    parent::validateValue($url, $errors);
    if ($errors) {
      return $url;
    }

    $parts = parse_url($url);
    if (strpos($parts["host"], ".wikipedia.org") === false) {
      $errors[self::ERROR_WRONG_HOSTNAME] = $this->intl->t("Only links to articles on wikipedia.org are allowed.");
      return $url;
    }
    if ($parts["host"] != "{$this->intl->languageCode}.wikipedia.org") {
      $errors[self::ERROR_WRONG_LOCALE] = $this->intl->t(
        "Only links to Wikipedia in the current language ({0}) are allowed.",
        [ $this->intl->getTranslations("languages")[$this->intl->languageCode]->name ]
      );
      return $url;
    }

    return $url;
  }

}
