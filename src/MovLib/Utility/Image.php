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
namespace MovLib\Utility;

use \finfo;
use \MovLib\Exception\ImageException;
use \MovLib\Utility\Network;

/**
 * The utility class image contains methods to work with all kinds of images.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Image {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Image style for image with 150 pixel in width and height.
   *
   * @var int
   */
  const STYLE_150 = 150;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Process an uploaded image.
   *
   * @param string $formElementName
   *   The name of the file form element to process.
   * @param string $imagePath
   *   The desired storage path within our uploads directory.
   * @return array
   *   Associative array containing the following keys:
   *   <ul>
   *     <li><b>path:</b> Absolute path to the image on the server.</li>
   *     <li><b>uri:</b> Absolute (static) URI to the image.</li>
   *     <li><b>ext:</b> The image's extension.</li>
   *     <li><b>mime:</b> The image's MIME type.</li>
   *   </ul>
   * @throws ImageException
   *   If something is odd with the uploaded file.
   */
  public static function upload($formElementName, $imagePath) {
    if (isset($_FILES[$formElementName]["error"]) && $_FILES[$formElementName]["error"] == UPLOAD_ERR_NO_FILE) {
      return;
    }
    if (!is_file($_FILES[$formElementName]["tmp_name"])) {
      throw new ImageException("No valid input found.");
    }
    if ($_FILES[$formElementName]["error"] != UPLOAD_ERR_OK) {
      throw new ImageException("Upload error.");
    }
    $finfo = new finfo();
    $mime = $finfo->file($_FILES[$formElementName]["tmp_name"], FILEINFO_MIME_TYPE);
    switch ($mime) {
      case "image/jpg":
      case "image/jpeg":
        $extension = "jpg";
        break;

      case "image/png":
        $extension = "png";
        break;

      case "image/svg+xml":
        $extension = "svg";
        break;

      case "image/webp":
        $extension = "webp";
        break;

      default:
        throw new ImageException("No valid MIME type.");
    }
    try {
      $path = "{$_SERVER["HOME"]}/uploads/{$imagePath}.{$extension}";
      move_uploaded_file($_FILES[$formElementName]["tmp_name"], $path);
    } catch (ErrorException $e) {
      throw new ImageException("Could not move uploaded file.", $e);
    }
    return [
      "path" => $path,
      "uri"  => "https://" . Network::SERVER_NAME_STATIC . "/uploads/{$imagePath}.{$extension}",
      "ext"  => $extension,
      "mime" => $mime,
    ];
  }

}
