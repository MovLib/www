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
namespace MovLib\Presenter;

use \MovLib\Exception\ImageException;
use \MovLib\Model\MovieImageModel;
use \MovLib\Model\MoviePosterModel;
use \MovLib\View\HTML\ImageView;

/**
 * Generic presenter for images (e.g. movie posters).
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class ImageDetailsPresenter extends GalleryPresenter {

  /**
   * The images to display in the stream.
   * Numeric array containing the image models.
   *
   * @var array
   */
  public $streamImages;

  /**
   * Initialize the ImagePresenter.
   *
   * @global \MovLib\Model\I18nModel $i18n
   */
  public function __construct() {
    global $i18n;
    try {
      $this->{__FUNCTION__ . $this->getAction()}();
      $this->title = $i18n->t("{0} details", [ ucfirst($_SERVER["TAB"]) ]);
      $this->view = $this->view ?: new ImageView($this);
      $this->setPresentation();
    } catch (ImageException $e) {
      $this->setPresentation("Error\\NotFound");
    }
  }

  /**
   * {@inheritDoc}
   * @global \MovLib\Model\I18nModel $i18n
   */
  private function __constructMovie() {
    global $i18n;
    $this->initMovie();
    if ($this->model->deleted === true) {
      return $this->setPresentation("Error\\Gone");
    }
    switch ($_SERVER["TAB"]) {
      case "poster":
        $this->streamImages = $this->model->getPosters();
        $this->model = new MoviePosterModel($_SERVER["ID"], $_SERVER["IMAGE_ID"]);
        $this->galleryTitle = $i18n->t("{0} Posters", [ $this->entityTitle ]);
        break;
      case "lobby-card":
        $this->streamImages = $this->model->getLobbyCards();
        $this->model = new MovieImageModel($_SERVER["ID"], $_SERVER["TAB"], $_SERVER["IMAGE_ID"]);
        $this->galleryTitle = $i18n->t("{0} Lobby Cards", [ $this->entityTitle ]);
        break;
      case "photo":
        $this->streamImages = $this->model->getPhotos();
        $this->model = new MovieImageModel($_SERVER["ID"], $_SERVER["TAB"], $_SERVER["IMAGE_ID"]);
        $this->galleryTitle = $i18n->t("{0} Photos", [ $this->entityTitle ]);
        break;
    }
    return $this;
  }

  /**
   * {@inheritDoc}
   * @global \MovLib\Model\I18nModel $i18n
   */
  public function getBreadcrumb() {
    global $i18n;
    $breadcrumb = parent::getBreadcrumb();
    $breadcrumb[] = [ $i18n->r("/{$_SERVER["ACTION"]}/{0}/{$this->tabPluralized}", [ $_SERVER["ID"] ]), $this->galleryTitle ];
    return $breadcrumb;
  }

  /**
   * {@inheritDoc}
   * @global \MovLib\Model\I18nModel $i18n
   */
  public function getSecondaryNavigation() {
    global $i18n;
    $navigation = parent::getSecondaryNavigation();
    array_unshift($navigation, [
      $i18n->r("/{$_SERVER["ACTION"]}/{0}/{$this->tabPluralized}", [ $_SERVER["ID"] ]),
      "<i class='icon icon--arrow-left'></i>{$i18n->t("Back to {0}", [ $i18n->t("{$this->tabPluralized}") ])}"
    ]);
    return $navigation;
  }

}