<?php

/* !
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
 * @todo Description of DependencyInjectionBase
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class DependencyInjectionBase extends \MovLib\Core\Presentation\Base {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Active global config instance.
   *
   * @var \MovLib\Core\Config
   */
  protected $config;

  /**
   * The active dependency injection container.
   *
   * @var \MovLib\Core\HTTP\DIContainerHTTP
   */
  protected $diContainerHTTP;

  /**
   * Active file system instance.
   *
   * @var \MovLib\Core\FileSystem
   */
  protected $fs;

  /**
   * Active intl instance.
   *
   * @var \MovLib\Core\Intl
   */
  protected $intl;

  /**
   * Active kernel instance.
   *
   * @var \MovLib\Core\Kernel
   */
  protected $kernel;

  /**
   * The active log instance.
   *
   * @var \MovLib\Core\Log
   */
  protected $log;

  /**
   * The presenting presenter.
   *
   * @var null|\MovLib\Presentation\AbstractPresenter
   */
  protected $presenter;

  /**
   * Active HTTP request instance.
   *
   * @var \MovLib\Core\HTTP\Request
   */
  protected $request;

  /**
   * Active HTTP response instance.
   *
   * @var \MovLib\Core\HTTP\Response
   */
  protected $response;

  /**
   * Active HTTP session instance.
   *
   * @var \MovLib\Core\HTTP\Session
   */
  protected $session;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new presentation object.
   *
   * @param \MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP
   *   HTTP dependency injection container.
   */
  public function __construct(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP) {
    $this->diContainerHTTP = $diContainerHTTP;
    $this->config          = $diContainerHTTP->config;
    $this->fs              = $diContainerHTTP->fs;
    $this->intl            = $diContainerHTTP->intl;
    $this->kernel          = $diContainerHTTP->kernel;
    $this->log             = $diContainerHTTP->log;
    $this->request         = $diContainerHTTP->request;
    $this->response        = $diContainerHTTP->response;
    $this->session         = $diContainerHTTP->session;
    if (isset($diContainerHTTP->presenter)) {
      $this->presenter = $diContainerHTTP->presenter;
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Generate an internal link.
   *
   * This method should be used if you link to a page, but can't predict or know if this might be the page the user is
   * currently viewing. We don't want any links within a document to itself, but there are various reasons why you might
   * need that. Please use common sense. In general you should simply create the anchor element instead of calling this
   * method.
   *
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
  final public function a($route, $text, array $attributes = null, $ignoreQuery = true) {
    // We don't want any links to the current page (as per W3C recommendation). We also have to ensure that the anchors
    // aren't tabbed to, therefor we completely remove the href attribute. While we're at it we also remove the title
    // attribute because it doesn't add any value for screen readers without any target (plus the user is actually on
    // this very page).
    if ($route == $this->request->uri) {
      // Remove all attributes which aren't allowed on an anchor with empty href attribute.
      $unset = [ "download", "href", "hreflang", "rel", "target", "type" ];
      for ($i = 0; $i < 6; ++$i) {
        if (isset($attributes[$unset[$i]])) {
          unset($attributes[$unset[$i]]);
        }
      }
      // Ensure that this anchor is still "tabable".
      $attributes["tabindex"] = "0";
      $attributes["title"]    = $this->intl->t("You’re currently viewing this page.");
      $this->addClass("active", $attributes);
    }
    else {
      // We also have to mark the current anchor as active if the caller requested that we ignore the query part of the
      // URI (default behaviour of this method). We keep the title attribute in this case as it's a clickable link.
      if ($ignoreQuery === true && $route == $this->request->path) {
        $this->addClass("active", $attributes);
      }

      // Add the route to the anchor element.
      $attributes["href"] = $route{0} == "#" ? $route : $this->fs->urlEncodePath($route);
    }

    // Put it all together.
    return "<a{$this->expandTagAttributes($attributes)}>{$text}</a>";
  }

  /**
   * Get a callout.
   *
   * @param string $message
   *   The callout's message.
   * @param string $title [optional]
   *   The callout's title, defaults to <code>NULL</code>.
   * @param array $attributes [optional]
   *   The callout's additional attributes, defaults to an empty array.
   * @param integer $level [optional]
   *   The callout's heading level, defaults to <code>3</code>.
   * @param string $type [optional]
   *   The callout's type, one of <code>NULL</code> (default), <code>"error"</code>, <code>"info"</code>,
   *   <code>"success"</code> or <code>"warning"</code>.
   * @return string
   *   The callout.
   */
  final public function callout($message, $title = null, $attributes = [], $level = 3, $type = null) {
    $title && ($title = "<h{$level} class='title'>{$title}</h{$level}>");
    $type  && ($type = " callout-{$type}");
    $this->addClass("callout{$type}", $attributes);
    return "<div{$this->expandTagAttributes($attributes)}>{$title}{$message}</div>";
  }

  /**
   * Get an error callout.
   *
   * <b>Color: RED</b>
   *
   * @param string $message
   *   The callout's message.
   * @param string $title [optional]
   *   The callout's title, defaults to <code>NULL</code>.
   * @param array $attributes [optional]
   *   The callout's additional attributes, defaults to an empty array.
   * @param integer $level [optional]
   *   The callout's heading level, defaults to <code>3</code>.
   * @return string
   *   The error callout.
   */
  final public function calloutError($message, $title = null, $attributes = [], $level = 3) {
    return $this->callout($message, $title, $attributes, $level, "error");
  }

  /**
   * Get an info callout.
   *
   * <b>Color: BLUE</b>
   *
   * @param string $message
   *   The callout's message.
   * @param string $title [optional]
   *   The callout's title, defaults to <code>NULL</code>.
   * @param array $attributes [optional]
   *   The callout's additional attributes, defaults to an empty array.
   * @param integer $level [optional]
   *   The callout's heading level, defaults to <code>3</code>.
   * @return string
   *   The info callout.
   */
  final public function calloutInfo($message, $title = null, $attributes = [], $level = 3) {
    return $this->callout($message, $title, $attributes, $level, "info");
  }

  /**
   * Get an success callout.
   *
   * <b>Color: GREEN</b>
   *
   * @param string $message
   *   The callout's message.
   * @param string $title [optional]
   *   The callout's title, defaults to <code>NULL</code>.
   * @param array $attributes [optional]
   *   The callout's additional attributes, defaults to an empty array.
   * @param integer $level [optional]
   *   The callout's heading level, defaults to <code>3</code>.
   * @return string
   *   The success callout.
   */
  final public function calloutSuccess($message, $title = null, $attributes = [], $level = 3) {
    return $this->callout($message, $title, $attributes, $level, "success");
  }

  /**
   * Get an info callout.
   *
   * <b>Color: ORANGE</b>
   *
   * @param string $message
   *   The callout's message.
   * @param string $title [optional]
   *   The callout's title, defaults to <code>NULL</code>.
   * @param array $attributes [optional]
   *   The callout's additional attributes, defaults to an empty array.
   * @param integer $level [optional]
   *   The callout's heading level, defaults to <code>3</code>.
   * @return string
   *   The warning callout.
   */
  final public function calloutWarning($message, $title = null, $attributes = [], $level = 3) {
    return $this->callout($message, $title, $attributes, $level, "warning");
  }

  /**
   * Output a "check back later" callout for a feature that isn't implemented yet.
   *
   * @param string $what
   *   The translated feature title.
   * @param integer $level [optional]
   *   The callout's heading level, defaults to <code>2</code>.
   * @return string
   *   The "check back later" callout.
   */
  final public function checkBackLater($what, $level = 2) {
    return $this->calloutInfo(
      $this->intl->t("The {0} feature isn’t implemented yet.", $this->placeholder($what)),
      $this->intl->t("Check back later"),
      null,
      $level
    );
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
  final public function expandTagAttributes($attributes) {
    // Only expand if we have something to expand.
    if ($attributes) {
      // Local variables used to collect the expanded tag attributes.
      $expanded = null;

      // Go through all attributes and expand them.
      foreach ((array) $attributes as $name => $value) {
        // @devStart
        // @codeCoverageIgnoreStart
        assert(!empty($name), "The name of an attribute cannot be empty.");
        // @codeCoverageIgnoreEnd
        // @devEnd

        // Any attribute that starts with a hash is a configuration attribute and shouldn't be included in the actually
        // printed attributes.
        if ($name{0} == "#") {
          continue;
        }

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
        // All other attributes are treated equally, but only if they have a value. But beware that the alt attribute
        // is an exception to this rule.
        elseif ($name == "alt" || !empty($value)) {
          // Only output the language attribute if it differs from the current document language.
          if ($name == "lang" && $this->intl->languageCode == $value) {
            continue;
          }

          // Some attributes pass their values as arrays, which we have to expand to a space separated list.
          if ($value === (array) $value) {
            $value = implode(" ", $value);
          }

          // Only encode the value if it's actually not empty. Note that the not empty check from above might not cover
          // this situation here because the attribute could be an alternative text.
          if (!empty($value)) {
            $value = $this->htmlEncode($value);
          }

          // Finally we can put it all together.
          $expanded .= " {$name}='{$value}'";
        }
      }

      return $expanded;
    }
  }

  /**
   * Format the given weblinks.
   *
   * @param array $weblinks
   *   The weblinks to format.
   * @return null|string
   *   The formatted weblinks, <code>NULL</code> if there are no weblinks to format.
   */
  final public function formatWeblinks(array $weblinks) {
    if (empty($weblinks)) {
      return;
    }
    $formatted = null;
    $c = count($weblinks);
    for ($i = 0; $i < $c; ++$i) {
      if ($formatted) {
        $formatted .= trim($this->intl->t("{0}, {1}"), "{}01");
      }
      $weblink = str_replace("www.", "", parse_url($weblinks[$i], PHP_URL_HOST));
      $formatted .= "<a href='{$weblinks[$i]}' target='_blank'>{$weblink}</a>";
    }
    return $formatted;
  }

  /**
   * Get global <code>lang</code> attribute for any HTML tag if language differs from current display language.
   *
   * @param string $lang
   *   The ISO alpha-2 language code of the entity you want to display and have compared to the current language.
   * @return null|string
   *   <code>NULL</code> if given <var>$lang</var> matches current display language, otherwise the global <code>lang</code>
   *   attribute ready for print (e.g. <code>" lang='de'"</code>).
   */
  final public function lang($lang) {
    if ($lang != $this->intl->languageCode) {
      return " lang='{$this->htmlEncode($lang)}'";
    }
  }

}
