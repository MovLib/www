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

use \MovLib\Partial\FormElement\InputText;

/**
 * Defines the sex object.
 *
 * @link https://en.wikipedia.org/wiki/ISO/IEC_5218
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Sex {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Not Known
   *
   * @var integer
   */
  const UNKNOWN = 0;

  /**
   * Male
   *
   * @var integer
   */
  const MALE = 1;

  /**
   * Female
   *
   * @var integer
   */
  const FEMALE = 2;

  /**
   * Not Applicable
   *
   * @var integer
   */
  const NOT_APPLICABLE = 9;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Add input text elements to the form for each valid sex.
   *
   * @param \MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP
   *   Active HTTP dependency injection container.
   * @param \MovLib\Partial\Form $form
   *   The form to which the sex input field should be added.
   * @param string $id
   *   The input text's global unique identifier.
   * @param array $values
   *   An array that will contain the input text values, the array should be keyed by ISO 5218 sex codes.
   * @param array $attributes [optional]
   *   Additional attributes for the input text element.
   * @param string $label [optional]
   *   A translated string that should be used as label for the input text elements. Note that any string within the
   *   label that matches either <code>"{0}"</code> or <code>"{sex}"</code> will be replaced by the translated sex's
   *   name. Defaults to <code>NULL</code>.
   * @return this
   */
  public function addInputTextElements(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP, \MovLib\Partial\Form &$form, $id, array $values, array $attributes = null, $label = null) {
    $sexes = [
      Sex::UNKNOWN => $diContainerHTTP->intl->t("Unisex"),
      Sex::MALE    => $diContainerHTTP->intl->t("Male"),
      Sex::FEMALE  => $diContainerHTTP->intl->t("Female"),
    ];
    foreach ($sexes as $code => $name) {
      if ($label) {
        $name = str_replace([ "{0}", "{sex}" ], $name, $label);
      }
      $form->addElement(new InputText($diContainerHTTP, "{$id}-{$code}", $name, $values[$code], $attributes));
    }
    return $this;
  }

}
