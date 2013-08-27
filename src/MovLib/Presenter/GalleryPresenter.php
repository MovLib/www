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

use \MovLib\Exception\MovieException;
use \MovLib\Model\MovieModel;
use \MovLib\View\HTML\GalleryView;

/**
 * Generic presenter for image galleries.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class GalleryPresenter extends AbstractPresenter {

  // ------------------------------------------------------------------------------------------------------------------- Properties

  /**
   * The images to display for the view.
   * Numeric array containing subclass instances of \MovLib\Model\AbstractImageModel.
   *
   * @var array
   */
  public $images;

  /**
   * The image type to be used in links to the image detail page.
   * @var string
   */
  public $imageType;

  /**
   * The model for this gallery.
   *
   * @var \MovLib\Model\AbstractModel
   */
  public $model;

  /**
   * The secondary navigation points for the HTML views.
   *
   * @var array
   */
  public $secondaryNavigationPoints;

  /**
   * The title of the page to be rendered.
   *
   * @var string
   */
  public $title;

  /**
   * The localized title of the gallery to upload to.
   * @var type
   */
  public $galleryTitle;

  // ------------------------------------------------------------------------------------------------------------------- Constructors and overriden methods

  /**
   * Initialize a new gallery presenter.
   */
  public function __construct() {
    $this->{__FUNCTION__ . $this->getAction()}();
    $this->view = new GalleryView($this);
    $this->setPresentation();
  }

  /**
   * Render the movie gallery.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance for translations.
   * @return this
   */
  private function __constructMovie() {
    global $i18n;
    try {
      $this->initMovie();
      switch ($_SERVER["TAB"]) {
        case "poster":
          $this->galleryTitle = "Posters";
          $this->images = $this->model->getPosters();
          break;
        case "lobby-card":
          $this->galleryTitle = "Lobby Cards";
          $this->images = $this->model->getLobbyCards();
          break;
        case "photo":
          $this->galleryTitle = "Photos";
          $this->images = $this->model->getPhotos();
          break;
      }
    } catch (MovieException $e) {
      $this->setPresentation("Error\\NotFound");
    }
    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public function getBreadcrumb() {
    global $i18n;
    $breadcrumb = [];
    switch ($this->getAction()) {
      case "movie":
        $breadcrumb[] = [
          $i18n->r("/movies"),
          $i18n->t("Movies"),
          [ "title" => $i18n->t("Have a look at the latest movie entries at MovLib.") ]
        ];
        $breadcrumb[] = [ $i18n->r("/movie/{0}", [ $this->model->id ]), $this->title ];
        break;
      case "person":
        $breadcrumb[] = [
          $i18n->r("/persons"),
          $i18n->t("Persons"),
          [ "title" => $i18n->t("Have a look at the latest person entries at MovLib.") ]
        ];
        $breadcrumb[] = [ $i18n->r("/person/{0}", [ $this->model->id ]), $this->title ];
        break;
      case "series":
        $breadcrumb[] = [
          $i18n->r("/series"),
          $i18n->t("Series"),
          [ "title" => $i18n->t("Have a look at the latest series entries at MovLib.") ]
        ];
        $breadcrumb[] = [ $i18n->r("/series/{0}", [ $this->model->id ]), $this->title ];
        break;
    }
    return $breadcrumb;
  }

  // ------------------------------------------------------------------------------------------------------------------- Utility methods

  /**
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance for translations.
   * @return type
   */
  public function getSecondaryNavigation() {
    global $i18n;
    switch ($this->getAction()) {
      case "movie":
        $this->secondaryNavigationPoints = [
          [ $i18n->r("/movie/{0}", [ $this->model->id ]), "<i class='icon icon--film'></i>{$i18n->t("Back to {0}", [ $i18n->t("movie") ])}" ],
          [ $i18n->r("/movie/{0}/{1}/upload", [ $this->model->id, $i18n->t($_SERVER["TAB"]) ]), "<i class='icon icon--upload'></i>{$i18n->t("Upload")}", [ "class" => "menuitem--separator" ] ],
          [ $i18n->r("/movie/{0}/{1}", [ $this->model->id, $i18n->t("posters") ]), $i18n->t("Posters") ],
          [ $i18n->r("/movie/{0}/{1}", [ $this->model->id, $i18n->t("lobby-cards") ]), $i18n->t("Lobby Cards") ],
          [ $i18n->r("/movie/{0}/{1}", [ $this->model->id, $i18n->t("photos") ]), $i18n->t("Photos") ]
        ];
        break;
      case "person":
        break;
      case "series":
        break;
    }
    return $this->secondaryNavigationPoints;
  }

  /**
   * Initializes common movie properties including the model and the title.
   *
   */
  protected function initMovie() {
    $this->model = new MovieModel($_SERVER["ID"]);
    if (empty($this->model->getTitleDisplay())) {
        $this->title = $this->model->originalTitle;
    }
    else {
      $this->title = $this->model->getTitleDisplay();
    }
  }

}
