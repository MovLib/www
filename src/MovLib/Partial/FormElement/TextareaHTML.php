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

use \MovLib\Exception\ValidationException;

/**
 * The most basic HTML contenteditable text form element.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/Guide/HTML/Content_Editable
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class TextareaHTML extends \MovLib\Partial\FormElement\TextareaHTMLRaw {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "TextareaHTML";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Error code for invalid HTML.
   *
   * @var integer
   */
  const ERROR_INVALID_HTML = 1;

  /**
   * Error code for disallowed HTML tags.
   *
   * @var integer
   */
  const ERROR_DISALLOWED_TAGS = 2;

  /**
   * Error code for disallowed CSS classes.
   *
   * @var integer
   */
  const ERROR_DISALLOWED_CSS_CLASSES = 3;

  /**
   * Error code for images without caption.
   *
   * @var integer
   */
  const ERROR_IMAGE_NO_CAPTION = 4;

  /**
   * Error code for images without img tags.
   *
   * @var integer
   */
  const ERROR_IMAGE_NO_IMAGE = 5;

  /**
   * Error code for figures with the wrong number of children.
   *
   * @var integer
   */
  const ERROR_IMAGE_CHILDREN = 6;

  /**
   * Error code for images without src.
   *
   * @var integer
   */
  const ERROR_IMAGE_INVALID_SOURCE = 7;

  /**
   * Error code for images from external sources.
   *
   * @var integer
   */
  const ERROR_IMAGE_EXTERNAL = 8;

  /**
   * Error code for images which don't exist.
   *
   * @var integer
   */
  const ERROR_IMAGE_NON_EXISTENT = 9;

  /**
   * Error code for images captions with disallowed tags.
   *
   * @var integer
   */
  const ERROR_IMAGE_CAPTION_DISALLOWED_TAGS = 10;

  /**
   * Error code for links with no target.
   *
   * @var integer
   */
  const ERROR_LINK_NO_TARGET = 11;

  /**
   * Error code for invalid links.
   *
   * @var integer
   */
  const ERROR_LINK_INVALID = 12;

  /**
   * Error code for disallowed external links.
   *
   * @var integer
   */
  const ERROR_LINK_EXTERNAL = 13;

  /**
   * Error code for links containing credentials.
   *
   * @var integer
   */
  const ERROR_LINK_CREDENTIALS = 14;

  /**
   * Error code for links containing ports.
   *
   * @var integer
   */
  const ERROR_LINK_PORT = 15;

  /**
   * Error code for quotations with disallowed tags.
   *
   * @var integer
   */
  const ERROR_QUOTATION_DISALLOWED_TAGS = 16;

  /**
   * Error code for quotations without a source.
   *
   * @var integer
   */
  const ERROR_QUOTATION_NO_SOURCE = 17;

  /**
   * Error code for quotations without a text.
   *
   * @var integer
   */
  const ERROR_QUOTATION_NO_TEXT = 18;


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
    "em"     => "&lt;em&gt;",
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
   * Whether we are inside a <code><figure></code> or not.
   *
   * @var boolean
   */
  protected $figure = false;

  /**
   * The first allowed heading level.
   *
   * @var integer
   */
  protected $headingLevel = 3;

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
   * The list information array used to determine when and if to close a list.
   *
   * Format: <code>[ "tag" => "ol"|"ul", "level" => "$level of first list opening", "allowed_tags" => "backup of allowed tags" ]</code>
   *
   * @var boolean|array
   */
  protected $list = false;

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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new HTML form element.
   *
   * @param \MovLib\Core\HTTP\Container $container
   *   HTTP dependency injection container.
   * @param string $id
   *   The text's global identifier.
   * @param string $label
   *   The text's label text.
   * @param mixed $value [optional]
   *   The form element's value, defaults to <code>NULL</code> (no value).
   * @param array $attributes [optional]
   *   Additional attributes for the text, defaults to <code>NULL</code> (no additional attributes).
   */
  public function __construct(\MovLib\Core\HTTP\Container $container, $id, $label, &$value, array $attributes = null) {
    parent::__construct($container, $id, $label, $value, $attributes);
    if (isset($attributes["data-allow-external"])) {
      $this->allowExternalLinks = true;
    }
    // Load the CKEditor javascript and styles for now.
    $this->presenter->headElements                  .= "<script type='text/javascript' src='/bower/ckeditor/ckeditor.js'></script>";
    // CKEditor configuration.
    $allowedTags = array_keys($this->allowedTags);
    if (isset($this->allowedTags["figure"])) {
      $mode = 2;
      $allowedTags[] = "img";
      $allowedTags[] = "figcaption";
    }
    elseif (isset ($this->allowedTags["blockquote"])) {
      $mode = 1;
      $allowedTags[] = "cite";
    }
    else {
      $mode = 0;
    }
    $config = [
      "allowedTags"    => $allowedTags,
      "language"       => $this->intl->languageCode,
      "headingLevel"   => $this->headingLevel,
      "mode"           => $mode,
    ];
    $this->presenter->javascriptSettings["ckeditor"] = (object) $config;
    $this->presenter->javascripts[]                  = "InputHTML";
    $this->presenter->stylesheets[]                  = "inputhtml";
  }

  /**
   * Get the HTML textarea form element.
   *
   * @return string
   *   The HTML textarea form element.
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->attributes["id"] = $this->id;
    $this->attributes["name"] = $this->id;
    $this->attributes["aria-multiline"] = "true";
    return
      "{$this->required}{$this->helpPopup}{$this->helpText}" .
      "<p class='inputhtml'>" .
        "<label for='{$this->id}'>{$this->label}</label>" .
        "<textarea{$this->presenter->expandTagAttributes($this->attributes)}>{$this->htmlDecode($this->value)}</textarea>" .
      "</p>"
    ;
    // @devStart
    // @codeCoverageIgnoreStart

    } catch (\Exception $e) {
      return $this->calloutError("<pre>{$e}</pre>", "Stacktrace");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function validateValue($html, &$errors) {
    // Empty value, abort.
    if (empty($html)) {
      return $html;
    }

    // Parse the HTML input with tidy and clean it.
    // Double decode to circumvent hacks and decode all entities to characters.
    /* @var $tidy \tidy */
    $tidy = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body>{$this->htmlDecodeEntities($this->htmlDecodeEntities($html))}</body></html>");
    $tidy->cleanRepair();
    if ($tidy->getStatus() === 2) {
      $errors[self::ERROR_INVALID_HTML] = $this->intl->t("Invalid HTML in “{label}” text.", [ "label" => $this->label ]);
      return $html;
    }

    // Validate DOM and normalize Unicode.
    $output = \Normalizer::normalize($this->validateDOM($tidy->body(), $this->allowedTags, $errors, $this->level));

    // If there were errors, simply return the user's input.
    if ($errors) {
      return $html;
    }

    // Reset the level to its default state.
    $this->level = 0;

    // The output should never contain invalid HTML, but it is possible in development.
    // @devStart
    // @codeCoverageIgnoreStart
    $tidy = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body>{$output}</body></html>");
    $tidy->cleanRepair();
    if ($tidy->getStatus() === 2) {
      $errors[self::ERROR_INVALID_HTML] = $this->intl->t(
        "Invalid HTML after the validation in “{label}” text.",
        [ "label" => $this->label ]
      );
      return $html;
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Return the encoded HTML, secure by default.
    return $this->htmlEncode($output);
  }

  /**
   * Validates and sanitizes HTML anchors.
   *
   * @param \tidyNode $node
   *   The anchor.
   * @param mixed $errors
   *   Parameter to collect error messages.
   * @return string
   *   The tag name with the validated attributes.
   */
  protected function validateA($node, &$errors) {
    $attributes  = [];
    $validateURL = null;

    // Check if the <code>href</code> attribute was set and validate the URL.
    if (empty($node->attribute) || empty($node->attribute["href"])) {
      $errors[self::ERROR_LINK_NO_TARGET] = $this->intl->t(
        "Links without a link target in “{label}” text.",
        [ "label" => $this->label ]
      );
      return "a";
    }

    // Parse and validate the parts of the URL.
    if ($node->attribute["href"]{0} == "/") {
      $node->attribute["href"] = "{$this->request->scheme}://{$this->config->hostname}{$node->attribute["href"]}";
    }

    if (($parts = parse_url($node->attribute["href"])) === false || empty($parts["host"])) {
      $errors[self::ERROR_LINK_INVALID] = $this->intl->t(
        "Invalid link in “{label}” text ({link_url}).",
        [ "label" => $this->label, "link_url" => "<code>{$this->htmlEncode($node->attribute["href"])}</code>" ]
      );
      return "a";
    }

    // Make absolute paths as URLs for the internal domain and omit query string.
    if ($parts["host"] == $this->config->hostname || strpos($parts["host"], $this->config->hostname) !== false) {
      $attributes["href"] = null;
      // This is needed, because filter_var doesn't accept protocol relative URLs.
      $validateURL = "https://{$parts["host"]}";
//      $parts["host"] = null;
      $parts["query"] = null;
    }
    // Add rel="nofollow" to external links, sanitize the protocol and fill query string offset if it doesn't exist.
    // If external links are not allowed, abort.
    else {
      if ($this->allowExternalLinks === false) {
        $errors[self::ERROR_LINK_EXTERNAL] = $this->intl->t(
          "No external links are allowed in “{label}” text.",
          [ "label" => $this->label ]
        );
        return "a";
      }
      if (isset($parts["scheme"]) && ($parts["scheme"] == "http" || $parts["scheme"] == "https")) {
        $attributes["href"] = "{$parts["scheme"]}://";
      }
      else {
        $attributes["href"] = "http://";
      }
      $attributes["rel"]    = "nofollow";
      $attributes["target"] = "_blank";
      $attributes["href"]   = "{$attributes["href"]}{$parts["host"]}";
      $validateURL          = $attributes["href"];
      $parts["query"]       = isset($parts["query"]) ? "?{$parts["query"]}" : null;
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
      $errors[self::ERROR_LINK_CREDENTIALS] = $this->intl->t(
        "Credentials are not allowed in “{label}” text ({link_url}).",
        [ "label" => $this->label, "link_url" => "<code>{$this->htmlEncode($node->attribute["href"])}</code>" ]
      );
      return "a";
    }
    if (isset($parts["port"])) {
      $errors[self::ERROR_LINK_PORT] = $this->intl->t(
        "Ports are not allowed in “{label}” text ({link_url}).",
        [ "label" => $this->label, "link_url" => "<code>{$this->htmlEncode($node->attribute["href"])}</code>" ]
      );
      return "a";
    }

    if (filter_var($validateURL, FILTER_VALIDATE_URL, FILTER_REQUIRE_SCALAR | FILTER_FLAG_HOST_REQUIRED) === false) {
      $errors[self::ERROR_LINK_INVALID] = $this->intl->t(
        "Invalid link in “{label}” text ({link_url}).",
        [ "label" => $this->label, "link_url" => "<code>{$this->htmlEncode($node->attribute["href"])}</code>" ]
      );
      throw new \ErrorException;
    }

    return "a{$this->expandTagAttributes($attributes)}";
  }

  /**
   * Validate <code><blockquote></code>.
   *
   * @param \tidyNode $node
   *   The blockquote node to validate.
   * @param mixed $errors
   *   Parameter to collect error messages.
   * @return string
   *   The starting tag including allowed attributes.
   * @throws \MovLib\Exception\ValidationException
   */
  protected function validateBlockquote($node, &$errors) {
    $this->blockquote      = true;
    $this->insertLastChild = "blockquote";

    // We don't have to check for children, because tidy already purges empty <blockquote> tags.

    /* @var $lastChild \tidyNode */
    $lastChild = array_pop($node->child);

    // Validate that <cite> only contains text and/or anchor nodes.
    if ($lastChild->name == "cite" && count($lastChild->child) > 0) {
      $citeAllowedTags = [
        "a"      => "&lt;a&gt;",
        "b"      => "&lt;b&gt;",
        "em"     => "&lt;em&gt;",
        "i"      => "&lt;i&gt;",
        "strong" => "&lt;strong&gt;",
      ];
      $citeContent = $this->validateDOM($lastChild, $citeAllowedTags, $errors);
      $this->lastChild = "<cite>{$citeContent}</cite>";
    }
    // A <blockquote> without a <cite> is invalid.
    else {
      $errors[self::ERROR_QUOTATION_NO_SOURCE] = $this->intl->t(
        "The “{label}” text contains a quotation without source.",
        [ "label" => $this->label ]
      );
      return "blockquote";
    }

    // Do not allow quotations without content.
    if (!isset($node->child[0])) {
      $errors[self::ERROR_QUOTATION_NO_TEXT] = $this->intl->t(
        "The “{label}” text contains quotation without text.",
        [ "label" => $this->label ]
      );
      return "blockquote";
    }

    return "blockquote{$this->validateUserClasses($node, $errors)}";
  }

  /**
   * Validate a DOM tree starting at <code>$node</code>.
   *
   * @param \tidyNode $node
   *   The node to start from.
   * @param array $allowedTags
   *   Associative array containing the tag names as keys and the encoded tags as values.
   * @param mixed $errors
   *   Parameter to collect error messages.
   * @param integer $level [optional]
   *   The level to use (global or local). Defaults to <code>0</code>.
   * @return string
   *   The parsed and sanitized output.
   * @throws ValidationException
   */
  protected function validateDOM($node, &$allowedTags, &$errors, &$level = 0) {
    $nodes       = [ $level => [ $node]];
    $endTags     = [];
    $output      = null;

    // Traverse through the constructed document and validate its contents.
    do {
      while (!empty($nodes[$level])) {
        // Retrieve the next node from the stack.
        $node = array_shift($nodes[$level]);

        if ($level > 0) {
          // If we encounter a text node, simply encode its contents and continue with the next node.
          if ($node->type === TIDY_NODETYPE_TEXT) {
            $output .= $this->htmlEncode($node->value);
          }
          // If we encounter an allowed HTML tag validate it.
          elseif (isset($allowedTags[$node->name])) {
            // If we're already inside <blockquote>, ensure it doesn't contain any disallowed elements.
            if ($this->blockquote === true && isset($this->blockquoteDisallowedTags[$node->name])) {
              $errors[self::ERROR_QUOTATION_DISALLOWED_TAGS] = $this->intl->t(
                "Found disallowed tag {tag} in quotation.",
                [ "tag" => "<code>&lt;{$node->name}&gt;</code>" ]
              );
            }

            // Stack a closing tag to the current level, if needed.
            if (!isset($this->emptyTags[$node->name])) {
              $endTags[$level][] = "</{$node->name}>";
            }

            // Directly take care of the most common element that has allowed attributes, the content is validated in
            // the next iteration.
            if ($node->name == "p") {
              $node->name = "p{$this->validateUserClasses($node, $errors)}";
            }
            // If there are more complex validations to be done for the tag, invoke the corresponding method.
            else {
              $methodName = "validate{$node->name}";
              if (method_exists($this, $methodName)) {
                $node->name = $this->{$methodName}($node, $errors, $level);
              }
            }

            // Append a starting tag including valid attributes (if any) of the current node to the output.
            $output .= "<{$node->name}>";
          }
          // Encountered a tag that is not allowed, abort.
          else {
            $allowedTagsList = implode(" ", $allowedTags);
            if ($this->figure === true) {
              $errors[self::ERROR_IMAGE_CAPTION_DISALLOWED_TAGS] = $this->intl->t(
                "Found disallowed HTML tags in image caption, allowed tags are: {taglist}",
                [ "taglist" => "<code>{$allowedTagsList}</code>" ]
              );
            }
            else {
              $errors[self::ERROR_DISALLOWED_TAGS] = $this->intl->t(
                "Found disallowed HTML tags, allowed tags are: {taglist}",
                [ "taglist" => "<code>{$allowedTagsList}</code>" ]
              );
            }
          }
        }

        // Stack the child nodes to the next level if there are any.
        if (!empty($node->child)) {
          $nodes[++$level] = $node->child;
        }
      }

      // There are no more nodes to process in this level (while loop above has already handled them).
      // Go one level down and proceed with the next node.
      $level--;

      // Append all ending tags of the current level to the output, if we are greater than level 0 and if there are any.
      if ($level > 0 && isset($endTags[$level])) {
        while (($endTag = array_pop($endTags[$level]))) {
          if ($endTag == "</{$this->insertLastChild}>") {
            $this->blockquote      = false;
            $output               .= "{$this->lastChild}{$endTag}";
            $this->insertLastChild = null;
            $this->lastChild       = null;
          }
          else {
            $output .= $endTag;
          }
          // Check if we are at the end of a list and if the level fits.
          // If so, restore allowed tags and list flag.
          if ($this->list && "</{$this->list["tag"]}>" == $endTag && $this->list["level"] === $level) {
            $this->allowedTags = $this->list["allowed_tags"];
            $this->list        = false;
          }
        }
      }
    }
    while ($level > 0);

    return $output;
  }

  /**
   * Validate figure.
   *
   * @todo We have to keep reference of images in texts in order to update their cache buster string and remove them
   *       if the image is deleted.
   * @param \tidyNode $node
   *   The figure node to validate.
   * @param mixed $errors
   *   Parameter to collect error messages.
   *
   * @return string
   *   The starting tag including allowed attributes.
   * @throws ValidationException
   */
  protected function validateFigure($node, &$errors, &$level) {
    $this->insertLastChild = "figure";
    $this->figure          = true;

    if (count($node->child) === 2) {
      // Of course we can communicate the caption as seperate element, as it's visible to the user.
      if ($node->child[1]->name != "figcaption" || empty($node->child[1]->child)) {
        $errors[self::ERROR_IMAGE_NO_CAPTION] = $this->intl->t("The image caption is mandatory and cannot be empty.");
      }
      $figcaption = $node->child[1];

      // Always communicate the <figure> element as image, the actual implementation isn't the user's concern and might
      // change with future web technologies.
      if ($node->child[0]->name != "img" || empty($node->child[0]->attribute["src"])) {
        $errors[self::ERROR_IMAGE_NO_IMAGE] = $this->intl->t("The image is mandatory and cannot be empty.");
      }
      $image = $node->child[0];

      // Delete all children and remove them from the main loop stack, we validate and export them ourselves. Note that
      // we always have to do so, because even if one of the above errors happened, the main loop continues.
      $node->child  = null;

      if (isset($errors[self::ERROR_IMAGE_NO_CAPTION]) || isset($errors[self::ERROR_IMAGE_NO_IMAGE])) {
        return "figure";
      }
    }
    else {
      $errors[self::ERROR_IMAGE_CHILDREN] =
        $this->intl->t("An image may only consist of an image and a caption, nothing more and nothing less.");
      return "figure";
    }

    // Validate the caption.
    $captionAllowedTags = [
      "a"      => "&lt;a&gt;",
      "b"      => "&lt;b&gt;",
      "br"     => "&lt;br&gt;",
      "em"     => "&lt;em&gt;",
      "i"      => "&lt;i&gt;",
      "strong" => "&lt;strong&gt;",
    ];

    $validateDOMFigcaptionLevel = 0;
    $caption = $this->validateDOM($figcaption, $captionAllowedTags, $errors, $validateDOMFigcaptionLevel);

    // Disable the special figure check in the main validation loop.
    $this->figure = false;

    // Increase the level, since we need an ending tag, but have no children.
    $level++;

    // Set the caption as the image's alt text and remove markup.
    $alt = strip_tags($caption);

    // @todo Refactor the following to correctly validate the URL, combine with validateA and the validation method in
    //       in inputURL!

    // Validate the image's src URL.
    if (($url = parse_url($image->attribute["src"])) === false || !isset($url["host"])) {
      $errors[self::ERROR_IMAGE_INVALID_SOURCE] = $this->intl->t("Image URL seems to be invalid.");
      return "figure";
    }

    // If a host is present check if it's from MovLib.
    if (isset($url["host"]) && $url["host"] != $this->config->hostname && strpos($url["host"], ".{$this->config->hostname}") === false) {
      $errors[self::ERROR_IMAGE_EXTERNAL] = $this->intl->t(
        "Only images from {movlib} are allowed.",
        [ "movlib" => $this->config->sitename ]
      );
      return "figure";
    }

    // Check that the image actually exists and set width and height.
    try {
      $imgAttributes = getimagesize("dr://var/public{$url["path"]}")[3];
    }
    catch (\Exception $e) {
      $errors[self::ERROR_IMAGE_NON_EXISTENT] = $this->intl->t(
        "Image doesn’t exist ({image_src}).",
        [ "image_src" => "<code>{$image->attribute["src"]}</code>" ]
      );
      return "figure";
    }

    // Build the image tag.
    // We hide the image for screenreaders, because the figcaption is also read to the user and contains semantic markup
    // which the alt attribute cannot provide.
    $this->lastChild = "<img alt='{$alt}' aria-hidden='true' {$imgAttributes} src='//{$this->config->hostname}{$url["path"]}'><figcaption>{$caption}</figcaption>";

    return "figure{$this->validateUserClasses($node, $errors)}";
  }

  /**
   * Validate list.
   *
   * @param \tidyNode $node
   *   The list node to validate.
   * @param mixed $errors
   *   Parameter to collect error messages.
   * @return string
   *   The starting tag including allowed attributes.
   */
  protected function validateList($node, &$errors) {
    // If this is the first opening list tag, set the list information array accordingly and constrain the allowed tags.
    if ($this->list === false) {
      $this->list = [
        "tag"          => $node->name,
        "level"        => $this->level,
        "allowed_tags" => $this->allowedTags,
      ];
      $this->allowedTags = [
        "a"      => "&lt;a&gt;",
        "b"      => "&lt;b&gt;",
        "em"     => "&lt;em&gt;",
        "i"      => "&lt;i&gt;",
        "li"     => "&lt;li&gt;",
        "ol"     => "&lt;ol&gt;",
        "strong" => "&lt;strong&gt;",
        "ul"     => "&lt;ul&gt;",
      ];
    }
    return "{$node->name}{$this->validateUserClasses($node, $errors)}";
  }

  /**
   * Validate ordered list.
   *
   * @param \tidyNode $node
   *   The list node to validate.
   * @param mixed $errors
   *   Parameter to collect error messages.
   * @return string
   *   The starting tag including allowed attributes.
   */
  protected function validateOl($node, &$errors) {
    return $this->validateList($node, $errors);
  }

  /**
   * Validate unordered list.
   *
   * @param \tidyNode $node
   *   The list node to validate.
   * @param mixed $errors
   *   Parameter to collect error messages.
   * @return string
   *   The starting tag including allowed attributes.
   */
  protected function validateUl($node, &$errors) {
    return $this->validateList($node, $errors);
  }

  /**
   * Validate that the node only contains allowed user CSS classes.
   *
   * @param \tidyNode $node
   *   The node to validate.
   * @param mixed $errors
   *   Parameter to collect error messages.
   * @return null|string
   *   The expanded class attribute if present, otherwise <code>NULL</code>.
   * @throws \MovLib\Exception\ValidationException
   */
  protected function validateUserClasses($node, &$errors) {
    if (isset($node->attribute) && !empty($node->attribute["class"])) {
      if (!isset($this->userClasses[$node->attribute["class"]])) {
        $classes = implode(" ", array_keys($this->userClasses));
        $errors[self::ERROR_DISALLOWED_CSS_CLASSES] = $this->intl->t(
          "Disallowed CSS classes in “{label}” text, allowed values are: {classes}",
          [ "label" => $this->label, "classes" => "<code>{$classes}</code>" ]
        );
        return null;
      }
      else {
        return " class='{$node->attribute["class"]}'";
      }
    }
  }

}
