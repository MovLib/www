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
namespace MovLib\View\HTML\Movie;

use \MovLib\Utility\String;
use \MovLib\View\HTML\AbstractView;

/**
 * Shows a single movie.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MovieShowView extends AbstractView {

  /**
   * The movie presenter currently controlling this view.
   *
   * @var \MovLib\Presenter\MoviePresenter
   */
  protected $presenter;

  /**
   * Construct new movie show view.
   *
   * @param \MovLib\Presenter\MoviePresenter $presenter
   *  The movie presenter currently controlling this view.
   */
  public function __construct($presenter) {
    parent::__construct($presenter, $presenter->displayTitle);
    if (empty($presenter->movieModel->year) === false) {
      $this->title .= " ({$this->presenter->movieModel->year})";
    }
    $this->stylesheets[] = "modules/movie.css";
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    global $i18n;
    $secondaryNavPoints = [
      [ $i18n->r("/movie/{0}", [ $this->presenter->movieModel->id ]), "<i class='icon icon--eye'></i>{$i18n->t("View")}", [
        "accesskey" => "v",
        "title"     => $i18n->t("View the {0}.", [ $i18n->t("movie") ]),
      ]],
      [ $i18n->r("/movie/{0}/discussion", [ $this->presenter->movieModel->id ]), "<i class='icon icon--comment'></i>{$i18n->t("Discuss")}", [
        "accesskey" => "d",
        "title"     => $i18n->t("Discussion about the {0}.", [ $i18n->t("movie") ])
      ]],
      [ $i18n->r("/movie/{0}/edit", [ $this->presenter->movieModel->id ]), "<i class='icon icon--pencil'></i>{$i18n->t("Edit")}", [
        "accesskey" => "e",
        "title"     => $i18n->t("You can edit this {0}.", [ $i18n->t("movie") ]),
      ]],
      [ $i18n->r("/movie/{0}/history", [ $this->presenter->movieModel->id ]), "<i class='icon icon--back-in-time'></i>{$i18n->t("History")}", [
        "accesskey" => "h",
        "class"     => "menuitem--separator",
        "title"     => $i18n->t("Past versions of this {0}.", [ $i18n->t("movie") ]),
      ]],
      [ "#synopsis", $i18n->t("Synopsis"), [ "title" => $i18n->t("Go to section: {0}", [ $i18n->t("Synopsis") ]) ] ],
      [ "#taglines", $i18n->t("Taglines"), [ "title" => $i18n->t("Go to section: {0}", [ $i18n->t("Taglines") ]) ] ],
      [ "#releases", $i18n->t("Releases"), [ "title" => $i18n->t("Go to section: {0}", [ $i18n->t("Releases") ]) ] ]
    ];
    // Build the taglines list.
    $taglines = $this->presenter->movieModel->getTagLines();
    $c = count($taglines);
    if ($c > 0) {
      for ($i = 0; $i < $c; ++$i) {
        $taglines[$i] = "{$taglines[$i]["tagline"]} ({$i18n->getLanguages()["id"][ $taglines[$i]["languageId"] ]["name"]})";
      }
      $taglineList = "<ul><li>" . implode("</li><li>", $taglines) . "</li></ul>";
    } else {
      $taglineList = $i18n->t("No taglines assigned yet.") . " " . $this->a("/movie/{0}/edit", $i18n->t("Add Taglines"), [ "title" => $i18n->t("You can edit this {0}.", [ $i18n->t("movie") ]) ]);
    }

    return
      "<div class='container'>" .
        "<div class='row'>" .
          "<aside class='span span--3'>{$this->getSecondaryNavigation($i18n->t("Movie navigation"), $secondaryNavPoints)}</aside>" .
          "<div class='span span--9'>" .
            "<h2 id='synopsis'>" . $i18n->t("Synopsis") . "</h2>" . $this->presenter->movieModel->synopsis .
            "<h2 id='taglines'>" . $i18n->t("Taglines") . "</h2>" . $taglineList .
          "</div>" .
        "</div>" .
      "</div>"
    ;
  }

  /**
   * Get the content wrapped in the outer content <tt>div</tt>.
   * Override to provide the special movie header with the poster and basic information.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @return string
   *   The rendered content ready for print.
   */
  public function getRenderedContent($tag = "div", $attributes = []) {
    global $i18n;
    $this->addClass("{$this->getShortName()}-content", $attributes);
    $attributes["id"] = "content";
    $attributes["role"] = "main";
    // Build the link for the movie year.
    $yearLink = "";
    if (isset($this->presenter->movieModel->year)) {
      $yearLink = " <small>" . $this->a(
        $i18n->r("/movies/year/{0}", [ $this->presenter->movieModel->year ]),
        $this->presenter->movieModel->year,
        [ "title" => $i18n->t("Go to movies of the year: {0}", [ $this->presenter->movieModel->year ]) ]
      ) . "</small>";
    }
    // Build the genre list.
    $genres = $this->presenter->movieModel->getGenres();
    $c = count($genres);
    if ($c > 0) {
      for ($i = 0; $i < $c; ++$i) {
        $name = $genres[$i]["nameLocalized"] ?: $genres[$i]["name"];
        $genres[$i] = $this->a(
          $i18n->r("/genres/{0}", [ String::convertToRoute($name) ]),
          $name,
          [ "title" => $i18n->t("Go to genre: {0}", [ $name ]) ]
        );
      }
      $genreList = implode(", ", $genres);
    } else {
      $genreList = $i18n->t("No genres assigned yet.") . " " . $this->a("/movie/{0}/edit", $i18n->t("Add Genres"), [ "title" => $i18n->t("You can edit this {0}.", [ $i18n->t("movie") ]) ]);
    }
    // Build the countries list.
    $countries = $this->presenter->movieModel->getCountries();
    $c = count($countries);
    if ($c > 0) {
      for ($i = 0; $i < $c; ++$i) {
        $name = $countries[$i]["isoCode"];
        $countries[$i] = $this->a(
          $i18n->r("/countries/{0}", [ String::convertToRoute($name) ]),
          $name,
          [ "title" => $i18n->t("Go to country: {0}", [ $name ]) ]
        );
      }

      $countryList = implode(", ", $countries);
    } else {
      $countryList = $i18n->t("No countries assigned yet.") . " " . $this->a("/movie/{0}/edit", $i18n->t("Add Countries"), [ "title" => $i18n->t("You can edit this {0}.", [ $i18n->t("movie") ]) ]);
    }
    // Build the directors list.
    $directors = $this->presenter->movieModel->getDirectors();
    $c = count($directors);
    if ($c > 0) {
      for ($i = 0; $i < $c; ++$i) {
        $directors[$i] = $this->a(
          $i18n->r("/persons/{0}", [ $directors[$i]["id"] ]),
          $directors[$i]["name"],
          [ "title" => $i18n->t("Go to person: {0}", [ $directors[$i]["name"] ]) ]
        );
      }

      $directorList = implode(", ", $directors);
    } else {
      $directorList = $i18n->t("No directors assigned yet.") . " " . $this->a("/movie/{0}/edit", $i18n->t("Add Directors"), [ "title" => $i18n->t("You can edit this {0}.", [ $i18n->t("movie") ]) ]);
    }
    // Build the styles list.
    $styles = $this->presenter->movieModel->getStyles();
    $c = count($styles);
    if ($c > 0) {
      for ($i = 0; $i < $c; ++$i) {
        $name = $styles[$i]["nameLocalized"] ?: $styles[$i]["name"];
        $styles[$i] = $this->a(
          $i18n->r("/styles/{0}", [ String::convertToRoute($name) ]),
          $name,
          [ "title" => $i18n->t("Go to style: {0}", [ $name ]) ]
        );
      }

      $styleList = implode(", ", $styles);
    } else {
      $styleList = $i18n->t("No styles assigned yet.") . " " . $this->a("/movie/{0}/edit", $i18n->t("Add Styles"), [ "title" => $i18n->t("You can edit this {0}.", [ $i18n->t("movie") ]) ]);
    }
    // Build the languages list.
    $languages = $this->presenter->movieModel->getLanguages();
    $c = count($languages);
    if ($c > 0) {
      for ($i = 0; $i < $c; ++$i) {
        $name = $languages[$i]["nameLocalized"] ?: $languages[$i]["name"];
        $languages[$i] = $this->a(
          $i18n->r("/languages/{0}", [ String::convertToRoute($name) ]),
          $name,
          [ "title" => $i18n->t("Go to language page: {0}", [ $name ]) ]
        );
      }

      $languagesList = implode(", ", $languages);
    } else {
      $languagesList = $i18n->t("No languages assigned yet.") . " " . $this->a("/movie/{0}/edit", $i18n->t("Add Languages"), [ "title" => $i18n->t("You can edit this {0}.", [ $i18n->t("movie") ]) ]);
    }
    // Build the titles list.
    $titles = $this->presenter->movieModel->getTitles();
    $c = count($titles);
    if ($c > 0) {
      for ($i = 0; $i < $c; ++$i) {
        $titles[$i] = "{$titles[$i]["title"]} ({$i18n->getCountries()["id"][ $titles[$i]["languageId"] ]["name"]})";
      }

      $titlesList = implode(", ", $titles);
    } else {
      $titlesList = $i18n->t("No additional titles assigned yet.") . " " . $this->a("/movie/{0}/edit", $i18n->t("Add Titles"), [ "title" => $i18n->t("You can edit this {0}.", [ $i18n->t("movie") ]) ]);
    }
    // Calculate the width for the rating stars in px.
    $rating = 4.5;
    $ratingWidth = ($rating * 19 + (ceil($rating) - 1) * 5);

    return
      "<{$tag}{$this->expandTagAttributes($attributes)}>" .
        "<div id='content__header'>" .
          "<div id='movie__header' class='container'>" .
            "<header class='row'>" .
              "<div class='span span--9'>" .
                "<h1 id='content__header__title' class='title'>{$this->presenter->displayTitle}{$yearLink}</h1>" .
                "<p>{$i18n->t("“{0}” (<em>original title</em>)", [ $this->presenter->displayTitle ])}</p>" .
                "<div id='movie__rating'>" .
                  "<span class='visuallyhidden'>{$i18n->t("Rating")}: </span><div><span>&#xe80d;</span><span>&#xe80d;</span><span>&#xe80d;</span><span>&#xe80d;</span><span>&#xe80d;</span></div>" .
                  "<div id='movie__rating__form-wrapper' style='width: {$ratingWidth}px'>" .
                    "<form method='post' action='#'><input type='hidden' name='rating' value='1'><input type='submit' value='&#xe80d;' title='{$i18n->t("Rate this movie with {0} stars.", [ 1 ])}'></form>" .
                    "<form method='post' action='#'><input type='hidden' name='rating' value='2'><input type='submit' value='&#xe80d;' title='{$i18n->t("Rate this movie with {0} stars.", [ 2 ])}'></form>" .
                    "<form method='post' action='#'><input type='hidden' name='rating' value='3'><input type='submit' value='&#xe80d;' title='{$i18n->t("Rate this movie with {0} stars.", [ 3 ])}'></form>" .
                    "<form method='post' action='#'><input type='hidden' name='rating' value='4'><input type='submit' value='&#xe80d;' title='{$i18n->t("Rate this movie with {0} stars.", [ 4 ])}'></form>" .
                    "<form method='post' action='#'><input type='hidden' name='rating' value='5'><input type='submit' value='&#xe80d;' title='{$i18n->t("Rate this movie with {0} stars.", [ 5 ])}'></form>" .
                  "</div>" .
                "</div>" .
                "<p><span class='visuallyhidden'>{$i18n->t("Length")}:</span> {$this->presenter->movieModel->runtime} {$i18n->t("min.")} - <span class='visuallyhidden'>{$i18n->t("Genres")}: </span>{$genreList} - <span class='visuallyhidden'>{$i18n->t("Countries")}: </span>{$countryList}</p>" .
                "<p>{$i18n->t("Directors")}: {$directorList}</p>" .
                "<p>{$i18n->t("Styles")}: {$styleList}</p>" .
                "<p>{$i18n->t("Languages")}: {$languagesList}</p>" .
                "<p>{$i18n->t("Titles")}: {$titlesList}</p>" .
              "</div>" .
              "<img class='span span--3' src='/uploads/posters/1/w300/Roundhay-Garden-Scene.1.en.jpg' alt=''>" .
            "</header>" .
          "</div>" .
        "</div>" .
        $this->getAlerts() .
        $this->getContent() .
      "</{$tag}>"
    ;
  }

}