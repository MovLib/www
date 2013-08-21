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
namespace MovLib\Model;

use \MovLib\Exception\ErrorException;
use \MovLib\Exception\ImageException;
use \MovLib\Model\BaseModel;
use \MovLib\Utility\Network;
use \MovLib\Utility\Validation;

/**
 * Contains methods for models that contain images.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractImageModel extends BaseModel {


  // ------------------------------------------------------------------------------------------------------------------- Properties
  // Each property has to have the <code>image*</code> prefix to ensure that no name collisions are possible with
  // properties of the base or child classes.


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

  // ------------------------------------------------------------------------------------------------------------------- Common image styles

  /**
   * Image style for galleries.
   * @var int
   */
  const IMAGESTYLE_GALLERY = "140x140>";

  // ------------------------------------------------------------------------------------------------------------------- Protected Methods


  /**
   * Set paths and URIs for image.
   *
   * @param string $imageName
   *   The name of the image.
   * @param array $imageStyles
   *   The available image styles.
   * @return $this
   */
  protected function initImage($imageName, $imageStyles) {
    $this->imageName = $imageName;
    // Remove unsafe path characters from the image styles.
    $c = count($imageStyles);
    for ($i = 0; $i < $c; ++$i) {
      $this->imageStyles[$imageStyles[$i]] = [
        "style" => $imageStyles[$i],
        "name" => Validation::fileName($imageStyles[$i]),
      ];
    }
    if (isset($this->imageExtension) && isset($this->imageHash)) {
      $path = "/uploads/{$this->imageDirectory}/{$this->imageName}.{$this->imageHash}.{$this->imageExtension}";
      $this->imagePath = $_SERVER["HOME"] . $path;
      $this->imageUri  = "https://" . Network::SERVER_NAME_STATIC . $path;
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
   * @return $this
   */
  protected function generateImageStylePaths() {
    foreach ($this->imageStyles as $style => $data) {
      $path = "/uploads/{$this->imageDirectory}/{$data["name"]}/{$this->imageName}.{$this->imageHash}.{$this->imageExtension}";
      $this->imageStyles[$style]["path"] = $_SERVER["HOME"] . $path;
      $this->imageStyles[$style]["uri"] = "https://" . Network::SERVER_NAME_STATIC . $path;
    }
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Generate all image styles for this image.
   *
   * @return $this
   */
  public function generateImageStyles() {
    foreach ($this->imageStyles as $style => $data) {
      if (!is_dir(($dir = dirname($data["path"])))) {
        mkdir($dir);
      }
      exec("convert {$this->imagePath} -filter 'Lanczos' -resize '{$style}' -quality 75 -strip {$data["path"]} && chmod 777 {$data["path"]}");
//      chmod($data["path"], 0777);
      if (filesize($data["path"]) > 10240) {
        exec("convert {$data["path"]} -interlace 'line' {$data["path"]}");
      }
    }
    return $this;
  }

  /**
   * Get width, height and URI of specified image style.
   *
   * @staticvar array $styles
   *   Used to cache generated image arrays.
   * @param string $style
   *   The desired image style's name.
   * @return array
   *   Associative array containing all image information.
   */
  public function getImageStyle($style) {
    if (!isset($this->imageStyles[$style]["width"])) {
      if (!is_file($this->imageStyles[$style]["path"])) {
        $this->generateImageStyles();
      }
      list($this->imageStyles[$style]["width"], $this->imageStyles[$style]["height"]) = getimagesize($this->imageStyles[$style]["path"]);
    }
    return $this->imageStyles[$style];
  }

  /**
   * Validate uploaded image and move to storage.
   *
   * @param string $formElementName
   *   The value of the <em>name</em>-attribute of the <em>file</em>-input-element of the form.
   * @return $this
   * @throws ImageException
   *   If something is odd with the uploaded file.
   */
  public function uploadImage($formElementName) {
    try {
      list($width, $height) = getimagesize($_FILES[$formElementName]["tmp_name"]);
      $ext = $this->imageSupported[$_FILES[$formElementName]["type"]];
      $hash = filemtime($_FILES[$formElementName]["tmp_name"]);
      $path = "{$_SERVER["HOME"]}/uploads/{$this->imageDirectory}/{$this->imageName}.{$hash}.{$ext}";
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
    $this->imageUri       = "https://" . Network::SERVER_NAME_STATIC . "/uploads/{$this->imageDirectory}/{$this->imageName}.{$hash}.{$ext}";
    return $this->generateImageStylePaths()->generateImageStyles();
  }

  /**
   * Deletes this image and all its styles from storage.
   *
   * @return $this
   */
  public function deleteImage() {
    unlink($this->imagePath);
    foreach ($this->imageStyles as $style => $data) {
      unlink($data["path"]);
    }
    $this->imageExists = false;
    return $this;
  }

}
