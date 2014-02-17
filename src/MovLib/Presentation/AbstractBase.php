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
namespace MovLib\Presentation;

/**
 * The abstract presentation class provides several HTML related utility methods.
 *
 * Almost all presentation related classes deal with HTML, the only exception are the API related presentation classes.
 * Any other class can extends this class and use the utility methods.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Protected Methods


  /**
   * Generate an internal link.
   *
   * This method should be used if you link to a page, but can't predict or know if this might be the page the user is
   * currently viewing. We don't want any links within a document to itself, but there are various reasons why you might
   * need that. Please use common sense. In general you should simply create the anchor element instead of calling this
   * method.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @link http://www.w3.org/TR/html5/text-level-semantics.html#the-a-element
   * @link http://www.nngroup.com/articles/avoid-within-page-links/ Avoid Within-Page Links
   * @param string $route
   *   The original English route.
   * @param string $text
   *   The translated text that should appear as link on the page.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the link element.
   * @param boolean $ignoreQuery [optional]
   *   Whether to ignore the query string while checking if the link should be marked active or not. Default is to
   *   ignore the query string.
   * @return string
   *   The internal link ready for print.
   */
  final protected function a($route, $text, array $attributes = null, $ignoreQuery = true) {
    global $i18n, $kernel;

    // We don't want any links to the current page (as per W3C recommendation). We also have to ensure that the anchors
    // aren't tabbed to, therefor we completely remove the href attribute. While we're at it we also remove the title
    // attribute because it doesn't add any value for screen readers without any target (plus the user is actually on
    // this very page).
    if ($route == $kernel->requestURI) {
      // Remove all attributes which aren't allowed on an anchor with empty href attribute.
      $unset = [ "download", "href", "hreflang", "rel", "target", "type" ];
      for ($i = 0; $i < 6; ++$i) {
        if (isset($attributes[$unset[$i]])) {
          unset($attributes[$unset[$i]]);
        }
      }
      // Ensure that this anchor is still "tabable".
      $attributes["tabindex"] = "0";
      $attributes["title"]    = $i18n->t("You’re currently viewing this page.");
      $this->addClass("active", $attributes);
    }
    else {
      // We also have to mark the current anchor as active if the caller requested that we ignore the query part of the
      // URI (default behaviour of this method). We keep the title attribute in this case as it's a clickable link.
      if ($ignoreQuery === true && $route == $kernel->requestPath) {
        $this->addClass("active", $attributes);
      }

      // Add the route to the anchor element.
      $attributes["href"] = $route;
    }

    // Put it all together.
    return "<a{$this->expandTagAttributes($attributes)}>{$text}</a>";
  }

  /**
   * Add CSS class(es) to attributes array of an element.
   *
   * This method is useful if you're dealing with an element and you don't know if any CSS class(es) have already been
   * added to it's attributes array.
   *
   * @param string $class
   *   The CSS class(es) that should be added to the element's attributes array.
   * @param array $attributes [optional]
   *   The attributes array of the element to which the CSS class(es) should be added.
   * @return this
   */
  final protected function addClass($class, array &$attributes = null) {
    $attributes["class"] = empty($attributes["class"]) ? $class : "{$attributes["class"]} {$class}";
    return $this;
  }

  /**
   * Collapse all kinds of whitespace characters to a single space.
   *
   * @param string $string
   *   The string to collapse.
   * @return string
   *   The collapsed string.
   */
  final protected function collapseWhitespace($string) {
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
   * @param null|array $attributes
   *   Associative array containing the elements attributes. If no attributes are present (e.g. you're handling an
   *   object which sometimes has attributes but not always) an empty string will be returned.
   * @return string
   *   String representation of the attributes array, or empty string if no attributes are present.
   */
  final protected function expandTagAttributes($attributes) {
    // Only expand if we have something to expand.
    if ($attributes) {
      // Local variables used to collect the expanded tag attributes.
      $expanded = null;

      // Ensure we start from the beginning of the array.
      reset($attributes);

      // Go through all attributes and expand them.
      foreach ($attributes as $name => $value) {
        // Special handling of boolean attributes, only include them if they are true and do not include the value.
        if ($value === (boolean) $value) {
          $value && ($expanded .= " {$name}");
        }
        // Special handling of empty attributes (added to the attributes array without any key).
        elseif ($name === (integer) $name) {
          // @devStart
          // @codeCoverageIgnoreStart
          if (empty($value)) {
            throw new \LogicException("The value of an empty attribute (numeric key) cannot be empty");
          }
          // @codeCoverageIgnoreEnd
          // @devEnd
          $expanded .= " {$value}";
        }
        // All other attributes are treated equally, but only if they have a value.
        elseif (!empty($value)) {
          // @devStart
          // @codeCoverageIgnoreStart
          if (empty($name)) {
            throw new \LogicException("An attribute's name cannot be empty");
          }
          // @codeCoverageIgnoreEnd
          // @devEnd

          // Only output the language attribute if it differs from the current document language.
          if ($name == "lang") {
            $expanded .= $this->lang($value);
          }
          else {
            $expanded .= " {$name}='{$this->htmlEncode($value)}'";
          }
        }
      }

      return $expanded;
    }
  }

  /**
   * Format given Bytes to human readable form.
   *
   * <b>Example usages with Intl ICU</b>
   * <pre>$i18n->t("{0,number} {1}", $this->formatBytes($bytes));</pre>
   *
   * @deprecated
   * @internal
   *   A loop is actually slower than the current implementation.
   * @staticvar array $units
   *   Available file size units.
   * @param int $bytes
   *   The number to format.
   * @return array
   *   Numeric array containing the truncated number in offset 0 and the unit in offset 1.
   */
  final protected function formatBytes($bytes) {
    // https://en.wikipedia.org/wiki/Mebibyte
    if ($bytes >= 1048576) {
      return [ ceil($bytes / 1048576), "<abbr title='Mebibyte'>MiB</abbr>" ];
    }
    // https://en.wikipedia.org/wiki/Kibibyte
    if ($bytes >= 1024) {
      return [ ceil($bytes / 1024), "<abbr title='Kibibyte'>KiB</abbr>" ];
    }
    // https://en.wikipedia.org/wiki/Byte
    return [ $bytes, "<abbr title='Byte'>B</abbr>" ];
  }

  /**
   * Get the image.
   *
   * @param \MovLib\Data\Image\Style $style
   *   The desired style object.
   * @param boolean $route [optional]
   *   The route to which the image should be linked, default <code>TRUE</code> will use the route from the style
   *   instance, set it to <code>FALSE</code> for no linking at all and include your own route for custom linking.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the image. Please note that the image specific attributes are
   *   always overriden by this method, this includes: <code>"src"</code>, <code>"width"</code> and <code>"height"</code>
   * @param array $anchorAttributes [optional]
   *   Additional attributes for the anchor.
   * @return string
   *   The image.
   */
  final protected function getImage($style, $route = true, array $attributes = null, array $anchorAttributes = null) {
    if (!isset($attributes["alt"])) {
      $attributes["alt"] = $style->alt;
    }
    if ($style->placeholder === true) {
      $this->addClass("placeholder", $attributes);

      // Ensure we don't declare any of our placeholder images as being an image for anything.
      if (isset($attributes["itemprop"])) {
        unset($attributes["itemprop"]);
      }
    }
    $attributes["src"]    = $style->src;
    $attributes["width"]  = $style->width;
    $attributes["height"] = $style->height;
    $image                = "<img{$this->expandTagAttributes($attributes)}>";

    if ($route !== false) {
      $this->addClass("img", $anchorAttributes);
      return $this->a(($route === true ? $style->route : $route), $image, $anchorAttributes);
    }
    return $image;
  }

  /**
   * Get the raw HTML string.
   *
   * @param string $text
   *   The encoded HTML string that should be decoded.
   * @return string
   *   The raw HTML string.
   */
  final protected function htmlDecode($text) {
    return htmlspecialchars_decode($text, ENT_QUOTES | ENT_HTML5);
  }

  /**
   * Decodes all HTML entities including numerical ones to regular UTF-8 bytes.
   *
   * Double-escaped entities will only be decoded once (<code>"&amp;lt;"</code> becomes <code>"&lt;"</code>, not
   * <code>"<"</code>). Be careful when using this function, as it will revert previous sanitization efforts
   * (<code>"&lt;script&gt;"</code> will become <code>"<script>"</code>).
   *
   * @param string $text
   *   The text to decode entities in.
   * @return string
   *   <var>$text</var> with all HTML entities decoded.
   */
  final protected function htmlDecodeEntities($text) {
    return html_entity_decode($text, ENT_QUOTES | ENT_HTML5);
  }

  /**
   * Encode special characters in a plain-text string for display as HTML.
   *
   * <b>Always</b> use this method before displaying any plain-text string to the user.
   *
   * @param string $text
   *   The plain-text string to process.
   * @return string
   *   <var>$text</var> with encoded HTML special characters.
   */
  final protected function htmlEncode($text) {
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5);
  }

  /**
   * Get global <code>lang</code> attribute for any HTML tag if language differs from current display language.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param string $lang
   *   The ISO alpha-2 language code of the entity you want to display and have compared to the current language.
   * @return null|string
   *   <code>NULL</code> if given <var>$lang</var> matches current display language, otherwise the global <code>lang</code>
   *   attribute ready for print (e.g. <code>" lang='de'"</code>).
   */
  final protected function lang($lang) {
    global $i18n;
    if ($lang != $i18n->languageCode) {
      $lang = $this->htmlEncode($lang);
      return " lang='{$lang}'";
    }
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
  final protected function normalizeLineFeeds($text) {
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
  final protected function placeholder($text) {
    return "<em class='placeholder'>{$this->htmlEncode($text)}</em>";
  }

}
