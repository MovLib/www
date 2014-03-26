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
use \MovLib\Presentation\Partial\Place;
use \MovLib\Presentation\Partial\Date;

/**
 * Presentation of a single company.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\Company\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new company presentation.
   *
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct() {
    $this->company = new FullCompany((integer) $_SERVER["COMPANY_ID"]);
    $this->initPage($this->company->name);
    $this->initLanguageLinks("/company/{0}", [ $this->company->id]);
    $this->initBreadcrumb([[ $this->intl->rp("/companies"), $this->intl->t("Companies") ]]);
    $this->sidebarInit();

    $kernel->stylesheets[] = "company";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    // Enhance the page title with microdata.
    $this->schemaType = "Corporation";
    $this->pageTitle  = "<span property='name'>{$this->company->name}</span>";

    if ($this->company->deleted === true) {
      return $this->goneGetContent();
    }

    // Put the company information together.
    $info = null;
    if ($this->company->foundingDate && $this->company->defunctDate) {
      $info .= (new Date($this->company->foundingDate))->format([ "itemprop" => "foundingDate", "title" => $this->intl->t("Founding Date") ]);
      $info .= " – " . (new Date($this->company->defunctDate))->format([ "title" => $this->intl->t("Defunct Date") ]);
    }
    else if ($this->company->foundingDate) {
      $info .= "{$this->intl->t("Founded")}: " . (new Date($this->company->foundingDate))->format([ "itemprop" => "foundingDate", "title" => $this->intl->t("Founding Date") ]);
    }
    if ($this->company->place) {
      $info .= "<br><span itemprop='location'>". new Place($this->company->place) . "</span>";
    }

    // Construct the wikipedia link.
    if ($this->company->wikipedia) {
      if ($info) {
        $info .= "<br>";
      }
      $info .= "<span class='ico ico-wikipedia'></span><a href='{$this->company->wikipedia}' itemprop='sameAs' target='_blank'>{$this->intl->t("Wikipedia Article")}</a>";
    }

    $headerImage = $this->getImage($this->company->getStyle(FullCompany::STYLE_SPAN_02), true, [ "itemprop" => "image" ]);
    $this->headingBefore = "<div class='r'><div class='s s10'>";
    $this->headingAfter = "<p>{$info}</p></div><div id='company-logo' class='s s2'>{$headerImage}</div></div>";


    // ----------------------------------------------------------------------------------------------------------------- Build page sections.


    $content = null;
    // Description section
    if ($this->company->description) {
      $content .= $this->getSection("description", $this->intl->t("Description"), $this->htmlDecode($this->company->description));
    }

    // Additional names section.
    $companyAliases = $this->company->aliases;
    if (!empty($companyAliases)) {
      $aliases = null;
      $c       = count($companyAliases);
      for ($i = 0; $i < $c; ++$i) {
        $aliases .= "<li class='mb10 s s10' property='additionalName'>{$companyAliases[$i]}</li>";
      }
      $content .= $this->getSection("aliases", $this->intl->t("Also Known As"), "<ul class='grid-list r'>{$aliases}</ul>");
    }

     // External links section.
    $companyLinks = $this->company->links;
    if ($companyLinks) {
      $links = null;
      $c     = count($companyLinks);
      for ($i = 0; $i < $c; ++$i) {
        $hostname = str_replace("www.", "", parse_url($companyLinks[$i], PHP_URL_HOST));
        $links .= "<li class='mb10 s s10'><a href='{$companyLinks[$i]}' property='url' rel='nofollow' target='_blank'>{$hostname}</a></li>";
      }
      $content .= $this->getSection("links", $this->intl->t("External Links"), "<ul class='grid-list r'>{$links}</ul>");
    }

    if ($content) {
      return $content;
    }

    return new Alert(
      $this->intl->t("{sitename} has no further details about this company.", [ "sitename"    => $this->config->siteName ]),
      $this->intl->t("No Data Available"),
      Alert::SEVERITY_INFO
    );
  }

  /**
   * Construct a section in the main content and add it to the sidebar.
   *
   * @param string $id
   *   The section's unique identifier.
   * @param string $title
   *   The section's translated title.
   * @param string $content
   *   The section's content.
   * @return string
   *   The section ready for display.
   */
  protected function getSection($id, $title, $content) {
    // Add the section to the sidebar as anchor.
    $this->sidebarNavigation->menuitems[] = [ "#{$id}", $title ];

    return "<div id='{$id}'><h2>{$title}</h2>{$content}</div>";
  }

}
