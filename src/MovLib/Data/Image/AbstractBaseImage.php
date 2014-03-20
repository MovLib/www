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

use \MovLib\Data\StreamWrapper\StreamWrapperFactory;

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
const SPAN_01 = 60;
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

use \MovLib\Data\Shell;

/**
 * Minimum image implementation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractBaseImage {
  use \MovLib\Data\Image\TraitOptimizeImage;


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * 70x>
   *
   * Default image dimension for preview area of input file elements if the image exists.
   *
   * @var int
   */
  const STYLE_SPAN_01 = \MovLib\Data\Image\SPAN_01;

  /**
   * 140x>
   *
   * Default image dimension for image listings.
   *
   * @var int
   */
  const STYLE_SPAN_02 = \MovLib\Data\Image\SPAN_02;

  /**
   * Global minimum height for uploaded images.
   *
   * @var int
   */
  const IMAGE_MIN_HEIGHT = \MovLib\Data\Image\SPAN_02;

  /**
   * Global minimum width for uploaded images.
   *
   * @var int
   */
  const IMAGE_MIN_WIDTH = \MovLib\Data\Image\SPAN_02;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The image's changed timestamp.
   *
   * @var int
   */
  public $changed;

  /**
   * The image's directory within the uploads directory.
   *
   * @var string
   */
  protected $directory = "";

  /**
   * Flag indicating whether this image exists or not.
   *
   * @var boolean
   */
  public $imageExists = false;

  /**
   * The image's localized route.
   *
   * @var string
   */
  public $imageRoute;

  /**
   * The image's extension.
   *
   * @var string
   */
  public $extension;

  /**
   * The image's filename without extension (and the leading dot of the extension).
   *
   * @var string
   */
  protected $filename;

  /**
   * Raw URL encoded version of filename.
   *
   * @var string
   */
  private $filenameEncoded;

  /**
   * The original image's filesize.
   *
   * @var integer
   */
  public $filesize;

  /**
   * The image's height.
   *
   * @internal Must be public for validation.
   * @var int
   */
  public $height;

  /**
   * The image's placeholder path within the asset image directory.
   *
   * @var string
   */
  protected $placeholder = "logo/vector";

  /**
   * The image's placeholder extension.
   *
   * @var string
   */
  protected $placeholderExtension = "svg";

  /**
   * All available styles information, mapped to database.
   *
   * @var array
   */
  public $styles;

  /**
   * All available styles inofrmation, including dynamic data (translations).
   *
   * @var array
   */
  protected $stylesCache;

  /**
   * The image's width.
   *
   * @internal Must be public for validation.
   * @var int
   */
  public $width;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the <var>$style</var> for this image.
   *
   * @param mixed $style
   *   The desired style, use the objects <var>STYLE_*</var> class constants. Defaults to <var>STYLE_SPAN_02</var>.
   * @return \MovLib\Data\Image\Style
   *   The image's desired style object.
   */
  abstract public function getStyle($style = self::STYLE_SPAN_02);

  /**
   * Upload the <var>$source</var>, overriding any existing image.
   *
   * @param string $source
   *   Absolute path to the uploaded image.
   * @param string $extension
   *   The three letter image extension (e.g. <code>"jpg"</code>).
   * @param integer $height
   *   The height of the uploaded image.
   * @param integer $width
   *   The width of the uploaded image.
   * @return this
   * @throws \RuntimeException
   */
  abstract public function upload($source, $extension, $height, $width);


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
   * @return this
   * @throws \RuntimeException
   */
  protected function convert($source, $style, $width = null, $height = null, $crop = false) {
    // Build the ImageMagick resize argument based on the passed parameters.
    if (!$width) {
      $width = $style;
    }
    if ($crop === true) {
      $resizeArg = "'{$width}x{$height}>^' -gravity 'Center' -crop '{$width}x{$height}+0+0' +repage";
    }
    else {
      $resizeArg = "'{$width}x{$height}>'";
    }

    // Nothing to do if the style already exists and the arguments haven't changed.
    $destination = $this->getPath($style);
    if (is_file($destination) && isset($this->styles[$style]) && $this->styles[$style]["resizeArg"] == $resizeArg) {
      return $this;
    }

    // Generate the desired image style with ImageMagick. We directly call the binary instead of using some kind of
    // abstraction layer, we don't need any fancy object just to resize an image.
    try {
      $source      = escapeshellarg($source);
      $destination = escapeshellarg($destination);
      Shell::execute("convert {$source} -filter Lanczos -resize {$resizeArg} -quality 75 {$destination}");
    }
    catch (\RuntimeException $e) {
      // @todo Log error, convert command isn't available!
      throw $e;
    }

    // Store width and height of the generated image in the database. This allows us to set the width and height
    // attributes without any IO.
    list($this->styles[$style]["width"], $this->styles[$style]["height"]) = getimagesize($destination);

    // We need to store the arguments as well, for efficient regeneration of styles; otherwise we don't know if the
    // style has changed.
    $this->styles[$style]["resizeArg"] = $resizeArg;

    // We return the absolute path to the just generated image for chaining resize actions. This ensures best quality
    // if we have to resize from a very huge image down to a very small one.
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
   * @throws \LogicException
   */
  protected function getPath($style = null) {
    global $kernel;

    // We always have to generate the absolute path to the image within our persistent storage, doesn't matter if it
    // exists or not, as it may be requested to move or convert an image that was just uploaded. Of course we need the
    // directory, filename and the extension to do so. The concrete image is responsible for this.
    if (empty($this->directory) || empty($this->filename) || empty($this->extension)) {
      throw new \LogicException("Directory, filename and/or extension cannot be empty.");
    }

    // If no style was given the path to the original is desired.
    if (empty($style)) {
      return "{$kernel->documentRoot}/private/upload/{$this->directory}/{$this->filename}.{$this->extension}";
    }

    // Otherwise the path to the given style.
    return "{$kernel->documentRoot}/public/upload/{$this->directory}/{$this->filename}.{$style}.{$this->extension}";
  }

  /**
   * Get the absolute URL to the image.
   *
   * @global \MovLib\Kernel $kernel
   * @staticvar array $placeholders
   *   Used to cache placeholder URLs.
   * @param mixed $style [optional]
   *   The style for which you want the URL, if no style is given (default) the URL to the original file is returned.
   * @return string
   *   The absolute (static) URL to the image.
   * @throws \LogicException
   */
  public function getURL($style = null) {
    static $placeholders = [];
    // If the image doesn't exist but a URL is claimed return the placeholder.
    if ($this->imageExists === false) {
      if (!isset($placeholders[$this->placeholder])) {
        $placeholders[$this->placeholder] = StreamWrapperFactory::create(
          "asset://img/{$this->placeholder}.{$this->placeholderExtension}"
        )->getExternalURL();
      }
      return $placeholders[$this->placeholder];
    }

    // We need a directory, filename and extension if the image exists.
    if (empty($this->directory) || empty($this->filename) || empty($this->extension)) {
      throw new \LogicException("Directory, filename and/or extension cannot be empty.");
    }

    // If no style was given the URL to the original is desired. The originals are always delivered from the current
    // subdomain. While this busts the user's cache for this file if she or he changed the language it ensure that the
    // correct interface is shown to users who aren't authenticated and therefor aren't allowed to access the originals.
    if (empty($style)) {
      return "/upload/private/{$this->directory}/{$this->filenameEncoded}.{$this->extension}?c={$this->changed}";
    }

    // Otherwise the URL to the given style.
    if (!$this->filenameEncoded) {
      // The file's name might still contain characters that aren't save to use in HTML or in requests.
      $this->filenameEncoded = rawurlencode($this->filename);
    }
    global $kernel;
    return "//{$kernel->domainStatic}/upload/{$this->directory}/{$this->filenameEncoded}.{$style}.{$this->extension}?{$this->changed}";
  }

}
