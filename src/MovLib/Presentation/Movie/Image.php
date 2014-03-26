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

use \MovLib\Data\Image\AbstractMovieImage;
use \MovLib\Data\Movie\Movie;
use \MovLib\Data\User\User;
use \MovLib\Presentation\Partial\Country;
use \MovLib\Presentation\Partial\Date;
use \MovLib\Presentation\Partial\DateTime;
use \MovLib\Presentation\Partial\Language;
use \MovLib\Presentation\TraitDeletionRequest;

/**
 * Abstract base class for all movie image presentations.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Image extends \MovLib\Presentation\Movie\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The amount of images to the left and right of the currently presented image in the image stream.
   *
   * @var integer
   */
  const IMAGE_STREAM_COUNT = 4;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The class name of the movie image.
   *
   * @var string
   */
  protected $class;

  /**
   * Total image count.
   *
   * @var integer
   */
  protected $count = 0;

  /**
   * Position of the current image within all images.
   *
   * @var integer
   */
  protected $current;

  /**
   * The movie image we are currently presenting.
   *
   * @var \MovLib\Data\Image\AbstractMovieImage
   */
  protected $image;

  /**
   * Rendered image stream.
   *
   * @var string
   */
  protected $streamImages;

  /**
   * Rendered previous link.
   *
   * @var string
   */
  protected $streamPrevious;

  /**
   * Rendered next link.
   *
   * @var string
   */
  protected $streamNext;

  /**
   * All available images as JSON string for our JavaScript.
   *
   * @var string
   */
  protected $streamJSON;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie image presentation.
   *
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct() {
    // Try to load the movie and export all variables to class scope.
    $this->movie = new Movie((integer) $_SERVER[ "MOVIE_ID" ]);
    $this->class = "\\MovLib\\Data\\Image\\Movie{$_SERVER["IMAGE_CLASS"]}";
    $this->image = new $this->class($this->movie->id, $this->movie->displayTitleWithYear, (integer) $_SERVER["IMAGE_ID"]);

    // We have to build the image stream right away because we need to know how many images we have in total and the
    // position of our image within all the images.
    $images = call_user_func("{$this->class}::getImages", $this->movie->id);
    $more   = $streamArray = null;

    /* @var $image \MovLib\Data\Image\AbstractMovieImage */
    while ($image = $images->fetch_object($this->class, [ $this->movie->id, $this->movie->displayTitleWithYear ])) {
      // We have to go through all images to create the JSON data for our JavaScript, which means we can easily keep
      // count as well.
      $this->count++;

      // If this is the current image start building the visible image stream.
      if ($image->id === $this->image->id) {
        // We found the position of our current image.
        $this->current = $this->count;

        // Only go through all of this if the $streamArray isn't still NULL / empty.
        if ($streamArray) {
          // Get the last four images that were added to the stream array.
          $streamArray = array_slice($streamArray, -self::IMAGE_STREAM_COUNT, self::IMAGE_STREAM_COUNT);

          // Count them, we desperately need the exact amount of images.
          if (($c = count($streamArray)) < self::IMAGE_STREAM_COUNT) {
            // Create a copy of the images we have.
            $streamCopy  = $streamArray;

            // Create new empty array that we can fill with the correct offsets.
            $streamArray = [];

            // The formula is easy, we take the current index and add the result of the total available places minus the
            // total count of available elements which gives us the new position.
            $x = self::IMAGE_STREAM_COUNT - $c;
            for ($i = $c - 1; $i >= 0; --$i) {
              $streamArray[$i + $x] = $streamCopy[$i];
            }
          }
        }

        // Finally add the current image to the stream array exactly in the middle of the stream.
        $streamArray[self::IMAGE_STREAM_COUNT] = $image;
        $more                                  = self::IMAGE_STREAM_COUNT;
      }
      // $more has either to be NULL or greater than zero for us to put another image into it.
      elseif (!$more || $more > 0) {
        $streamArray[] = $image;

        // If $more isn't NULL decrease it to ensure that we reach zero and don't add too many images to this array
        // for no reason.
        if ($more) {
          --$more;
        }
      }

      // The identifier and the absolute URL are more than enough to build all images and their links with JS. We add
      // every image to the JSON array because it makes things easy as soon as we stop page loads and directly navigate
      // the image stream with JS as each anchor/image combination within the stream directly corresponds to the offset
      // within this array.
      $this->streamJSON[] = [ "id" => $image->id, "src" => $image->getStyle()->src ];
    }

    $images->free();
    $this->streamJSON = json_encode($this->streamJSON);

    // Format the visible images.
    $c = self::IMAGE_STREAM_COUNT * 2 + 1;
    for ($i = 0; $i < $c; ++$i) {
      $image               = empty($streamArray[$i]) ? null : $this->getImage($streamArray[$i]->getStyle(AbstractMovieImage::STYLE_SPAN_01_SQUARE));
      $this->streamImages .= "<div class='s s1'>{$image}</div>";
    }

    // Check if we have a previous image.
    $this->streamPrevious = self::IMAGE_STREAM_COUNT - 1;
    if (!empty($streamArray[$this->streamPrevious])) {
      $this->streamPrevious = [ "class" => "ico ico-chevron-left s s1", "href" => $streamArray[$this->streamPrevious]->route, "rel" => "previous" ];
    }
    else {
      $this->streamPrevious = [ "aria-hidden" => "true", "class" => "ico ico-chevron-left mute s s1" ];
    }

    // Check if we have a next image.
    $this->streamNext = self::IMAGE_STREAM_COUNT + 1;
    if (!empty($streamArray[$this->streamNext])) {
      $this->streamNext = [ "class" => "ico ico-chevron-right s s1 tar", "href" => $streamArray[$this->streamNext]->route, "rel" => "next" ];
    }
    else {
      $this->streamNext = [ "aria-hidden" => "true", "class" => "ico ico-chevron-right mute s s1 tar" ];
    }

    // Initialize the breadcrumb ...
    $this->initBreadcrumb([[ $this->intl->rp("/movie/{0}/{$this->image->routeKeyPlural}", [ $this->movie->id ]), $this->image->namePlural ]]);
    $this->breadcrumbTitle = "{$this->image->name} {$this->current}";

    // Translate title once, we don't need Intl formatting for the numbers.
    $title  = $this->intl->t("{image_name} {current} of {total} from {title}");
    $search = [ "{image_name}", "{current}", "{total}", "{title}" ];

    // ... initialize page and extend the page's visible title with a link and micro-data ...
    $this->initPage(str_replace($search, [ $this->image->name, $this->current, $this->count, $this->movie->displayTitleWithYear ], $title));
    $pageTitle = "<span itemprop='name'{$this->lang($this->movie->displayTitleLanguageCode)}>{$this->movie->displayTitle}</span>";
    if ($this->movie->year) {
      $pageTitle = $this->intl->t("{0} ({1})", [ $pageTitle, "<span itemprop='datePublished'>{$this->movie->year}</span>" ]);
    }
    $this->pageTitle = str_replace($search, [
      $this->image->name,
      $this->current,
      $this->count,
      "<span itemscope itemtype='http://schema.org/Movie'><a href='{$this->movie->route}' itemprop='url'>{$pageTitle}</a>",
    ], $title);

    // ... initialize small sidebar ...
    $this->initSidebar();
    $this->sidebarSmall = true;

    // ... and finally the language links, CSS class, schema and stylesheet.
    $this->initLanguageLinks("/movie/{0}/{$this->image->routeKey}/{1}", [ $this->movie->id, $this->image->id ]);
    $this->bodyClasses    .= " imagedetails";
    $this->schemaType      = "ImageObject";
    $kernel->stylesheets[] = "imagedetails";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the page's content.
   *
   * @return string
   */
  protected function getPageContent() {
    // Add large button to the header to ensure that nobody has to search for the upload page.
    $this->headingBefore = "<a class='btn btn-large btn-success fr' href='{$this->intl->r("/movie/{0}/{$this->image->routeKey}/upload", [ $this->movie->id ])}'>{$this->intl->t("Upload New")}</a>";

    // Format the optional fields.
    $dl = null;
    if (!empty($this->image->description)) {
      $dl .= "<dt>{$this->intl->t("Description")}</dt><dd itemprop='description'>{$this->htmlDecode($this->image->description)}</dd>";
    }
    if (!empty($this->image->publishingDate)) {
      $date = new Date($this->image->publishingDate);
      $dl  .= "<dt>{$this->intl->t("Publishing Date")}</dt><dd>{$date->format([ "itemprop" => "datePublished" ])}</dd>";
    }
    if (!empty($this->image->authors)) {
      $dl .= "<dt>{$this->intl->t("Author")}</dt><dd itemprop='copyrightHolder'>{$this->htmlDecode($this->image->authors)}</dd>";
    }
    if ($this->image->countryCode) {
      $country = (new Country($this->image->countryCode, [ "itemprop" => "contentLocation" ]))->getFlag(true);
      $dl     .= "<dt>{$this->intl->t("Country")}</dt><dd>{$country}</dd>";
    }
    if ($this->image->languageCode && $this->image->languageCode != "xx") {
      $language = new Language($this->image->languageCode, [ "itemprop" => "inLanguage" ]);
      $dl      .= "<dt>{$this->intl->t("Language")}</dt><dd>{$language}</dd>";
    }
    $uploader = new User(User::FROM_ID, $this->image->uploaderId);
    $dateTime = new DateTime($this->image->changed, [ "itemprop" => "uploadDate" ]);

    $offers = null;
    // @todo Build shop links
    if (!$offers) {
      $offers = "<dd>{$this->intl->t("No links have been added to this {image_name}, {0}add one{1}?", [
        "<a href='{$this->intl->r("/movie/{0}/{$this->image->routeKey}/{1}/edit", [
          $this->movie->id, $this->image->id,
        ])}'>", "</a>", "image_name" => $this->image->name,
      ])}</dd>";
    }

    // Render the final presentation.
    return
      "<meta itemprop='representativeOfPage' content='true'>" .
      "<div class='c' id='imagedetails'>" .
        "<script>{$this->streamJSON}</script>" .
        "<div class='cf stream'>" .
          "<a{$this->expandTagAttributes($this->streamPrevious)}><span class='vh'>{$this->intl->t("Previous {image_name}", [ "image_name" => $this->image->name ])}</span></a>" .
          $this->streamImages .
          "<a{$this->expandTagAttributes($this->streamNext)}><span class='vh'>{$this->intl->t("Next {image_name}", [ "image_name" => $this->image->name ])}</span></a>" .
        "</div>" .
        TraitDeletionRequest::getDeletionRequestedAlert($this->image->deletionId) .
        "<div class='r wrapper'>" .
          "<div class='s s8 tac image'>{$this->getImage(
            $this->image->getStyle(AbstractMovieImage::STYLE_SPAN_07),
            $this->image->getURL(),
            [ "itemprop" => "thumbnailUrl" ],
            [ "itemprop" => "contentUrl", "target" => "_blank" ]
          )}</div>" .
          "<dl class='s s4 description'>" .
            $dl .
            "<dt>{$this->intl->t("Provided by")}</dt><dd><a href='{$uploader->route}' itemprop='creator provider'>{$uploader->name}</a></dd>" .
            "<dt>{$this->intl->t("Dimensions")}</dt><dd>{$this->intl->t("{width} × {height}", [
              "width"  => "<span itemprop='width'>{$this->image->width}&nbsp;<abbr title='{$this->intl->t("Pixel")}'>px</abbr></span>",
              "height" => "<span itemprop='height'>{$this->image->height}&nbsp;<abbr title='{$this->intl->t("Pixel")}'>px</abbr></span>",
            ])}</dd>" .
            "<dt>{$this->intl->t("File size")}</dt><dd itemprop='contentSize'>{$this->intl->t("{0,number} {1}", $this->formatBytes($this->image->filesize))}</dd>" .
            "<dt>{$this->intl->t("Upload on")}</dt><dd>{$dateTime}</dd>" .
            "<dt>{$this->intl->t("Buy this {image_name}", [ "image_name" => $this->image->name ])}</dt>{$offers}" .
          "</dl>" .
        "</div>" .
      "</div>"
    ;
  }

  /**
   * Initialize the movie image presentation's sidebar.
   *
   * @return this
   */
  protected function initSidebar() {
    $args = [ $this->movie->id, $this->image->id ];
    return $this->sidebarInit([
      [ $this->image->route, $this->intl->t("View"), [ "class" => "ico ico-view" ] ],
      [ $this->intl->r("/movie/{0}/{$this->image->routeKey}/{1}/edit", $args), $this->intl->t("Edit"), [ "class" => "ico ico-edit" ] ],
      [ $this->intl->r("/movie/{0}/{$this->image->routeKey}/{1}/history", $args), $this->intl->t("History"), [ "class" => "ico ico-history" ] ],
      [ $this->intl->r("/movie/{0}/{$this->image->routeKey}/{1}/delete", $args), $this->intl->t("Delete"), [ "class" => "delete ico ico-delete" ] ]
    ]);
  }

}
