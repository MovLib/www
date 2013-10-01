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

  /**
   * Maximum file size.
   *
   * The maximum size an image can have, currently set to 15 MB (value given is in Bytes).
   *
   * @var int
   */
  public $maximumFileSize = 15728640;

  public $path;

  public $width;

  public $height;

  public $type;

  public $size;

  protected $error;

  /**
   * Instantiate new input form element of type file.
   *
   * @param string $id
   *   The form element's global unique identifier.
   */
  public function __construct($id) {
    parent::__construct($id);
    if (isset($_FILES[$this->id])) {
      $this->error = $_FILES[$this->id]["error"];
      $this->path  = $_FILES[$this->id]["tmp_name"];
      $this->size  = $_FILES[$this->id]["size"];
    }
  }

  /**
   * @inheritdoc
   */
  public function __toString() {
    $this->attributes["accept"]           = "image/jpeg, image/png";
    $this->attributes["data-maxfilesize"] = $this->maximumFileSize;
    $this->attributes["type"]             = "file";
    return "{$this->help}<p><label{$this->expandTagAttributes($this->labelAttributes)}>{$this->label}</label><input{$this->expandTagAttributes($this->attributes)}></p>";
  }

  /**
   * @inheritdoc
   */
  public function validate(){
    global $i18n;

    if ($this->error === UPLOAD_ERR_NO_FILE) {
      if (isset($this->attributes["aria-required"])) {
        throw new ValidationException($i18n->t("The highlighted image field is required."));
      }
      return $this;
    }

    if ($this->size > $this->maximumFileSize) {
      throw new ValidationException($i18n->t("The image is too large: it must be {0,number} {1} or less.", $this->formatBytes($this->maximumFileSize)));
    }

    try {
      list($this->width, $this->height, $this->type) = getimagesize($this->path);

      if ($this->width === 0 || $this->height === 0 || ($this->type !== IMAGETYPE_JPEG || $this->type !== IMAGETYPE_PNG)) {
        trigger_error("");
      }
    }
    catch (\MovLib\Exception\ErrorException $e) {
      throw new ValidationException($i18n->t("Unsupported image type and/or corrupt image, the following types are supported: {0}", [ $this->attributes["accept"] ]));
    }

    return $this;
  }

}
