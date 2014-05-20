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
 * Base class for input form elements with an atomic value and default rendering.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractInput extends \MovLib\Partial\FormElement\AbstractFormElement {

  /**
   * Instantiate new input form element.
   *
   * @param \MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP
   *   HTTP dependency injection container.
   * @param string $id
   *   The input text's unique global identifier.
   * @param string $label
   *   The input text's translated label text.
   * @param string $value
   *   The input text's value, defaults to <code>NULL</code>.
   * @param array $attributes [optional]
   *   The input text's attributes array, the following attributes are hardcoded:
   *   <ul>
   *     <li><code>"id"</code> is set to <var>$id</var></li>
   *     <li><code>"name"</code> is set to <var>$id</var></li>
   *     <li><code>"type"</code> is set to <code>"text"</code></li>
   *     <li><code>"value"</code> is set to <code>$value</code></li>
   *   </ul>
   */
  public function __construct(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP, $id, $label, &$value, array $attributes = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (!defined("static::TYPE")) {
      throw new \LogicException("You have to define the TYPE class constant for an input element");
    }
    $disallowedAttributes = [ "id", "name", "value", "type" ];
    foreach ($disallowedAttributes as $attribute) {
      if (isset($attribute[$attribute])) {
        throw new \LogicException("You must not set any of the following attributes: " . implode(", ", $disallowedAttributes));
      }
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    parent::__construct($diContainerHTTP, $id, $label, $value, $attributes);
    $this->attributes["id"]    = $this->attributes["name"] = $this->id;
    $this->attributes["value"] =& $this->value;
    $this->attributes["type"]  = static::TYPE;
  }

  /**
   * Get the input text form element.
   *
   * @return string
   *   The input text form element.
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      $input = "<input{$this->expandTagAttributes($this->attributes)}>";
      if (isset($this->attributes["#field_prefix"])) {
        $input = "{$this->attributes["#field_prefix"]}{$input}";
      }
      if (isset($this->attributes["#field_suffix"])) {
        $input .= $this->attributes["#field_suffix"];
      }
      $string = "{$this->required}{$this->helpPopup}{$this->helpText}<p><label for='{$this->id}'>{$this->label}</label>{$input}</p>";
      if (isset($this->attributes["#prefix"])) {
        $string = "{$this->attributes["#prefix"]}{$string}";
      }
      if (isset($this->attributes["#suffix"])) {
        $string .= $this->attributes["#suffix"];
      }
      return $string;
    // @devStart
    // @codeCoverageIgnoreStart
    }
    catch (\Exception $e) {
      return $this->calloutError("<pre>{$e}</pre>", "Stacktrace");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }

}
