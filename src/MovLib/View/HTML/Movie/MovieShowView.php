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
      [ "#releases", $i18n->t("Releases"), [ "title" => $i18n->t("Go to section: {0}", [ $i18n->t("Releases") ]) ] ]
    ];

    return
      "<div class='container'>" .
        "<div class='row'>" .
          "<aside class='span span--3'>{$this->getSecondaryNavigation($i18n->t("Movie navigation"), $secondaryNavPoints)}</aside>" .
          "<div class='span span--9'>" .
            "<h2>" . $i18n->t("Synopsis") . "</h2>" . $this->presenter->movieModel->synopsis .
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
    // Build the styles list.
    $styles = $this->presenter->movieModel->getStyles();
    $c = count($styles);
    if ($c >0) {
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
    // Calculate the width for the rating stars in %.
    $rating = 4.5;
    $ratingWidth = $rating * 20 - 4 * (6 - ceil($rating));

    return
      "<{$tag}{$this->expandTagAttributes($attributes)}>" .
        "<div id='content__header'>" .
          "<div id='movie__header' class='container'>" .
            "<header class='row'>" .
              "<div class='span span--9'>" .
                "<h1 id='content__header__title' class='title'>{$this->presenter->displayTitle}{$yearLink}</h1>" .
                "<p>{$i18n->t("“{0}” (<em>original title</em>)", [ $this->presenter->displayTitle ])}</p>" .
                "<div id='movie__rating'>" .
                  "<div><i class='icon icon--star'></i><i class='icon icon--star'></i><i class='icon icon--star'></i><i class='icon icon--star'></i><i class='icon icon--star'></i></div>" .
                  "<div style='width: {$ratingWidth}%'><i class='icon icon--star'></i><i class='icon icon--star'></i><i class='icon icon--star'></i><i class='icon icon--star'></i><i class='icon icon--star'></i></div>" .
                "</div>" .
                "<p>{$i18n->t("Length")}: {$this->presenter->movieModel->runtime} {$i18n->t("min.")}</p>" .
                "<p>{$i18n->t("Genres")}: {$genreList}</p>" .
                "<p>{$i18n->t("Styles")}: {$styleList}</p>" .
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