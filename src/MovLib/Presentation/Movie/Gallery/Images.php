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

use \MovLib\Data\Image\MovieImage;
use \MovLib\Data\Image\MovieLobbyCard;
use \MovLib\Data\Image\MoviePoster;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Country;

/**
 * Movie images gallery presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Images extends \MovLib\Presentation\Movie\AbstractMoviePage {
  use \MovLib\Presentation\TraitPagination;


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The movie image's type identifier.
   *
   * @var integer
   */
  const TYPE_ID = MovieImage::TYPE_ID;

  /**
   * The name of the image class to instantiate in the loop.
   *
   * @var string
   */
  private $imageClassName;

  /**
   * The plural route key for this gallery.
   *
   * @var string
   */
  private $routeKeyPlural;

  /**
   * The route key for this gallery's images.
   *
   * @var string
   */
  private $routeKey;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie images presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct() {
    global $i18n;
    $this->init($i18n->t("Images"));
    $this->initGallery(
      "images",
      "image",
      "Image",
      $i18n->t("Images for {title}", [ "title" => $this->movie->displayTitleWithYear ]),
      $i18n->t("Images for {title}", [ "title" => "<a href='{$this->movie->route}'>{$this->movie->displayTitleWithYear}</a>" ])
    );
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getPageContent() {
    global $i18n;

    // Add large button to the header to ensure that nobody has to search for the upload page.
    $this->headingBefore = "<a class='btn btn-large btn-success fr' href='{$i18n->r("/movie/{0}/{$this->routeKey}/upload", [ $this->movie->id ])}'>{$i18n->t("Upload New")}</a>";

    // Get all images of the current movie image type and go through them to create the image grid.
    $images = $this->movie->getImageResult(static::TYPE_ID, $this->resultsOffset, $this->resultsPerPage);
    $list   = null;
    /* @var $image \MovLib\Data\Image\MovieImage */
    while ($image = $images->fetch_object("\\MovLib\\Data\\Image\\Movie{$this->imageClassName}", [ $this->movie->id, $this->movie->displayTitleWithYear ])) {
      $country = null;
      if ($image->countryCode) {
        $country = (new Country($image->countryCode, [ "itemprop" => "contentLocation" ]))->getFlag();
      }
      $list .=
        "<li class='s s2 tac' itemscope itemtype='http://schema.org/ImageObject'>{$this->getImage(
          $image->getStyle(MovieImage::STYLE_SPAN_02),
          true,
          [ "class" => "grid-img", "itemprop" => "thumbnail" ],
          [ "itemprop" => "url" ]
        )}{$country} {$i18n->t("{width} × {height}", [
          // The length unit is mandatory for distances: http://schema.org/Distance
          "width"  => "<span itemprop='width'>{$image->width}<span class='vh'> px</span></span>",
          "height" => "<span itemprop='height'>{$image->height}<span class='vh'> px</span></span>",
        ])}</li>"
      ;
    }
    if ($list) {
      return "<div id='filter'>LIMIT {$this->resultsPerPage} OFFSET {$this->resultsPerPage}</div><ol class='img-grid no-list r'>{$list}</ol>";
    }
    return new Alert(
      $i18n->t("We couldn’t find any images matching your filter criteria, or there simply aren’t any images available. Would you like to {0}upload a new image{1}?", [
        "<a href='{$i18n->r("/movie/{0}/{$this->routeKey}/upload", [ $this->movie->id ])}'>", "</a>"
      ]),
      $i18n->t("No Images"),
      Alert::SEVERITY_INFO
    );
  }

  /**
   * Initialize the gallery presentation.
   *
   * @param string $routeKeyPlural
   *   The plural route key without the <code>"/movie/{0}/"</code> part.
   * @param string $routeKey
   *   The route key without the <code>"/movie/{0}/"</code> part.
   * @param string $imageClassName
   *   The image class's name without namespace and the <code>"Movie"</code> prefix.
   * @param string $title
   *   The title for the <code><title></code> tag.
   * @param string $pageTitle
   *   The title for the <code><h1></code> tag.
   * @return this
   */
  protected function initGallery($routeKeyPlural, $routeKey, $imageClassName, $title, $pageTitle) {
    $this->routeKeyPlural = $routeKeyPlural;
    $this->routeKey       = $routeKey;
    $this->imageClassName = $imageClassName;
    $this->initPage($title);
    $this->initLanguageLinks("/movie/{0}/{$routeKeyPlural}", [ $this->movie->id ], true);
    $this->initPagination($this->movie->getImageCount(static::TYPE_ID));
    $this->pageTitle      = $pageTitle;
    return $this;
  }

  /**
   * Initialize the movie gallery sidebar.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return this
   */
  protected function initSidebar() {
    global $i18n;
    // Compile arguments array once.
    $args = [ $this->movie->id ];

    // Create array containing all available movie image types and their sidebar menuitems.
    $typePages = [
      MovieImage::TYPE_ID     => [ $i18n->rp("/movie/{0}/images", $args), $i18n->t("Images"), [ "class" => "ico ico-image" ] ],
      MoviePoster::TYPE_ID    => [ $i18n->rp("/movie/{0}/posters", $args), $i18n->t("Posters"), [ "class" => "ico ico-poster" ] ],
      MovieLobbyCard::TYPE_ID => [ $i18n->rp("/movie/{0}/lobby-cards", $args), $i18n->t("Lobby Cards"), [ "class" => "ico ico-lobby-card" ] ],
    ];

    // Initialize the sidebar menuitems with the menuitem for the current movie image type first and the corresponding
    // upload page second.
    $sidebarMenuitems = [
      $typePages[static::TYPE_ID],
      [ $i18n->r("/movie/{0}/{$this->routeKey}/upload", $args), $i18n->t("Upload"), [ "class" => "ico ico-upload" ] ],
      [ $this->movie->route, $i18n->t("Back to movie"), [ "class" => "ico ico-movie separator" ] ],
    ];

    // Remove the current movie image sidebar menuitem from the movie image types array and iterate over the remaining
    // pages and add them to the sidebar menuitems.
    unset($typePages[static::TYPE_ID]);
    foreach ($typePages as $sidebarMenuitem) {
      $sidebarMenuitems[] = $sidebarMenuitem;
    }
    // We could easily add the separator class to the last item without knowing its index.
    //$this->addClass("separator", $sidebarMenuitems[count($sidebarMenuitems) - 1]);

    // Initialize the sidebar with the menuitems.
    return $this->initSidebarTrait($sidebarMenuitems);
  }

}
