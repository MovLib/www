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
namespace MovLib\Presenter\Gallery;

use \MovLib\Model\MovieImageModel;
use \MovLib\Model\MoviePosterModel;
use \MovLib\Presenter\Gallery\MovieGalleryPresenter;

/**
 * Present single movie image.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieGalleryImagePresenter extends MovieGalleryPresenter {
  use \MovLib\Presenter\Gallery\TraitGalleryImagePresenter;

  /**
   * Instantiate new movie image presenter.
   */
  public function __construct() {
    global $i18n;
    switch ($_SERVER["TAB"]) {
      case "poster":
        $this->imageTitle = $i18n->t("Poster");
        $this->imageModel = new MoviePosterModel($_SERVER["ID"], $_SERVER["IMAGE_ID"]);
        break;

      case "lobby-card":
        $this->imageTitle = $i18n->t("Lobby Card");
        $this->imageModel = new MovieImageModel($_SERVER["ID"], $_SERVER["TAB"], $_SERVER["IMAGE_ID"]);
        break;

      case "photo":
        $this->imageTitle = $i18n->t("Photo");
        $this->imageModel = new MovieImageModel($_SERVER["ID"], $_SERVER["TAB"], $_SERVER["IMAGE_ID"]);
        break;
    }
    $this->init();
  }

}
