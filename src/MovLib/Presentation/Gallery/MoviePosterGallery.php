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
namespace MovLib\Presentation\Gallery;

use \MovLib\Data\Image\Movie as MovieImage;
use \MovLib\Data\MovieImages;
use \MovLib\View\ImageStyle\ResizeImageStyle;

/**
 * The movie poster gallery.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MoviePosterGallery extends \MovLib\Presentation\Movie\AbstractMoviePage {
  use \MovLib\Presentation\Gallery\TraitGallery;
  use \MovLib\Presentation\Gallery\TraitMovieGallery;

  /**
   * Instantiate new movie poster gallery presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @throws \MovLib\Exception\Client\ErrorNotFoundException
   */
  public function __construct() {
    global $i18n;
    $this->initMovie();
    $this->entityTitle = $this->title;
    $this->title = "{$i18n->t("Posters of")} “{$this->title}”";
    $this->init($this->title);
    $startId = empty($_GET["last_id"]) ? 0 : $_GET["last_id"];
    $this->images = (new MovieImages(
      $this->model->id,
      MovieImage::IMAGETYPE_POSTER,
      new ResizeImageStyle(MovieImage::IMAGESTYLE_GALLERY),
      $i18n->r("/movie/{0}/poster", [ $this->model->id ]),
      $this->entityTitle)
    )->getOrderedByCreatedAsc($startId);
    $this->uploadRoute = $i18n->r("/movie/{0}/posters/upload", [ $this->model->id ]);
    $this->noImagesText = $i18n->t("No Posters for “{0}”.", [ $this->entityTitle ]);
    $this->uploadText = $i18n->t("Want to upload your Posters? {0}", [
        $this->a($this->uploadRoute, "Click here to do so.")
    ]);
  }

}
