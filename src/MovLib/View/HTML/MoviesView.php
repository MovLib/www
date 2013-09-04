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

use \MovLib\Model\MoviePosterModel;
use \MovLib\View\HTML\AbstractPageView;

/**
 * Takes care of formatting movie listings.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MoviesView extends AbstractPageView {

  /**
   * Instantiate new movie view.
   *
   * @param \MovLib\Presenter\MoviesPresenter $presenter
   *   The movie presenter controlling this view.
   */
  public function __construct($presenter) {
    global $i18n;
    $this->init($presenter, $i18n->t("Movies"));
    $this->stylesheets[] = "modules/movies.css";
  }

  /**
   * @inheritdoc
   */
  public function getContent() {
    global $i18n;
    return
      "<div class='container'>" .
        "<div class='row'>{$i18n->t("On the {0}movies{1} page you can find the latest movies that have been added to our database.", [
          "<strong>", "</strong>"
        ])}</div>" .
        "<div class='row'>" .
          "<aside class='span span--3'>{$this->getSecondaryNavigation($i18n->t("Sort the movie entries"), $this->presenter->getSecondaryNavigation())}</aside>" .
          "<div class='span span--9'>{$this->getOrderedList(
            $this->presenter->moviesModel->getMoviesByCreated(),
            $i18n->t("No movies match your criteria."),
            function ($movieModel) use ($i18n) {
              $title = $movieModel->getDisplayTitle();
              $countries = $movieModel->getCountries();
              $titleSuffix = array_column($countries, "code");
              if (isset($movieModel->year)) {
                $titleSuffix[] = $movieModel->year;
              }
              if (!empty($titleSuffix = $this->getCommaSeparatedList($titleSuffix, ""))) {
                $titleSuffix = " ({$titleSuffix})";
              }
              return
                $this->a(
                  $this->r("/movie/{0}", [ $movieModel->id ]),
                  "<article>" .
                    "<div class='movies-list__poster'>{$this->getImage($movieModel->getDisplayPoster(), MoviePosterModel::IMAGESTYLE_SMALL, [
                      "alt" => $i18n->t("{0} movie poster", [ $title ])
                    ])}</div>" .
                    "<div class='movies-list__info clear-fix'>" .
                      "<h2>{$title}{$titleSuffix}</h2>" .
                      "<p>{$i18n->t("“{0}” (<em>original title</em>)", [ $movieModel->originalTitle ])}</p>" .
                    "</div>" .
                  "</article>",
                  [ "tabindex" => $this->getTabindex() ]
                )
              ;
            },
            [ "id" => "movies__latest" ]
          )}</div>" .
        "</div>" .
      "</div>"
    ;
  }

}