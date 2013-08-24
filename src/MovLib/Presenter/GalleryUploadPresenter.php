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
use \MovLib\Model\MovieImageModel;
use \MovLib\Model\MoviePosterModel;
use \MovLib\View\HTML\GalleryUploadAnonymousView;
use \MovLib\View\HTML\GalleryUploadView;

/**
 * Generic image upload presenter for galleries.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class GalleryUploadPresenter extends GalleryPresenter {

  /**
   * Initialize new GalleryUploadPresenter.
   */
  public function __construct() {
    $this
      ->{__FUNCTION__ . $this->getMethod()}()
      ->setPresentation()
    ;
  }

  /**
   * Initialize the presenter for GET requests.
   *
   * @global \MovLib\Model\SessionModel $user
   *   The global session model instance.
   * @return this
   */
  public function __constructGet() {
    global $user;
    $this->{__FUNCTION__ . $this->getAction()}();
    if ($user->isLoggedIn) {
      $this->view = new GalleryUploadView($this);
    }
    else {
      $this->view = new GalleryUploadAnonymousView($this);
    }
    return $this;
  }

  /**
   * Initialize the movie specific properties of this upload page.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance for translations.
   * @return this
   */
  public function __constructGetMovie() {
    global $i18n;
    try {
      $this->initMovie();
      switch ($_SERVER["TAB"]) {
        case "poster":
          $this->model = new MoviePosterModel($_SERVER["ID"]);
          $this->galleryTitle = $i18n->t("Poster");
          break;
        case "lobby-card":
          $this->model = new MovieImageModel($_SERVER["ID"], "lobby-card");
          $this->galleryTitle = $i18n->t("Lobby Card");
          break;
        case "photo":
          $this->model = new MovieImageModel($_SERVER["ID"], "photo");
          $this->galleryTitle = $i18n->t("Photo");
          break;
      }
    } catch (MovieException $e) {
      $this->setPresentation("Error\\NotFound");
    }
    return $this;
  }

  /**
   * Initialize the presenter for POST requests.
   *
   * @global \MovLib\Model\SessionModel $user
   *   The global session model instance.
   * @return this
   */
  public function __constructPost() {
    global $user;
    $this->{__FUNCTION__ . $this->getAction()}();
    if ($user->isLoggedIn) {
      $this->view = new GalleryUploadView($this);
    }
    else {
      $this->view = new GalleryUploadAnonymousView($this);
    }
    return $this;
  }

  public function __constructPostMovie() {
    global $i18n;
    try {
      $this->initMovie();
      switch ($_SERVER["TAB"]) {
        case "poster":
          $this->model = new MoviePosterModel($_SERVER["ID"]);
          break;
        case "lobby-card":
          $this->model = new MovieImageModel($_SERVER["ID"], "lobby-card");
          break;
        case "photo":
          $this->model = new MovieImageModel($_SERVER["ID"], "photo");
          break;
      }
    }
    catch (MovieException $e) {
      $this->setPresentation("Error\\NotFound");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getBreadcrumb() {
    global $i18n;
    $breadcrumb = parent::getBreadcrumb();
    switch ($this->getAction()) {
      case "movie":
        $breadcrumb[] = [
          $i18n->r("/movie/{0}/{1}-gallery", [ $this->model->id, $i18n->t($_SERVER["TAB"]) ]),
          "{$this->title} {$this->galleryTitle}"
        ];
        break;
      case "person":
        $breadcrumb[] = [
          $i18n->r("/person/{0}/{1}-gallery", [ $this->model->id, $i18n->t($_SERVER["TAB"]) ]),
          "{$this->title} {$this->galleryTitle}"
        ];
        break;
      case "series":
        $breadcrumb[] = [
          $i18n->r("/series/{0}/{1}-gallery", [ $this->model->id, $i18n->t($_SERVER["TAB"]) ]),
          "{$this->title} {$this->galleryTitle}"
        ];
        break;
    }
    return $breadcrumb;
  }

}
