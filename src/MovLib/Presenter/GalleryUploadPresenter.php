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

use \MovLib\Model\MovieImageModel;
use \MovLib\Model\MoviePosterModel;

/**
 * Generic image upload presenter for galleries.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class GalleryUploadPresenter extends AbstractPresenter {

  /**
   * The model for this gallery upload.
   * @var \MovLib\Model\AbstractImageModel
   */
  public $model;

  /**
   * The secondary navigation points for the HTML views.
   *
   * @var array
   */
  public $secondaryNavigationPoints;

  /**
   * The title of the page to render (localized and without "upload" included).
   * @var string
   */
  public $title;

  /**
   * The localized title of the gallery to upload to.
   * @var type
   */
  public $galleryTitle;

  /**
   * Initialize new GalleryUploadPresenter.
   */
  public function __construct() {
    $this
      ->{__FUNCTION__ . $this->getMethod()}()
      ->setPresentation()
    ;
  }

  public function __constructGet() {
    $this->{__FUNCTION__ . $this->getAction()}();
    return $this;
  }

  /**
   * Initialize the movie specific properties of this upload page.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance for translations.
   * @return $this
   */
  public function __constructGetMovie() {
    global $i18n;
    if (empty($this->model->getTitleDisplay())) {
        $this->title = $this->model->originalTitle;
      }
      else {
        $this->title = $this->model->getTitleDisplay();
      }
    if (isset($this->model->year)) {
      $this->title .= " ({$this->model->year})";
    }
    $this->secondaryNavigationPoints = [
      [ $i18n->r("/movie/{0}/{1}-gallery/upload", [ $this->model->id, $i18n->t($_SERVER["TAB"]) ]), "<i class='icon icon--upload-alt'></i>{$i18n->t("Upload")}", [ "class" => "menuitem--separator" ] ],
      [ $i18n->r("/movie/{0}/{1}-gallery", [ $this->model->id, $i18n->t("poster") ]), $i18n->t("Posters") ],
      [ $i18n->r("/movie/{0}/{1}-gallery", [ $this->model->id, $i18n->t("lobby-card") ]), $i18n->t("Lobby Cards") ],
      [ $i18n->r("/movie/{0}/{1}-gallery", [ $this->model->id, $i18n->t("photo") ]), $i18n->t("Photos") ]
    ];
    switch ($_SERVER["TAB"]) {
      case "poster":
        $this->model = new MoviePosterModel($_SERVER["ID"]);
        $this->galleryTitle = $i18n->t("Posters");
        break;
      case "lobby-card":
        $this->model = new MovieImageModel($_SERVER["ID"], "lobby-card");
        $this->galleryTitle = $i18n->t("Lobby Cads");
        break;
      case "photo":
        $this->model = new MovieImageModel($_SERVER["ID"], "photo");
        $this->galleryTitle = $i18n->t("Photos");
        break;
    }
    return $this;
  }

  public function __costructPost() {
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
        $breadcrumb[] = [
          $i18n->r("/movie/{0}/{1}-gallery", [ $this->model->id, $i18n->t($_SERVER["TAB"]) ]),
          "{$this->title} {$this->galleryTitle}"
        ];
        break;
      case "person":
        $breadcrumb[] = [
          $i18n->r("/persons"),
          $i18n->t("Persons"),
          [ "title" => $i18n->t("Have a look at the latest person entries at MovLib.") ]
        ];
        $breadcrumb[] = [ $i18n->r("/person/{0}", [ $this->model->id ]), $this->title ];
        $breadcrumb[] = [
          $i18n->r("/person/{0}/{1}-gallery", [ $this->model->id, $i18n->t($_SERVER["TAB"]) ]),
          "{$this->title} {$this->galleryTitle}"
        ];
        break;
      case "series":
        $breadcrumb[] = [
          $i18n->r("/series"),
          $i18n->t("Series"),
          [ "title" => $i18n->t("Have a look at the latest series entries at MovLib.") ]
        ];
        $breadcrumb[] = [ $i18n->r("/series/{0}", [ $this->model->id ]), $this->title ];
        $breadcrumb[] = [
          $i18n->r("/series/{0}/{1}-gallery", [ $this->model->id, $i18n->t($_SERVER["TAB"]) ]),
          "{$this->title} {$this->galleryTitle}"
        ];
        break;
    }
    return $breadcrumb;
  }

}