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

  /**
   * Maximum file size.
   *
   * The maximum size an image can have, currently set to 15 MB (value given is in Bytes).
   *
   * @var int
   */
  public $maximumFileSize = 15728640;

  /**
   * The global minimum height for images.
   *
   * @var int
   */
  public $minimumHeight = 140;

  /**
   * The global minimum width for images.
   *
   * @var int
   */
  public $minimumWidth = 140;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new input form element of type file.
   *
   * @param string $id
   *   The form element's global unique identifier.
   * @param \MovLib\Data\Image\AbstractImage $concreteImage
   *   The abstract image instance that's responsible for this image.
   * @param int $style [optional]
   *   An image style that should be generated right away and not delayed.
   */
  public function __construct($id, $concreteImage) {
    parent::__construct($id);
    $this->image = $concreteImage;
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    global $i18n;
    $this->attributes["accept"]           = "image/jpeg, image/png";
    $this->attributes["data-maxfilesize"] = $this->maximumFileSize;
    $this->attributes["data-minheight"]   = $this->minimumHeight;
    $this->attributes["data-minwidth"]    = $this->minimumWidth;
    $this->attributes["type"]             = "file";
    list($size, $unit) = $this->formatBytes($this->maximumFileSize);
    return "{$this->help}<p><label{$this->expandTagAttributes($this->labelAttributes)}>{$this->label}</label><input{$this->expandTagAttributes($this->attributes)}></p><small>{$i18n->t(
      "Image must be larger than {0}x{1} and less than {2} {3}. Allowed image types: JPG and PNG",
      [ $this->minimumHeight, $this->minimumWidth, $size, $unit ]
    )}</small>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function validate(){
    global $i18n;

    // Check if file is present, if not check if it is required.
    if (empty($_FILES[$this->id]) || $_FILES[$this->id]["error"] === UPLOAD_ERR_NO_FILE) {
      if (isset($this->attributes["aria-required"])) {
        throw new ValidationException($i18n->t("The highlighted image field is required."));
      }
      return $this;
    }

    // Make sure the file isn't too large.
    if ($_FILES[$this->id]["size"] > $this->maximumFileSize) {
      throw new ValidationException($i18n->t("The image is too large: it must be {0,number} {1} or less.", $this->formatBytes($this->maximumFileSize)));
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
    if ($height < $this->minimumHeight || $width < $this->minimumWidth) {
      throw new ValidationException($i18n->t("The image is too small, it must be larger than {0}x{1} pixels.", [ $this->minimumWidth, $this->minimumHeight ]));
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
