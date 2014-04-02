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
namespace MovLib\Presentation\Movie\Images;

use \MovLib\Data\Image\AbstractMovieImage;
use \MovLib\Data\Movie\Movie;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Country;

/**
 * Base class for all movie images presentation pages.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractBase extends \MovLib\Presentation\Movie\AbstractBase {
  use \MovLib\Presentation\TraitPagination;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The class name of the movie image.
   *
   * @var string
   */
  protected $class;

  /**
   * The translated name of the movie image (e.g. <code>$this->intl->t("Poster")</code>).
   *
   * @var string
   */
  protected $name;

  /**
   * The translated plural name of the movie image (e.g. <code>$this->intl->t("Posters")</code>).
   *
   * @var string
   */
  protected $namePlural;

  /**
   * The movie image's route key (e.g. <code>"poster"</code>).
   *
   * @var string
   */
  protected $routeKey;

  /**
   * The movie image's plural route key (e.g. <code>"posters"</code>).
   *
   * @var string
   */
  protected $routeKeyPlural;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie images presentation.
   *
   * @param string $className
   *   The movie image's class name we have to present, without the leading <code>"Movie"</code>.
   * @param string $name
   *   The movie image's translated name (e.g. <code>$this->intl->t("Poster")</code>).
   * @param string $namePlural
   *   The movie image's translated plural name (e.g. <code>$this->intl->t("Posters")</code>).
   * @param string $routeKey
   *   The movie image's route key (e.g. <code>"poster"</code>).
   * @param string $routeKeyPlural
   *   The movie image's plural route key (e.g. <code>"posters"</code>).
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct($className, $name, $namePlural, $routeKey, $routeKeyPlural) {
    // Try to load the movie and export all variables to class scope.
    $this->movie          = new Movie((integer) $_SERVER["MOVIE_ID"]);
    $this->class          = "\\MovLib\\Data\\Image\\Movie{$className}";
    $this->name           = $name;
    $this->namePlural     = $namePlural;
    $this->routeKey       = $routeKey;
    $this->routeKeyPlural = $routeKeyPlural;

    // Translate title once.
    $title  = $this->intl->t("{image_name} for {title}");
    $search = [ "{image_name}", "{title}" ];

    // Initialize the breadcrumb ...
    $this->initBreadcrumb();
    $this->breadcrumbTitle = $namePlural;

    // ... initialize page and extend the page's visible title with a link and micro-data ...
    $this->initPage(str_replace($search, [ $namePlural, $this->movie->displayTitleWithYear ], $title));
    $pageTitle = "<span itemprop='name'{$this->lang($this->movie->displayTitleLanguageCode)}>{$this->movie->displayTitle}</span>";
    if ($this->movie->year) {
      $pageTitle = $this->intl->t("{0} ({1})", [ $pageTitle, "<span itemprop='datePublished'>{$this->movie->year}</span>" ]);
    }
    $this->pageTitle = str_replace($search, [
      $namePlural,
      "<span itemscope itemtype='http://schema.org/Movie'><a href='{$this->movie->route}' itemprop='url'>{$pageTitle}</a></span>"
    ], $title);

    // ... initialize the rest of the page.
    $this->initLanguageLinks("/movie/{0}/{$routeKeyPlural}", [ $this->movie->id ], true);
    $this->paginationInit(call_user_func("{$this->class}::getCount", $this->movie->id));
    $this->initSidebar();
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the page's content.
   *
   * @return string
   *   The page's content.
   */
  protected function getPageContent() {
    // Add large button to the header to ensure that nobody has to search for the upload page.
    $this->headingBefore = "<a class='btn btn-large btn-success fr' href='{$this->intl->r("/movie/{0}/{$this->routeKey}/upload", [ $this->movie->id ])}'>{$this->intl->t("Upload New")}</a>";

    // Only build the image listing if there are images to list.
    if ($this->paginationTotalResults === 0) {
      return new Alert(
        $this->intl->t(
          "We couldn’t find any {image_name_plural} matching your filter criteria, or there simply aren’t any {image_name_plural} available. Would you like to {0}upload a new {image_name}{1}?",
          [
            "<a href='{$this->intl->r("/movie/{0}/{$this->routeKey}/upload", [ $this->movie->id ])}'>", "</a>",
            "image_name"        => $this->name,
            "image_name_plural" => $this->namePlural,
          ]
        ),
        $this->intl->t("No {image_name_plural}", [ "image_name_plural" => $this->namePlural ]),
        Alert::SEVERITY_INFO
      );
    }

    // Get all images of the current movie and go through them to create the image grid list.
    $images = call_user_func("{$this->class}::getImages", $this->movie->id, $this->paginationOffset, $this->paginationLimit);
    $list   = null;

    /* @var $image \MovLib\Data\Image\AbstractMovieImage */
    while ($image = $images->fetch_object($this->class, [ $this->movie->id, $this->movie->displayTitleWithYear ])) {
      // Some images have country information attached which we want to display in the listing with a small icon.
      $country = null;
      if ($image->countryCode) {
        $country = (new Country($image->countryCode, [ "itemprop" => "contentLocation" ]))->getFlag();
      }

      // Put the image's list entry together and continue with next result.
      $list .=
        "<li class='mb20 s s2 tac' itemscope itemtype='http://schema.org/ImageObject'>{$this->getImage(
          $image->getStyle(AbstractMovieImage::STYLE_SPAN_02),
          true,
          [ "itemprop" => "thumbnail" ],
          [ "itemprop" => "url" ]
        )}{$country} {$this->intl->t("{width} × {height}", [
          // The length unit is mandatory for distances: http://schema.org/Distance
          "width"  => "<span itemprop='width'>{$image->width}<span class='vh'> px</span></span>",
          "height" => "<span itemprop='height'>{$image->height}<span class='vh'> px</span></span>",
        ])}</li>"
      ;
    }

    // Put it all together and we're done.
    return "<div id='filter'>LIMIT {$this->paginationLimit} OFFSET {$this->paginationLimit}</div><ol class='grid-list no-list r'>{$list}</ol>";
  }

  /**
   * Initialize the movie gallery sidebar.
   *
   * @return this
   */
  protected function initSidebar() {
    // Compile arguments array once.
    $args = [ $this->movie->id ];

    // Create array containing all available movie image types and their sidebar menuitems. We need all of them because
    // we add the non active ones at the bottom of the sidebar navigation for easy switching between the different types.
    $typePages = [
      "posters"     => [ $this->intl->rp("/movie/{0}/posters", $args), $this->intl->t("Posters"), [ "class" => "ico ico-poster" ] ],
      "lobby-cards" => [ $this->intl->rp("/movie/{0}/lobby-cards", $args), $this->intl->t("Lobby Cards"), [ "class" => "ico ico-lobby-card" ] ],
      "backdrops"   => [ $this->intl->rp("/movie/{0}/backdrops", $args), $this->intl->t("Backdrops"), [ "class" => "ico ico-image" ] ],
    ];

    // Initialize the sidebar menuitems with the menuitem for the current movie image type first and the corresponding
    // upload page second.
    $sidebarMenuitems = [
      $typePages[$this->routeKeyPlural],
      [ $this->intl->r("/movie/{0}/{$this->routeKey}/upload", $args), $this->intl->t("Upload"), [ "class" => "ico ico-upload" ] ],
      [ $this->movie->route, $this->intl->t("Back to movie"), [ "class" => "ico ico-movie separator" ] ],
    ];

    // Remove the current movie image sidebar menuitem from the movie image types array and iterate over the remaining
    // pages and add them to the sidebar menuitems.
    unset($typePages[$this->routeKeyPlural]);
    foreach ($typePages as $sidebarMenuitem) {
      $sidebarMenuitems[] = $sidebarMenuitem;
    }
    // We could easily add the separator class to the last item without knowing its index.
    //$this->addClass("separator", $sidebarMenuitems[count($sidebarMenuitems) - 1]);

    // Initialize the sidebar with the menuitems.
    return $this->sidebarInit($sidebarMenuitems);
  }

}
