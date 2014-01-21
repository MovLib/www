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

/**
 * HTML textarea input supporting line by line entries for plain text.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputLinesText extends \MovLib\Presentation\Partial\FormElement\InputText {

  /**
   * @inheritdoc
   */
  public function __construct($id, $label, array $attributes = null) {
    parent::__construct($id, $label, $attributes);
    unset($this->attributes["type"]);
  }

  /**
   * @inheritdoc
   */
  public function render() {
    $this->attributes["spellcheck"] = "true";
    unset($this->attributes["value"]);
    if (is_array($this->value)) {
      $value = implode("", $this->value);
    }
    else {
      $value = $this->value;
    }
    return "{$this->help}<p><label for='{$this->id}'>{$this->label}</label><textarea{$this->expandTagAttributes($this->attributes)}>{$value}</textarea></p>";
  }

  /**
   * @inheritdoc
   */
  public function validate() {
    // Check for required input.
    if (empty($this->value)) {
      $this->value = null;
      if (in_array("required", $this->attributes)) {
        throw new ValidationException($i18n->t("The “{0}” text field is mandatory.", [ $this->label ]));
      }
      return $this;
    }

    // Normalize line feeds.
    $this->value = $this->normalizeLineFeeds($this->value);

    // Split text on line feeds and validate each line.
    $lines = explode("\n", $this->value);
    $c = count($lines);
    for ($i = 0; $i < $c; ++$i) {
      // Set value to current line and make use of the text validation function of InputText.
      $this->value = $lines[$i];
      $lines[$i] = parent::validate()->value;
    }

    // Everything's fine, the value can now be used as an array of lines.
    $this->value = $lines;
  }
}
