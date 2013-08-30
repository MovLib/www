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
namespace MovLib\View\HTML;

use \IntlDateFormatter;
use \MovLib\Model\AbstractImageModel;

/**
 * Generic view for showing image details.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class ImageDetailsView extends AbstractPageView {

  /**
   * Initialize new ImageView.
   */
  public function __construct($presenter) {
    parent::__construct($presenter, $presenter->title);
    $this->stylesheets[] = "modules/image-details.css";
  }

  /**
   * {@inheritDoc}
   * @global \MovLib\Model\I18nModel $i18n
   * @global \MovLib\Model\SessionModel $user
   */
  public function getContent() {
    global $i18n, $user;
    $imageStream = "";
    $c = count($this->presenter->streamImages);
    for ($i = 0; $i < $c; ++$i) {
      $imageStream .= $this->a(
        $i18n->r("/{$_SERVER["ACTION"]}/{0}/{$_SERVER["TAB"]}/{1}", [ $_SERVER["ID"], $_SERVER["IMAGE_ID"] ]),
        $this->getImage($this->presenter->streamImages[$i], AbstractImageModel::IMAGESTYLE_DETAILS_STREAM),
        [ "class" => "span span--1" ]
      );
    }
    $image = $this->getImage($this->presenter->model, AbstractImageModel::IMAGESTYLE_DETAILS);
    if ($user->isLoggedIn) {
      $image = $this->a($this->presenter->model->imageUri, $image);
    }
    $imageDetails = $this->presenter->model->getImageDetails();
    $country = "";
    if (isset($imageDetails["country"])) {
      $country = "<dt>{$i18n->t("Country:")}</dt>";
      if (empty($imageDetails["country"])) {
        $country .= "<dd class='alert alert--warning'>" .
          $i18n->t("No {0} assigned yet, {1}add {0}{2}?", [
            $i18n->t("country"),
            "<a href='{$i18n->r("/{$_SERVER["ACTION"]}/{0}/{$_SERVER["TAB"]}/{1}/edit", [ $_SERVER["ID"], $_SERVER["IMAGE_ID"] ])}'>",
            "</a>"
          ]) .
        "</dd>";
      }
      else {
        $country .= "<dd>{$this->a($i18n->r("/country/{0}", [ $imageDetails["country"]["code"] ]), $imageDetails["country"]["name"])}</dd>";
      }
    }
    if (empty($imageDetails["description"])) {
      $imageDetails["description"] = "<dd class='alert alert--warning'>" .
        $i18n->t("No {0} assigned yet, {1}add {0}{2}?", [
          $i18n->t("description"),
          "<a href='{$i18n->r("/{$_SERVER["ACTION"]}/{0}/{$_SERVER["TAB"]}/{1}/edit", [ $_SERVER["ID"], $_SERVER["IMAGE_ID"] ])}'>",
          "</a>"
        ]) .
      "</dd>";
    }
    else {
      $imageDetails["description"] = "<dd>{$imageDetails["description"]}</dd>";
    }
    return
      "<div class='container'>" .
        "<div class='row'>" .
          "<aside class='span span--3'>" .
            $this->getSecondaryNavigation(
              $i18n->t("{0} details navigation", [ $i18n->t(ucfirst($_SERVER["TAB"])) ]),
              $this->presenter->getSecondaryNavigation()
            ) .
          "</aside>" .
          "<div class='span span--9'>" .
            "<div id='image-details-stream'>{$imageStream}</div>" .
            "<div id='image-details-image'>" .
              $image .
            "</div>" .
            "<dl class='dl--horizontal' id='image-details-details'>" .
              "<dt>{$i18n->t("Description:")}</dt>" .
              $imageDetails["description"] .
              $country .
              "<dt>{$i18n->t("Dimensions:")}</dt>" .
              "<dd>{$i18n->t("{0} × {1} pixels", [ $imageDetails["imageWidth"], $imageDetails["imageHeight"] ])}</dd>" .
              "<dt>{$i18n->t("User:")}</dt>" .
              "<dd>{$this->a($i18n->r("/user/{0}", [ $imageDetails["#user"]->name ]), $imageDetails["#user"]->name)}</dd>" .
              "<dt>{$i18n->t("Creation date:")}</dt>" .
              "<dd>{$i18n->formatDate($imageDetails["created"], $user->timezone, IntlDateFormatter::MEDIUM)}</dd>" .
              "<dt>{$i18n->t("Last update:")}</dt>" .
              "<dd>{$i18n->formatDate($imageDetails["changed"], $user->timezone, IntlDateFormatter::MEDIUM)}</dd>" .
              "<dt>{$i18n->t("Source:")}</dt>" .
              "<dd>{$imageDetails["source"]}</dd>" .
            "</dl>" .
          "</div>" .
        "</div>" .
      "</div>"
    ;
  }

}