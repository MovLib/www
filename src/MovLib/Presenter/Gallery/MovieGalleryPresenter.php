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

use \MovLib\Model\MovieModel;
use \MovLib\Presenter\Gallery\AbstractGalleryPresenter;
use \MovLib\View\HTML\Error\GoneView;
use \MovLib\View\HTML\Error\NotFoundView;

/**
 * Takes care of presenting movie galleries.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieGalleryPresenter extends AbstractGalleryPresenter {

  /**
   * Instantiate new movie gallery presenter.
   */
  public function __construct() {
    $this->init();
  }

  /**
   * Initialize new movie gallery presenter.
   *
   * We have to use an init method here because of our child classes.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @return this
   */
  public function init() {
    global $i18n;
    try {
      $this->model = new MovieModel($_SERVER["ID"]);
      if ($this->model->deleted === true) {
        new GoneView($this);
        return;
      }
      $this->title = $this->model->getDisplayTitle();
      if ($this->model->year) {
        $this->title .= " ({$this->model->year})";
      }
      switch ($_SERVER["TAB"]) {
        case "poster":
          $this->galleryTitle = $i18n->t("Posters");
          $this->images = $this->model->getPosters();
          break;

        case "lobby-card":
          $this->galleryTitle = $i18n->t("Lobby Cards");
          $this->images = $this->model->getLobbyCards();
          break;

        case "photo":
          $this->galleryTitle = $i18n->t("Photos");
          $this->images = $this->model->getPhotos();
          break;
      }
      $this->setView();
    } catch (Exception $e) {
      $this->view = new NotFoundView($this);
    }
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getBreadcrumb() {
    global $i18n;
    return [
      [ $i18n->r("/movies"), $i18n->t("Movies"), [ "title" => $i18n->t("Have a look at the latest {0} entries at MovLib.", [ $i18n->t("movie") ]) ] ],
      [ $i18n->r("/movie/{0}", [ $_SERVER["ID"] ]), $this->title ],
    ];
  }

  /**
   * @inheritdoc
   */
  public function getSecondaryNavigationPoints() {
    global $i18n;
    $points = [
      [ $i18n->r("/movie/{0}", [ $this->model->id ]), "<i class='icon icon--film'></i>{$i18n->t("Back to {0}", [ $i18n->t("movie") ])}" ],
      [ $i18n->r("/movie/{0}/{1}/upload", [ $this->model->id, $i18n->t("{$_SERVER["TAB"]}s") ]), "<i class='icon icon--upload'></i>{$i18n->t("Upload")}", [
        "class" => "menuitem--separator"
      ]],
    ];
    foreach ([ "poster" => "Posters", "lobby-card" => "Lobby Card", "photo" => "Photos" ] as $route => $title) {
      $points[] = [ $i18n->r("/movie/{0}/{$i18n->t("{$route}s")}", [ $this->model->id ]), $i18n->t($title), $_SERVER["TAB"] == $route ? [ "class" => "active" ] : null ];
    }
    return $points;
  }

}
