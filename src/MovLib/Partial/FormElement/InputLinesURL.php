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

namespace MovLib\Partial\FormElement;

use \MovLib\Exception\ValidationException;

/**
 * HTML textarea input supporting line by line entries for URLs.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputLinesURL extends \MovLib\Partial\FormElement\InputURL {

  /**
   * The user's raw input in this field.
   * @var string
   */
  protected $valueRaw;

  /**
   * @inheritdoc
   */
  public function __construct($id, $label, array $attributes = null) {
    parent::__construct($id, $label, $attributes);
    unset($this->attributes["type"]);
    if (!is_array($this->value)) {
      $this->valueRaw = $this->value;
    }
    else {
      $this->valueRaw = implode("\n", array_values($this->value));
    }
  }

  /**
   * @inheritdoc
   */
  public function render() {
    unset($this->attributes["value"]);
    return "{$this->helpPopup}<p><label for='{$this->id}'>{$this->label}</label><textarea{$this->expandTagAttributes($this->attributes)}>{$this->valueRaw}</textarea></p>";
  }

  /**
   * @inheritdoc
   */
  public function validate() {
    // Check for required input.
    if (empty($this->value)) {
      $this->valueRaw = null;
      if (in_array("required", $this->attributes)) {
        throw new ValidationException($this->intl->t("The “{0}” text field is mandatory.", [ $this->label ]));
      }
      return $this;
    }

    // Normalize line feeds.
    $this->valueRaw = $this->normalizeLineFeeds($this->valueRaw);

    // Split text on line feeds and validate each line.
    $lines = explode("\n", $this->valueRaw);
    $c = count($lines);
    try {
      for ($i = 0; $i < $c; ++$i) {
        // Set value to current line and make use of the URL validation function of InputURL.
        $this->value = $lines[$i];
        $lines[$i] = parent::validate()->value;
      }
    }
    catch (ValidationException $e) {
      $this->value = null;
      throw $e;
    }

    // Everything's fine, the value can now be used as an array of lines.
    $this->value = $lines;

    return $this;
  }
}
