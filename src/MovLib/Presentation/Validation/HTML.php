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
namespace MovLib\Presentation\Validation;

use \HTMLPurifier;
use \HTMLPurifier_Config as HTMLPurifierConfig;

/**
 * HTML validation class.
 *
 * As of now we are using HTMLPurifier to validate HTML input. In the future we'd like to change to a custom solution
 * that throws errors instead of trying to sanitize the input. Users should enter valid HTML, especially because we
 * are targeting towards a WYSIWYG based on content editable and JavaScript. Nobody should ever enter invalid HTML,
 * unless the person has no JavaScript and has to enter it directly.
 *
 * Validation should never try to sanitize, but our time is limited and this is the fastest approach.
 *
 * @link http://htmlpurifier.org/
 * @see \MovLib\Presentation\Validation\InterfaceValidation
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class HTML {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Basic HTML tags for formatting texts.
   *
   * @var int
   */
  const FORMAT_BASIC_HTML = 0;

  /**
   * Basic HTML tags plus anchors.
   *
   * @var int
   */
  const FORMAT_ANCHORS = 1;

  /**
   * Basic HTML and anchor tags plus headings and lists.
   *
   * @var int
   */
  const FORMAT_EXTENDED_HTML = 2;

  /**
   * Extended HTML tags plus images.
   *
   * @var int
   */
  const FORMAT_IMAGES_HTML = 3;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * String containing all allowed HTML tags and attributes for the currently active format.
   *
   * @link http://htmlpurifier.org/live/configdoc/plain.html#HTML.Allowed
   * @var string
   */
  protected $allowedHTML = "b,br,em,i,p[class]";

  /**
   * Whetever to allow external links or not.
   *
   * @var boolean
   */
  public $allowExternal = false;

  /**
   * The currently active format.
   *
   * @var int
   */
  protected $format = self::FORMAT_BASIC_HTML;

  /**
   * The validated HTML.
   *
   * @var string
   */
  public $html;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new HTML validator.
   *
   * @param string $html [optional]
   *   The HTML fragment to validate.
   * @param int $format [optional]
   *   The format to validate against.
   * @param boolean $allowExternal [optional]
   *   Whetever to allow external URLs or not.
   */
  public function __construct($html = null, $format = self::FORMAT_BASIC_HTML, $allowExternal = false) {
    $this->html = $html;
    $this->setFormat($format, $allowExternal);
  }

  /**
   * Get the HTML.
   *
   * @return string
   *   The HTML.
   */
  public function __toString() {
    return $this->html;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Set the HTML to validate.
   *
   * @param string $html
   *   The HTML to validate.
   */
  public function set($html) {
    $this->html = $html;
  }

  /**
   * Set the HTML format.
   *
   * @param int $format
   *   The format, use the <var>FORMAT_*</var> class constants.
   * @param boolean $allowExternal [optional]
   *   Whetever to allow external URLs or not.
   * @return this
   */
  public function setFormat($format, $allowExternal = false) {
    if ($format >= self::FORMAT_ANCHORS) {
      $this->allowedHTML .= ",a[href]";
    }
    if ($format >= self::FORMAT_EXTENDED_HTML) {
      $this->allowedHTML .= ",blockquote[cite],dd,dl,dt,h2,h3,h4,h5,h6,li,ol,q[cite],ul";
    }
    if ($format >= self::FORMAT_IMAGES_HTML) {
      $this->allowedHTML .= ",img[alt|class|height|src|width]";
    }
    $this->format = $format;
    $this->allowExternal = $allowExternal;
    return $this;
  }

  /**
   * Validate the HTML with the current options.
   *
   * @return string
   *   The validated HTML.
   * @throws \MovLib\Exception\ValidationException
   */
  public function validate() {

    // Create configuration for HTMLPurifier
    $htmlPurifierConfig = HTMLPurifierConfig::createDefault();
    $htmlPurifierConfig->set("Attr.AllowedClasses", [ "user-left" => true, "user-center" => true, "user-right" => true ]);
    $htmlPurifierConfig->set("AutoFormat.AutoParagraph", true);
    $htmlPurifierConfig->set("HTML.Doctype", "HTML 4.01 Strict"); // No HTML5 support!
    $htmlPurifierConfig->set("HTML.Nofollow", true);
    $htmlPurifierConfig->set("HTML.TargetBlank", true);
    $htmlPurifierConfig->set("HTML.Allowed", $this->allowedHTML);
    $htmlPurifierConfig->set("Output.Newline", "\n");
    $htmlPurifierConfig->set("Output.SortAttr", true);
    $htmlPurifierConfig->set("URI.AllowedSchemes", [ "http" => true, "https" => true ]);
    $htmlPurifierConfig->set("URI.Base", $_SERVER["SERVER_NAME"]);
    $htmlPurifierConfig->set("URI.DefaultScheme", "https");
    $htmlPurifierConfig->set("URI.DisableExternal", !$this->allowExternal);
    $htmlPurifierConfig->set("URI.DisableExternalResources", true);
    $htmlPurifierConfig->set("URI.Host", $GLOBALS["movlib"]["default_domain"]);

    // Instantiate and purify.
    $htmlPurifier = new HTMLPurifier($htmlPurifierConfig);
    $this->html = $htmlPurifier->purify($this->html);

    // @todo Should we compare the purified HTML against the user supplied HTML at this point and throw an error if
    //       they don't match? This could be a bit harsh, e.g. it could throw an error simply because we re-order the
    //       attributes and the user supplied attributes weren't ordered. Very problematic, I think it's best to stick
    //       to the sanitization and wait for our own custom implementation for a correct validation with proper error
    //       messages, exceptions and error codes.

    return $this->html;
  }

}
