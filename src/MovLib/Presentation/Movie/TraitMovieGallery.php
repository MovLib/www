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

use \MovLib\Exception\RedirectException;
use \MovLib\Presentation\Partial\Alert;

/**
 * Trait for all movie galleries.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitMovieGallery {

  /**
   * The movie's title.
   *
   * @var string
   */
  public $movieTitle;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getBreadcrumbs() {
    global $i18n;
    return [
      [ $i18n->r("/movies"), $i18n->t("Movies"), [ "title" => $i18n->t("Have a look at the latest {0} entries at MovLib.", [ $i18n->t("movie") ]) ] ],
      [ $i18n->r("/movie/{0}", [ $_SERVER["MOVIE_ID"] ]), $this->movieTitle ],
    ];
  }

  /**
   * @inheritdoc
   */
  protected function getGoneContent() {
    global $i18n;
    throw new RedirectException($i18n->r("/movie/{0}", [ $this->model->id ]), 302);
  }

  /**
   * @inheritdoc
   */
  protected function getImageDetails() {
    global $i18n;
    /**
     * @var \MovLib\Data\MovieImage
     */
    $this->image;
    $details = [];
    if (empty($this->image->description)) {
      $this->image->description = new Alert("{$i18n->t("No {0} available, could you provide one?", [ $i18n->t("Description") ])} {$this->a(
        $this->editRoute, [ $this->model->id, $this->image->sectionId ],
        $i18n->t("Click here to do so.")
      )}");
    }
    $details[] = [ $i18n->t("Description"), $this->image->description ];
  }

  /**
   * @inheritdoc
   */
  public function getSecondaryNavigationMenuitems() {
    global $i18n;
    $points = [
      [ $i18n->r("/movie/{0}", [ $this->model->id ]), "<i class='icon icon--film'></i>{$i18n->t("Back to {0}", [ $i18n->t("movie") ])}" ],
      [ $this->uploadRoute, "<i class='icon icon--upload'></i>{$i18n->t("Upload")}", [
        "class" => "separator"
      ]],
    ];
    foreach ([ "posters" => "Posters", "lobby-cards" => "Lobby Card", "photos" => "Photos" ] as $route => $title) {
      $points[] = [ $i18n->r("/movie/{0}/{$i18n->t("{$route}")}", [ $this->model->id ]), $i18n->t($title), "{$_SERVER["TAB"]}s" == $route ? [ "class" => "active" ] : null ];
    }
    return $points;
  }

}
