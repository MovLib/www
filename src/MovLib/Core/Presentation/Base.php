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
namespace MovLib\Core\Presentation;

/**
 * Defines methods that are used in all classes that deal with presentations.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Base {

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
  final public function addClass($class, array &$attributes = null) {
    $attributes["class"] = empty($attributes["class"]) ? $class : "{$attributes["class"]} {$class}";
    return $this;
  }

  // @devStart
  // @codeCoverageIgnoreStart
  /**
   * Generate random "Lorem Ipsum" text.
   *
   * <b>NOTE</b><br>
   * Using this method will make your page slow!
   *
   * @param integer $amount [optional]
   *   How much of <var>$what</var>.
   * @param string $what [optional]
   *   One of <code>"paras"</code> (default), <code>"words"</code>, <code>"bytes"</code>, or <code>"lists"</code>.
   * @param start $start [optional]
   *   Start offset in the returned text.
   * @return string
   *   Random "Lorem Ipsum" text.
   */
  final public function blindtext($amount = 1, $what = "paras", $start = 0) {
    return simplexml_load_file("http://www.lipsum.com/feed/xml?amount={$amount}&what={$what}&start={$start}")->lipsum;
  }
  // @codeCoverageIgnoreEnd
  // @devEnd

  /**
   * Collapse all kinds of whitespace characters to a single space.
   *
   * @param string $string
   *   The string to collapse.
   * @return string
   *   The collapsed string.
   */
  final public function collapseWhitespace($string) {
    return trim(preg_replace("/\s\s+/m", " ", preg_replace("/[\n\r\t\x{00}\x{0B}]+/m", " ", $string)));
  }

  /**
   * Get a callout.
   *
   * @param string $title
   *   The callout's title.
   * @param string $message
   *   The callout's message.
   * @param string $type [optional]
   *   The callout's type, one of <code>NULL</code> (default), <code>"danger"</code>, <code>"info"</code>, or
   *   <code>"warning"</code>.
   * @param integer $level [optional]
   *   The callout's heading level, defaults to <code>3</code>.
   * @return string
   *   The callout.
   */
  public function callout($title, $message, $type = null, $level = 3) {
    $type && ($type = " callout-{$type}");
    return "<div class='callout{$type}'><h{$level} class='title'>{$title}</h{$level}>{$message}</div>";
  }

  /**
   * Get an image.
   *
   * @param \MovLib\Data\Image\ImageStyle $imageStyle
   *   The image style to get the image for.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the image tag, defaults to <code>[]</code>.
   * @param boolean|string $route [optional]
   *   Whether to use the default route from the image style for linking (<code>TRUE</code> and default) or not
   *   <code>FALSE</code> or an arbitrary route.
   * @param array $routeAttributes [optional]
   *   Additional attributes that should be applied to the wrapping anchor element if <var>$route</var> is set to
   *   <code>TRUE</code> or an arbitrary route is given.
   * @return string
   *   The image.
   */
  final public function img(\MovLib\Data\Image\ImageStyle $imageStyle, array $attributes = [], $route = true, array $routeAttributes = null) {
    // The alt attribute is mandatory on image elements.
    if (empty($attributes["alt"])) {
      $attributes["alt"] = $imageStyle->alt;
    }

    // Add CSS class for additional styling and be sure to remove any structured data from the image tag if this is a
    // placeholder image we're going to display.
    if ($imageStyle->placeholder) {
      $this->addClass("placeholder", $attributes);

      if (isset($attributes["property"])) {
        unset($attributes["property"]);
      }
    }

    // Extract the necessary image tag attributes from the image style.
    $attributes["src"]    = $imageStyle->url;
    $attributes["width"]  = $imageStyle->width;
    $attributes["height"] = $imageStyle->height;
    $image                = "<img{$this->expandTagAttributes($attributes)}>";

    if ($route !== false) {
      // @devStart
      // @codeCoverageIgnoreStart
      assert(
        empty($attributes["property"]),
        "Don't set a property and link an image, this won't be interpreted correctly by structured data tools!"
      );
      // @codeCoverageIgnoreEnd
      // @devEnd
      if ($route === true) {
        $route = $imageStyle->route;
      }
      $routeAttributes["href"] = $route;
      $this->addClass("no-link", $routeAttributes);
      $image = "<a{$this->expandTagAttributes($routeAttributes)}>{$image}</a>";
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
  final public function htmlDecode($text) {
    if (empty($text)) {
      // @devStart
      // @codeCoverageIgnoreStart
      if (isset($this->log)) {
        $this->log->debug("You should avoid passing empty texts to htmlDecode");
      }
      // @codeCoverageIgnoreEnd
      // @devEnd
      return $text;
    }
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
  final public function htmlDecodeEntities($text) {
    if (empty($text)) {
      // @devStart
      // @codeCoverageIgnoreStart
      if (isset($this->log)) {
        $this->log->debug("You should avoid passing empty texts to htmlDecodeEntities");
      }
      // @codeCoverageIgnoreEnd
      // @devEnd
      return $text;
    }
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
  final public function htmlEncode($text) {
    if (empty($text)) {
      // @devStart
      // @codeCoverageIgnoreStart
      if (isset($this->log)) {
        $this->log->debug("You should avoid passing empty texts to htmlEncode");
      }
      // @codeCoverageIgnoreEnd
      // @devEnd
      return $text;
    }
    return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5);
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
  final public function normalizeLineFeeds($text) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($text) || !is_string($text)) {
      throw new \InvalidArgumentException("\$text cannot be empty and must be of type string.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
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
  final public function placeholder($text) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($text) || !is_string($text)) {
      throw new \InvalidArgumentException("\$text cannot be empty and must be of type string.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    return "<em class='placeholder'>{$this->htmlEncode($text)}</em>";
  }

}
