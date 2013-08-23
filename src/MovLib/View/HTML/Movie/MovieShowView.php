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

use \MovLib\Exception\MovieException;
use \MovLib\Model\MoviePosterModel;
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
   * The year suffix for the heading.
   *
   * @var string
   */
  private $yearSuffix = "";

  /**
   * Construct new movie show view.
   *
   * @param \MovLib\Presenter\MoviePresenter $presenter
   *  The movie presenter currently controlling this view.
   */
  public function __construct($presenter) {
    parent::__construct($presenter, $presenter->displayTitle);
    if (empty($presenter->movieModel->year) === false) {
      $this->yearSuffix = " ({$this->presenter->movieModel->year})";
      $this->title .= $this->yearSuffix;
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
      [ $i18n->r("/movie/{0}/history", [ $this->presenter->movieModel->id ]), "<i class='icon icon--history'></i>{$i18n->t("History")}", [
        "accesskey" => "h",
        "class"     => "menuitem--separator",
        "title"     => $i18n->t("Past versions of this {0}.", [ $i18n->t("movie") ]),
      ]]
    ];
    /**
     * @var array Numeric Array holding all the pages content points in a uniform way.
     *   Format:
     *     [0] => [ "id" => "first section id", "title" => "translated title", "content" ) => "section content" ]
     *     [1] => [ "id" => "second section id", "title" => "translated title", "content" ) => "section content" ]
     */
    $contents = [];

    // ----------------------------------------------------------------------------------------------------------------- Synopsis

    $contents[] = [
      "id"      =>  "synopsis",
      "title"   =>  $i18n->t("Synopsis"),
      "content" =>  $this->presenter->movieModel->synopsis
    ];

    // ----------------------------------------------------------------------------------------------------------------- Directors

    $contents[] = [
      "id"      =>  "directors",
      "title"   =>  $i18n->t("Directors"),
      "content" => $this->getUnorderedList($this->presenter->movieModel->getDirectors(), "", function ($item) use ($i18n) {
        return $this->a($i18n->r("/person/{0}", [ $item["id"] ]), $item["name"]);
      })
    ];

    // ----------------------------------------------------------------------------------------------------------------- Titles

    $contents[] = [
      "id"      =>  "titles",
      "title"   =>  $i18n->t("Titles"),
      "content" => $this->getUnorderedList($this->presenter->movieModel->getTitles(), "", function ($item) { return $item["title"]; })
    ];

    // ----------------------------------------------------------------------------------------------------------------- Taglines

    $contents[] = [
      "id"      =>  "taglines",
      "title"   =>  $i18n->t("Taglines"),
      "content" => $this->getUnorderedList($this->presenter->movieModel->getTagLines(), "", function ($item) { return $item["tagline"]; })
    ];
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
      $languagesList = $i18n->t("No languages assigned yet.") . " " . $this->a($i18n->r("/movie/{0}/edit", [ $this->presenter->movieModel->id ]), $i18n->t("Add Languages"), [ "title" => $i18n->t("You can edit this {0}.", [ $i18n->t("movie") ]) ]);
    }

    $content = "";
    $c = count($contents);
    for ($i = 0; $i < $c; ++$i) {
      $secondaryNavPoints[] = [ "#{$contents[$i]["id"]}", $contents[$i]["title"], [ "title" => $i18n->t("Go to section", [ $contents[$i]["title"] ]) ] ];
      if (empty($contents[$i]["content"])) {
        $contents[$i]["content"] = $i18n->t("No {0} assigned yet, {1}add {0}{2}?", [
              $contents[$i]["title"],
              "<a href='{$i18n->r("/movie/{0}/edit-{1}", [ $contents[$i]["id"] ])}'>",
              "</a>"
          ]
        );
      }
      $content .=
        "<div id='{$contents[$i]["id"]}' class='movie-section'>" .
          "<h2>{$contents[$i]["title"]}<small>{$this->a($i18n->r("/movie/{0}/edit-{1}", [ $this->presenter->movieModel->id, $contents[$i]["id"] ]), $i18n->t("Edit"))}</small></h2>" .
          $contents[$i]["content"] .
        "</div>"
      ;
    }

    return
      "<div class='container'>" .
        "<div class='row'>" .
          "<aside class='span span--3'>{$this->getSecondaryNavigation($i18n->t("Movie navigation"), $secondaryNavPoints)}</aside>" .
          "<div class='span span--9'>{$content}" .
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
   * @global \MovLib\Model\SessionModel $user
   * @return string
   *   The rendered content ready for print.
   */
  public function getRenderedContent($tag = "div", $attributes = []) {
    global $i18n, $user;
    $this->addClass("{$this->getShortName()}-content", $attributes);
    $attributes["id"] = "content";
    $attributes["role"] = "main";
    // Build the link for the movie year.
    $yearLink = "";
    if (isset($this->presenter->movieModel->year)) {
      $yearLink = $this->a(
        $i18n->r("/movies/year/{0}", [ $this->presenter->movieModel->year ]),
        $this->presenter->movieModel->year,
        [ "title" => $i18n->t("Go to movies of the year ") ]
      );
    } else {
      $i18n->t("No year assigned yet.") . " " . $this->a($i18n->r("/movie/{0}/edit", [ $this->presenter->movieModel->id ]), $i18n->t("Add Year"), [ "title" => $i18n->t("You can edit this {0}.", [ $i18n->t("movie") ]) ]);
    }

    // Calculate the width for the rating stars in px.
    $rating = $this->presenter->movieModel->rating;
    $ratingWidth = ($rating * 19 + (ceil($rating) - 1) * 5);
    $ratingWidth = $ratingWidth < 0 ? 0 : $ratingWidth;

    // Construct the "Your rating" section for the rating explanation.
    if ($user->isLoggedIn === false) {
      $userRating = $i18n->t(
        "please {0}log in{1} to rate this movie",
        [
          "<a href='{$i18n->r("/user/login")}' title='Click here to log in to your account.'>",
          "</a>"
        ]
      );
    } elseif (($userRating = $this->presenter->ratingModel->getMovieRating($user->id, $this->presenter->movieModel->id))) {
      $userRating = $i18n->t("your rating: ", [ $userRating ]);
    } else {
      $userRating = $i18n->t("you haven't rated this movie yet");
    }

    try {
      $poster = $this->presenter->movieModel->getPosterDisplay();
    } catch (MovieException $e) {
      $poster = new MoviePosterModel();
    }
    return
      "<{$tag}{$this->expandTagAttributes($attributes)}>" .
        "<div id='content__header'>" .
          "<div id='movie__header' class='container'>" .
            "<header class='row'>" .
              "<div class='span span--9'>" .

                // ----------------------------------------------------------------------------------------------------- Display title & original title

                "<h1 id='content__header__title' class='title'>{$this->presenter->displayTitle}<small>{$this->yearSuffix}</small></h1>" .
                "<p>{$i18n->t("“{0}” (<em>original title</em>)", [ $this->presenter->movieModel->originalTitle ])}</p>" .

                // ----------------------------------------------------------------------------------------------------- Rating form & explanatory text

                "<form action='{$i18n->r("/movie/{0}", [ $this->presenter->movieModel->id ])}' id='movie__rating' method='post'>" .
                  "<span class='visuallyhidden'>{$i18n->t("Rating")}: </span>" .
                  "<div id='movie__rating__back' style='clip:rect(0,{$ratingWidth}px,20px,0)'>" .
                    "<span>&#xe000;</span>" .
                    "<span>&#xe000;</span>" .
                    "<span>&#xe000;</span>" .
                    "<span>&#xe000;</span>" .
                    "<span>&#xe000;</span>" .
                  "</div>" .
                  "<div id='movie__rating__front'>" .
                    "<label class='popup-container'>" .
                      "<small class='popup popup--inverse'>{$i18n->t("Awful")}</small>" .
                      "<button name='rating' title='{$i18n->t("Rate this movie with {0} stars.", [ 1 ])}' type='submit' value='1'>&#xe000;</button>" .
                    "</label>" .
                    "<label class='popup-container'>" .
                      "<small class='popup popup--inverse'>{$i18n->t("Not that bad")}</small>" .
                      "<button name='rating' title='{$i18n->t("Rate this movie with {0} stars.", [ 2 ])}' type='submit' value='1'>&#xe000;</button>" .
                    "</label>" .
                    "<label class='popup-container'>" .
                      "<small class='popup popup--inverse'>{$i18n->t("Fair")}</small>" .
                      "<button name='rating' title='{$i18n->t("Rate this movie with {0} stars.", [ 3 ])}' type='submit' value='1'>&#xe000;</button>" .
                    "</label>" .
                    "<label class='popup-container'>" .
                      "<small class='popup popup--inverse'>{$i18n->t("Pretty Good")}</small>" .
                      "<button name='rating' title='{$i18n->t("Rate this movie with {0} stars.", [ 4 ])}' type='submit' value='1'>&#xe000;</button>" .
                    "</label>" .
                    "<label class='popup-container'>" .
                      "<small class='popup popup--inverse'>{$i18n->t("Great")}</small>" .
                      "<button name='rating' title='{$i18n->t("Rate this movie with {0} stars.", [ 5 ])}' type='submit' value='1'>&#xe000;</button>" .
                    "</label>" .
                  "</div>" .
                "</form>" .
                "<p id='movie__rating__description' class='small'>" .
                  $i18n->t(
                    "Rated by {0}{1,number,integer} users{2} with an {3}average rating{4} of {5,number} ({6}).",
                    [
                      "<a href='{$i18n->r("/movie/{0}/rating-demographics", [ $this->presenter->movieModel->id ])}' title='{$i18n->t("View Rating demographics")}'>",
                      $this->presenter->movieModel->votes,
                      "</a>",
                      "<a href='{$i18n->r("/help/average-rating")}' title='{$i18n->t("Go to help page: {0}", [ "Average Rating"] )}'>",
                      "</a>",
                      $this->presenter->movieModel->rating,
                      $userRating
                    ]) .
                "</p>" .

                // ----------------------------------------------------------------------------------------------------- Year & Countries

                "<p class='small'>" .
                  "<span class='visuallyhidden'>{$i18n->t("Year")}: </span>{$yearLink} - " .
                  "<span class='visuallyhidden'>{$i18n->t("Countries")}: </span>{$this->getCommaSeparatedList(
                    $this->presenter->movieModel->getCountries(),
                    $i18n->t("No {0} assigned yet, {1}add {0}{2}?", [ $i18n->t("countries"), "<a href='{$i18n->r("/movie/{0}/edit", [ $this->presenter->movieModel->id ])}'>", "</a>" ]),
                    function ($item) use ($i18n) {
                      return $this->a($i18n->r("/country/{0}", [ $item["code"] ]), $item["name"]);
                    }
                  )}" .
                "</p>" .

                // ----------------------------------------------------------------------------------------------------- Runtime, Genres & Styles

                "<p class='small'>" .
                  "<span class='visuallyhidden'>{$i18n->t("Length")}:</span> {$this->presenter->movieModel->runtime} {$i18n->t("min.")}" .
                  " | <span class='visuallyhidden'>{$i18n->t("Genres")}:</span> {$this->getCommaSeparatedList(
                    $this->presenter->movieModel->getGenres(),
                    $i18n->t("No {0} assigned yet, {1}add {0}{2}?", [ $i18n->t("genres"), "<a href='{$i18n->r("/movie/{0}/edit-genres", [ $this->presenter->movieModel->id ])}'>", "</a>" ]),
                    function ($item) use ($i18n) {
                      return $this->a($i18n->r("/genre/{0}", [ $item["id"] ]), $item["name"]);
                    }
                  )}" .
                  " | <span class='visuallyhidden'>{$i18n->t("Styles")}:</span> {$this->getCommaSeparatedList(
                    $this->presenter->movieModel->getStyles(),
                    $i18n->t("No {0} assigned yet, {1}add {0}{2}?", [ $i18n->t("styles"), "<a href='{$i18n->r("/movie/{0}/edit-styles", [ $this->presenter->movieModel->id ])}'>", "</a>"]),
                    function ($item) use ($i18n) {
                      return $this->a($i18n->r("style/{0}", [ $item["id"] ]), $item["name"]);
                    }
                  )}" .
                "</p>" .

              "</div>" .
              // ------------------------------------------------------------------------------------------------------- Poster
              $this->a(
                $i18n->r("/movie/{0}/{1}-gallery", [ $this->presenter->movieModel->id, $i18n->t("poster") ]),
                $this->getImage(
                  $poster,
                  MoviePosterModel::IMAGESTYLE_LARGE_FIXED_WIDTH,
                  [ "alt" => $this->presenter->displayTitle ]),
                [ "class" => "span span--3" ]
              ) .
            "</header>" .
          "</div>" .
        "</div>" .
        $this->getAlerts() .
        $this->getContent() .
      "</{$tag}>"
    ;
  }

}
