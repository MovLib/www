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
const S01 = "60";
const S02 = "140";
const S03 = "220";
const S04 = "300";
const S05 = "380";
const S06 = "460";
const S07 = "540";
const S08 = "620";
const S09 = "700";
const S10 = "780";
const S11 = "860";
const S12 = "940";
// @codeCoverageIgnoreEnd

use \MovLib\Data\Shell;
use \MovLib\Data\Image\ImageStyle;

/**
 * Defines the base class for read only images.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractReadOnlyImage extends \MovLib\Data\AbstractEntity {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * 60x>
   *
   * Default image dimension for preview area of input file elements if the image exists.
   *
   * @var integer
   */
  const S01 = \MovLib\Data\Image\S01;

  /**
   * 140x>
   *
   * Default image dimension for listings.
   *
   * @var integer
   */
  const S02 = \MovLib\Data\Image\S02;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The image's alternative text in the current locale.
   *
   * @var string
   */
  public $imageAlternativeText;

  /**
   * The image's changed UNIX timestamp.
   *
   * @var string
   */
  public $imageChanged;

  /**
   * The image's directory URI.
   *
   * @var string
   */
  public $imageDirectoryURI;

  /**
   * The image's filename.
   *
   * @var string
   */
  public $imageFilename;

  /**
   * The image's filesize in Bytes of the original.
   *
   * @var integer
   */
  public $imageFilesize;

  /**
   * The image's height in Pixel of the original.
   *
   * @var integer
   */
  public $imageHeight;

  /**
   * The image's placeholder URI.
   *
   * <b>NOTE</b><br>
   * The placeholder URI must point to an asset and must be of type SVG.
   *
   * @var string
   */
  protected $imagePlaceholder = "asset://img/logo/vector.svg";

  /**
   * The image's styles.
   *
   * @var array
   */
  protected $imageStyles;

  /**
   * The image's styles cache.
   *
   * @var array
   */
  protected $imageStylesCache;

  /**
   * The image's width in Pixel of the original.
   *
   * @var integer
   */
  public $imageWidth;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Conver source image to given width, height, optionally crop and use given style for storage.
   *
   * Only images that are big enough will be downscaled, if the source image is too small no conversion is performed.
   * Instead it's simply copied to the target location (specified by style). If you pass width and height the image will
   * keep its aspect ratio.
   *
   * @param string $source
   *   Canonical absolute path or URI to the source image for conversion.
   * @param integer $style
   *   One of the style constants supported by the concrete image.
   * @param integer $width [optional]
   *   The desired width of the converted image, defaults to <var>$style</var>.
   * @param integer $height [optional]
   *   The desired height of the converted image, defaults to <var>NULL</var>.
   * @param boolean $crop [optional]
   *   Whether to crop the image or not, defaults to <code>FALSE</code>.
   * @return this
   * @throws \MovLib\Exception\ShellException
   *   If the ImageMagick call fails.
   */
  final protected function convert($source, $style, $width = null, $height = null, $crop = false) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($source), "\$source cannot be empyt!");
    assert($this->fs->realpath($source) !== false, "\$source must be either a canonical absolute local path or a URI of a file that actually exists.");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Build the ImageMagick resize argument based on the passed parameters.
    $width  || ($width = $style);
    if ($crop) {
      $resizeArgument = "'{$width}x{$height}>^' -gravity 'Center' -crop '{$width}x{$height}+0+0' +repage";
    }
    else {
      $resizeArgument = "'{$width}x{$height}>'";
    }

    // Nothing to do if the image style is stored in the database and the resize arguments match.
    if (isset($this->imageStyles[$style]) && $this->imageStyles[$style]->resizeArguments == $resizeArgument) {
      $this->log->warning("Convert called for existing image style without change of resize arguments.");
      return $this;
    }

    // It's a bit more complicated for possible cache entries.
    if (isset($this->imageStylesCache[$style])) {
      // Simply remove if the cached entry is a placeholder.
      if ($this->imageStylesCache[$style]->placeholder) {
        // @devStart
        // @codeCoverageIgnoreStart
        $this->log->debug("Unsetting cached image style.");
        // @codeCoverageIgnoreEnd
        // @devEnd
        unset($this->imageStylesCache[$style]);
      }
      // Simply return if the cached entry equals the style we are about to generate.
      elseif ($this->imageStylesCache[$style]->resizeArguments == $resizeArgument) {
        $this->log->warning("Convert called for cached image style without change of resize arguments.");
        return $this;
      }
    }

    $imageStyle = $this->getImageStyle($style);
    (new Shell())->execute("convert '{$this->fs->realpath($source)}' -filter Lanczos -resize {$resizeArgument} -quality 80 '{$this->fs->realpath($imageStyle->uri)}'");

    list($imageStyle->width, $imageStyle->height) = getimagesize($imageStyle->uri);
    $imageStyle->resizeArguments = $resizeArgument;

    return $this;
  }

  final public function getImageStyle($style) {
    // Directly return if we have this image style already cached.
    if (isset($this->imageStylesCache[$style])) {
      return $this->imageStylesCache[$style];
    }

    // If the image doesn't exist at all use the placeholder.
    if (empty($this->imageChanged)) {
      $imageStyle = new ImageStyle($this->fs->getExternalURL($this->imagePlaceholder), $style);
    }
    // The image exists but we have no cached image style.
    else {
      // We don't have this style at all, generate new style.
      if (empty($this->imageStyles[$style])) {
        $imageStyle = new ImageStyle($this->getImageStyleURL($style), $style, $this->getImageStyleURI($style));
      }
      // We have the image style from the database.
      else {
        $imageStyle = $this->imageStyles[$style];
      }
      $imageStyle->placeholder = false;
    }

    // Fill in the dyanmic properties, cache, and return.
    $imageStyle->alt   = $this->imageAlternativeText;
    $imageStyle->route = $this->route->url;
    return ($this->imageStylesCache[$style] = $imageStyle);
  }

  /**
   * Get the image style's URI.
   *
   * @param mixed $style
   *   The image style's name.
   * @return string
   *   The image style's URI.
   */
  final protected function getImageStyleURI($style) {
    return "{$this->imageDirectoryURI}/{$this->imageFilename}.{$style}.{$this->imageExtension}";
  }

  /**
   * Get the image style's URL.
   *
   * @param mixed $style
   *   The image style's name.
   * @return string
   *   The image style's URL.
   */
  final protected function getImageStyleURL($style) {
    return $this->fs->getExternalURL($this->getImageStyleURI($style), $this->imageChanged);
  }

}
