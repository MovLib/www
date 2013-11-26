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
namespace MovLib\Data\Image;

/**
 * All available image widths as namespace constants.
 *
 * All are direct matches to the CSS grid classes, you should only use these widths for all of your styles, to ensure
 * that they always match the grid system. There are special occasions where images will not match the grid system,
 * they need special attention. For an example of this have a look at the stream images of the various image details
 * presentations.
 *
 * @internal The zero prefixing ensures natural sorting in IDEs.
 */
// @codeCoverageIgnoreStart
const SPAN_01 = 70;
const SPAN_02 = 140;
const SPAN_03 = 220;
const SPAN_04 = 300;
const SPAN_05 = 380;
const SPAN_06 = 460;
const SPAN_07 = 540;
const SPAN_08 = 620;
const SPAN_09 = 700;
const SPAN_10 = 780;
const SPAN_11 = 860;
const SPAN_12 = 940;
// @codeCoverageIgnoreEnd

use \MovLib\Data\UnixShell as sh;

/**
 * Abstract base image implementation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractImage {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Default image dimension for preview area of input file elements if the image exists.
   *
   * @see \MovLib\Data\Image\SPAN_01
   * @var int
   */
  const IMAGE_STYLE_SPAN_01 = 70;

  /**
   * Default image dimension for image listings.
   *
   * @see \MovLib\Data\Image\SPAN_02
   * @var int
   */
  const IMAGE_STYLE_SPAN_02 = 140;

  /**
   * Global minimum height for uploaded images.
   *
   * @see \MovLib\Data\Image\SPAN_02
   * @var int
   */
  const IMAGE_MIN_HEIGHT = 140;

  /**
   * Global minimum width for uploaded images.
   *
   * @see \MovLib\Data\Image\SPAN_02
   * @var int
   */
  const IMAGE_MIN_WIDTH = 140;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The image's changed timestamp.
   *
   * @var int
   */
  protected $imageChanged;

  /**
   * The image's created timestamp.
   *
   * @var int
   */
  protected $imageCreated;

  /**
   * The image's directory within the uploads directory.
   *
   * @var string
   */
  protected $imageDirectory = "";

  /**
   * Flag indicating whether this image exists or not.
   *
   * @var boolean
   */
  public $imageExists = false;

  /**
   * The image's extension.
   *
   * @var string
   */
  protected $imageExtension;

  /**
   * The image's height.
   *
   * @internal Must be public for validation.
   * @var int
   */
  public $imageHeight;

  /**
   * The image's name.
   *
   * @var string
   */
  protected $imageName;

  /**
   * The image's placeholder path, name and extension within the assets path.
   *
   * @var string
   */
  protected $imagePlaceholder = "logo/vector.svg";

  /**
   * The original image's filesize.
   *
   * @var integer
   */
  protected $imageSize;

  /**
   * All available styles information, mapped to database.
   *
   * @var array
   */
  protected $imageStyles;

  /**
   * All available styles inofrmation, including dynamic data (translations).
   *
   * @var array
   */
  protected $imageStylesCache;

  /**
   * The image's width.
   *
   * @internal Must be public for validation.
   * @var int
   */
  public $imageWidth;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Generate all supported image styles.
   *
   * @param string $source
   *   Absolute path to the uploaded image.
   * @return this
   */
  protected abstract function generateImageStyles($source);

  /**
   * Get the <var>$style</var> for this image.
   *
   * @param mixed $style
   *   The desired style, use the objects <var>IMAGE_STYLE_*</var> constants. Defaults to <var>IMAGE_STYLE_SPAN_02</var>.
   * @return \MovLib\Data\Image\Style
   *   The image's desired style object.
   */
  public abstract function getImageStyle($style = self::IMAGE_STYLE_SPAN_02);


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Convert source image to given width, height, optionally crop and use given style for storage.
   *
   * Only images that are big enough will be downscaled, if the source image is too small no conversion is performed.
   * Instead it's simply copied to the target location (specified by style). If you pass width and height the image will
   * keep its aspect ratio.
   *
   * @param string $source
   *   Absolute path to the source image for conversion.
   * @param mixed $style
   *   The style constant.
   * @param int $width [optional]
   *   The desired width of the converted image, defaults to using <var>$style</var> as width.
   * @param int $height [optional]
   *   The desired height of the converted image, defaults to no height which basically means that the height is
   *   calculated according to the desired <var>$width</var>.
   * @param boolean $crop [optional]
   *   If set to <code>TRUE</code> the image will be resized first to width x height and then cropped to the center,
   *   defaults to no cropping.
   * @return string
   *   The absolute path to the converted image.
   * @throws \RuntimeException
   */
  protected function convertImage($source, $style, $width = null, $height = null, $crop = false) {
    if (!$width) {
      $width = $style;
    }
    if ($crop === true) {
      $args = "'{$width}x{$height}>^' -gravity 'Center' -crop '{$width}x{$height}+0+0' +repage";
    }
    else {
      $args = "'{$width}x{$height}>'";
    }
    $destination = $this->getImagePath($style);
    if (sh::execute("convert '{$source}' -define 'filter:support=2.5' -filter 'Lagrange' -quality 80 -unsharp 0x0.75+0.75+0.008 -resize {$args} '{$destination}'") === false) {
      throw new \RuntimeException("Couldn't convert '{$source}' to '{$style}'");
    }
    list($this->imageStyles[$style]["width"], $this->imageStyles[$style]["height"]) = getimagesize($destination);
    return $destination;
  }

  /**
   * Deletes the original image, all styles and the directory (if empty) from the persistent storage.
   *
   * @global \MovLib\Kernel $kernel
   * @return this
   */
  protected function deleteImage() {
    global $kernel;

    // Unserialize the styles if they are still serialized.
    if (!is_array($this->imageStyles)) {
      $this->imageStyles = unserialize($this->imageStyles);
    }

    // Add the original file to the styles array (DRY), this is why getImagePath() and getImageURL() check with empty()
    // against their parameter.
    $this->imageStyles[""] = null;
    foreach ($this->imageStyles as $styleName => $styleInfo) {
      try {
        $imagePath = $this->getImagePath($styleName);
        unlink($imagePath);

        // Silently fail if attempting to delete a non-empty directory.
        $imageDirectory = dirname($imagePath);
        sh::executeDetached("rmdir -p '{$imageDirectory}'");
      }
      catch (\ErrorException $e) {
        error_log($e);
      }
    }
    $kernel->delayMethodCall([ $this, "commit" ]);

    $this->imageExists = false;
    return $this;
  }

  /**
   * Get the absolute path to the image.
   *
   * <b>NOTE</b>
   * This method will always return the absolute path to the image, no matter if it exists or not.
   *
   * @global \MovLib\Kernel $kernel
   * @param mixed $style [optional]
   *   The style for which you want the path, if no style is given (default) the path to the original file is returned.
   * @return string
   *   The absolute path to the image.
   */
  protected function getImagePath($style = null) {
    global $kernel;
    if (empty($style)) {
      $path = "private/upload/{$this->imageDirectory}/{$this->imageName}.{$this->imageExtension}";
    }
    else {
      $path = "public/upload/{$this->imageDirectory}/{$this->imageName}.{$style}.{$this->imageExtension}";
    }
    return "{$kernel->documentRoot}/{$path}";
  }

  /**
   * Get the absolute (static) URL to the image.
   *
   * @global \MovLib\Kernel $kernel
   * @param mixed $style [optional]
   *   The style for which you want the URL, if no style is given (default) the URL to the original file is returned.
   * @return string
   *   The absolute (static) URL to the image.
   */
  protected function getImageURL($style = null) {
    global $kernel;
    if (empty($style)) {
      $url = "private/upload/{$this->imageDirectory}/{$this->imageName}.{$this->imageExtension}";
    }
    else {
      $url = "upload/{$this->imageDirectory}/{$this->imageName}.{$style}.{$this->imageExtension}?c={$this->imageChanged}";
    }
    return "//{$kernel->domainStatic}/{$url}";
  }

  /**
   * Upload the <var>$source</var> as this image, overriding the existing image.
   *
   * @param string $source
   *   Absolute path to the uploaded image.
   * @param string $extension
   *   The three letter image extension (e.g. <code>"jpg"</code>).
   * @param int $height
   *   The height of the uploaded image in pixels.
   * @param int $width
   *   The width of the uploaded image in pixels.
   * @return this
   * @throws \ErrorException
   * @throws \LogicException
   */
  public function uploadImage($source, $extension, $height, $width) {
    // We have to export the extension to class scope in order to move the original image.
    $this->imageExtension = $extension;

    // Strip meta data from uploaded image and move to persistent storage.
    $original = $this->getImagePath();
    sh::execute("convert '{$source}' -strip +repage '{$original}'");
    sh::executeDetached("rm {$source}");

    // Collect all data we want to know about the newly uploaded image.
    $this->imageChanged = $this->imageCreated = $_SERVER["REQUEST_TIME"];
    $this->imageHeight  = $height;
    $this->imageSize    = filesize($original);
    $this->imageWidth   = $width;

    // Let the concrete class create the various image styles.
    $this->generateImageStyles($original);
    if (!isset($this->imageStyles[self::IMAGE_STYLE_SPAN_01]) || !isset($this->imageStyles[self::IMAGE_STYLE_SPAN_02])) {
      throw new \LogicException("Every image instance has to generate the default styles!");
    }

    // Must be last because extending classes use it to determine if they have to update or insert.
    $this->imageExists = true;
    return $this;
  }

}
