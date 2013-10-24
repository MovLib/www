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

use \MovLib\Presentation\Validation\HTML;

/**
 * HTML textarea form element.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/textarea
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Textarea extends \MovLib\Presentation\Partial\FormElement\AbstractFormElement {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The textarea's content.
   *
   * @var null|string
   */
  public $content;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new textarea form element.
   *
   * @param string $id
   *   The textarea's global identifier.
   * @param string $label
   *   The textarea's label text.
   * @param mixed $content [optional]
   *   The textarea's content, defaults to <code>NULL</code> (no content).
   * @param array $attributes [optional]
   *   Additional attributes for the textarea, defaults to <code>NULL</code> (no additional attributes).
   * @param string $help [optional]
   *   The textarea's help text, defaults to <code>NULL</code> (no help text).
   * @param boolean $helpPopup
   *   Whetever the help should be displayed as popup or not, defaults to <code>TRUE</code> (display as popup).
   */
  public function __construct($id, $label, $content = null, array $attributes = null, $help = null, $helpPopup = true) {
    parent::__construct($id, $label, $attributes, $help, $helpPopup);
    $this->attributes["aria-multiline"] = "true";
    if (!isset($this->attributes["data-format"])) {
      $this->attributes["data-format"] = HTML::FORMAT_BASIC_HTML;
    }
    if (!isset($this->attributes["data-allow-external"])) {
      $this->attributes["data-allow-external"] = false;
    }
    $this->content = $content;
    if (isset($_POST[$this->id])) {
      $this->content = empty($_POST[$this->id]) ? null : $_POST[$this->id];
    }
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    return "{$this->help}<p><label for='{$this->id}'>{$this->label}</label><textarea{$this->expandTagAttributes($this->attributes)}>{$this->content}</textarea></p>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function validate() {
    $this->content = (new HTML($this->content, $this->attributes["data-format"], $this->attributes["data-allow-external"]))->validate();
    return $this;
  }

}
