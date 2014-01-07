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
use \MovLib\Data\User\User;
use \MovLib\Presentation\Partial\Country;
use \MovLib\Presentation\Partial\DateTime;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\Button;
use \MovLib\Presentation\Partial\Language;

/**
 * Present a single movie image.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Image extends \MovLib\Presentation\Movie\Images {
  use \MovLib\Presentation\TraitFormPage;


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * How many images we want within our image stream before and after the showed image.
   *
   * @var integer
   */
  const STREAM_IMAGE_COUNT = 4;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The movie image to present.
   *
   * @var \MovLib\Data\Image\MovieImage
   */
  protected $image;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the page's content.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @return string
   */
  protected function getPageContent() {
    global $i18n, $kernel;

    // Build the form to like this image.
    $this->form = new Form($this, [ new Button("like", "<span class='vh'>{$i18n->t("")}</span>", []) ]);

    // Build the image stream.
    $images      = $this->movie->getImageStreamResult($this->imageTypeId);
    $streamArray = $streamJSON = $more = null;

    /* @var $image \MovLib\Data\Image\MovieImage */
    while ($image = $images->fetch_object("\\MovLib\\Data\\Image\\Movie{$this->imageClassName}", [ $this->movie->id, $this->movie->displayTitleWithYear ])) {
      // If this is the current image start building the visible image stream.
      if ($image->id === $this->image->id) {
        // Only go through all of this if the $streamArray isn't still NULL / empty.
        if ($streamArray) {
          // Get the last four images that were added to the stream array.
          $streamArray = array_slice($streamArray, -self::STREAM_IMAGE_COUNT, self::STREAM_IMAGE_COUNT);

          // Count them, we desperately need four images.
          if (($c = count($streamArray)) < self::STREAM_IMAGE_COUNT) {
            // Create a copy of the images we have.
            $streamCopy  = $streamArray;

            // Create new empty array that we can fill with the correct offsets.
            $streamArray = [];

            // The formula is easy, we take the current index and add the result of the total available places minus the
            // total count of available elements which gives us the new position.
            $x = self::STREAM_IMAGE_COUNT - $c;
            for ($i = $c - 1; $i >= 0; --$i) {
              $streamArray[$i + $x] = $streamCopy[$i];
            }
          }
        }

        // Finally add the current image to the stream array exactly in the middle of the stream.
        $streamArray[self::STREAM_IMAGE_COUNT] = $image;
        $more                                  = self::STREAM_IMAGE_COUNT;
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
      $streamJSON[] = [ "id" => $image->id, "src" => $image->getStyle()->src ];
    }

    $images->free();
    $streamJSON = json_encode($streamJSON);

    // Format the visible images.
    $stream = null;
    $c      = self::STREAM_IMAGE_COUNT * 2 + 1;
    for ($i = 0; $i < $c; ++$i) {
      $image   = empty($streamArray[$i]) ? null : $this->getImage($streamArray[$i]->getStyle(MovieImage::STYLE_SPAN_01_SQUARE));
      $stream .= "<div class='s s1'>{$image}</div>";
    }

    // Check if we have a previous image.
    $previous = self::STREAM_IMAGE_COUNT - 1;
    if (!empty($streamArray[$previous])) {
      $previous = [ "class" => "ico ico-chevron-left s s1", "href" => $streamArray[$previous]->route, "rel" => "previous" ];
    }
    else {
      $previous = [ "aria-hidden" => "true", "class" => "ico ico-chevron-left mute s s1" ];
    }

    // Check if we have a next image.
    $next = self::STREAM_IMAGE_COUNT + 1;
    if (!empty($streamArray[$next])) {
      $next = [ "class" => "ico ico-chevron-right s s1 tar", "href" => $streamArray[$next]->route, "rel" => "next" ];
    }
    else {
      $next = [ "aria-hidden" => "true", "class" => "ico ico-chevron-right mute s s1 tar" ];
    }

    // Format the optional fields.
    $dl = null;
    if (!empty($this->image->description)) {
      $dl .= "<dt>{$i18n->t("Description")}</dt><dd itemprop='text'>{$kernel->htmlDecode($this->image->description)}</dd>";
    }
    if ($this->image->countryCode) {
      $country = (new Country($this->image->countryCode, [ "itemprop" => "contentLocation" ]))->getFlag(true);
      $dl .= "<dt>{$i18n->t("Country")}</dt><dd>{$country}</dd>";
    }
    if ($this->image->languageCode && $this->image->languageCode != "xx") {
      $language = new Language($this->image->languageCode, [ "itemprop" => "inLanguage" ]);
      $dl .= "<dt>{$i18n->t("Language")}</dt><dd>{$language}</dd>";
    }
    $uploader = new User(User::FROM_ID, $this->image->uploaderId);
    $dateTime = new DateTime($this->image->changed, [ "itemprop" => "uploadDate" ]);

    // Render the final presentation.
    return
      "<meta itemprop='representativeOfPage' content='true'>" .
      "<div class='c' id='imagedetails'>" .
        "<script>{$streamJSON}</script>" .
        "<div class='cf stream'>" .
          "<a{$this->expandTagAttributes($previous)}><span class='vh'>{$i18n->t("Previous {image_type_name}", [
            "image_type_name" => $this->imageTypeName
          ])}</span></a>" .
          $stream .
          "<a{$this->expandTagAttributes($next)}><span class='vh'>{$i18n->t("Next {image_type_name}", [
            "image_type_name" => $this->imageTypeName
          ])}</span></a>" .
        "</div>" .
        "<div class='r wrapper'>" .
          "<div class='s s8 tac image'>{$this->getImage(
            $this->image->getStyle(MovieImage::STYLE_SPAN_07),
            $this->image->getURL(),
            [ "itemprop" => "thumbnail" ],
            [ "itemprop" => "contentUrl", "target" => "_blank" ]
          )}</div>" .
          "<dl class='s s4 description'>" .
            $dl .
            "<dt>{$i18n->t("Uploader")}</dt><dd><a href='{$uploader->route}' itemprop='provider'>{$uploader->name}</a></dd>" .
            "<dt>{$i18n->t("Dimensions")}</dt><dd>{$i18n->t("{width} × {height}", [
              "width"  => "<span itemprop='width'>{$this->image->width}&nbsp;<abbr title='{$i18n->t("Pixel")}'>px</abbr></span>",
              "height" => "<span itemprop='height'>{$this->image->height}&nbsp;<abbr title='{$i18n->t("Pixel")}'>px</abbr></span>",
            ])}</dd>" .
            "<dt>{$i18n->t("File Size")}</dt><dd itemprop='contentSize'>{$i18n->t("{0,number} {1}", $this->formatBytes($this->image->filesize))}</dd>" .
            "<dt>{$i18n->t("Uploaded")}</dt><dd>{$dateTime}</dd>" .
          "</dl>" .
        "</div>" .
      "</div>"
    ;
  }

  /**
   * Initialize the movie image show page.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @return this
   */
  protected function initImagePage() {
    global $i18n, $kernel;

    $kernel->stylesheets[]             = "imagedetails";
    $formattedId                       = $i18n->format("{0,number,integer}", [ $_SERVER["IMAGE_ID"] ]);
    $this->initMoviePage($i18n->t("{image_type_name} {id}", [ "id" => $formattedId, "image_type_name" => $this->imageTypeName ]));
    $class                             = "\\MovLib\\Data\\Image\\Movie{$this->imageClassName}";
    $this->image                       = new $class($this->movie->id, $this->movie->displayTitleWithYear, (integer) $_SERVER["IMAGE_ID"]);
    $this->initPage($i18n->t("{image_type_name} {id} of {title}", [
      "image_type_name" => $this->imageTypeName,
      "id"              => $formattedId,
      "title"           => $this->movie->displayTitleWithYear,
    ]));
    $this->initLanguageLinks("/movie/{0}/{$this->routeKey}/upload", [ $this->movie->id ]);
    $this->pageTitle                   = $i18n->t("{image_type_name} {id} of {title}", [
      "image_type_name" => $this->imageTypeName,
      "id"              => $formattedId,
      "title"           => "<a href='{$this->movie->route}'>{$this->movie->displayTitleWithYear}</a>",
    ]);
    $this->breadcrumb->menuitems[]     = [ $i18n->rp("/movie/{0}/{$this->routeKeyPlural}", [ $this->movie->id ]), $this->imageTypeNamePlural ];
    $this->bodyClasses                .= " imagedetails";
    $this->schemaType                  = "ImageObject";
    $this->initSidebar()->smallSidebar = true;

    return $this;
  }

  /**
   * Initialize the movie image details sidebar.
   *
   * @return this
   */
  protected function initSidebar() {
    global $i18n;
    // Combile arguments array once.
    $args = [ $this->movie->id, $this->image->id ];

    return $this->initSidebarTrait([
      [ $i18n->r("/movie/{0}/{$this->routeKey}/{1}", $args), $i18n->t("View"), [ "class" => "ico ico-view" ] ],
      [ $i18n->r("/movie/{0}/{$this->routeKey}/{1}/edit", $args), $i18n->t("Edit"), [ "class" => "ico ico-edit" ] ],
      [ $i18n->r("/movie/{0}/{$this->routeKey}/{1}/history", $args), $i18n->t("History"), [ "class" => "ico ico-history" ] ],
      [ $i18n->r("/movie/{0}/{$this->routeKey}/{1}/delete", $args), $i18n->t("Delete"), [ "class" => "delete ico ico-delete" ] ],
    ]);
  }

  /**
   * Valid form callback.
   *
   * @return this
   */
  protected function valid() {
    return $this;
  }

}
