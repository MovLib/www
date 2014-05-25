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

use \MovLib\Data\Image\ImageFullsizeEffect;
use \MovLib\Data\Image\ImageResizeEffect;
use \MovLib\Data\Image\ImageStylePlaceholder;

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
const S01 = 60;
const S02 = 140;
const S03 = 220;
const S04 = 300;
const S05 = 380;
const S06 = 460;
const S07 = 540;
const S08 = 620;
const S09 = 700;
const S10 = 780;
const S11 = 860;
const S12 = 940;
// @codeCoverageIgnoreEnd

/**
 * Defines the base class for read only images.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractReadOnlyImageEntity extends \MovLib\Data\AbstractEntity {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The image's alternative text in the current locale.
   *
   * @var string
   */
  public $imageAlternativeText;

  /**
   * The image's cache buster MD5 hash.
   *
   * @var string
   */
  public $imageCacheBuster;

  /**
   * Whether this image exists or not.
   *
   * @var boolean
   */
  public $imageExists = false;

  /**
   * The image's directory URI.
   *
   * @var string
   */
  public $imageDirectory;

  /**
   * The image's extension without dot.
   *
   * @var string
   */
  public $imageExtension;

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
  public $imageStyles;

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


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Save the image styles to persistent storage.
   *
   * @return this
   * @throws \mysqli_sql_exception
   */
  abstract protected function imageSaveStyles();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init() {
    // @devStart
    // @codeCoverageIgnoreStart
    foreach ([ "AlternativeText", "Directory" ] as $property) {
      $property = "image{$property}";
      assert(!empty($this->$property), "You must set the \${$property} property in your class " . static::class . ".");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->imageExists = (boolean) $this->imageCacheBuster;
    $this->imageStyles && ($this->imageStyles = unserialize($this->imageStyles));
    return parent::init();
  }

  /**
   * Generate image styles.
   *
   * @return this
   * @throws \RuntimeException
   *   If applying of the image effect fails.
   */
  final protected function imageGenerateStyles() {
    /* @var $imageEffect \MovLib\Data\Image\AbstractImageEffect */
    foreach ($this->imageGetEffects() as $style => $imageEffect) {
      $imageEffect->apply($this, $this->fs, $style, $this->imageGetURI(), $this->imageGetStyleURI($style));
    }
    return $this;
  }

  /**
   * Get the available image effects.
   *
   * @return array
   *   The available image effects.
   */
  protected function imageGetEffects() {
    return [
      ""   => new ImageFullsizeEffect(),
      "s1" => new ImageResizeEffect(\MovLib\Data\Image\S01),
      "s2" => new ImageResizeEffect(\MovLib\Data\Image\S02),
    ];
  }

  /**
   * Get the image's style.
   *
   * @param string $style [optional]
   *   The desired image style, defaults to <code>"s2"</code>.
   * @return \MovLib\Data\Image\ImageStyle
   *   The desired image's style.
   */
  final public function imageGetStyle($style = "s2") {
    // Nothing to do if we have this style already cached.
    if (isset($this->imageStylesCache[$style])) {
      return $this->imageStylesCache[$style];
    }

    // Check if the image exists.
    if ($this->imageCacheBuster) {
      if (empty($this->imageStyles[$style]) || !is_file($this->imageGetStyleURI($style))) {
        $this->log->warning("Generating all image styles because file is missing from storage!");
        $this->imageGenerateStyles()->imageSaveStyles();
      }
      $this->imageStylesCache[$style] = $this->imageStyles[$style];
      $this->imageStylesCache[$style]->url = $this->fs->getExternalURL($this->imageGetStyleURI($style), $this->imageCacheBuster);
    }
    else {
      $this->imageStylesCache[$style] = new ImageStylePlaceholder(
        $this->imageGetEffects()[$style]->width,
        $this->fs->getExternalURL($this->imagePlaceholder)
      );
    }

    // Export dynamic properties to cached image style version and return.
    $this->imageStylesCache[$style]->alt   = $this->imageAlternativeText;
    $this->imageStylesCache[$style]->route = $this->route;
    return $this->imageStylesCache[$style];
  }

  /**
   * Get the URI of the image or image style.
   *
   * @param string $style [optional]
   *   The style to get the URI for, defaults to <code>NULL</code> and returns the URI of the fullsize image.
   * @return string
   *   The URI of the image or image style.
   */
  final protected function imageGetStyleURI($style = null) {
    if (!empty($style)) {
      $style = ".{$style}";
    }
    return "{$this->imageDirectory}/{$this->imageFilename}{$style}.{$this->imageExtension}";
  }

  /**
   * Get the URI of the original image.
   *
   * @return string
   *   The URI of the original image.
   */
  final protected function imageGetURI() {
    return str_replace("upload://", "dr://var/lib/uploads/", "{$this->imageDirectory}/{$this->imageFilename}.{$this->imageExtension}");
  }

  /**
   * Set file system instance.
   *
   * The entity needs a file system instance to interact with the local file system, but only if it's desired to display
   * the image of an entity. Therefore it's an optional dependency and only injected if needed.
   *
   * @param \MovLib\Core\FileSystem $fileSystem
   *   The file system instance to set.
   * @return this
   */
  final public function setFileSystem(\MovLib\Core\FileSystem $fileSystem) {
    $this->fs = $fileSystem;
    return $this;
  }

}
