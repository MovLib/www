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
 * HTML input type file form element.
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
  use \MovLib\Presentation\Partial\FormElement\TraitReadonly;

  /**
   * Abstract image instance.
   *
   * @var \MovLib\Data\AbstractImage
   */
  private $abstractImage;

  /**
   * Instantiate new input form element of type file.
   *
   * @param \MovLib\Data\AbstractImage $abstractImage
   *   Abstract image instance.
   * @param string $id
   *   The form element's global unique identifier.
   * @param string $label
   *   The form element's label text.
   * @param array $attributes [optional]
   *   The form element's attributes.
   * @param array $labelAttributes [optional]
   *   The form element's label attributes.
   */
  public function __construct($abstractImage, $id, $label, array $attributes = null, array $labelAttributes = null) {
    parent::__construct($id, $attributes, $label, $labelAttributes);
    $this->attributes["accept"]              = implode(", ", array_column($abstractImage->imageSupported, "mime"));
    $this->attributes["type"]                = "file";
    $this->attributes["data-max-file-size"]  = $abstractImage->imageMaxFileSize;
    $this->abstractImage                     = $abstractImage;
  }

  /**
   * @inheritdoc
   */
  public function __toString(){
    return
      "{$this->help}<p>" .
        "<label{$this->expandTagAttributes($this->labelAttributes)}>{$this->label}</label>" .
        "<input type='hidden' name='MAX_FILE_SIZE' value='{$this->abstractImage->imageMaxFileSize}'>" .
        "<input{$this->expandTagAttributes($this->attributes)}>" .
      "</p>"
    ;
  }

  /**
   * @inheritdoc
   */
  public function validate(){
    global $i18n;

    try {
      switch ($_FILES[$this->id]["error"]) {
        case UPLOAD_ERR_OK:
          break;

        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          throw new ValidationException($i18n->t("The uploaded image is too large: it must be {0,number} {1} or less.", $this->formatBytes($this->abstractImage->imageMaxFileSize)));

        case UPLOAD_ERR_PARTIAL:
        case UPLOAD_ERR_NO_FILE:
          throw new ValidationException($i18n->t("The image was not completely uploaded, please try again."));

        default:
          // @todo We have a serious problem!
          throw new ValidationException($i18n->t("Unknown error!"));
      }

      list($width, $height, $type) = getimagesize($_FILES[$this->id]["tmp_name"]);

      if ($width === 0 || $height === 0 || !in_array($type, $this->abstractImage->imageSupported["types"]) || !in_array($_FILES[$this->id]["type"], $this->abstractImage->imageSupported["mimes"])) {
        throw new ValidationException($i18n->t("Unsupported image type and/or corrupt file, the following types are supported: {0}", [ $this->attributes["accept"] ]));
      }

      if ($_FILES[$this->id]["size"] > $this->abstractImage->imageMaxFileSize) {
        throw new ValidationException($i18n->t("The uploaded image is too large: it must be {0,number} {1} or less.", $this->formatBytes($this->abstractImage->imageMaxFileSize)));
      }

      $this->abstractImage->imageMoveToStorage($_FILES[$this->id]["tmp_name"], $width, $height, $type);
    }
    catch (ErrorException $e) {
      throw new ValidationException($i18n->t("Unsupported image type and/or corrupt file, the following types are supported: {0}", [ $this->attributes["accept"] ]));
    }
    return $this;
  }

}
