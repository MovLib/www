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
namespace MovLib\View\HTML;

use \MovLib\Utility\String;
use \ReflectionClass;

/**
 * The base view contains several utility methods used by all view objects.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractBaseView {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Global tabindex.
   *
   * @var int
   */
  private static $tabindex = 1;


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Get a comma separated list containing the items supplied.
   *
   * @param array $items
   *   The items to be displayed in the list.
   * @param string $ifNoItemsText
   *   The text that should be displayed if <var>$items</var> is empty.
   * @param callable $callback [optional]
   *   The callback will be called with each list item.
   * @return string
   *   The comma separated list.
   */
  public function getCommaSeparatedList(array $items, $ifNoItemsText, $callback = null) {
    if ($c = count($items)) {
      $list = "";
      for ($i = 0; $i < $c; ++$i) {
        if (!empty($items[$i])) {
          if ($i !== 0) {
            $list .= ", ";
          }
          $list .= $callback ? $callback($items[$i]) : $items[$i];
        }
      }
      return $list;
    }
    return $ifNoItemsText;
  }

  /**
   * Get a description list with <var>$items</var>.
   *
   * @param array $items
   *   The items for the description list, in the following format:
   *   <pre>$items = [
   *     [ "dt", "dd" ],
   *   ];</pre>
   * @param string $ifNoItemsText
   *   Text to return if <var>$items</var> is empty.
   * @param callable $callback [optional]
   *   The callback will be called with each list item, the first parameter is the description title and the second
   *   parameter is the description data, e.g. <code>$callback($dt, $dd)</code>.
   * @param array $attributes [optional]
   *   The HTML-attributes for the description list.
   * @return string
   *   The description list or the <var>$ifNoItemText</var> if <var>$items</var> is empty.
   */
  public function getDescriptionList(array $items, $ifNoItemsText, $callback = null, array $attributes = null) {
    if ($c = count($items)) {
      $list = "";
      for ($i = 0; $i < $c; ++$i) {
        if (!empty($items[$i])) {
          if ($callback) {
            $list .= $callback($items[$i][0], $items[$i][1]);
          }
          else {
            $list .= "<dt>{$items[$i][0]}</dt><dd>{$items[$i][1]}</dd>";
          }
        }
      }
      return "<dl{$this->expandTagAttributes($attributes)}>{$list}</dl>";
    }
    return $ifNoItemsText;
  }

   /**
   *
   * @param \MovLib\Model\AbstractImageModel $imageModel
   * @param string $style
   * @param array $attributes
   * @return string
   */
  public function getImage($imageModel, $style, array $attributes = null) {
    if ($imageModel->imageExists === true) {
      if (!isset($attributes["alt"])) {
        $attributes["alt"] = "";
      }
      $imageData = $imageModel->getImageStyle($style);
      $attributes["width"] = $imageData->width;
      $attributes["height"] = $imageData->height;
      $attributes["src"] = $imageData->uri;
      return "<img{$this->expandTagAttributes($attributes)}>";
    }
    return "no image";
  }

  /**
   * Get the views short class name (e.g. <em>abstract</em> for <em>AbstractView</em>).
   *
   * The short name is the name of the current instance of this class without the namespace only in lower case letters.
   * This is used to mark various HTML elements for easy CSS and JavaScript access. For instance the
   * <code>&lt;body&gt;</code>-element has this class applied, or the <code>&lt;div&gt;</code> that wraps the pages
   * content in full view (with <code>"-content"</code> suffix).
   *
   * @staticvar string $shortName
   *   Used to cache the short name of this instance.
   * @return string
   *   The short name of the class (lowercased).
   */
  public function getShortName() {
    static $shortName = null;
    if ($shortName === null) {
      // Always remove the "view" suffix from the name, this is redundant and not needed in the frontend.
      $shortName = substr(strtolower((new ReflectionClass($this))->getShortName()), 0, -4);
    }
    return $shortName;
  }

  /**
   * Get the HTML-code for an ordered list containing the items supplied.
   *
   * @param array $items
   *   The items to be displayed in the list.
   * @param string $ifNoItemsText
   *   The text that should be displayed if <var>$items</var> is empty.
   * @param callable $callback [optional]
   *   The callback will be called with each list item.
   * @param array $attributes [optional]
   *   The HTML-attributes for the unordered list.
   * @return string
   *   The unordered list.
   */
  public function getOrderedList(array $items, $ifNoItemsText, $callback = null, array $attributes = null) {
    if ($c = count($items)) {
      $list = "";
      for ($i = 0; $i < $c; ++$i) {
        if ($callback) {
          $list .= $callback($items[$i]);
        }
        else {
          $list .= "<li>{$items[$i]}</li>";
        }
      }
      return "<ol{$this->expandTagAttributes($attributes)}>{$list}</ol>";
    }
    return $ifNoItemsText;
  }

  /**
   * Get the HTML-code for an unordered list containing the items supplied.
   *
   * @param array $items
   *   The items to be displayed in the list.
   * @param string $ifNoItemsText
   *   The text that should be displayed if <var>$items</var> is empty.
   * @param callable $callback [optional]
   *   The callback will be called with each list item.
   * @param array $attributes [optional]
   *   The HTML-attributes for the unordered list.
   * @return string
   *   The unordered list.
   */
  public function getUnorderedList(array $items, $ifNoItemsText, $callback = null, array $attributes = null) {
    if ($c = count($items)) {
      $list = "";
      for ($i = 0; $i < $c; ++$i) {
        if ($callback) {
          $list .= $callback($items[$i]);
        }
        else {
          $list .= "<li>{$items[$i]}</li>";
        }
      }
      return "<ul{$this->expandTagAttributes($attributes)}>{$list}</ul>";
    }
    return $ifNoItemsText;
  }


  // ------------------------------------------------------------------------------------------------------------------- Protected Methods


  /**
   * Create HTML anchor element for MovLib internal links.
   *
   * <b>IMPORTANT:</b> Do not use this method to generate anchor elements for external links!
   *
   * <b>IMPORTANT:</b> Always use this method to generate crosslinks! This method ensures that no links within the
   * document point to the currently displayed document itself; as per W3C recommendation.
   *
   * @param string $route
   *   The expanded URL to which we should link (only internal routes).
   * @param string $text
   *   The already translated text that should be displayed as anchor.
   * @param array $attributes [optional]
   *   Array with attributes for the anchor element (e.g. title).
   * @return string
   *   The anchor element ready for print.
   */
  public function a($route, $text, array $attributes = null) {
    // Never create a link to the current page, we have to rebuild the current route because we aren't interested in
    // any arguments (query strings, fragments).
    // http://www.nngroup.com/articles/avoid-within-page-links/
    if ($route == "{$_SERVER["SCHEME"]}://{$_SERVER["SERVER_NAME"]}{$_SERVER["PATH_INFO"]}") {
      // A hash keeps the anchor element itself valid but removes the link to the current page—perfect!
      $route = "#";
      // Remove the title if we have one in the attributes array.
      if (isset($attributes["title"])) {
        unset($attributes["title"]);
      }
      $this->addClass("active", $attributes);
    }
    return "<a href='{$route}'{$this->expandTagAttributes($attributes)}>{$text}</a>";
  }

  /**
   * Add/overwrite attributes within the given attributes.
   *
   * @param array $newAttributes
   *   Array containing the new attributes.
   * @param mixed $oldAttributes
   *   The array containing the previously set attributes for the element. An array will be automatically created if the
   *   passed variable isn't one.
   * @return this
   */
  protected function addAttributes(array $newAttributes, &$oldAttributes) {
    if (!is_array($oldAttributes)) {
      $oldAttributes = [];
    }
    foreach ($newAttributes as $k => $v) {
      if (is_numeric($k)) {
        $oldAttributes[] = $v;
      }
      else {
        $oldAttributes[$k] = $v;
      }
    }
    return $this;
  }

  /**
   * Add CSS class(es).
   *
   * @param string $class
   *   String of CSS class(es) that should be added to <var>$attributes</var>.
   * @param mixed $attributes
   *   The array containing the previously set attributes for the elment. An array will be automatically created if the
   *   passed variable isn't one.
   * @return this
   */
  protected function addClass($class, &$attributes) {
    if (!is_array($attributes)) {
      $attributes = [];
    }
    $attributes["class"] = empty($attributes["class"]) ? $class : "{$attributes["class"]} {$class}";
    return $this;
  }

  /**
   * Expand the given HTML element attributes for usage on an HTML element.
   *
   * Attributes within the attributes array should be sorted alphabetically. This increases gzip efficiency if some
   * elements are repeated very often with the same attributes within a page (very unlikely, but just that you know).
   *
   * @param array $attributes [optional]
   *   The attributes that should be expanded.
   * @return string
   *   Expanded attributes or empty string.
   */
  protected function expandTagAttributes(array $attributes = null) {
    $expandedAttributes = "";
    if (!empty($attributes)) {
      foreach ($attributes as $attribute => $value) {
        switch ($attribute) {
          case "href":
          case "src":
          case "action":
            $value = strtr($value, "&", "&amp;");
            break;

          default:
            $value = String::checkPlain($value);
        }
        $expandedAttributes .= is_numeric($attribute) ? " {$value}" : " {$attribute}='{$value}'";
      }
    }
    return $expandedAttributes;
  }

  /**
   * Get current counter of the global <code>tabindex</code>-attribute for HTML elements and increment the static
   * variable associated with it once.
   *
   * Many browsers have a very strange <kbd>tab</kbd>-policy. This counter variable is to make sure that users who love
   * or have to use the keyboard can easily navigate through our pages. You should only use the tabindex for
   * <strong>important</strong> page elements. For instance, the main navigation isn't that important for a user if he
   * already reached the page he wants. On the other hand the header search field is a very important field, in contrast
   * to that the associated search submit button is not. If a user is using the <kbd>tab</kbd>-key to navigate through
   * the page, she or he most certainly also knows that he can easily submit the form by hitting enter within the search
   * field itself.
   *
   * Use all your knowledge as web user to decide whetever an element is important enough to make use of this index or
   * not.
   *
   * @link http://www.w3.org/TR/2010/WD-wai-aria-practices-20100916/#focus_tabindex
   * @link http://www.w3.org/TR/wai-aria/usage#managingfocus
   * @return int
   *   The current counter of the index.
   */
  protected function getTabindex() {
    return self::$tabindex++;
  }

}
