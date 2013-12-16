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
namespace MovLib\Presentation\ImageDetails;

use \MovLib\Data\AbstractImage;
use \MovLib\Data\Image\Movie as MovieImage;
use \MovLib\Data\MovieImages;
use \MovLib\Exception\Client\ErrorNotFoundException;
use \MovLib\View\ImageStyle\ResizeCropCenterImageStyle;

/**
 * Presentation for lobby card details.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieLobbyCardDetails extends \MovLib\Presentation\Movie\AbstractMoviePage {
  use \MovLib\Presentation\ImageDetails\TraitImageDetails;
  use \MovLib\Presentation\Gallery\TraitMovieGallery;

  /**
   * Instantiate new poster details presentation.
   */
  public function __construct() {
    global $i18n;
    $this->initMovie();
    $this->image = new MovieImage($this->model->id, MovieImage::IMAGETYPE_LOBBYCARD, $_SERVER["IMAGE_ID"]);
    if ($this->image->exists === false) {
      throw new ErrorNotFoundException("");
    }
    $this->entityTitle  = $this->title;
    $this->imagesRoute  = $i18n->r("/movie/{0}/lobby-card", [ $this->model->id ]);
    $this->uploadRoute  = $i18n->rp("/movie/{0}/lobby-cards/upload", [ $this->model->id ]);
    $this->editRoute    = $i18n->r("/movie/{0}/lobby-card/{1}/edit", [ $this->model->id, $this->image->imageId ]);
    $this->lastImageId = $this->image->getTotalCount();
    $this->namePattern = "Lobby Card {0} of {1} from “{2}”";
    $this->initPage($i18n->t($this->namePattern, [ $this->image->imageId, $this->lastImageId, $this->title ]));
    $this->streamImages = (new MovieImages(
      $this->model->id,
      MovieImage::IMAGETYPE_LOBBYCARD,
      new ResizeCropCenterImageStyle(AbstractBaseImage::IMAGESTYLE_DETAILS_STREAM),
      $this->imagesRoute,
      $this->entityTitle
    ))->getOrderedByCreatedAsc($this->image->imageId, true);
  }

  /**
   * @inheritdoc
   */
  protected function getStreamImages($imageId, $paginationSize) {
    return
      (new MovieImages(
        $this->model->id,
        MovieImage::IMAGETYPE_LOBBYCARD,
        new ResizeCropCenterImageStyle(AbstractBaseImage::IMAGESTYLE_DETAILS_STREAM),
        $this->imagesRoute,
        $this->entityTitle
      ))->getOrderedByCreatedAsc($imageId, false, $paginationSize);
  }

}
