<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright Â© 2013-present {@link https://movlib.org/ MovLib}.
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
namespace MovLib\Presentation\Release;

use \MovLib\Data\Release\Release;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Country;
use \MovLib\Presentation\Partial\Date;

/**
 * Latest releases.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright Â© 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Index extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitSidebar;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new latest releases presentation
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  public function __construct() {
    global $i18n, $kernel;
    $this->initPage($i18n->t("Releases"));
    $this->initLanguageLinks("/releases", null, true);
    $this->initBreadcrumb();
    $this->sidebarInit([
      [ $i18n->rp("/releases"), $i18n->t("Releases"), [ "class" => "ico ico-release" ] ],
      [ $i18n->r("/release/random"), $i18n->t("Random") ],
    ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  protected function getPageContent() {
    global $i18n, $kernel;

    $list           = null;
    $releasesResult = Release::getReleases();
    $releaseRoute   = $i18n->r("/release/{0}");
    $releaseTitle   = $i18n->t("{0} ({1})");
    $date           = new Date();

    /* @var $release \MovLib\Data\Release\Release */
    while ($release = $releasesResult->fetch_object("\\MovLib\\Data\\Release\\Release")) {
      $route = str_replace("{0}", $release->id, $releaseRoute);

      $title = "<a href='{$route}'>$release->title</a>";
      if ($release->edition) {
        $title = str_replace([ "{0}", "{1}" ], [ $title, $release->edition], $releaseTitle);
      }

      $formats = [];
      $release->mediaCounts = unserialize($release->mediaCounts);
      foreach ($release->mediaCounts as $format => $count) {
        $formats[] = $i18n->t("{0} × {1}", [ $count, $format ]);
      }
      $formats = "<small>{$i18n->t("({0})", [ implode(", ", $formats) ])}</small>";

      $labels = null;
      $labelsResult = $release->getLabels();
      if ($labelsResult) {
        foreach ($labelsResult as $labelInfo) {
          if ($labels) {
            $labels .= "<br>";
          }
          $labelInfo["name"] = "<a href='{$i18n->r("/company/{0}", [ $labelInfo["id"] ])}'>{$labelInfo["name"]}</a>";
          if ($labelInfo["catalog_number"]) {
            $labelInfo["name"] = str_replace(
              [ "{0}", "{1}" ],
              [ $labelInfo["name"], $labelInfo["catalog_number"] ],
              $releaseTitle
            );
          }
          $labels .= $labelInfo["name"];
        }
      }

      $publishingDates = null;
      if ($release->publishingDateRental) {
        $date->setDate($release->publishingDateRental);
        $release->publishingDateRental = "<small>{$i18n->t("{0} (Rental)", [ $date->format() ])}</small>";
      }
      if ($release->publishingDateSale) {
        $date->setDate($release->publishingDateSale);
        $release->publishingDateSale = "<small>{$i18n->t("{0} (Sale)", [ $date->format() ])}</small>";
      }

      $country = new Country($release->countryCode);

      $list .=
        "<tr>" .
          "<td>" .
            "<a class='no-link' href='{$route}'><img class='placeholder' height='60' src='{$this->getURL("asset://img/logo/vector.svg")}' width='60'></a>" .
          "</td>" .
          "<td>{$title}{$formats}</div></td>" .
          "<td class='small'>{$labels}</td>" .
          "<td>{$release->publishingDateRental}{$release->publishingDateSale}</td>" .
          "<td class='tac'>{$country->getFlag()}</td>" .
        "</tr>"
      ;
    }
    if ($list) {
      return
        "<table class='sortable table-striped'>" .
          "<colgroup>" .
            "<col class='w10'>" .
            "<col class='w35'>" .
            "<col class='w30'>" .
            "<col class='w20'>" .
            "<col class='w5'>" .
          "</colgroup>" .
          "<thead class='tal'><tr>" .
            "<th></th>" . // Spacer for image
            "<th>{$i18n->t("Title")}</th>" .
            "<th>{$i18n->t("Label")}</th>" .
            "<th>{$i18n->t("Published")}</th>" .
            "<th></th>" . // Country
        "</tr></thead><tbody>{$list}</tbody></table>"
      ;
    }

    return new Alert(
      $i18n->t("No releases match your search criteria."),
      $i18n->t("No Releases"),
      Alert::SEVERITY_INFO
    );
  }

}
