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


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The image class's variable name suffix (e.g. <code>"LobbyCard"</code>).
   *
   * @var string
   */
  protected $imageClassName;

  /**
   * The image type's unique identifier.
   *
   * @var string
   */
  protected $imageTypeId;

  /**
   * The image type's translated name (e.g. <code>"Lobby Card"</code>).
   *
   * @var string
   */
  protected $imageTypeName;

  /**
   * The image type's translated plural name (e.g. <code>"Lobby Cards"</code>).
   *
   * @var string
   */
  protected $imageTypeNamePlural;

  /**
   * The image route's variable name part (e.g. <code>"lobby-card"</code>).
   *
   * @var string
   */
  protected $routeKey;

  /**
   * The image route's variable plural name part (e.g. <code>"lobby-cards"</code>.
   *
   * @var string
   */
  protected $routeKeyPlural;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie images presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct() {
    global $i18n;
    $this->imageClassName      = "Image";
    $this->imageTypeId         = MovieImage::TYPE_ID;
    $this->imageTypeName       = $i18n->t("Image");
    $this->imageTypeNamePlural = $i18n->t("Images");
    $this->routeKey            = "image";
    $this->routeKeyPlural      = "images";
    $this->initImagePage();
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
    $images = $this->movie->getImageResult($this->imageTypeId, $this->resultsOffset, $this->resultsPerPage);
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
      $i18n->t(
        "We couldn’t find any {image_type_name_plural} matching your filter criteria, or there simply aren’t any {image_type_name_plural} available. Would you like to {0}upload a new {image_type_name}{1}?",
        [
          0                        => "<a href='{$i18n->r("/movie/{0}/{$this->routeKey}/upload", [ $this->movie->id ])}'>",
          1                        => "</a>",
          "image_type_name"        => $this->imageTypeName,
          "image_type_name_plural" => $this->imageTypeNamePlural
        ]
      ),
      $i18n->t("No {image_type_name_plural}", [ "image_type_name_plural" => $this->imageTypeNamePlural ]),
      Alert::SEVERITY_INFO
    );
  }

  /**
   * Initialize the gallery presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @return this
   */
  protected function initImagePage() {
    global $i18n, $kernel;
    $kernel->stylesheets[] = "image-grid";
    $this->initMoviePage($this->imageTypeNamePlural);
    $this->initPage($i18n->t("{image_type_name} for {title}", [
        "image_type_name" => $this->imageTypeNamePlural,
        "title"           => $this->movie->displayTitleWithYear,
    ]));
    $this->initLanguageLinks("/movie/{0}/{$this->routeKeyPlural}", [ $this->movie->id ], true);
    $this->initPagination($this->movie->getImageCount($this->imageTypeId));
    $this->pageTitle           = $i18n->t("{image_type_name} for {title}", [
      "image_type_name" => $this->imageTypeNamePlural,
      "title"           => "<a href='{$this->movie->route}'>{$this->movie->displayTitleWithYear}</a>",
    ]);
    return $this->initSidebar();
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

    // Create array containing all available movie image types and their sidebar menuitems. We need all of them because
    // we add the non active ones at the bottom of the sidebar navigation for easy switching between the different types.
    $typePages = [
      MoviePoster::TYPE_ID    => [ $i18n->rp("/movie/{0}/posters", $args), $i18n->t("Posters"), [ "class" => "ico ico-poster" ] ],
      MovieLobbyCard::TYPE_ID => [ $i18n->rp("/movie/{0}/lobby-cards", $args), $i18n->t("Lobby Cards"), [ "class" => "ico ico-lobby-card" ] ],
      MovieImage::TYPE_ID     => [ $i18n->rp("/movie/{0}/images", $args), $i18n->t("Images"), [ "class" => "ico ico-image" ] ],
    ];

    // Initialize the sidebar menuitems with the menuitem for the current movie image type first and the corresponding
    // upload page second.
    $sidebarMenuitems = [
      $typePages[$this->imageTypeId],
      [ $i18n->r("/movie/{0}/{$this->routeKey}/upload", $args), $i18n->t("Upload"), [ "class" => "ico ico-upload" ] ],
      [ $this->movie->route, $i18n->t("Back to movie"), [ "class" => "ico ico-movie separator" ] ],
    ];

    // Remove the current movie image sidebar menuitem from the movie image types array and iterate over the remaining
    // pages and add them to the sidebar menuitems.
    unset($typePages[$this->imageTypeId]);
    foreach ($typePages as $sidebarMenuitem) {
      $sidebarMenuitems[] = $sidebarMenuitem;
    }
    // We could easily add the separator class to the last item without knowing its index.
    //$this->addClass("separator", $sidebarMenuitems[count($sidebarMenuitems) - 1]);

    // Initialize the sidebar with the menuitems.
    return $this->initSidebarTrait($sidebarMenuitems);
  }

}
