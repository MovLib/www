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
namespace MovLib\Presentation\Company;

use \MovLib\Data\Company\FullCompany;
use \MovLib\Presentation\Error\Gone;

/**
 * Base presenation of company pages.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractBase extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitSidebar;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The company to present.
   *
   * @var \MovLib\Data\Company\Full
   */
  protected $company;

  /**
   * The translated route to the company's edit page.
   *
   * @var string
   */
  protected $routeEdit;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new basic company presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Error\Gone
   */
  public function __construct() {
    global $i18n, $kernel;

    // Try to load company data.
    $this->company = new FullCompany((int)$_SERVER["COMPANY_ID"]);

    $this->title = $this->company->name;

    $breadcrumbArgs = [ [ $i18n->rp("/companies"), $i18n->t("Companies") ] ];
    if ($kernel->requestURI != $this->company->route) {
      $breadcrumbArgs[] = [ $this->company->route, $this->company->name ];
    }
    $this->initBreadcrumb($breadcrumbArgs);

    // Initialize edit route, sidebar and schema.
    $routeArgs = [ $this->company->id ];
    $this->routeEdit = $i18n->r("/company/{0}/edit", $routeArgs);
    $this->sidebarInit([
      [ $this->company->route, $i18n->t("View"), [ "class" => "ico ico-view" ] ],
      [ $i18n->r("/company/{0}/discussion", $routeArgs), $i18n->t("Discuss"), [ "class" => "ico ico-discussion" ] ],
      [ $this->routeEdit, $i18n->t("Edit"), [ "class" => "ico ico-edit" ] ],
      [ $i18n->r("/company/{0}/history", $routeArgs), $i18n->t("History"), [ "class" => "ico ico-history" ] ],
      [ $i18n->r("/company/{0}/delete", $routeArgs), $i18n->t("Delete"), [ "class" => "ico ico-delete separator" ] ],

      [ $i18n->rp("/company/{0}/movies", $routeArgs), "{$i18n->t("Movies")} <span class='fr'>{$i18n->format("{0,number}", [ $this->company->getMovieCount() ])}</span>", [ "class" => "ico ico-movie" ] ],
      [ $i18n->rp("/company/{0}/series", $routeArgs), "{$i18n->t("Series")} <span class='fr'>{$i18n->format("{0,number}", [ $this->company->getSeriesCount() ])}</span>", [ "class" => "ico ico-series" ] ],
      [ $i18n->rp("/company/{0}/releases", $routeArgs), "{$i18n->t("Releases")} <span class='fr'>{$i18n->format("{0,number}", [ $this->company->getReleasesCount() ])}</span>", [ "class" => "ico ico-release separator" ] ],
    ]);
    $this->schemaType = "Corporation";
    $kernel->stylesheets[] = "company";

    // Display Gone page if this person was deleted.
    if ($this->company->deleted === true) {
      // @todo Implement Gone presentation for companies instead of this generic one.
      throw new Gone;
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Construct content and sidebar.
   *
   * @param string $id
   *   The section identifier.
   * @param string $title
   *   The translated section title.
   * @param array|string $content
   *   The content of the section.
   */
  protected function getSection($id, $title, $content) {
    $this->sidebarNavigation->menuitems[] = [ "#{$id}", $title ];
    $result = "<div id='{$id}'><h2>{$title}</h2>";
    if (is_array($content)) {
      foreach ($content as $subId => $subSection) {
        $this->sidebarNavigation->menuitems[] = [ "#{$id}-{$subId}", $subSection[0] ];
        $attributes = isset($subSection[2]) ? $this->expandTagAttributes($subSection[2]) : null;
        $result .= "<div id='{$id}-{$subId}'><h3{$attributes}>{$subSection[0]}</h3>{$subSection[1]}</div>";
      }
    }
    else {
      $result .= $content;
    }
    return "{$result}</div>";
  }
  
}
