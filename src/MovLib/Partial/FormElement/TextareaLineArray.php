<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
 * HTML textarea input supporting line by line entries for plain text.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class TextareaLineArray extends \MovLib\Partial\FormElement\InputText {

  /**
   * @inheritdoc
   */
  public function __construct(\MovLib\Core\HTTP\Container $container, $id, $label, &$value, array $attributes = null) {
    parent::__construct($container, $id, $label, $value, $attributes);
    unset($this->attributes["type"]);
    $this->attributes["spellcheck"] = "true";
    if (is_array($this->value)) {
      $this->value = implode("\n", array_values($this->value));
    }
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      unset($this->attributes["value"]);
      if (is_array($this->value)) {
        $this->value = implode("\n", array_values($this->value));
      }
      return "{$this->helpPopup}<p><label for='{$this->id}'>{$this->label}</label><textarea{$this->expandTagAttributes($this->attributes)}>{$this->value}</textarea></p>";
    // @devStart
    // @codeCoverageIgnoreStart
    }
    catch (\Exception $e) {
      return $this->calloutError("<pre>{$e}</pre>", "Stacktrace");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }

 /**
   * Validate the submitted text.
   *
   * @param string $text
   *   The user submitted text to validate.
   * @param mixed $errors
   *   Parameter to collect error messages.
   * @return string
   *   The valid and sanitized text.
   */
  protected function validateValue($text, &$errors) {
    // Normalize line feeds.
    $this->value = $this->normalizeLineFeeds($text);
    $result = [];
    // Split text on line feeds and validate each line.
    $lines = explode("\n", $this->value);
    $c = count($lines);
    for ($i = 0; $i < $c; ++$i) {
      if (!empty(trim($lines[$i]))) {
        // Make use of the validation function of InputText.
        $result[] = parent::validateValue($lines[$i], $errors);
      }
    }
    return $result;
  }

}