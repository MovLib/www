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
namespace MovLib\Data;

/**
 * Represents an uploaded file.
 *
 * @link http://php.net/manual/features.file-upload.post-method.php
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class UploadedFile {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The file's error code.
   *
   * @link http://php.net/manual/features.file-upload.errors.php
   * @var integer
   */
  public $error;

  /**
   * The file's original name as submitted by the client's user agent.
   *
   * @var string
   */
  public $name;

  /**
   * The file's absolute temporary path.
   *
   * @var string
   */
  public $path;

  /**
   * The file's size in bytes.
   *
   * @var integer
   */
  public $size;

  /**
   * The file's MIME type as submitted by the client's user agent (e.g. <code>"image/png"</code>).
   *
   * @var string
   */
  public $type;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Instantiate new uploaded file.
   *
   * @param string $id
   *   The global unique identifier of the uploaded file (the value of the <code>"name"</code> attribute).
   */
  public function __construct($id) {
    if (isset($_FILES[$id])) {
      $this->error = $_FILES[$id]["error"];
      $this->name  = $_FILES[$id]["name"];
      $this->path  = $_FILES[$id]["tmp_name"];
      $this->size  = $_FILES[$id]["size"];
      $this->type  = $_FILES[$id]["type"];
    }
    else {
      $this->error = UPLOAD_ERR_NO_FILE;
    }
  }

}
