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
namespace MovLib\Presentation\Movie;

use \MovLib\Data\MovieImage;
use \MovLib\Exception\Client\NotFoundException;

/**
 * Description of PosterDetails
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class PosterDetails extends \MovLib\Presentation\Movie\AbstractMoviePage {
  use \MovLib\Presentation\TraitImageDetails;
  use \MovLib\Presentation\Movie\TraitMovieGallery;

  /**
   *
   */
  public function __construct() {
    global $i18n;
    $this->initMovie();
    $this->entityTitle = $this->title;
    $this->uploadRoute = $i18n->r("/movie/{0}/posters/upload", [ $this->model->id ]);
    $this->image = new MovieImage($this->model->id, MovieImage::IMAGETYPE_POSTER, $_SERVER["IMAGE_ID"]);
    if ($this->image->imageExists === false) {
      throw new NotFoundException("");
    }
    $this->editRoute = $i18n->r("/movie/{0}/poster/{1}/edit", [ $this->model->id, $this->image->sectionId ]);
    list($position, $totalCount) = $this->image->getPositionAndTotalCount();
    $this->init($i18n->t("Poster {0} of {1} from “{2}”", [ $position, $totalCount, $this->title ]));
    $this->streamImages = $this->model->getPosters();
  }

}
