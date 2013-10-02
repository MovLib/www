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
    $this->attributes["accept"]           = "image/jpeg, image/png";
    $this->attributes["data-maxfilesize"] = $this->maximumFileSize;
    $this->attributes["type"]             = "file";
    foreach ([ "imageMaxHeight" => "maxheight", "imageMaxWidth" => "maxwidth", "imageMinHeight" => "minheight", "imageMinWidth" => "minwidth" ] as $constrain => $attr) {
      if (isset($this->image->{$constrain})) {
        $this->attributes["data-{$attr}"] = $this->image->{$constrain};
      }
    }
    return "{$this->help}<p><label{$this->expandTagAttributes($this->labelAttributes)}>{$this->label}</label><input{$this->expandTagAttributes($this->attributes)}></p>";
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

    // Check all dimension constrains.
    $errors = null;
    if (isset($this->image->imageMaxHeight) && $height > $this->image->imageMaxHeight) {
      $errors[] = $i18n->t("The iamge is too high: the maximum height is {0} pixel", [ $this->image->imageMaxHeight ]);
    }
    if (isset($this->image->imageMaxWidth) && $width > $this->image->imageMaxWidth) {
      $errors[] = $i18n->t("The iamge is too broad: the maximum width is {0} pixel", [ $this->image->imageMaxWidth ]);
    }
    if (isset($this->image->imageMinHeight) && $height < $this->image->imageMinHeight) {
      $errors[] = $i18n->t("The image is too small: the minimum height is {0} pixel", [ $this->image->imageMinHeight ]);
    }
    if (isset($this->image->imageMinWidth) && $width < $this->image->imageMinWidth) {
      $errors[] = $i18n->t("The image is narrow: the minimum width is {0} pixel", [ $this->image->imageMinWidth ]);
    }
    if ($errors) {
      throw new ValidationException(implode("<br>", $errors));
    }

    // Do not use image_type_to_extension() because it returns long extensions (jpeg instead of jpg).
    if ($type === IMAGETYPE_JPEG) {
      $extension = "jpg";
    }
    if ($type === IMAGETYPE_PNG) {
      $extension = "png";
    }

    // All other styles can be generated after the response was sent to the user.
    $this->image->moveUploadedImage($_FILES[$this->id]["tmp_name"], $width, $height, $extension);

    return $this;
  }

}
