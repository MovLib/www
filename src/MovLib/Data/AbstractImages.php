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

/**
 * Contains properties and methods for models containing multiple images.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractImages extends \MovLib\Data\Database {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Name of the directory within the uploads directory of the server.
   *
   * @var string
   */
  protected $imagesDirectory;

  /**
   * The style for displaying the images.
   *
   * @var \MovLib\View\ImageStyle\AbstractImageStyle
   */
  protected $style;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize images with paths and URIs.
   *
   * @param array $images
   * @param \MovLib\View\ImageStyle\AbstractImageStyle $style
   *   The style to apply to the images.
   * @return this
   */
  protected function initImagePaths($images, $style) {
    $path = "uploads/{$this->imagesDirectory}/{$style->dimensions}/";
    $imagePath = "{$_SERVER["DOCUMENT_ROOT"]}/{$path}";
    $imageUri = "{$GLOBALS["movlib"]["static_domain"]}{$path}";
    $this->style = $style;
    $this->style->path = $imagePath;
    $c = count($images);
    for ($i = 0; $i < $c; ++$i) {
      $images[$i]["filename"] = "{$images[$i]["filename"]}.{$images[$i]["hash"]}.{$images[$i]["ext"]}";
      $images[$i]["path"] = "{$imagePath}{$images[$i]["filename"]}";
      $images[$i]["src"] = "{$imageUri}{$images[$i]["filename"]}";
    }
    return $images;
  }

}
