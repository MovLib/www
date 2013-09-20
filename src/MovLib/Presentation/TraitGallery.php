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
namespace MovLib\Presentation;

use \MovLib\Data\MovieImage;
use \MovLib\Presentation\Partial\Lists;

/**
 * Base trait for all gallery presentations.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitGallery {

  /**
   * Numeric array containing <code>\MovLib\Data\AbstractImage</code> objects to display.
   *
   * @var array
   */
  protected $images;

  /**
   * Numeric array containing the translated route portions for the images.
   *
   * Format:
   *   <ul>
   *     <li><code>0 => Entity portion (e.g. "movie").</code></li>
   *     <li><code>1 => Entity id .</code></li>
   *     <li><code>2 => Image type portion (e.g. "poster").</code></li>
   *     <li><code>3 => Image id. Please note, that this will be set dynamically in this presenation.</code></li>
   *   </ul>
   *
   * @var array
   */
  protected $imagesRoute;

  /**
   * The already translated text to display if there are no images present.
   *
   * @var string
   */
  protected $noImagesText;

  /**
   * The already translated route for the upload.
   *
   * @var string
   */
  protected $uploadRoute;

  /**
   * The already tranlated text, encouraging the user to upload images.
   *
   * @var string
   */
  protected $uploadText;

  /**
   * @inheritdoc
   */
  protected function init($title) {
    $this->stylesheets[] = "modules/gallery.css";
    return parent::init($title);
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    if ($this->model->deleted === true) {
      return $this->getGoneContent();
    }
    $c = count($this->images);
    for ($i = 0; $i < $c; ++$i) {
      $this->imagesRoute[3] = $this->images[$i]->sectionId;
      $this->images[$i] = [
        $this->getImage(
          $this->images[$i],
          MovieImage::IMAGESTYLE_GALLERY,
          [ "alt" => "{$this->title} {$this->images[$i]->imageAlt}" ],
          "/" . implode("/", $this->imagesRoute)
        ),
        [ "class" => "span span--2"]
      ];
    }
    return (new Lists(
      $this->images,
      "<p>{$this->noImagesText}</p>" .
      "<p>{$this->uploadText}</p>",
      [ "id" => "gallery-list"])
    )->toHtmlList("ol");
  }

}
