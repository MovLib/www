<?php

/*!
 *  This file is part of {@link https://github.com/MovLib MovLib}.
 *
 *  Copyright © 2013-present {@link http://movlib.org/ MovLib}.
 *
 *  MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 *  License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 *  version.
 *
 *  MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 *  of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License along with MovLib.
 *  If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */

namespace MovLib\Presentation\Partial\FormElement;

use \MovLib\Exception\ValidationException;

/**
 * HTML input type number form element.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputNumber extends \MovLib\Presentation\Partial\FormElement\AbstractInput  {

  /**
   * Options array for PHP's built in <code>filter_var()</code> function.
   *
   * @var array
   */
  protected $options;


  /**
   * Instantiate new input form element of type number.
   *
   * @param string $id
   *   The date's global unique identifier.
   * @param string $label
   *   The date's translated label text.
   * @param array $attributes [optional]
   *   The date's additional attributes, the following attributes are set by default:
   *   <ul>
   *     <li><code>"id"</code> is set to <var>$id</var></li>
   *     <li><code>"name"</code> is set to <var>$id</var></li>
   *     <li><code>"type"</code> is set to <code>"number"</code></li>
   *   </ul>
   *   You <b>should not</b> override any of the default attributes.
   */
  public function __construct($id, $label, array $attributes = null) {
    parent::__construct($id, $label, $attributes);
    $this->attributes["type"] = "number";
    if (isset($this->attributes["max"])) {
      $this->options["max_range"] = $this->attributes["max"];
    }
    if (isset($this->attributes["min"])) {
      $this->options["min_range"] =  $this->attributes["min"];
    }
  }

  /**
   * @inheritdoc
   */
  public function validate() {
    global $i18n;

    if (empty($this->value)) {
      $this->value = null;
      if (in_array("required", $this->attributes)) {
        throw new ValidationException($i18n->t("The “{0}” number is mandatory.", [ $this->label ]));
      }
      return $this;
    }

    if (($this->value = filter_var($this->value, FILTER_VALIDATE_INT, [ "options" => $this->options, "flags" => FILTER_NULL_ON_FAILURE ])) === null) {
      throw new ValidationException($i18n->t("“{0}” is not a valid number.", [ $this->label ]));
    }

    return $this;
  }
}
