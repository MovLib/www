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
namespace MovLib\Presentation\Movie\ImageDetails;

use \MovLib\Data\Movie\Movie;
use \MovLib\Data\Image\MoviePoster;
use \MovLib\Presentation\Partial\Lists\Description;
use \MovLib\Presentation\Partial\Country;

/**
 * @todo Description of Poster
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class PosterShow extends \MovLib\Presentation\Movie\AbstractMoviePage {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The poster we present.
   *
   * @var \MovLib\Data\Image\MoviePoster
   */
  protected $image;

  /**
   * The page's short title.
   *
   * @var string
   */
  protected $shortTitle;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new image details poster show presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;

    try {
      $this->movie = new Movie($_SERVER["MOVIE_ID"]);
    }
    catch (\OutOfBoundsException $e) {
      throw $e;
    }

    try {
      $this->image = new MoviePoster($this->movie->id, $this->movie->displayTitleWithYear, $_SERVER["IMAGE_ID"]);
    }
    catch (\OutOfBoundsException $e) {
      throw $e;
    }

    // Initialize presentation
    $this->shortTitle = $i18n->t("Poster {id}", [ "id" => $this->image->id ]);
    $this->init($i18n->t("Poster {id} of {movie_title}", [ "id" => $this->image->id, "movie_title" => $this->movie->displayTitleWithYear ]), $this->shortTitle);
    $this->pageTitle  = $i18n->t("Poster {id} of {movie_title}", [ "id" => $this->image->id, "movie_title" => $this->movie->displayTitleWithYear ]);

    // Alter the sidebar navigation and include the various image types.
    $this->sidebarNavigation->menuitems[0][1] = $i18n->t("Back to movie");
    $this->sidebarNavigation->menuitems[] = [ $i18n->rp("/movie/{0}/posters", [ $this->movie->id ]), $i18n->t("Posters"), [ "class" => "active" ] ];
    $this->sidebarNavigation->menuitems[] = [ $i18n->rp("/movie/{0}/lobby-cards", [ $this->movie->id ]), $i18n->t("Lobby Cards") ];
    $this->sidebarNavigation->menuitems[] = [ $i18n->rp("/movie/{0}/photos", [ $this->movie->id ]), $i18n->t("Photos") ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getBreadcrumbs() {
    global $i18n;
    $trail = parent::getBreadcrumbs();
    $trail[] = [ $this->routeMovie, $this->movie->displayTitleWithYear ];
    $trail[] = [ $i18n->rp("/movie/{0}/posters", [ $this->movie->id ]), $i18n->t("Posters") ];
    return $trail;
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  protected function getPageContent() {
    global $i18n, $kernel;

    $posters    = $this->movie->getImageStreamResult(MoviePoster::TYPE_ID, $this->image->id);
    $stream     = array_fill(0, 10, "");
    $streamJSON = [];
    /* @var $poster \MovLib\Data\Image\MoviePoster */
    while ($poster = $posters->fetch_object("\\MovLib\\Data\\Image\\MoviePoster", [ $this->movie->id, $this->movie->displayTitleWithYear ])) {
      $streamJSON[$poster->id] = [ $poster->getStyle()->src ];
    }

    // TEST
    $img = $this->getImage($this->image->getStyle(MoviePoster::STYLE_SPAN_01));
    $stream[1] = "<div class='s s1 first'>{$img}</div>";
    $stream[2] = "<div class='s s1'>{$img}</div>";
    $stream[3] = $stream[2];
    $stream[4] = $stream[2];
    $stream[5] = $stream[2];
    $stream[6] = $stream[2];
    $stream[7] = $stream[2];
    $stream[8] = $stream[2];
    $stream[9] = "<div class='s s1 last'>{$img}</div>";
    // TEST

    $stream     = implode("", $stream);
    $streamJSON = json_encode($streamJSON);

    $description                      = new Description([
      $i18n->t("Description") => $kernel->htmlDecode($this->image->description),
      $i18n->t("Country")     => (new Country($this->image->countryCode))->getFlag(true),
    ]);
    $description->attributes["class"] = "dl--horizontal";
    $description->attributes["id"]    = "imagedescription";

    return
      // js-jis = JavaScript - Image Stream JSON
      "<script id='js-isj' type='application/json'>{$streamJSON}</script>" .
      "<div id='imagestream'>{$stream}</div>" .
      "<div id='imagedetails'>{$this->getImage($this->image->getStyle(MoviePoster::STYLE_SPAN_05), false)}</div>" .
      $description
    ;
  }

}
