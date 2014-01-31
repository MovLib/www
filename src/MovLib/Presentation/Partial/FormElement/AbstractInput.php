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
namespace MovLib\Presentation\Partial\FormElement;

/**
 * @todo Description of AbstractInput
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractInput extends \MovLib\Presentation\Partial\FormElement\AbstractFormElement {

  /**
   * The form element's value.
   *
   * @var string
   */
  public $value;

  /**
   * Instantiate new input form element.
   *
   * @param string $id
   *   The input element's unique global identifier.
   * @param string $label
   *   The input element's translated label text.
   * @param array $attributes [optional]
   *   The input element's attributes array.
   */
  public function __construct($id, $label, array $attributes = null) {
    parent::__construct($id, $label, $attributes);
    $this->value = $this->filterInput($this->id);
    if (empty($this->value) && isset($this->attributes["value"])) {
      $this->value = $this->attributes["value"];
    }
  }

  /**
   * @inheritdoc
   */
  protected function render() {
    if (empty($this->value)) {
      unset($this->attributes["value"]);
    }
    return "{$this->help}<p><label for='{$this->id}'>{$this->label}</label><input{$this->expandTagAttributes($this->attributes)}></p>";
  }

}
