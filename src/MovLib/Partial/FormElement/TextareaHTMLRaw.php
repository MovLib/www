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

use \MovLib\Presentation\Error\Unauthorized;

/**
 * Raw HTML textarea form element.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class TextareaHTMLRaw extends \MovLib\Partial\FormElement\AbstractFormElement {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "TextareaHTMLRaw";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Get the raw HTML textarea form element.
   *
   * @return string
   *   The raw HTML textarea form element.
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      // Remove tags that are inserted by our auto-paragraph method and empty attributes (inserted by Tidy).
      $content = str_replace(
        [ "\n\n", "<br>", "<p>", "</p>", "=''", '=""' ],
        [ "\n", "", "", "\n", "", "" ],
        tidy_get_output(tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body>{$this->presenter->htmlDecode($this->value)}</body></html>"))
      );

      $this->attributes["id"] = $this->id;
      $this->attributes["name"] = $this->id;
      $this->attributes["aria-multiline"] = "true";
      return
        "{$this->required}{$this->helpPopup}{$this->helpText}<p>" .
          "<label for='{$this->id}'>{$this->label}</label>" .
          "<textarea{$this->presenter->expandTagAttributes($this->attributes)}>{$content}</textarea>" .
        "</p>"
      ;
    // @devStart
    // @codeCoverageIgnoreStart
    }
    catch (\Exception $e) {
      return $this->calloutError("<pre>{$e}</pre>", "Stacktrace");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Auto insert paragraphs (and breaks).
   *
   * @link http://core.trac.wordpress.org/browser/trunk/src/wp-includes/formatting.php WordPress source code
   * @link https://github.com/trademark/VanillaPlugins/blob/master/AutoParagraph/class.autoparagraph.plugin.php
   * @param string $html
   *   The text which has to be formatted.
   * @return string
   *   Text which has been converted into correct paragraph tags.
   */
  protected function autoParagraph($html) {
    // Just to make things a little easier, pad the end
    $html = $this->normalizeLineFeeds("{$html}\n");

    // Trim each line.
    $html = preg_replace("#^\s*(.*)#", "$1", $html);

    // Normalize break tags.
    $html = preg_replace("#<br */?>#", "<br>", $html);

    // Replace more than one break in a row with two line feeds.
    $html = preg_replace("#<br>\s*<br>#", "\n\n", $html);

    // Space things out a little.
    $allblocks = "(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|map|area|blockquote|address|math|p|h[1-6]|hr|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary|code)";

    // Insert one line feed before each block level tag.
    $html = preg_replace("#(<{$allblocks}[^>]*>)#", "\n$1", $html);

    // Insert two line feeds after each block level tag.
    $html = preg_replace("#(</{$allblocks}>)#", "$1\n\n", $html);

    // Take care of duplicates.
    $html = preg_replace("#\n\n+#", "\n\n", $html);

    // Ensure no whitespace is present after an image tag and the following caption.
    //$html = preg_replace("#<img(.*)>\s+<#U", "<img$1><", $html);

    // Make paragraphs, including one at the end.
    $lines = preg_split("#\n\s*\n#", $html, -1, PREG_SPLIT_NO_EMPTY);

    // Enclose all paragraphs.
    $html = null;
    $c    = count($lines);
    for ($i = 0; $i < $c; ++$i) {
      $lines[$i] = trim($lines[$i], "\n");
      $html     .= "<p>{$lines[$i]}</p>\n";
    }

    // Under certain conditions a paragraph might only contain whitespace characters.
    $html = preg_replace("#<p>\s*</p>#", "", $html);

    // Close paragraphs before cerating elements.
    $html = preg_replace("#<p>([^<]+)</(div|address|form)>#", "<p>$1</p></$2>", $html);

    // Don't wrap block tags in paragraphs.
    $html = preg_replace("#<p>\s*(</?{$allblocks}[^>]*>)\s*</p>#", "$1", $html);

    // Problem with nested lists captions.
    $html = preg_replace("#<p>(<li.+?)</p>#", "$1", $html);

    // Move the opening paragraph inside the blockquote (opening and closing).
    $html = preg_replace("#<p><blockquote([^>]*)>#i", "<blockquote$1><p>", $html);
    $html = str_replace("</blockquote></p>", "</p></blockquote>", $html);

    // Again, don't wrap block tags with paragraphs.
    $html = preg_replace("#<p>\s*(</?{$allblocks}[^>]*>)#", "$1", $html);
    $html = preg_replace("#(</?{$allblocks}[^>]*>)\s*</p>#", "$1", $html);

    // Make line breaks.
    $html = preg_replace("#(?<!<br>)\s*\n#", "<br>\n", $html);

    // No breaks behind block elements.
    $html = preg_replace("#(</?{$allblocks}[^>]*>)\s*<br>#", "$1", $html);

    // No breaks before closing block elements if there is only whitespace.
    $html = preg_replace("#<br>(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol|code)[^>]*>)#", "$1", $html);

    // Remove all left over line feeds.
    $html = preg_replace("#\n+#", "", $html);

    // Collapse whitespace characters.
    return $this->collapseWhitespace($html);
  }

  /**
   * Validate the user submitted HTML.
   *
   * @param string $html
   *   The user submitted HTML to validate.
   * @param null|array $errors
   *   Variable used to collect error messages.
   * @return string
   *   The valid HTML.
   */
  protected function validateValue($html, &$errors) {
    // Trim the submitted HTML.
    $html = trim($html);

    // Normalize to NFC form as recommended by W3C.
    $html = \Normalizer::normalize($html);

    // Insert breaks and paragraphs.
    $html = $this->autoParagraph($html);

    // Return encoded version (secure by default).
    return $this->htmlEncode($html);
  }

}
