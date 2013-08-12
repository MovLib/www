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
namespace MovLib\View\HTML;

use \MovLib\View\HTML\AbstractView;

/**
 * Description of MoviesView
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MoviesView extends AbstractView {

  /**
   * Initialize new MoviesView.
   *
   * @param \MovLib\Presenter\MoviesPresenter $presenter
   *  The presenter controlling this view.
   * @param string $title
   *  The page's title.
   */
  public function __construct($presenter, $title) {
    parent::__construct($presenter, $title);
    $this->stylesheets[] = "modules/movies.css";
  }

  /**
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global I18n Model instance for translations.
   */
  public function getContent() {
    global $i18n;
    $moviesList = "<ul id='movies__latest'>";
    $movies = $this->presenter->moviesModel->getMoviesByCreated();
    $c = count($movies);
    for ($i = 0; $i < $c; ++$i) {
      $posterURL = "";
      $posterAlt = $i18n->t("No poster available.");
      if (empty($movies[$i]["#movie"]->getPosters()) === false) {
        $poster = $movies[$i]["#movie"]->getPosters()[0];
        /** @todo Remove magic number from style in the URL. */
        $posterURL = "/uploads/posters/{$movies[$i]["#movie"]->id}/w75/{$poster["filename"]}.{$poster["ext"]}";
        $posterAlt = $i18n->t("{0} movie poster.", [ $movies[$i]["#movie"]->displayTitle ]);

      }
      $countriesAndYear = [];
      if (empty($movies[$i]["#movie"]->getCountries()) === false) {
        $cc = count($movies[$i]["#movie"]->getCountries());
        for ($ci = 0; $ci < $cc; ++$ci) {
          $countriesAndYear[] = $movies[$i]["#movie"]->getCountries()[$ci]["code"];
        }
      }
      if (isset($movies[$i]["#movie"]->year)) {
        $countriesAndYear[] = "{$movies[$i]["#movie"]->year}";
      }
      $countriesAndYear = $this->getCommaSeparatedList($countriesAndYear, "");
      if (strlen($countriesAndYear) > 0) {
        $countriesAndYear = " ({$countriesAndYear})";
      }
      $moviesList .=
        "<li>" .
          $this->a(
            $i18n->r("/movie/{0}", [ $movies[$i]["#movie"]->id ]),
            "<div class='movies-list_info'>" .
              "<h2>{$movies[$i]["#movie"]->displayTitle}{$countriesAndYear}</h2>" .
              "<p>{$i18n->t("“{0}” (<em>original title</em>)", [ $movies[$i]["#movie"]->originalTitle ])}</p>" .
            "</div>" .
            "<div class='movies-list__poster'><img src='{$posterURL}' alt='{$posterAlt}'></div>"
          ) .
        "</li>";
    }
    $moviesList .= "</ul>";
    return
      "<div class='container'>" .
        "<div class='row'>" .
          $i18n->t("On the {0}movies{1} page you can find the latest movies that have been added to our database.", [ "<strong>", "</strong>" ]) .
        "</div>" .
        "<div class='row'>" .
          $moviesList .
        "</div>" .
      "</div>";
  }

}