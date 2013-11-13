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
 * HTML contenteditable text form element.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Content_Editable
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputHTML extends \MovLib\Presentation\Partial\FormElement\AbstractFormElement {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The text's allowed HTML tag as associative array.
   * The keys consist of the predifined tidy constants. @see http://www.php.net/manual/en/tidy.constants.php
   *
   * @var array
   */
  protected $allowedTags = [
    TIDY_TAG_A  => "&lt;a&gt;",
    TIDY_TAG_B  => "&lt;b&gt;",
    TIDY_TAG_BR => "&lt;br&gt;",
    TIDY_TAG_H2 => "&lt;h2&gt;",
    TIDY_TAG_H3 => "&lt;h3&gt;",
    TIDY_TAG_H4 => "&lt;h4&gt;",
    TIDY_TAG_H5 => "&lt;h5&gt;",
    TIDY_TAG_H6 => "&lt;h6&gt;",
    TIDY_TAG_I  => "&lt;i&gt;",
    TIDY_TAG_P  => "&lt;p&gt;",
  ];

  /**
   * The HTML tags that don't need ending tags.
   * The keys consist of the predifined tidy constants. @see http://www.php.net/manual/en/tidy.constants.php
   *
   * @var array
   */
  protected $emptyTags = [
    TIDY_TAG_BR => true,
  ];

  /**
   * The text's raw content.
   *
   * @var null|string
   */
  protected $contentRaw;

  /**
   * The CSS classes the user can apply.
   *
   * @var array
   */
  protected $userClasses = [
    "user-left"   => true,
    "user-center" => true,
    "user-right"  => true,
  ];

  /**
   * The text's encoded content.
   *
   * @var null|string
   */
  public $value;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new HTML form element.
   *
   * @param string $id
   *   The text's global identifier.
   * @param string $label
   *   The text's label text.
   * @param mixed $content [optional]
   *   The text's content, defaults to <code>NULL</code> (no content).
   * @param array $attributes [optional]
   *   Additional attributes for the text, defaults to <code>NULL</code> (no additional attributes).
   * @param string $help [optional]
   *   The text's help text, defaults to <code>NULL</code> (no help text).
   * @param boolean $helpPopup
   *   Whether the help should be displayed as popup or not, defaults to <code>TRUE</code> (display as popup).
   */
  public function __construct($id, $label, $content = null, array $attributes = null, $help = null, $helpPopup = true) {
    parent::__construct($id, $label, $attributes, $help, $helpPopup);
    $this->attributes["aria-multiline"]  = "true";
    $this->attributes["contenteditable"] = "true";
    if (isset($this->attributes["data-allow-img"])) {
      $this->allowedTags[TIDY_TAG_IMG] = "&lt;img&gt;";
    }
    $this->contentRaw = $content;
    if (isset($_POST[$this->id])) {
      $this->contentRaw = empty($_POST[$this->id]) ? null : $_POST[$this->id];
    }
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    if (empty($this->contentRaw)) {
      $this->contentRaw = htmlspecialchars_decode($this->value, ENT_QUOTES | ENT_HTML5);
    }
//    return "{$this->help}<p><label for='{$this->id}'>{$this->label}</label><textarea{$this->expandTagAttributes($this->attributes)}>{$this->contentRaw}</textarea></p>";
    return "<fieldset>{$this->help}<legend>{$this->label}</legend><div{$this->expandTagAttributes($this->attributes)}>{$this->contentRaw}</div></fieldset>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  public function validate() {
    global $i18n;
    // Validate if we have input and throw an Exception if the field is required.
    if (empty($this->contentRaw)) {
      if (array_key_exists("required", $this->attributes)) {
        throw new ValidationException($i18n->t("“{0}” text is mandatory.", [ $this->label ]));
      }
      $this->value = "";
      return $this;
    }

    // Parse the HTML input with tidy and clean it.
    try {
      /* @var $tidy \tidy */
      $tidy = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body>{$this->contentRaw}</body></html>");
      $tidy->cleanRepair();
      if ($tidy->getStatus() === 2) {
        throw new \ErrorException;
      }
    }
    catch (\ErrorException $e) {
      throw new ValidationException($i18n->t("Invalid HTML in “{0}” text.", [ $this->label ]));
    }

    // Traverse through the constructed document and validate its contents.
    $level = 0;
    /* @var $node \tidyNode */
    $node = null;
    $nodes = [ $level => [ $tidy->body() ]];
    $endTags = [];
    $output = null;
    do {
      while (!empty($nodes[$level])) {
        // Retrieve the next node from the stack.
        $node = array_shift($nodes[$level]);

        if ($level > 0) {
          // Validate tag and attributes.
          if ($node->type === TIDY_NODETYPE_TEXT) {
            // Clean text and append to output.
            $output = "{$output}{$this->checkPlain($node->value)}";
          }
          elseif (isset($this->allowedTags[$node->id])) {
            if (method_exists($this, "validateTag{$node->name}")) {
              $node->name = $this->{"validateTag{$node->name}"}($node);
            }
            // Stack a closing tag to the current level, if needed.
            if (!isset($this->emptyTags[$node->id])) {
              $endTags[$level][] = "</{$node->name}>";
            }
            // Append a starting tag of the current node to the output.
            $output = "{$output}<{$node->name}>";
          }
          else {
            // Encountered a tag that is not allowed, abort.
            $allowedTags = implode(" ", array_values($this->allowedTags));
            throw new ValidationException($i18n->t(
              "The “{0}” text contains invalid HTML tags. Allowed tags are: {1}",
              [ $this->label, "<code>{$allowedTags}</code>" ],
              [ "comment" => "{0} is the name of the text, {1} is a list of allowed HTML tags. Both should not be translated." ]
            ));
          }
        }
        // Stack the child nodes to the next level if there are any.
        if (!empty($node->child)) {
          $level++;
          $nodes[$level] = $node->child;
        }
      }
      $level--;
      // Append all ending tags of the current level to the output, if we are higher than level 0 and if there are any.
      if ($level > 0 && isset($endTags[$level])) {
        while (($endTag = array_pop($endTags[$level]))) {
          $output = "{$output}{$endTag}";
        }
      }
    }
    while ($level > 0);

    // Parse and format the validated HTML output.
    try {
      $tidy = tidy_parse_string(
        "<!doctype html><html><head><title>MovLib</title></head><body>{$output}</body></html>",
        $tidyOptions,
        "utf8"
      );
      $tidy->cleanRepair();
      if ($tidy->getStatus() === 2) {
        throw new \ErrorException;
      }
    }
    catch (\ErrorException $e) {
      error_log($e);
      throw new ValidationException($i18n->t("Invalid HTML after the validation in “{0}” text.", [ $this->label ]), $e->code, $e);
    }

    // Replace redundant newlines, normalize UTF-8 characters and encode HTML characters.
    $this->value = $this->checkPlain(\Normalizer::normalize(str_replace(tidy_get_output($tidy), "\n\n", "\n")));
    \FB::send(htmlspecialchars_decode($this->value, ENT_QUOTES | ENT_HTML5));
    return $this;
  }

  /**
   * Validates and sanitizes HTML anchors.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @param \tidyNode $node
   *   The anchor.
   * @return string
   *   The tag name with the validated attributes.
   */
  protected function validateTagA($node) {
    global $i18n, $kernel;
    $attributes = [];
    $validateURL = null;

    // Check if the <code>href</code> attribute was set and validate the URL.
    if (!isset($node->attribute) || empty($node->attribute["href"])) {
      throw new ValidationException($i18n->t("Links without a link target in “{0}” text.", [ $this->label ]));
    }

    // Parse and validate the parts of the URL.
    if (($parts = parse_url($node->attribute["href"])) === false || !isset($parts["host"])) {
      throw new ValidationException($i18n->t(
        "Invalid link in “{0}” text ({1}).",
        [ $this->label, "<code>{$this->checkPlain($node->attribute["href"])}</code>" ],
        [ "comment" => "{0} is the name of the text, {1} is the value of the link’s href attribute. Both should not be translated." ]
      ));
    }

    // Make protocol relative URLs for the internal domain and omit query string.
    if ($parts["host"] == $kernel->domainDefault || strpos($parts["host"], ".{$kernel->domainDefault}") !== false) {
      $attributes["href"] = "//{$parts["host"]}";
      // This is needed, because filter_var doesn't accept protocol relative URLs.
      $validateURL = "{$kernel->scheme}:{$attributes["href"]}";
      $parts["query"] = null;
    }
    // Add rel="nofollow" to external links, sanitize the protocol and fill query string offset if it doesn't exist.
    // If external links are not allowed, abort.
    else {
      if (!isset($this->attributes["data-external"])) {
        throw new ValidationException($i18n->t("No external links are allowed in “{0}” text.", [ $this->label ]));
      }
      if (isset($parts["scheme"]) && ($parts["scheme"] == "http" || $parts["scheme"] == "https")) {
        $attributes["href"] = "{$parts["scheme"]}://";
      }
      else {
        $attributes["href"] = "http://";
      }
      $attributes["rel"] = "nofollow";
      $attributes["href"] = "{$attributes["href"]}{$parts["host"]}";
      $validateURL = $attributes["href"];
      $parts["query"] = isset($parts["query"]) ? "?{$parts["query"]}" : null;
    }

    // Initialize the path offset with "/" if it doesn't exist.
    $parts["path"] = isset($parts["path"]) ? $parts["path"] : "/";

    // Initialize the fragment offset with null if it doesn't exist.
    $parts["fragment"] = isset($parts["fragment"]) ? "#{$parts["fragment"]}" : null;

    // Append path, query and fragment to the URL.
    $attributes["href"] = "{$attributes["href"]}{$parts["path"]}{$parts["query"]}{$parts["fragment"]}";
    $validateURL = "{$validateURL}{$parts["path"]}{$parts["query"]}{$parts["fragment"]}";

    // Validate user, password and port, since we don't allow them.
    if (isset($parts["user"]) || isset($parts["pass"])) {
      throw new ValidationException($i18n->t(
        "Credentials are not allowed in “{0}” text ({1}).",
        [ $this->label, "<code>{$this->checkPlain($node->attribute["href"])}</code>" ],
        [ "comment" => "{0} is the name of the text, {1} is the value of the link’s href attribute. Both should not be translated." ]
      ));
    }
    if (isset($parts["port"])) {
      throw new ValidationException($i18n->t(
        "Ports are not allowed in “{0}” text ({1}).",
        [ $this->label, "<code>{$node->attribute["href"]}</code>" ],
        [ "comment" => "{0} is the name of the text, {1} is the value of the link’s href attribute. Both should not be translated." ]
      ));
    }

    if (filter_var($validateURL, FILTER_VALIDATE_URL, FILTER_REQUIRE_SCALAR | FILTER_FLAG_HOST_REQUIRED) === false) {
      throw new ValidationException($i18n->t(
        "Invalid link in “{0}” text ({1}).",
        [ $this->label, "<code>{$node->attribute["href"]}</code>" ],
        [ "comment" => "{0} is the name of the text, {1} is the value of the link’s href attribute. Both should not be translated." ]
      ));
    }

    return "a{$this->expandTagAttributes($attributes)}";
  }

  /**
   * Validates and sanitizes HTML images.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @param \tidyNode $node
   *   The image.
   * @return string
   *   The tag name with the validated attributes.
   */
  protected function validateTagImg($node) {
    global $i18n, $kernel;
    $attributes = [];

    // Check if the image contains the required <code>src</code> attribute.
    if (!isset($node->attribute) || !isset($node->attribute["src"])) {
      throw new ValidationException($i18n->t(
        "Empty image {0} attribute in “{1}” text.",
        [ "<code>src</code>", $this->label ],
        [ "comment" => "{0} is <code>src</code>, {1} is the name of the text. Both should not be translated." ]
      ));
    }

    // Validate the <code>src</code> URL.
    if (filter_var($node->attribute["src"], FILTER_VALIDATE_URL, FILTER_REQUIRE_SCALAR | FILTER_FLAG_HOST_REQUIRED) === false) {
      throw new ValidationException($i18n->t(
        "Invalid image {0} attribute in “{1}” text.",
        [ "<code>src</code>", $this->label ],
        [ "comment" => "{0} is <code>src</code>, {1} is the name of the text. Both should not be translated." ]
      ));
    }

    // Check if the image comes from our server.
    $url = parse_url($node->attribute["src"]);
    if (strpos($url["host"], $kernel->domainStatic) === false) {
      throw new ValidationException($i18n->t(
        "Only {0} images are allowed in “{1}” text.",
        [ $kernel->siteName, $this->label ],
        [ "comment" => "{0} is our site name, {1} is the name of the text. Both should not be translated." ]
      ));
    }

    // Check if the image exists and set <code>src</code>, <code>width</code> and <code>height</code> accordingly.
    try {
      $image = getimagesize("{$kernel->documentRoot}/public/upload{$url["path"]}");
    }
    catch (\ErrorException $e) {
      throw new ValidationException($i18n->t(
        "Image doesn’t exist in “{0}” text ({1}).",
        [ $this->label, "<code>{$this->checkPlain($node->attribute["src"])}</code>" ],
        [ "comment" => "{0} is the name of the text, {1} is the value of the image’s src attribute. Both should not be translated." ]
      ));
    }
    $attributes["src"] = $node->attribute["src"];
    $attributes["width"]  = $image[0];
    $attributes["height"] = $image[1];

    // Encode the <code>alt</code> attribute or fill in an empty one if it wasn't set.
    if (isset($node->attribute["alt"])) {
      $attributes["alt"] = $this->checkPlain($node->attribute["alt"]);
    }
    else {
      $attributes["alt"] = "";
    }

    return "img{$this->expandTagAttributes($attributes)}";
  }

  /**
   * Validates and sanitizes HTML paragraphs.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param \tidyNode $node
   *   The paragraph.
   * @return string
   *   The tag name with the validated attributes.
   */
  protected function validateTagP($node) {
    global $i18n;
    $class = null;
    // Validate that the <code>class</code> attribute only contains our user defined CSS classes.
    if (isset($node->attribute) && isset($node->attribute["class"]) && !isset($this->userClasses[$node->attribute["class"]])) {
      $classes = implode(" ", array_keys($this->userClasses));
      throw new ValidationException($i18n->t(
        "Invalid {0} attribute found in “{1}” text, allowed values are: {2}",
        [ "<code>class</code>", $this->label, "<code>{$classes}</code>" ],
        [ "comment" => "{0} is <code>class</code>, {1} is the name of the text, {2} is a list of allowed class values. All those should not be translated." ]
      ));
    }
    else {
      $class = " class='{$node->attribute["class"]}'";
    }
    return "p{$class}";
  }

}
