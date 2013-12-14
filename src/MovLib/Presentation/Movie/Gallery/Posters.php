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
namespace MovLib\Presentation\Movie\Gallery;

use \MovLib\Data\Movie\Movie;
use \MovLib\Data\Image\MoviePoster;
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
class Posters extends \MovLib\Presentation\Movie\AbstractMoviePage {
  use \MovLib\Presentation\TraitPagination;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  public function __construct() {
    global $i18n;

    try {
      $this->movie = new Movie($_SERVER["MOVIE_ID"]);
    }
    catch (\Exception $e) {
      throw $e;
    }

    // Shorter breadcrumb title for this page, the movie title is preceding us. Note that we have to set this before
    // calling init().
    $this->breadcrumbTitle = $i18n->t("Posters");

    // Initialize the page with the full unlinked title. This is displayed in the browser tab.
    $this->init($i18n->t("Posters for {title}", [ "title" => $this->movie->displayTitleWithYear ]));

    // We want the title in the page header linked back to the movie.
    $this->pageTitle = $i18n->t("Posters for {title}", [ "title" => "<a href='{$this->routeMovie}'>{$this->movie->displayTitleWithYear}</a>" ]);

    // Initialize pagination with total poster count.
    $this->initPagination($this->movie->getImageCount(MoviePoster::TYPE_ID));

    // Alter the sidebar navigation and include the various image types.
    $this->sidebarNavigation->menuitems[0][1] = $i18n->t("Back to movie");
    $this->sidebarNavigation->menuitems[] = [ $i18n->rp("/movie/{0}/posters", [ $this->movie->id ]), $i18n->t("Posters") ];
    $this->sidebarNavigation->menuitems[] = [ $i18n->rp("/movie/{0}/lobby-cards", [ $this->movie->id ]), $i18n->t("Lobby Cards") ];
    $this->sidebarNavigation->menuitems[] = [ $i18n->rp("/movie/{0}/photos", [ $this->movie->id ]), $i18n->t("Photos") ];

    // Make sure it's easy for users to upload new posters.
    $this->headingBefore = "<a class='button button--large button--success pull-right' href='{$i18n->r("/movie/{0}/poster/upload", [ $this->movie->id ])}'>{$i18n->t("Upload New Poster")}</a>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getBreadcrumbs() {
    $trail = parent::getBreadcrumbs();
    $trail[] = [ $this->routeMovie, $this->movie->displayTitleWithYear ];
    return $trail;
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  protected function getPageContent() {
    global $i18n;
    $posters = $this->movie->getImageResult(MoviePoster::TYPE_ID, $this->resultsOffset, $this->resultsPerPage);
    $list    = null;
    /* @var $poster \MovLib\Data\Image\MoviePoster */
    while ($poster = $posters->fetch_object("\\MovLib\\Data\\Image\\MoviePoster", [ $this->movie->id, $this->movie->displayTitleWithYear ])) {
      $country = null;
      if ($poster->countryCode) {
        $country = new Country($poster->countryCode);
      }
      $list .=
        "<li class='span span--2 tac' itemscope itemtype='http://schema.org/ImageObject'>{$this->getImage(
          $poster->getStyle(),
          true,
          [ "class" => "grid-img", "itemprop" => "image" ],
          [ "itemprop" => "url" ]
        )}{$country->getFlag()} {$i18n->t("{width}×{height}", [
          "width"  => "<span itemprop='width'>{$poster->width}</span>",
          "height" => "<span itemprop='height'>{$poster->height}</span>",
        ])}</li>"
      ;
    }
    if ($list) {
      return "<div id='filter'>LIMIT {$this->resultsPerPage} OFFSET {$this->resultsOffset}</div><ol class='img-grid no-list row'>{$list}</ol>";
    }
    return $i18n->t("No posters …");
  }

}
