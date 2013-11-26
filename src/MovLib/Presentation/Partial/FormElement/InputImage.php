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

use \MovLib\Data\Image\AbstractBaseImage as Image;
use \MovLib\Exception\Client\UnauthorizedException;
use \MovLib\Exception\ValidationException;

/**
 * HTML input type file form element specialized for image uploads.
 *
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html#attr-input-type
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Input
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputImage extends \MovLib\Presentation\Partial\FormElement\AbstractFormElement {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The image's extension.
   *
   * <b>Note:</b> This value is only available after validation!
   *
   * @var string
   */
  public $extension;

  /**
   * Available image extensions.
   *
   * @internal We don't use image_type_to_extension() because it uses long extensions (e.g. jpeg instead of jpg).
   * @var array
   */
  private $extensions = [
    IMAGETYPE_JPEG => "jpg",
    IMAGETYPE_PNG  => "png",
  ];

  /**
   * The image's height.
   *
   * <b>Note:</b> This value is only available after validation!
   *
   * @var integer
   */
  public $height;

  /**
   * The image instance responsible for storing this image.
   *
   * @var \MovLib\Data\Image\AbstractBaseImage
   */
  protected $image;

  /**
   * The image's absolute path.
   *
   * <b>Note:</b> This value is only available after validation!
   *
   * @var string
   */
  public $path;

  /**
   * The image's width.
   *
   * <b>Note:</b> This value is only available after validation!
   *
   * @var integer
   */
  public $width;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new input form element of type file.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @param string $id
   *   The form element's global unique identifier.
   * @param string $label
   *   The label test.
   * @param \MovLib\Data\Image\AbstractBaseImage $concreteImage
   *   The abstract image instance that's responsible for this image.
   * @param array $attributes [optional]
   *   Additional attributes.
   * @throws \MovLib\Exception\Client\UnauthorizedException
   */
  public function __construct($id, $label, $concreteImage, array $attributes = null) {
    global $i18n, $kernel, $session;
    if ($session->isAuthenticated === false) {
      throw new UnauthorizedException($i18n->t(
        "You must be signed in to upload images, please go to the {0}login page{2} to do so. If you don’t have an " .
        "account yet go to the {1}registration page{2} and sign up for a free {3} account.", [
          "<a href=''>", "<a href=''>", "</a>", $kernel->siteName
        ]
      ));
    }
    parent::__construct($id, $label, $attributes);
    $this->attributes["accept"]            = "image/jpeg,image/png";
    $this->attributes["data-max-filesize"] = ini_get("upload_max_filesize");
    $this->attributes["data-min-height"]   = isset($this->image->height) ? $this->image->height : Image::IMAGE_MIN_HEIGHT;
    $this->attributes["data-min-width"]    = isset($this->image->width) ? $this->image->width : Image::IMAGE_MIN_WIDTH;
    $this->attributes["type"]              = "file";
    $this->image                           = $concreteImage;
    $helpMessageAttributes                 = $this->formatBytes($this->attributes["data-max-filesize"]);
    $helpMessageAttributes[]               = $this->attributes["data-min-width"];
    $helpMessageAttributes[]               = $this->attributes["data-min-height"];
    $this->setHelp($i18n->t("Image must be larger than {2}x{3} and less than {0} {1}. Allowed image types: JPG and PNG", $helpMessageAttributes));
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  protected function render() {
    if ($this->image->exists === true) {
      return
        "<div class='row'>" .
          "<div class='span span--1'>{$this->getImage($this->image->getStyle(Image::STYLE_SPAN_01))}</div>" .
          "<div class='span span--8'>{$this->help}<label for='{$this->id}'>{$this->label}</label><input{$this->expandTagAttributes($this->attributes)}></div>" .
        "</div>"
      ;
    }
    return "{$this->help}<p><label for='{$this->id}'>{$this->label}</label><input{$this->expandTagAttributes($this->attributes)}></p>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   */
  public function validate(){
    global $i18n, $session;

    // Only authenticated user's are allowed to upload images.
    if ($session->isAuthenticated === false) {
      throw new UnauthorizedException;
    }

    if (empty($_FILES[$this->id]) || $_FILES[$this->id]["error"] === UPLOAD_ERR_NO_FILE) {
      if (in_array("required", $this->attributes)) {
        throw new ValidationException($i18n->t("The “{0}” image field is mandatory.", [ $this->label ]));
      }
      return $this;
    }

    if ($_FILES[$this->id]["error"] !== UPLOAD_ERR_OK) {
      switch ($_FILES[$this->id]["error"]) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          throw new ValidationException("The uploaded image is too large, it must be {0,number,integer} {1} or smaller.", [
            $this->formatBytes($this->attributes["data-max-filesize"])
          ]);

        case UPLOAD_ERR_PARTIAL:
          throw new ValidationException("The uploaded image wasn’t completely uploaded, please try again.");

        case UPLOAD_ERR_NO_TMP_DIR:
        case UPLOAD_ERR_CANT_WRITE:
        case UPLOAD_ERR_EXTENSION:
          $e = new ValidationException("There was an unknown problem while processing your upload, please try again.", $_FILES[$this->id]["error"]);
          error_log($e);
          throw $e;
      }
    }

    // Gather meta information about the uploaded image, getimagesize() will fail if this isn't a valid image.
    try {
      list($this->width, $this->height, $type) = getimagesize($_FILES[$this->id]["tmp_name"]);
      assert($this->width > 0);
      assert($this->height > 0);
      assert($type === IMAGETYPE_JPEG || $type === IMAGETYPE_PNG);
    }
    catch (\ErrorException $e) {
      throw new ValidationException($i18n->t("Unsupported image type and/or corrupt image, the following types are supported: JPG and PNG"));
    }

    // Check dimension constrains.
    if ($this->height < $this->attributes["data-min-height"] || $this->width < $this->attributes["data-min-width"]) {
      throw new ValidationException(
        $i18n->t("The image is too small, it must be larger than {0}x{1} pixels.",
        [ $this->attributes["data-min-height"], $this->attributes["data-min-width"] ]
      ));
    }

    // An image should only be replaced with another image if the resolution is greater than the previous resolution.
    // Of course there are situations where this is not the case, we still have to tell the user.
    //
    // @internal
    //   You have to make sure that the user doesn't have to reupload this image in your presentation class by rendering
    //   a form with a confirmation dialog.
    // @todo @Richard
    //   Think about a way to solve this kind of problem once and for all. Maybe with a ConfirmationException which is
    //   catched in main.php?
    if ($this->height < $this->image->height || $this->width < $this->image->width) {
      throw new ValidationException($i18n->t(
        "New images should have a better quality than already existing images, this includes the resolution. The " .
        "current image’s resolution is {0}x{1} pixels but yours is {2}x{3}. Please cofirm that your upload has a " .
        "better quality than the existing on despite the fact of smaller dimensions.",
        [ $this->image->width, $this->image->height, $this->width, $this->height ]
      ));
    }

    // Everything seems valid, export all values to class scope.
    $this->path      = $_FILES[$this->id]["tmp_name"];
    $this->extension = $this->extensions[$type];
    return $this;
  }

}
