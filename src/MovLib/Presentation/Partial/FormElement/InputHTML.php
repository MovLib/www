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
   * Associative array containing the allowed HTML tags.
   *
   * @var array
   */
  protected $allowedTags = [
    "a"      => "&lt;a&gt;",
    "b"      => "&lt;b&gt;",
    "br"     => "&lt;br&gt;",
    "i"      => "&lt;i&gt;",
    "p"      => "&lt;p&gt;",
    "strong" => "&lt;strong&gt;",
  ];

  /**
   * Whether external links are allowed or not for anchor elements.
   *
   * @var boolean
   */
  protected $allowExternalLinks = false;

  /**
   * Whether we are inside a <code><blockquote></code> or not.
   *
   * @var boolean
   */
  protected $blockquote = false;

  /**
   * Associative array containing all HTML tags that aren't allowed within a <code><blockquote></code>.
   *
   * @var array
   */
  protected $blockquoteDisallowedTags = [
    "blockquote" => false,
    "figure"     => false,
    "ul"         => false,
    "ol"         => false,
  ];

  /**
   * Associative array to identify empty HTML tags.
   *
   * @var array
   */
  protected $emptyTags = [
    "br"  => true,
    "img" => true,
  ];

  /**
   * Whether to insert last child and clear it or not.
   *
   * @var null|string
   */
  protected $insertLastChild;

  /**
   * Used for some elements that have a required last child element.
   *
   * @var null|\tidyNode
   */
  protected $lastChild;

  /**
   * The level within the HTML text DOM we are currently traversing.
   *
   * @var integer
   */
  protected $level = 0;

  /**
   * Associative array to identify allowed user CSS classes.
   *
   * @var array
   */
  protected $userClasses = [
    "user-left"   => true,
    "user-center" => true,
    "user-right"  => true,
  ];

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
   * @param string $help [optional]
   *   The text's help text, defaults to <code>NULL</code> (no help text).
   * @param boolean $helpPopup
   *   Whether the help should be displayed as popup or not, defaults to <code>TRUE</code> (display as popup).
   */
  public function __construct($id, $label, $value = null, array $attributes = null, $help = null, $helpPopup = true) {
    global $kernel;
    parent::__construct($id, $label, $attributes, $help, $helpPopup);
    $kernel->javascripts[]              = "InputHTML";
    $this->attributes["aria-multiline"] = "true";

    if (!empty($_POST[$this->id])) {
      $this->value    = $kernel->htmlEncode($_POST[$this->id]);
      $this->valueRaw = $_POST[$this->id];
    }
    elseif ($value) {
      $this->value    = $value;
      $this->valueRaw = $kernel->htmlDecode($value);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function render() {
    global $i18n;

    // We need to alter the div attributes in order to make them valid for this kind of HTML element. The div element
    // also needs a class for easy identification via CSS and JS whilst the textarea doesn't need anything because the
    // tag is more than sufficient for identification.
    $divAttributes                    = $this->attributes;
    $divAttributes["contenteditable"] = "true";
    $divAttributes["role"]            = "textbox";
    $this->addClass("content", $divAttributes);

    // The name attribute is always present for the textarea but nonsense for our div.
    unset($divAttributes["id"], $divAttributes["name"]);

    // The required attribute isn't allowed on our div element.
    if (($key = array_search("required", $divAttributes)) !== false) {
      unset($divAttributes[$key]);
    }

    // Use default placeholder text if none was provided.
    if (!isset($this->attributes["placeholder"])) {
      $this->attributes["placeholder"] = $i18n->t("Enter “{0}” text here …", [ $this->label ]);
    }

    // Build the editor based on available tags.
    $editor = null;

    return
      "{$this->help}<fieldset class='inputhtml'>" .
        "<legend>{$this->label}</legend>" .
        // The jshidden class uses display:none to hide its elements, this means that these elements aren't part of the
        // DOM tree and aren't parsed by user agents.
        "<p class='jshidden'><label for='{$this->id}'>{$this->label}</label><textarea{$this->expandTagAttributes($this->attributes)}>{$this->valueRaw}</textarea></p>" .
        // Same situation above but for user agents with disabled JavaScript.
        "<div class='editor nojshidden'>{$editor}" .
          // The content for the editable div is copied over from the textarea by the JS module. But we directly
          // include the placeholder because it's very short.
          "<div class='wrapper'><div{$this->expandTagAttributes($divAttributes)}></div><span aria-hidden='true' class='placeholder'>{$this->attributes["placeholder"]}</span></div>" .
        "</div>" .
      "</fieldset>"
    ;
  }

  /**
   * Allow <code><blockquote></code> elements.
   *
   * @return $this
   */
  public function allowBlockqoutes() {
    $this->allowedTags["blockquote"] = "&lt;blockquote&gt;";
    return $this;
  }

  /**
   * Allow external links.
   *
   * @return $this
   */
  public function allowExternalLinks() {
    $this->allowExternalLinks = true;
    return $this;
  }

  /**
   * Allow <code><h$level></code> to <code><h6></code> headings.
   *
   * @param integer $level [optional]
   *   The starting level of the headings. Allowed values are <code>2</code> to <code>6</code>. Defaults to <code>3</code>.
   * @return $this
   */
  public function allowHeadings($level = 3) {
    for ($i = $level; $i <= 6; ++$i) {
      $this->allowedTags["h{$i}"] = "&lt;h{$i}&gt;";
    }
    return $this;
  }

  /**
   * Allow images.
   *
   * @return $this
   */
  public function allowImages() {
    $this->allowedTags["figure"] = "&lt;figure&gt;";
    return $this;
  }

  /**
   * Allow unordered and ordered lists.
   *
   * @return $this
   */
  public function allowLists() {
    $this->allowedTags["ul"] = "&lt;ul&gt;";
    $this->allowedTags["ol"] = "&lt;ol&gt;";
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
      if (in_array("required", $this->attributes)) {
        throw new ValidationException($i18n->t("“{label}” is mandatory.", [ "label" => $this->label ]));
      }
      $this->value = null;
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

    /* @var $node \tidyNode */
    $node           = null;
    $nodes          = [ $this->level => [ $tidy->body() ] ];
    $endTags        = [];
    $output         = null;

    // Traverse through the constructed document and validate its contents.
    do {
      while (!empty($nodes[$this->level])) {
        // Retrieve the next node from the stack.
        $node = array_shift($nodes[$this->level]);

        if ($this->level > 0) {
          // If we encounter a text node, simply encode its contents and continue with the next node.
          if ($node->type === TIDY_NODETYPE_TEXT) {
            $output .= $kernel->htmlEncode($node->value);
          }
          // If we encounter an allowed HTML tag validate it.
          elseif (isset($this->allowedTags[$node->name])) {
            // If we're already inside <blockquote>, ensure it doesn't contain any disallowed elements.
            if ($this->blockquote === true && isset($this->blockquoteDisallowedTags[$node->name])) {
              throw new ValidationException($i18n->t("Found disallowed tag {0} in quotation.", [ "<code>&lt;{$node->name}&gt;</code>" ]));
            }

            // Directly take care of the most common element that has allowed attributes, the content is validated in
            // the next iteration.
            if ($node->name == "p") {
              $node->name = "p{$this->validateUserClasses($node)}";
            }
            // If there are more complex validations to be done for the tag, invoke the corresponding method.
            else {
              $methodName = "validate{$node->name}";
              if (method_exists($this, $methodName)) {
                $node->name = $this->{$methodName}($node);
              }
            }

            // Stack a closing tag to the current level, if needed.
            if (!isset($this->emptyTags[$node->name])) {
              $endTags[$this->level][] = "</{$node->name}>";
            }

            // Append a starting tag including valid attributes (if any) of the current node to the output.
            $output .= "<{$node->name}>";
          }
          // Encountered a tag that is not allowed, abort.
          else {
            $allowedTags = implode(" ", $this->allowedTags);
            throw new ValidationException($i18n->t("Found disallowed HTML tags, allowed tags are: {1}", [ "<code>{$allowedTags}</code>" ]));
          }
        }

        // Stack the child nodes to the next level if there are any.
        if (!empty($node->child)) {
          $nodes[++$this->level] = $node->child;
        }
      }

      // There are no more nodes to process in this level (while loop above has already handled them).
      // Go one level down and proceed with the next node.
      $this->level--;

      // Append all ending tags of the current level to the output, if we are greater than level 0 and if there are any.
      if ($this->level > 0 && isset($endTags[$this->level])) {
        while (($endTag = array_pop($endTags[$this->level]))) {
          if ($endTag == "</{$this->insertLastChild}>") {
            $this->blockquote      = false;
            $output               .= "{$this->lastChild}{$endTag}";
            $this->insertLastChild = null;
            $this->lastChild       = null;
          }
          else {
            $output .= $endTag;
          }
        }
      }
    }
    while ($this->level > 0);

    // Reset the level to its default state.
    $this->level = 0;

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
  protected function validateA($node) {
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
        [ $this->label, "<code>{$kernel->htmlEncode($node->attribute["href"])}</code>" ]
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
      $attributes["rel"]  = "nofollow";
      $attributes["href"] = "{$attributes["href"]}{$parts["host"]}";
      $validateURL        = $attributes["href"];
      $parts["query"]     = isset($parts["query"]) ? "?{$parts["query"]}" : null;
    }

    // Initialize the path offset with "/" if it doesn't exist.
    $parts["path"] = isset($parts["path"]) ? $parts["path"] : "/";

    // Initialize the fragment offset with null if it doesn't exist.
    $parts["fragment"] = isset($parts["fragment"]) ? "#{$parts["fragment"]}" : null;

    // Append path, query and fragment to the URL.
    $attributes["href"] = "{$attributes["href"]}{$parts["path"]}{$parts["query"]}{$parts["fragment"]}";
    $validateURL        = "{$validateURL}{$parts["path"]}{$parts["query"]}{$parts["fragment"]}";

    // Validate user, password and port, since we don't allow them.
    if (isset($parts["user"]) || isset($parts["pass"])) {
      throw new ValidationException($i18n->t(
        "Credentials are not allowed in “{0}” text ({1}).",
        [ $this->label, "<code>{$kernel->htmlEncode($node->attribute["href"])}</code>" ]
      ));
    }
    if (isset($parts["port"])) {
      throw new ValidationException($i18n->t(
        "Ports are not allowed in “{0}” text ({1}).",
        [ $this->label, "<code>{$kernel->htmlEncode($node->attribute["href"])}</code>" ]
      ));
    }

    if (filter_var($validateURL, FILTER_VALIDATE_URL, FILTER_REQUIRE_SCALAR | FILTER_FLAG_HOST_REQUIRED) === false) {
      throw new ValidationException($i18n->t(
        "Invalid link in “{0}” text ({1}).",
        [ $this->label, "<code>{$kernel->htmlEncode($node->attribute["href"])}</code>" ]
      ));
    }

    return "a{$this->expandTagAttributes($attributes)}";
  }

  /**
   * Validate <code><blockquote></code>.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param \tidyNode $node
   *   The blockquote node to validate.
   * @return string
   *   The starting tag including allowed attributes.
   * @throws \MovLib\Exception\ValidationException
   */
  protected function validateBlockquote($node) {
    global $i18n;
    $this->blockquote      = true;
    $this->insertLastChild = "blockquote";

    // Do not allow quotations without text.
    if (count($node->child) < 1) {
      throw new ValidationException(
        $i18n->t("The “{0}” text contains an empty quotation.",
        [ $this->label, "<code>&lt;{$node->name}&gt;</code>" ]
      ));
    }

    /* @var $lastChild \tidyNode */
    $lastChild = array_pop($node->child);

    // Validate that <cite> only contains text and/or anchor nodes.
    if ($lastChild->name == "cite") {
      $this->lastChild = "<cite>{$this->validateTextOnlyWithOptionalAnchors($lastChild, $i18n->t("attributions"))}</cite>";
    }
    // A <blockquote> without a <cite> is invalid.
    else {
      throw new ValidationException(
        $i18n->t("The “{0}” text contains a quotation without source.",
        [ $this->label, "<code>&lt;{$node->name}&gt;</code>" ]
      ));
    }

    return "blockquote{$this->validateUserClasses($node)}";
  }

  /**
   * Validate figure.
   *
   * @todo We have to keep reference of images in texts in order to update their cache buster string and remove them
   *       if the image is deleted.
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @param \tidyNode $node
   *   The figure node to validate.
   * @return string
   *   The starting tag including allowed attributes.
   * @throws ValidationException
   */
  protected function validateFigure($node) {
    global $i18n, $kernel;
    $this->insertLastChild = "figure";

    // Of course we can communicate the caption as seperate element, as it's visible to the user.
    if (count($node->child) !== 2 || $node->child[1]->name != "figcaption" || empty($node->child[1]->child)) {
      throw new ValidationException($i18n->t("The image caption is mandatory and cannot be empty."));
    }
    // Always communicate the <figure> element as image, the actual implementation isn't the user's concern and might
    // change with future web technologies.
    elseif ($node->child[0]->name != "img" || empty($node->child[0]->attribute["src"])) {
      throw new ValidationException($i18n->t("The image is mandatory and cannot be empty."));
    }

    // Validate the caption.
    $caption = $this->validateTextOnlyWithOptionalAnchors($node->child[1], $i18n->t("image captions"));

    // Use the caption's content as alt attribute for the image.
    $node->child[0]->attribute["alt"] = $caption;

    // Validate the image's src URL.
    if (($url = parse_url($node->child[0]->attribute["src"])) === false || !isset($url["host"])) {
      throw new ValidationException($i18n->t("Image URL seems to be invalid."));
    }

    // If a host is present check if it's from MovLib.
    if (isset($url["host"]) && $url["host"] != $kernel->domainStatic && strpos($url["host"], ".{$kernel->domainDefault}") === false) {
      throw new ValidationException($i18n->t("Only images from {0} are allowed.", [ $kernel->siteName ]));
    }

    // Check that the image actually exists and set width and height.
    try {
      $imgAttributes = getimagesize("{$kernel->documentRoot}/public{$url["path"]}")[3];
    }
    catch (\ErrorException $e) {
      throw new ValidationException($i18n->t("Image doesn’t exist ({1}).", [ "<code>{$node->attribute["src"]}</code>" ]));
    }

    // Build the image tag and validate the caption.
    $this->lastChild = "<img {$imgAttributes} src='//{$kernel->domainStatic}{$url["path"]}'><figcaption>{$caption}</figcaption>";

    // Delete all children, since they are already validated.
    $node->child = null;

    // Increase the level, since we need an ending tag, but have no children.
    $this->level++;

    return "figure{$this->validateUserClasses($node)}";
  }

  /**
   * Validates and sanitizes HTML elements which can only contain anchors or text.
   *
   * @todo Implement validation of plain text or anchors.
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @param \tidyNode $node
   *   The node to validate.
   * @param string $context
   *   The already translated context of the element for error messages.
   * @return string
   *   The validated content.
   */
  protected function validateTextOnlyWithOptionalAnchors($node, $context) {
    global $i18n, $kernel;
    $content = null;
    $c = count($node->child);
    // Iterate and validate all children.
    for ($i = 0; $i < $c; ++$i) {
      // If we encounter a text node, encode it and continue.
      if ($node->child[$i]->type === TIDY_NODETYPE_TEXT) {
        $content .= $kernel->htmlEncode($node->child[$i]->value);
      }
      // If the child is a link, check that it has only one child (text) and validate the link tag separately.
      elseif ($node->child[$i]->name == "a") {
        if (count($node->child[$i]->child) !== 1 || $node->child[$i]->child[0]->type !== TIDY_NODETYPE_TEXT) {
          throw new ValidationException($i18n->t("Only plain text is allowed for links in {context}.", [ "context" => $context]));
        }
        $content .= "<{$this->validateA($node->child[$i])}>{$kernel->htmlEncode($node->child[$i]->child[0]->value)}</a>";
      }
      // Other nodes are not allowed, abort.
      else {
        throw new ValidationException($i18n->t("Only plain text and links are allowed in {context}.", [ "context" => $context]));
      }
    }
    return $content;
  }

  /**
   * Validate that the node only contains allowed user CSS classes.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param \tidyNode $node
   *   The node to validate.
   * @return null|string
   *   The expanded class attribute if present, otherwise <code>NULL</code>.
   * @throws \MovLib\Exception\ValidationException
   */
  protected function validateUserClasses($node) {
    global $i18n;
    if (isset($node->attribute) && !empty($node->attribute["class"])) {
      if (!isset($this->userClasses[$node->attribute["class"]])) {
        $classes = implode(" ", array_keys($this->userClasses));
        throw new ValidationException($i18n->t(
          "Disallowed CSS classes in “{0}” text, allowed values are: {2}",
          [ $this->label, "<code>{$classes}</code>" ]
        ));
      }
      else {
        return " class='{$node->attribute["class"]}'";
      }
    }
  }

}
