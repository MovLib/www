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
use \MovLib\Model\AbstractModel;
use \MovLib\Utility\Network;

/**
 * Contains methods for models that contain images.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractImageModel extends AbstractModel {


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
   * Array containing all image styles.
   *
   * @var array
   */
  public $imageStyles = [];

  /**
   * Absolute paths and URIs to the different styled image versions.
   *
   * @var array
   */
  private $imageStylePathsAndUris = [];

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


  // ------------------------------------------------------------------------------------------------------------------- Protected Methods


  /**
   * Set paths and URIs for image.
   *
   * @param string $imageName
   *   The name of the image.
   * @return $this
   */
  protected function initImage($imageName) {
    $this->imageName = $imageName;
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
    $c = count($this->imageStyles);
    for ($i = 0; $i < $c; ++$i) {
      $path = "/uploads/{$this->imageDirectory}/{$this->imageStyles[$i]}/{$this->imageName}.{$this->imageHash}.{$this->imageExtension}";
      $this->imageStylePathsAndUris[$this->imageStyles[$i]] = [
        "path" => $_SERVER["HOME"] . $path,
        "uri"  => "https://" . Network::SERVER_NAME_STATIC . $path,
      ];
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
    $c = count($this->imageStyles);
    for ($i = 0; $i < $c; ++$i) {
      $path = $this->imageStylePathsAndUris[$this->imageStyles[$i]]["path"];
      if (!is_dir(($dir = dirname($path)))) {
        mkdir($dir);
      }
      exec("convert {$this->imagePath} -filter 'Lanczos' -resize '{$this->imageStyles[$i]}' -quality 75 -strip {$path}");
      if (filesize($path) > 10240) {
        exec("convert {$path} -interlace 'line' {$path}");
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
   *   Associative array in the following format:
   *   <ul>
   *     <li><b>width:</b> The width of the image.</li>
   *     <li><b>height:</b> The height of the image.</li>
   *     <li><b>src:</b> The absolute URI of the image.</li>
   *   </ul>
   */
  public function getImageStyle($style) {
    static $styles = null;
    if (!isset($styles[$style])) {
      if (!is_file($this->imageStylePathsAndUris[$style]["path"])) {
        $this->generateImageStyles();
      }
      list($styles[$style]["width"], $styles[$style]["height"]) = getimagesize($this->imageStylePathsAndUris[$style]["path"]);
      $styles[$style]["src"] = $this->imageStylePathsAndUris[$style]["uri"];
    }
    return $styles[$style];
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
    $c = count($this->imageStyles);
    for ($i = 0; $i < $c; ++$i) {
      unlink($this->imageStylePathsAndUris[$this->imageStyles[$i]]["path"]);
    }
    $this->imageExists = false;
    return $this;
  }

}
