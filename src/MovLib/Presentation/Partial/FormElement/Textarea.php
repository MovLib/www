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

use \DOMDocument;
use \DOMText;
use \MovLib\Exception\ValidationException;
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
   * The textarea's raw content.
   *
   * @var null|string
   */
  protected $content;

  /**
   * The textarea's encoded content.
   *
   * @var null|string
   */
  public $value;


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
   *   Whether the help should be displayed as popup or not, defaults to <code>TRUE</code> (display as popup).
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
   * @global \MovLib\Data\I18n $i18n
   */
  public function validate() {
    global $i18n;
    try {
      $dom = new DOMDocument();
      $dom->loadHTML("<meta charset='utf-8'>{$this->content}", LIBXML_NOENT);
      $level = -1;
      $nodes   = [ $level => [ $dom->getElementsByTagName("body")->item(0) ] ];
      $endTags = [];
      do {
        while (!empty($nodes[$level])) {
          /* @var $node \DOMNode */
          $node = array_shift($nodes[$level]);
          if ($level >= 0) {
            // If text node, sanitize text and simply append to sanitized content.
            if ($node instanceof DOMText) {
              $this->value = "{$this->value}{$this->checkPlain($node->wholeText)}";
            }
            else {
              // Sanitize tag and attributes + append to sanitized content.
              $this->value = "{$this->value}<{$node->tagName}>";
              // Stack end tag, if needed.
              $endTags[$level][] = "</{$node->tagName}>";
            }
          }
          // If we have children, stack them on the nodes array and increase level.
          if ($node->hasChildNodes()) {
            $level++;
            foreach ($node->childNodes as $childNode) {
              $nodes[$level][] = $childNode;
            }
          }
        }
        $level--;
        if ($level >= 0 && isset($endTags[$level])) {
          while ($endTag = array_pop($endTags[$level])) {
            $this->value = "{$this->value}{$endTag}";
          }
        }
      }
      while ($level >= 0);
    }
    catch (\ErrorException $e) {
      throw new ValidationException($i18n->t("Invalid HTML. Please correct your input and submit your work again."));
    }
    $this->value = htmlspecialchars($this->value);
    return $this;
  }

}
