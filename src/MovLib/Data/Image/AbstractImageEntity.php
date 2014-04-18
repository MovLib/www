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
 * Defines the base class for image entities.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractImageEntity extends \MovLib\Data\Image\AbstractReadOnlyImageEntity {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Global minimum height for uploaded images.
   *
   * @var integer
   */
  const IMAGE_MIN_HEIGHT = \MovLib\Data\Image\S02;

  /**
   * Global minimum width for uploaded images.
   *
   * @var integer
   */
  const IMAGE_MIN_WIDTH = \MovLib\Data\Image\S02;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The column name of the entity where the unique identifier is stored (without <code>"_id"</code> suffix).
   *
   * @var string
   */
  protected $entityKey;

  /**
   * Whether this image was just newly uploaded.
   *
   * <b>NOTE</b><br>
   * This flag only tells if the current instance was newly uploaded, it doesn't say anything about previous versions
   * of this image. If this flag is <code>TRUE</code> and {@see AbstractImageEntity::$imageExists} is <code>FALSE</code>
   * then it's a totally new upload.
   *
   * @var boolean
   */
  protected $imageUploaded = false;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  // @devStart
  // @codeCoverageIgnoreStart
  protected function init() {
    assert(!empty($this->entityKey), "You have to set the \$entityKey property in your concrete image class.");
    return parent::init();
  }
  // @codeCoverageIgnoreEnd
  // @devEnd

  /**
   * Delete the original image and all of its styles.
   *
   * @return this
   */
  final protected function imageDelete() {
    unlink($this->imageGetURI());
    $this->imageDeleteStyles();
    $this->imageCacheBuster = $this->imageExtension = $this->imageFilename = $this->imageFilesize = $this->imageHeight = $this->imageWidth = null;
    return $this;
  }

  /**
   * Delete image style.
   *
   * @return this
   */
  final protected function imageDeleteStyle($style) {
    if (isset($this->imageStyles[$style])) {
      unset($this->imageStyles[$style]);
    }
    if (isset($this->imageStylesCache[$style])) {
      unset($this->imageStylesCache[$style]);
    }
    unlink($this->imageGetStyleURI($style));
    return $this;
  }

  /**
   * Delete image styles.
   *
   * @return this
   */
  final protected function imageDeleteStyles() {
    foreach ($this->imageGetEffects() as $style => $imageEffect) {
      unlink($this->imageGetStyleURI($style));
    }
    $this->imageStylesCache = $this->imageStyles = null;
    return $this;
  }

  /**
   * Rename the generated image styles.
   *
   * @param string $oldFilename
   *   The current filename of the images.
   * @param string $newFilename
   *   The new filename of the images.
   * @return this
   */
  final protected function imageRename($oldFilename, $newFilename) {
    if ($oldFilename != $newFilename) {
      // Rename the original.
      $uri = $this->imageGetURI();
      rename($uri, str_replace($oldFilename, $newFilename, $uri));

      // Rename all generated styles.
      foreach ($this->imageGetEffects() as $style => $imageEffect) {
        $uri = $this->imageGetStyleURI($style);
        rename($uri, str_replace($oldFilename, $newFilename, $uri));
      }
    }
    $this->imageFilename = $newFilename;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function imageSaveStyles() {
    $styles = serialize($this->imageStyles);
    $stmt   = $this->getMySQLi()->prepare("UPDATE `{$this->tableName}` SET `styles` = ? WHERE `id` = ? AND `{$this->entityKey}_id` = ?");
    $stmt->bind_param("sdd", $styles, $this->id, $this->entityId);
    $stmt->execute();
    $stmt->close();
    return $this;
  }

  /**
   * Save the uploaded file as original and update/generate all image styles.
   *
   * @param \MovLib\Data\UploadedFile $uploadedFile
   *   The file that was uploaded.
   * @param integer $width
   *   The uploaded image's width in Pixel.
   * @param integer $height
   *   The uploaded image's height in Pixel.
   * @param string $extension
   *   The uploaded image's extension.
   * @return this
   */
  final public function imageSaveTemporary(\MovLib\Data\UploadedFile $uploadedFile, $width, $height, $extension) {
    $this->imageCacheBuster = md5_file($uploadedFile->path);
    $this->imageExtension   = $extension;
    $this->imageFilename    = basename($uploadedFile->path);
    $this->imageHeight      = $height;
    $this->imageWidth       = $width;

    // Move the original file from the temporary upload directory to the persistent storage within the correct directory
    // without altering the image in any way. We want to keep the original for future changes.
    $uri      = $this->imageGetURI();
    $realpath = $this->fs->realpath($uri);
    rename($uploadedFile->path, $realpath);

    // Now we can generate all styles based on the original.
    $this->imageGenerateStyles();

    // We're done at this point, the presenter that contains the form has to decide if this image should be stored to
    // the persistent storage or not.
    $this->imageUploaded = true;

    return $this;
  }

}
