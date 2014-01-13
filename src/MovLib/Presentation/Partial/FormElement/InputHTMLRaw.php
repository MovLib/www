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

use \MovLib\Exception\ValidationException;

/**
 * Raw HTML contenteditable text form element.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Content_Editable
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputHTMLRaw extends \MovLib\Presentation\Partial\FormElement\AbstractFormElement {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The text's HTML encoded content.
   *
   * @var null|string
   */
  public $value;

  /**
   * The text's HTML decoded content.
   *
   * @var null|string
   */
  protected $valueRaw;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new HTML form element.
   *
   * @global \MovLib\Kernel $kernel
   * @param string $id
   *   The text's global identifier.
   * @param string $label
   *   The text's label text.
   * @param mixed $value [optional]
   *   The form element's value, defaults to <code>NULL</code> (no value).
   * @param array $attributes [optional]
   *   Additional attributes for the text, defaults to <code>NULL</code> (no additional attributes).
   */
  public function __construct($id, $label, $value = null, array $attributes = null) {
    global $kernel;
    parent::__construct($id, $label, $attributes);
    $this->attributes["aria-multiline"] = "true";

    if (!empty($_POST[$this->id])) {
      $normalized     = \Normalizer::normalize($_POST[$this->id]);
      $this->valueRaw = $this->autoParagraph($normalized);
      $this->value    = $kernel->htmlEncode($this->valueRaw);
    }
    elseif ($value) {
      $this->value    = $value;
      $this->valueRaw = $kernel->htmlDecode($this->value);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Auto insert paragraphs (and breaks).
   *
   * @link http://core.trac.wordpress.org/browser/trunk/src/wp-includes/formatting.php WordPress source code
   * @param string $html
   *   The text which has to be formatted.
   * @return string
   *   Text which has been converted into correct paragraph tags.
   */
  protected function autoParagraph($html) {
    // Just to make things a little easier, pad the end
    $html = $this->normalizeLineFeeds("{$html}\n");

    // Normalize break tags.
    $html = preg_replace("#<br */?>#", "<br>", $html);

    // Replace more than one break in a row with to line feeds.
    $html = preg_replace("#<br>\s*<br>#", "\n\n", $html);

    // Space things out a little
    $allblocks = "(?:dl|dd|dt|ul|ol|li|blockquote|p|h[2-6]|figure|figcaption)";

    // Insert one line feed before each block level tag.
    $html = preg_replace("#(<{$allblocks}[^>]*>)#", "\n$1", $html);

    // Insert two line feeds after each block level tag.
    $html = preg_replace("#(</{$allblocks}>)#", "$1\n\n", $html);

    // Take care of duplicates
    $html = preg_replace("#\n\n+#", "\n\n", $html);

    // Ensure no whitespace is present after an image tag and the following caption.
    $html = preg_replace("#<img(.*)>\s+<#U", "<img$1><", $html);

    // Make paragraphs, including one at the end
    $lines = preg_split("#\n\s*\n#", $html, -1, PREG_SPLIT_NO_EMPTY);

    // Enclose all paragraphs.
    $html = null;
    $c   = count($lines);
    for ($i = 0; $i < $c; ++$i) {
      $lines[$i] = trim($lines[$i], "\n");
      $html     .= "<p>{$lines[$i]}</p>\n";
    }

    // Don't pee all over a tag
    $html = preg_replace("#<p>\s*(</?{$allblocks}[^>]*>)\s*</p>#", "$1", $html);

    // Problem with nested lists and figure captions.
    $html = preg_replace("#<p>(<(li|figcaption).+?)</p>#", "$1", $html);

    // Move the opening paragraph inside the blockquote (opening and closing.
    $html = preg_replace("#<p><blockquote([^>]*)>#i", "<blockquote$1><p>", $html);
    $html = str_replace("</blockquote></p>", "</p></blockquote>", $html);

    // Don't pee all over a block tag.
    $html = preg_replace("#<p>\s*(</?{$allblocks}[^>]*>)#", "$1", $html);
    $html = preg_replace("#(</?{$allblocks}[^>]*>)\s*</p>#", "$1", $html);

    // Make line breaks
    $html = preg_replace("#(?<!<br>)\s*\n#", "<br>\n", $html);

    // No breaks behind block elements.
    $html = preg_replace("#(</?{$allblocks}[^>]*>)\s*<br>#", "$1", $html);

    // No breaks before closing block elements if there is only whitespace.
    $html = preg_replace("#<br>(\s*</?(?:p|li|dl|dd|dt|ul|ol)[^>]*>)#", "$1", $html);

    // Remove all left over line feeds.
    return preg_replace("#\n+#", "", $html);
  }
  
  /**
   * @inheritdoc
   */
  protected function render() {
    global $i18n;

    // Important note: The majority of the code is commented out for a purpose, it is NOT dead code.
    // We will need it again once the WYSIWYG editor works. For now we'll stick with a plain <textarea>.

    // Pretty print HTML, since we only use <textarea> for now. This will change when InputHTML is finished.
    $tidy = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body>{$this->valueRaw}</body></html>");
    $tidy->cleanRepair();
    if ($tidy->getStatus() === 2) {
      throw new \ErrorException;
    }
    
    $content = str_replace(
      [ "\n\n", "<br>\n", "<p>", "</p>" ],
      [ "\n", "", "", "\n"],
      tidy_get_output($tidy)
    );

    // Use default placeholder text if none was provided.
    if (!isset($this->attributes["placeholder"])) {
      $this->attributes["placeholder"] = $i18n->t("Enter “{0}” text here …", [ $this->label ]);
    }
    
    return "{$this->help}<p><label for='{$this->id}'>{$this->label}</label><textarea{$this->expandTagAttributes($this->attributes)}>{$content}</textarea></p>";
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  public function validate() {
    global $i18n;

    // Validate if we have input and throw an Exception if the field is required.
    if (empty($this->valueRaw)) {
      if (in_array("required", $this->attributes)) {
        throw new ValidationException($i18n->t("“{label}” is mandatory.", [ "label" => $this->label ]));
      }
      $this->value = null;
      return $this;
    }

    // Nothing to do, it's an admin and we hope that our admins know what they do.
    return $this;
  }

}
