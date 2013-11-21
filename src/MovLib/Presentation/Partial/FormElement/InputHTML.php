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
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputHTML extends \MovLib\Presentation\Partial\FormElement\AbstractFormElement {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The text's allowed HTML tag as associative array.
   * The keys consist of the tag names.
   *
   * @var array
   */
  protected $allowedTags = [
    "a"  => "&lt;a&gt;",
    "b"  => "&lt;b&gt;",
    "br" => "&lt;br&gt;",
    "i"  => "&lt;i&gt;",
    "p"  => "&lt;p&gt;",
  ];

  /**
   * Configuration flag to determine if external links are allowed.
   *
   * @var boolean
   */
  protected $allowExternalLinks = false;

  /**
   * The HTML tags that don't need ending tags.
   * The keys consist of the tag names.
   *
   * @var array
   */
  protected $emptyTags = [
    "br"  => true,
    "img" => true,
  ];

  /**
   * The text's translated placeholder.
   *
   * @var string
   */
  protected $placeholder;

  /**
   * Configuration flag to determine if the text is required.
   *
   * @var boolean
   */
  protected $required = false;

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
   * The text's escaped content.
   *
   * @var null|string
   */
  public $value;

  /**
   * The text's raw content.
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
   * @param string $placeholder [optional]
   *   The text's placeholder, defaults to <code>"Enter the $label text here …"</code>.
   * @param array $attributes [optional]
   *   Additional attributes for the text, defaults to <code>NULL</code> (no additional attributes).
   * @param string $help [optional]
   *   The text's help text, defaults to <code>NULL</code> (no help text).
   * @param boolean $helpPopup
   *   Whether the help should be displayed as popup or not, defaults to <code>TRUE</code> (display as popup).
   */
  public function __construct($id, $label, $value = null, $placeholder = null, array $attributes = null, $help = null, $helpPopup = true) {
    global $kernel;
    parent::__construct($id, $label, $attributes, $help, $helpPopup);
    unset($this->attributes["name"]);
    unset($this->attributes["required"]);
    $this->attributes["aria-multiline"]  = "true";
    $this->attributes["contenteditable"] = "true";
    $this->attributes["role"]            = "textbox";
    $this->placeholder                   = $placeholder;
    if (!empty($_POST[$this->id])) {
      $this->value    = $kernel->htmlEncode($_POST[$this->id]);
      $this->valueRaw = $_POST[$this->id];
    }
    elseif ($value) {
      $this->value    = $value;
      $this->valueRaw = $kernel->htmlDecode($value);
    }
    $kernel->javascripts[] = "InputHTML";
  }

  /**
   * @inheritdoc
   */
  protected function render() {
    global $i18n, $kernel;
    $this->addClass("inputhtml-content", $this->attributes);
    if (!$this->placeholder) {
      $this->placeholder = $i18n->t("Enter the “{0}” text here …", [ $this->label ]);
    }
//    return "{$this->help}<p><label for='{$this->id}'>{$this->label}</label><textarea{$this->expandTagAttributes($this->attributes)}>{$this->contentRaw}</textarea></p>";
    return "{$this->help}<fieldset><legend>{$this->label}</legend><div class='inputhtml'><div{$this->expandTagAttributes($this->attributes)}>{$this->valueRaw}</div><span aria-hidden='true' class='placeholder'>{$this->placeholder}</span></div></fieldset>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods



  public function allowBlockqoutes() {
    $this->allowedTags["blockquote"] = "&lt;blockquote&gt;";
    return $this;
  }

  /**
   * Configures the text to allow external links.
   *
   * @return $this
   */
  public function allowExternalLinks() {
    $this->allowExternalLinks = true;
    return $this;
  }

  /**
   * Configures the text to allow headings, starting at <code>2</code>.
   *
   * @param int $level
   *   The starting level of the headings. Allowed values are <code>2</code> to <code>6</code>. Defaults to <code>3</code>.
   * @return $this
   */
  public function allowHeadings($level = 3) {
    if ($level >= 2) {
      for ($i = $level; $i <= 6; ++$i) {
        $this->allowedTags["h{$i}"] = "&lt;h{$i}&gt;";
      }
    }
    return $this;
  }

  /**
   * Configures the text to allow images.
   *
   * @return $this
   */
  public function allowImages() {
    $this->allowedTags["figure"]     = "&lt;figure&gt;";
    return $this;
  }

  /**
   * Configures the text to be required.
   *
   * @return $this
   */
  public function required() {
    $this->required = true;
    return $this;
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  public function validate() {
    global $i18n, $kernel;
    // Validate if we have input and throw an Exception if the field is required.
    if (empty($this->valueRaw)) {
      if ($this->required === true) {
        throw new ValidationException($i18n->t("“{0}” text is mandatory.", [ $this->label ]));
      }
      $this->value = "";
      return $this;
    }

    // Parse the HTML input with tidy and clean it.
    try {
      /* @var $tidy \tidy */
      $tidy = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body>{$this->valueRaw}</body></html>");
      $tidy->cleanRepair();
      if ($tidy->getStatus() === 2) {
        throw new \ErrorException;
      }
    }
    catch (\ErrorException $e) {
      throw new ValidationException($i18n->t("Invalid HTML in “{0}” text.", [ $this->label ]));
    }

    // Traverse through the constructed document and validate its contents.
    $level      = 0;
    /* @var $node \tidyNode */
    $node           = null;
    $nodes          = [ $level => [ $tidy->body()]];
    $endTags        = [];
    $output         = null;
    $blockquote     = false;
    $figure         = false;
    $immediateChild = null;
    do {
      while (!empty($nodes[$level])) {
        // Retrieve the next node from the stack.
        $node = array_shift($nodes[$level]);

        if ($level > 0) {
          // Validate tag and attributes.
          if ($node->type === TIDY_NODETYPE_TEXT) {
            // Clean text and append to output.
            $output .= "{$kernel->htmlEncode($node->value)}";
          }
          elseif (isset($this->allowedTags[$node->name])) {
            // If we are already nested in a <blockquote>, ensure that it doesn't contain <blockquote> or <figure> there.
            if ($blockquote === true && ($node->name == "blockquote" || $node->name == "figure")) {
              throw new ValidationException($i18n->t(
                "The “{0}” text contains the invalid element {1} inside a quotation or a figure.",
                [ $this->label, "<code><{$node->name}></code>" ],
                [ "comment" => "{0} is the name of the text, {1} is the name of the invalid element. Both should not be translated." ]
              ));
            }
            // If there are more complex validations to be done for the tag, invoke the corresponding method.
            if (method_exists($this, "validateTag{$node->name}")) {
              $node->name = $this->{"validateTag{$node->name}"}($node);
            }
            // Stack a closing tag to the current level, if needed.
            if (!isset($this->emptyTags[$node->name])) {
              $endTags[$level][] = "</{$node->name}>";
            }
            // Append a starting tag of the current node to the output.
            $output .= "<{$node->name}>";

            // We only allow <figure> elements with an <img> as first and a <figcaption> as second child.
            if ($node->name == "figure") {
              $alt = null;
              if (count($node->child) !== 2) {
                throw new ValidationException($i18n->t(
                  "The “{0}” text contains an invalid figure.",
                  [ $this->label ],
                  [ "comment" => "{0} is the name of the text, which should not be translated." ]
                ));
              }
              // Validate caption.
              if ($node->child[1]->name == "figcaption") {
                $immediateChild = $this->validateTextOnlyOrAnchor($node->child[1]);
                /** @todo double check */
                $alt = $kernel->htmlEncode(strip_tags(implode("", $node->child[1]->child)));
              }
              else {
                throw new ValidationException($i18n->t(
                  "The “{0}” text contains a figure without a caption.",
                  [ $this->label ],
                  [ "comment" => "{0} is the name of the text, which should not be translated." ]
                ));
              }

              // Validate image.
              if ($node->child[0]->name == "img") {
                $node->child[0]->attribute["alt"] = $alt;
                $immediateChild = "<{$this->validateTagImg($node->child[0])}>{$immediateChild}";
              }
              else {
                throw new ValidationException($i18n->t(
                  "The “{0}” text contains a figure without an image.",
                  [ $this->label ],
                  [ "comment" => "{0} is the name of the text, which should not be translated." ]
                ));
              }
              // Delete all children, since they are already validated.
              $node->child = null;
              // Increase the level, since we need an ending tag, but have no children.
              $level++;
            }

            // We only allow <blockquote> elements with a <cite> element right before the end tag.
            // Remove the cite element and validate it separately.
            if ($node->name == "blockquote") {
              $blockquote     = true;
              $lastChildNode  = array_pop($node->child);

              // Do not allow quotations without text.
              if (count($node->child) < 1) {
                throw new ValidationException($i18n->t(
                  "The “{0}” text contains an empty quotation.",
                  [ $this->label, "<code><{$node->name}></code>" ],
                  [ "comment" => "{0} is the name of the text, which should not be translated." ]
                ));
              }

              // If there is no <cite> as last child of the <blockquote>, abort.
              if ($lastChildNode->name != "cite") {
                throw new ValidationException($i18n->t(
                  "The “{0}” text contains a quotation without a supplied source.",
                  [ $this->label, "<code><{$node->name}></code>" ],
                  [ "comment" => "{0} is the name of the text, which should not be translated." ]
                ));
              }
              // Otherwise validate, that the <cite> only contains anchors or plain text.
              else {
                $immediateChild = $this->validateTextOnlyOrAnchor($lastChildNode);
              }
            }
          }
          // Encountered a tag that is not allowed, abort.
          else {
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
          $nodes[++$level] = $node->child;
        }
      }

      // There are no more nodes to process in this level (while loop above has already handled them). Go one level up and proceed.
      $level--;
      // Append all ending tags of the current level to the output, if we are higher than level 0 and if there are any.
      if ($level > 0 && isset($endTags[$level])) {
        while (($endTag = array_pop($endTags[$level]))) {
          if ($endTag == "</blockquote>" || $endTag == "</figure>") {
            $blockquote           = false;
            $output              .= "{$immediateChild}{$endTag}";
            $immediateChild       = null;
          }
          else {
            $output .= $endTag;
          }
        }
      }
    }
    while ($level > 0);

    // Parse and format the validated HTML output.
    // Please note that this error is impossible to provoke from the outside.
    // @codeCoverageIgnoreStart
    try {
      $tidy = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body>{$output}</body></html>");
      $tidy->cleanRepair();
      if ($tidy->getStatus() === 2) {
        throw new \ErrorException;
      }
    }
    catch (\ErrorException $e) {
      error_log($e);
      throw new ValidationException($i18n->t("Invalid HTML after the validation in “{0}” text.", [ $this->label ]));
    }
    // @codeCoverageIgnoreEnd

    // Replace redundant newlines, normalize UTF-8 characters and encode HTML characters.
    $this->value = $kernel->htmlEncode(\Normalizer::normalize(str_replace("\n\n", "\n", tidy_get_output($tidy))));
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
        [ $this->label, "<code>{$kernel->htmlEncode($node->attribute["href"])}</code>" ],
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
      if ($this->allowExternalLinks === false) {
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
        [ $this->label, "<code>{$kernel->htmlEncode($node->attribute["href"])}</code>" ],
        [ "comment" => "{0} is the name of the text, {1} is the value of the link’s href attribute. Both should not be translated." ]
      ));
    }
    if (isset($parts["port"])) {
      throw new ValidationException($i18n->t(
        "Ports are not allowed in “{0}” text ({1}).",
        [ $this->label, "<code>{$kernel->htmlEncode($node->attribute["href"])}</code>" ],
        [ "comment" => "{0} is the name of the text, {1} is the value of the link’s href attribute. Both should not be translated." ]
      ));
    }

    if (filter_var($validateURL, FILTER_VALIDATE_URL, FILTER_REQUIRE_SCALAR | FILTER_FLAG_HOST_REQUIRED) === false) {
      throw new ValidationException($i18n->t(
        "Invalid link in “{0}” text ({1}).",
        [ $this->label, "<code>{$kernel->htmlEncode($node->attribute["href"])}</code>" ],
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
    if (
          filter_var($node->attribute["src"], FILTER_VALIDATE_URL, FILTER_REQUIRE_SCALAR | FILTER_FLAG_HOST_REQUIRED) === false
          || ($url = parse_url($node->attribute["src"])) === false
          || !isset($url["host"])
        ) {
      throw new ValidationException($i18n->t(
        "Invalid image {0} attribute in “{1}” text.",
        [ "<code>src</code>", $this->label ],
        [ "comment" => "{0} is <code>src</code>, {1} is the name of the text. Both should not be translated." ]
      ));
    }

    // Check if the image comes from our server.
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
        [ $this->label, "<code>{$kernel->htmlEncode($node->attribute["src"])}</code>" ],
        [ "comment" => "{0} is the name of the text, {1} is the value of the image’s src attribute. Both should not be translated." ]
      ));
    }
    $url["path"]          = isset($url["path"]) ? $url["path"] : "/";
    $attributes["src"]    = $kernel->htmlEncode("//{$url["host"]}{$url["path"]}");
    $attributes["width"]  = $image[0];
    $attributes["height"] = $image[1];

    // Encode the <code>alt</code> attribute or fill in an empty one if it wasn't set.
    if (isset($node->attribute["alt"])) {
      $attributes["alt"] = $kernel->htmlEncode($node->attribute["alt"]);
    }
    else {
      $attributes["alt"] = "";
    }

    ksort($attributes);

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
    elseif (!empty ($node->attribute["class"])) {
      $class = " class='{$node->attribute["class"]}'";
    }
    return "p{$class}";
  }

  /**
   * Validates and sanitizes HTML elements which can only contain anchors or text.
   *
   * @todo Implement validation of plain text or anchors.
   * @param \tidyNode $node
   *   The node to validate.
   * @return string
   *   The tag with the validated contents.
   */
  protected function validateTextOnlyOrAnchor($node) {
    return "<$node->name>Not implemented yet.</$node->name>";
  }

}
