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
namespace MovLib\Presentation\Partial\Listing;

use \MovLib\Data\Company\Company as DataCompany;
use \MovLib\Presentation\Partial\Date;

/**
 * Special images list for company instances.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Company extends \MovLib\Presentation\Partial\Listing\AbstractListing {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The company photo's style.
   *
   * @var integer
   */
  public $imageStyle = DataCompany::STYLE_SPAN_01;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the rendered listing.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The rendered listing.
   */
  public function __toString() {
    global $i18n;
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      $list = null;
      $moviesTitle   = $i18n->t("Movies");
      $seriesTitle   = $i18n->t("Series");
      $releasesTitle = $i18n->t("Releases");
      /* @var $company \MovLib\Data\Company\Company */
      while ($company = $this->listItems->fetch_object("\\MovLib\\Data\\Company\\Company")) {
        // @devStart
        // @codeCoverageIgnoreStart
        if (!($company instanceof \MovLib\Data\Company\Company)) {
          throw new \LogicException($i18n->t("\$company has to be a valid company object!"));
        }
        // @codeCoverageIgnoreEnd
        // @devEnd
        $additionalInfo = null;
        if ($company->foundingDate || $company->defunctDate) {
          $additionalInfo    = "<br><span class='small'>";
          if ($company->foundingDate) {
            $additionalInfo .= (new Date($company->foundingDate))->format([ "property" => "foundingDate", "title" => $i18n->t("Founding Date") ]);
          }
          else {
            $additionalInfo .= $i18n->t("{0}unknown{1}", [ "<em title='{$i18n->t("Founding Date")}'>", "</em>" ]);
          }
          if ($company->defunctDate) {
            $additionalInfo .= " – " . (new Date($company->defunctDate))->format([ "title" => $i18n->t("Defunct Date") ]);
          }
          $additionalInfo   .= "</span>";
        }

        $list .=
          "<li class='hover-item r' typeof='Corporation'>" .
            "<div class='s s10'>" .
              $this->getImage($company->getStyle($this->imageStyle), $company->route, [ "property" => "image" ], [ "class" => "fl" ]) .
              "<span class='s s9'>" .
                "<span class='fr'>" .
                  "<a class='ico ico-movie label' href='{$i18n->rp("{$company->routeKey}/movies", [ $company->id ])}' title='{$moviesTitle}'>" .
                    " &nbsp; {$company->getMoviesCount()}" .
                  "</a>" .
                  "<a class='ico ico-series label' href='{$i18n->rp("{$company->routeKey}/series", [ $company->id ])}' title='{$seriesTitle}'>" .
                    " &nbsp; {$company->getSeriesCount()}" .
                  "</a>" .
                  "<a class='ico ico-release label' href='{$i18n->rp("{$company->routeKey}/releases", [ $company->id ])}' title='{$releasesTitle}'>" .
                    " &nbsp; {$company->getSeriesCount()}" .
                  "</a>" .
                "</span>" .
                "<a href='{$company->route}' property='url'><span property='name'>{$company->name}</span></a>{$additionalInfo}" .
              "</span>" .
            "</div>" .
          "</li>"
        ;
      }
      if ($list) {
        return "<ol class='hover-list no-list'>{$list}</ol>";
      }
      return (string) $this->noItemsText;
    // @devStart
    // @codeCoverageIgnoreStart
    }
    catch (\Exception $e) {
      return (string) new \MovLib\Presentation\Partial\Alert(
        "<pre>{$e}</pre>",
        $i18n->t("Error Rendering List"),
        \MovLib\Presentation\Partial\Alert::SEVERITY_ERROR
      );
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }

}
