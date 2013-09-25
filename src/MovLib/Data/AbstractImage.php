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
namespace MovLib\Data;

use \MovLib\Exception\ErrorException;
use \MovLib\Exception\ImageException;
use \MovLib\Data\User as UserModel;

/**
 * Contains methods and properties for models that contain images.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractImage extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Properties
  // Each property has to have the <code>image*</code> prefix to ensure that no name collisions are possible with
  // properties of the base or child classes.


  /**
   * The already translated text for the image's <code>alt</code> attribute.
   *
   * @var string
   */
  public $imageAlt;

  /**
   * Name of the directory within the uploads directory on the server.
   *
   * @var string
   */
  public $imageDirectory;

  /**
   * Flag indicating if the image exists.
   *
   * @var boolean
   */
  public $imageExists = false;

  /**
   * The image's extension (without dot).
   *
   * @var string
   */
  public $imageExtension;

  /**
   * The image's MD5 file hash.
   *
   * @var string
   */
  public $imageHash;

  /**
   * The image's height.
   *
   * @var int
   */
  public $imageHeight;

  /**
   * The name of the image.
   *
   * @var string
   */
  public $imageName;

  /**
   * The image's absolute path to the original image.
   *
   * @var string
   */
  public $imagePath;

  /**
   * The file size of the image in bytes.
   *
   * @var int
   */
  public $imageSize;

  /**
   * Associative array containing all image styles and meta data about each image style.
   *
   * @var array
   */
  private $imageStyles = [];

  /**
   * The model's supported MIME types plus desired extensions.
   *
   * @var array
   */
  public $imageSupported = [
    "image/jpeg" => "jpg",
    "image/png"  => "png",
  ];

  /**
   * The image's width.
   *
   * @var int
   */
  public $imageWidth;

  /**
   * The image's URI to the original image.
   *
   * @var string
   */
  public $imageUri;

  /**
   * The image's details as associative array.
   *
   * @var array
   */
  protected $details;

  /**
   * The image's license information as associative array.
   *
   * @var array
   */
  private $license = null;


  // ------------------------------------------------------------------------------------------------------------------- Common image styles


  /**
   * Image style for galleries.
   *
   * @var int
   */
  const IMAGESTYLE_GALLERY = "128x128";

  /**
   * Image style for the image detail view.
   *
   * @var int
   */
  const IMAGESTYLE_DETAILS = "x540";

  /**
   * Image style for the image stream in the image detail view.
   *
   * @var int
   */
  const IMAGESTYLE_DETAILS_STREAM = "60x60";

  // ------------------------------------------------------------------------------------------------------------------- Protected Methods


  /**
   * Set paths and URIs for image.
   *
   * @param string $imageName
   *   The name of the image.
   * @param array $imageStyles
   *   The available image styles.
   * @return this
   */
  protected function initImage($imageName, $imageStyles) {
    $this->imageName = $imageName;
    if (isset($this->imageExtension) && isset($this->imageHash)) {
      $path = "uploads/{$this->imageDirectory}/{$this->imageName}.{$this->imageHash}.{$this->imageExtension}";
      $this->imagePath = "{$_SERVER["DOCUMENT_ROOT"]}/{$path}";
      $this->imageUri = "{$GLOBALS["movlib"]["static_domain"]}{$path}";
      $c = count($imageStyles);
      for ($i = 0; $i < $c; ++$i) {
        $imageStyles[$i]->sourcePath = $this->imagePath;
        $imageStyles[$i]->imageUri = $this->imageUri;
        $this->imageStyles[$imageStyles[$i]->dimensions] = $imageStyles[$i];
      }
      if (is_file($this->imagePath)) {
        $this->imageExists = true;
        $this->generateImageStylePaths();
      }
    }
    return $this;
  }

  /**
   * Generate all paths (and URIs) to the several styles this model supports.
   *
   * @return this
   */
  protected function generateImageStylePaths() {
    foreach ($this->imageStyles as $style => $styleObj) {
      $path = "uploads/{$this->imageDirectory}/{$styleObj->dimensions}/{$this->imageName}.{$this->imageHash}.{$this->imageExtension}";
      $this->imageStyles[$style]->path = "{$_SERVER["DOCUMENT_ROOT"]}/{$path}";
      $this->imageStyles[$style]->uri = "{$GLOBALS["movlib"]["static_domain"]}{$path}";
    }
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Generate all image styles for this image.
   *
   * @return this
   */
  public function generateImageStyles() {
    foreach ($this->imageStyles as $style => $styleObj) {
      $styleObj->convert();
    }
    return $this;
  }

  /**
   * Get width, height and URI of specified image style.
   *
   * @param string $style
   *   The desired image style's name.
   * @return array
   *   Associative array containing all image information.
   */
  public function getImageStyle($style) {
    if (!isset($this->imageStyles[$style]->width)) {
      if (!is_file($this->imageStyles[$style]->path)) {
        $this->generateImageStyles();
      }
      list($this->imageStyles[$style]->width, $this->imageStyles[$style]->height) = getimagesize($this->imageStyles[$style]->path);
    }
    return $this->imageStyles[$style];
  }


  /**
   * Retrieve all the relevant image details including license and user information.
   *
   * @return array
   *   Associative array containing the image details.
   */
  public function getImageDetails() {
    if ($this->details === null) {
      foreach ([ "description", "imageWidth", "imageHeight", "imageSize", "created", "changed", "upvotes", "source" ] as $prop) {
        $this->details[$prop] = $this->{$prop};
      }
      $this->details["license"] = $this->getLicense($this->licenseId);
      $this->details["user"] = (array) (new UserModel(UserModel::FROM_ID, $this->userId));
    }
    return $this->details;
  }

  /**
   * Retrieve the license information from the database.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @param int $licenseId
   *   The license's unique ID.
   * @return array
   *   The license information as associative array.
   */
  public function getLicense($licenseId) {
    global $i18n;
    if (!$this->license) {
      // Please note, that an image must have a license. Therefore the direct index access is possible.
      $this->license = $this->select(
        "SELECT
          `name`,
          `description`,
          COLUMN_GET(`dyn_names`, '{$i18n->languageCode}' AS BINARY) AS `name_localized`,
          COLUMN_GET(`dyn_descriptions`, '{$i18n->languageCode}' AS BINARY) AS `description_localized`,
          `url`,
          `abbr`,
          `icon_extension`,
          `icon_hash`,
          `admin`
        FROM `licenses`
        WHERE `license_id` = ? LIMIT 1"
        , "i", [ $licenseId ]
      )[0];
      $this->license["name"] = $this->license["name_localized"] ?: $this->license["name"];
      $this->license["description"] = $this->license["description_localized"] ?: $this->license["description"];
      unset($this->license["name_localized"]);
      unset($this->license["description_localized"]);
    }
    return $this->license;
  }

  /**
   * Validate uploaded image and move to storage.
   *
   * @param string $formElementName
   *   The value of the <code>name</code>-attribute of the <code><file></code>-element of the form.
   * @return this
   * @throws \MovLib\Exception\ImageException
   */
  public function uploadImage($formElementName) {
    try {
      list($width, $height) = getimagesize($_FILES[$formElementName]["tmp_name"]);
      $ext = $this->imageSupported[$_FILES[$formElementName]["type"]];
      $hash = filemtime($_FILES[$formElementName]["tmp_name"]);
      $path = "{$_SERVER["DOCUMENT_ROOT"]}/uploads/{$this->imageDirectory}/{$this->imageName}.{$hash}.{$ext}";
      // Remove any meta data from the original image before saving to storage.
      exec("convert {$_FILES[$formElementName]["tmp_name"]} -strip {$path}");
    } catch (ErrorException $e) {
      throw new ImageException("Error processing uploaded file.", $e);
    }
    if ($this->imageExists === true) {
      $this->deleteImage();
    }
    $this->imageExists    = true;
    $this->imageExtension = $ext;
    $this->imageHash      = $hash;
    $this->imageHeight    = $height;
    $this->imagePath      = $path;
    $this->imageWidth     = $width;
    $this->imageUri       = "{$GLOBALS["movlib"]["static_domain"]}uploads/{$this->imageDirectory}/{$this->imageName}.{$hash}.{$ext}";
    return $this->generateImageStylePaths()->generateImageStyles();
  }

  /**
   * Deletes this image and all its styles from storage.
   *
   * @return this
   */
  public function deleteImage() {
    unlink($this->imagePath);
    foreach ($this->imageStyles as $style => $styleObj) {
      unlink($styleObj->path);
    }
    $this->imageExists = false;
    return $this;
  }

}
