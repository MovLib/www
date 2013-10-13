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
namespace MovLib\Presentation;

/**
 * The abstract presentation class provides several HTML related utility methods.
 *
 * Almost all presentation related classes deal with HTML, the only exception are the API related presentation classes.
 * Any other class can extends this class and use the utility methods.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Global counter for the tabindex across a single presentation process.
   *
   * @var int
   */
  private static $tabindex = 1;


  // ------------------------------------------------------------------------------------------------------------------- Protected Methods


  /**
   * Generate an internal link.
   *
   * This method should be used if you link to a page, but can't predict or know if this might be the page the user is
   * currently viewing. We don't want any links within a document to itself, but there are various reasons why you might
   * need that. Please use common sense. In general you should simply create the anchor element instead of calling this
   * method.
   *
   * @link http://www.nngroup.com/articles/avoid-within-page-links/ Avoid Within-Page Links
   * @param string $route
   *   The original English route.
   * @param string $text
   *   The translated text that should appear as link on the page.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the link element.
   * @return string
   *   The internal link ready for print.
   */
  protected final function a($route, $text, array $attributes = null) {
    // Recreate path to make sure we match the actual route and not the currently requested URI which might include
    // GET arguments.
    if ($route == $_SERVER["PATH_INFO"]) {
      // A hash keeps the anchor element itself valid but removes the link to the current page—perfect!
      $route = "#";
    }
    // Could be that the route that was passed to us is already a hash sign.
    if ($route == "#") {
      // Remove the title if we have one in the attributes array.
      if (isset($attributes["title"])) {
        unset($attributes["title"]);
      }
      $this->addClass("active", $attributes);
    }
    return "<a href='{$route}'{$this->expandTagAttributes($attributes)}>{$text}</a>";
  }

  /**
   * Add CSS class(es) to attributes array of an element.
   *
   * This method is useful if you're dealing with an element and you don't know if any CSS class(es) have already been
   * added to it's attributes array.
   *
   * @param string $class
   *   The CSS class(es) that should be added to the element's attributes array.
   * @param array $attributes
   *   The attributes array of the element to which the CSS class(es) should be added.
   * @return this
   */
  protected final function addClass($class, array &$attributes = []) {
    $attributes["class"] = empty($attributes["class"]) ? $class : "{$attributes["class"]} {$class}";
    return $this;
  }

  /**
   * Encode special characters in a plain-text string for display as HTML.
   *
   * <b>Always</b> use this method before displaying any plain-text string to the user.
   *
   * @param string $text
   *   The plain-text string to process.
   * @return string
   *   The <var>$text</var> with encoded HTML special characters.
   */
  protected final function checkPlain($text) {
    return htmlspecialchars($text, ENT_QUOTES|ENT_HTML5);
  }

  /**
   * Collapse all kinds of whitespace characters to a single space.
   *
   * @param string $string
   *   The string to collapse.
   * @return string
   *   The collapsed string.
   */
  protected final function collapseWhitespace($string) {
    return trim(preg_replace("/\s\s+/m", " ", preg_replace("/[\n\r\t\x{00}\x{0B}]+/m", " ", $string)));
  }

  /**
   * Expand the given attributes array to string.
   *
   * Many page elements aren't easily created by directly typing the string in the source code. Instead the have to go
   * through many staged of processing. We use associative arrays to allow all stages of processing to alter the
   * elemtns attributes before the element is finally printed. This method will expand these associative arrays to a
   * string that can be used to finally print the element.
   *
   * <b>Usage Example:</b>
   * <pre>$attributes = [ "class" => "css-class", "id" => "css-id" ];
   * echo "<div{$this->expandAttributes($attributes)}></div>";</pre>
   *
   * @param null|array $attributes [optional]
   *   Associative array containing the elements attributes. If no attributes are present (e.g. you're handling an
   *   object which sometimes has attributes but not always) an empty string will be returned.
   * @return string
   *   String representation of the attributes array, or empty string if no attributes are present.
   */
  protected final function expandTagAttributes(array $attributes = null) {
    $expanded = "";
    if (isset($attributes)) {
      foreach ($attributes as $name => $value) {
        if (is_numeric($name)) {
          $expanded .= " {$value}";
        }
        else {
          if (is_bool($value)) {
            $value = $value ? "true" : "false";
          }
          $expanded .= " {$name}='{$this->checkPlain($value)}'";
        }
      }
    }
    return $expanded;
  }

  /**
   * Format given Bytes to human readable form.
   *
   * <b>Example usages with Intl ICU</b>
   * <pre>$i18n->t("{0,number} {1}", [ $this->formatBytes($bytes) ]);</pre>
   *
   * @staticvar array $units
   *   Available file size units.
   * @param int $bytes
   *   The number to format.
   * @return array
   *   Numeric array containing the truncated number in offset 0 and the unit in offset 1.
   */
  protected final function formatBytes($bytes) {
    if ($bytes >= 1000000000000) {
      return [ round($bytes / 1000000000000, 2), "TB" ];
    }
    if ($bytes >= 1000000000) {
      return [ round($bytes / 1000000000, 2), "GB" ];
    }
    if ($bytes >= 1000000) {
      return [ round($bytes / 1000000, 2), "MB" ];
    }
    if ($bytes >= 1000) {
      return [ round($bytes / 1000, 2), "kB" ];
    }
    return [ round($bytes, 2), "B" ];
  }

  /**
   * Get the image.
   *
   * @param \MovLib\Data\Image\AbstractImage $image
   *   An instance of <code>AbstractImage</code>.
   * @param string $style
   *   The desired style, must be present within the passed <code>AbstractImage</code> instance.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the image. Please note that <code>"width"</code>,
   *   <code>"height"</code> and <code>"src"</code> are always set and overriden if set before. This is to ensure that
   *   the image really matches the desired style.
   * @param array $route [optional]
   *   The route for the anchor to surround the image with.
   * @param array $anchorAttributes [optional]
   *   The additional attributes for the anchor.
   * @return string
   *   The image.
   */
  protected final function getImage($image, $style, array $attributes = null, $route = null, array $anchorAttributes = null) {
    if ($image->imageExists === true) {
      if (!isset($attributes["alt"])) {
        $attributes["alt"] = "";
      }
      if ($route) {
        $this->addClass("ia", $anchorAttributes);
        return "<a{$this->expandTagAttributes($anchorAttributes)}></a>";
      }
      return "<img{$this->expandTagAttributes($image->getImageStyleAttributes($style, $attributes))}>";
    }
    return "no image";
  }

  /**
   * Get the next global tabindex.
   *
   * @return int
   */
  protected final function getTabindex() {
    return self::$tabindex++;
  }

  /**
   * Normalize all kinds of line feeds to *NIX style (real LF).
   *
   * @link http://stackoverflow.com/a/7836692/1251219 How to replace different newline styles in PHP the smartest way?
   * @param string $text
   *   The text to normalize.
   * @return string
   *   The normalized text.
   */
  protected final function normalizeLineFeeds($text) {
    return preg_replace("/\R/u", "\n", $text);
  }

  /**
   * Formats text for emphasized display in a placeholder inside a sentence.
   *
   * @param string $text
   *   The text to format (plain-text).
   * @return string
   *   The formatted text (html).
   */
  protected final function placeholder($text) {
    return "<em class='placeholder'>{$this->checkPlain($text)}</em>";
  }

}
