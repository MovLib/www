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
namespace MovLib\Presentation\Gallery;

use \IntlDateFormatter;
use \MovLib\Data\Image\Movie as MovieImage;
use \MovLib\Exception\Client\RedirectSeeOtherException;
use \MovLib\Presentation\Partial\Alert;

/**
 * Trait for all movie galleries.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitMovieGallery {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getBreadcrumbs() {
    global $i18n;
    return [
      [ $i18n->r("/movies"), $i18n->t("Movies"), [ "title" => $i18n->t("Have a look at the latest {0} entries at MovLib.", [ $i18n->t("movie") ]) ] ],
      [ $i18n->r("/movie/{0}", [ $_SERVER["MOVIE_ID"] ]), $this->entityTitle ],
    ];
  }

  /**
   * @inheritdoc
   */
  protected function getGoneContent() {
    global $i18n;
    throw new RedirectSeeOtherException($i18n->r("/movie/{0}", [ $this->model->id ]));
  }

  /**
   * @inheritdoc
   */
  protected function getImageDetails() {
    global $i18n, $session;
    $details = $this->image->getImageDetails();
    if (empty($details["description"])) {
      $details["description"] = new Alert("{$i18n->t("No {0} available, could you provide one?", [ $i18n->t("Description") ])} {$this->a(
        $i18n->r("/movie/{0}/{$_SERVER["TAB"]}/{1}/edit", [ $this->model->id, $this->image->imageId ]),
        $i18n->t("Click here to do so.")
      )}");
    }
    $desc = [[ $i18n->t("Description"), $details["description"] ]];
    if ($this->image->type !== MovieImage::IMAGETYPE_PHOTO) {
      if (empty($details["country"])) {
        $details["country"] = new Alert("{$i18n->t("No {0} available, could you provide one?", [ $i18n->t("Country") ])} {$this->a(
          $i18n->r("/movie/{0}/{$_SERVER["TAB"]}/{1}/edit", [ $this->model->id, $this->image->imageId ]),
          $i18n->t("Click here to do so.")
        )}");
      }
      else {
        $details["country"] = $this->a($i18n->r("/country/{0}", [ $details["country"]["code"] ]), $details["country"]["name"]);
      }
      $desc[] = [ $i18n->t("Country"), $details["country"] ];
    }
    $desc[] = [ $i18n->t("Dimensions"), $i18n->t("{0} × {1} pixels", [ $details["imageWidth"], $details["imageHeight"] ]) ];
    $desc[] = [ $i18n->t("Size"), msgfmt_format_message($i18n->locale, "{0,number,integer}", [ $details["imageSize"] ]) ];
    $desc[] = [ $i18n->t("User"), $this->a($i18n->r("/user/{0}", [ $details["user"]["name"] ]), $details["user"]["name"]) ];
    $desc[] = [ $i18n->t("Creation Date"), $i18n->formatDate($details["created"], $session->userTimeZoneId, IntlDateFormatter::MEDIUM) ];
    $desc[] = [ $i18n->t("Last Update"), $i18n->formatDate($details["changed"], $session->userTimeZoneId, IntlDateFormatter::MEDIUM) ];
    $desc[] = [ $i18n->t("Source"), $details["source"] ];
    return $desc;
  }

  /**
   * @inheritdoc
   */
  public function getSecondaryNavigationMenuitems() {
    global $i18n;
    $points = [
      [ $i18n->r("/movie/{0}", [ $this->model->id ]), "<i class='icon icon--film'></i>{$i18n->t("Back to {0}", [ $i18n->t("movie") ])}" ],
      [ $this->uploadRoute, "<i class='icon icon--upload'></i>{$i18n->t("Upload")}", [
        "class" => "separator"
      ]],
    ];
    foreach ([ "posters" => "Posters", "lobby-cards" => "Lobby Cards", "photos" => "Photos" ] as $route => $title) {
      $points[] = [ $i18n->r("/movie/{0}/{$i18n->t("{$route}")}", [ $this->model->id ]), $i18n->t($title), "{$_SERVER["TAB"]}s" == $route ? [ "class" => "active" ] : null ];
    }
    return $points;
  }

}
