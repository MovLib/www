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
namespace MovLib\Presentation\Partial\FormElement;

use \MovLib\Data\Image\AbstractImage as Image;
use \MovLib\Exception\ErrorException;
use \MovLib\Exception\ImageException;
use \MovLib\Exception\ValidationException;

/**
 * HTML input type file form element specialized for image uploads.
 *
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html#attr-input-type
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Input
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputImage extends \MovLib\Presentation\Partial\FormElement\AbstractFormElement {


  // ------------------------------------------------------------------------------------------------------------------- Properties


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
   * The image instance responsible for storing this image.
   *
   * @var \MovLib\Data\Image\AbstractImage
   */
  protected $image;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new input form element of type file.
   *
   * @param string $id
   *   The form element's global unique identifier.
   * @param string $label
   *   The label test.
   * @param \MovLib\Data\Image\AbstractImage $concreteImage
   *   The abstract image instance that's responsible for this image.
   * @param array $attributes [optional]
   *   Additional attributes.
   */
  public function __construct($id, $label, $concreteImage, array $attributes = null) {
    global $i18n;
    parent::__construct($id, $label, $attributes);
    $this->attributes["accept"]            = "image/jpeg,image/png";
    $this->attributes["data-max-filesize"] = ini_get("upload_max_filesize");
    $this->attributes["type"]              = "file";
    $this->image                           = $concreteImage;
    if ($this->image->imageExists === true) {
      $this->attributes["data-min-height"] = $this->image->imageHeight;
      $this->attributes["data-min-width"]  = $this->image->imageWidth;
    }
    else {
      $this->attributes["data-min-height"] = Image::IMAGE_MIN_HEIGHT;
      $this->attributes["data-min-width"]  = Image::IMAGE_MIN_WIDTH;
    }
    $helpMessageAttributes = $this->formatBytes($this->attributes["data-max-filesize"]);
    $helpMessageAttributes[] = $this->attributes["data-min-width"];
    $helpMessageAttributes[] = $this->attributes["data-min-height"];
    $this->setHelp($i18n->t("Image must be larger than {2}x{3} and less than {0} {1}. Allowed image types: JPG and PNG", $helpMessageAttributes));
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    if ($this->image->imageExists === true) {
      return
        "<div class='row'>" .
          "<div class='span span--1'>{$this->getImage($this->image, Image::IMAGE_STYLE_SPAN1)}</div>" .
          "<div class='span span--8'>{$this->help}<label for='{$this->id}'>{$this->label}</label><input{$this->expandTagAttributes($this->attributes)}></div>" .
        "</div>"
      ;
    }
    return "{$this->help}<p><label for='{$this->id}'>{$this->label}</label><input{$this->expandTagAttributes($this->attributes)}></p>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function validate(){
    global $i18n;

    if (empty($_FILES[$this->id])) {
      if (in_array("required", $this->attributes)) {
        throw new ValidationException($i18n->t("The “{0}” image field is mandatory.", [ $this->label ]));
      }
      return $this;
    }

    // Gather meta information about the uploaded image, getimagesize() will fail if this isn't a valid image.
    try {
      list($width, $height, $type) = getimagesize($_FILES[$this->id]["tmp_name"]);
      assert($width > 0);
      assert($height > 0);
      assert($type === IMAGETYPE_JPEG || $type === IMAGETYPE_PNG);
    }
    catch (ErrorException $e) {
      throw new ValidationException($i18n->t("Unsupported image type and/or corrupt image, the following types are supported: JPG and PNG"));
    }

    // Check dimension constrains.
    if ($height < $this->attributes["data-min-height"] || $width < $this->attributes["data-min-width"]) {
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
    if ($height < $this->image->imageHeight || $width < $this->image->imageWidth) {
      throw new ImageException($i18n->t(
        "New images should have a better quality than already existing images, this includes the resolution. The " .
        "current image’s resolution is {0}x{1} pixels but yours is {2}x{3}. Please cofirm that your upload has a " .
        "better quality than the existing on despite the fact of smaller dimensions.",
        [ $this->image->imageWidth, $this->image->imageHeight, $width, $height ]
      ));
    }

    // Time to move the image to our persistent storage, all seems valid.
    $this->image->moveUploadedImage($_FILES[$this->id]["tmp_name"], $width, $height, $this->extensions[$type]);
    return $this;
  }

}
