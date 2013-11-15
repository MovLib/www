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
namespace MovLib\Presentation\Movie;

use \MovLib\Data\Movie;
use \MovLib\Exception\MovieException;
use \MovLib\Exception\Client\ErrorNotFoundException;

/**
 * Description of TraitMoviePage
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitMoviePage {
  
  /**
   * The movie to display.
   *
   * @var \MovLib\Data\Movie
   */
  protected $model;

 /**
   * Initialize the movie model and the title.
   *
   * @throws ErrorNotFoundException
   */
  protected function initMovie() {
    try {
      $this->model = new Movie($_SERVER["MOVIE_ID"]);
      $this->title = $this->model->getDisplayTitle();
      if (isset($this->model->year)) {
        $this->title .= " ({$this->model->year})";
      }
    } catch (MovieException $e) {
      throw new ErrorNotFoundException($e);
    }
  }
   
  /**
   * @inheritdoc
   */
  public function getSecondaryNavigationMenuItems() {
    global $i18n;
    return [
      [ $i18n->r("/movie/{0}", [ $this->model->id ]), "<i class='icon icon--eye'></i>{$i18n->t("View")}", [
        "accesskey" => "v",
        "title"     => $i18n->t("View the {0}.", [ $i18n->t("movie") ]),
      ]],
      [ $i18n->r("/movie/{0}/discussion", [ $this->model->id ]), "<i class='icon icon--comment'></i>{$i18n->t("Discuss")}", [
        "accesskey" => "d",
        "title"     => $i18n->t("Discussion about the {0}.", [ $i18n->t("movie") ])
      ]],
      [ $i18n->r("/movie/{0}/edit", [ $this->model->id ]), "<i class='icon icon--pencil'></i>{$i18n->t("Edit")}", [
        "accesskey" => "e",
        "title"     => $i18n->t("You can edit this {0}.", [ $i18n->t("movie") ]),
      ]],
      [ $i18n->r("/movie/{0}/history", [ $this->model->id ]), "<i class='icon icon--history'></i>{$i18n->t("History")}", [
        "accesskey" => "h",
        "class"     => "separator",
        "title"     => $i18n->t("Past versions of this {0}.", [ $i18n->t("movie") ]),
      ]],
      [ $i18n->r("/movie/{0}/titles", [ $this->model->id ]), "<i class='icon icon--eye'></i>{$i18n->t("Titles")}", [
        "accesskey" => "t",
        "title"     => $i18n->t("View the titles of the {0}.", [ $i18n->t("movie") ]),
      ]],
    ];
  }

}
