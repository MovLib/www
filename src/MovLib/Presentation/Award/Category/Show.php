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
namespace MovLib\Presentation\Award\Category;

use \MovLib\Data\Award;
use \MovLib\Data\AwardCategory;
use \MovLib\Presentation\Partial\Alert;

/**
 * Presentation of a single award category.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\Award\Category\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new award presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct() {
    global $i18n, $kernel;
    $this->award         = new Award((integer) $_SERVER["AWARD_ID"]);
    $this->awardCategory = new AwardCategory((integer) $_SERVER["AWARD_CATEGORY_ID"]);
    $this->initPage($this->awardCategory->name);
    $this->initLanguageLinks("/award/{0}/category/{1}", [ $this->award->id, $this->awardCategory->id ]);
    $this->initBreadcrumb([
      [ $i18n->rp("/awards"), $i18n->t("Awards") ],
      [ $this->award->route, $this->award->name ],
      [ $i18n->rp("/award/{0}/categories", [ $this->award->id ]), $i18n->t("Categories") ],
    ]);
    $this->sidebarInit();

    $kernel->stylesheets[] = "award";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  protected function getPageContent() {
    global $i18n, $kernel;

    // Enhance the page title with microdata.
    $this->schemaType = "Intangible";
    $this->pageTitle  = "<span property='name'>{$this->awardCategory->name}</span>";

    if ($this->awardCategory->deleted === true) {
      return $this->goneGetContent();
    }

    // Put the award information together.
    $info = null;

    if ($this->awardCategory->firstAwardingYear && $this->awardCategory->lastAwardingYear) {
      $info .= "<span>{$i18n->t("from {0} to {1}", [
        $this->awardCategory->firstAwardingYear,
        $this->awardCategory->lastAwardingYear
      ])}</span>";
    }
    else if ($this->awardCategory->firstAwardingYear) {
      $info .= "<span>{$i18n->t("since {0}", [ $this->awardCategory->firstAwardingYear ])}</span>";
    }
    else if ($this->awardCategory->lastAwardingYear) {
      $info .= "<span>{$i18n->t("until {0}", [ $this->awardCategory->lastAwardingYear ])}</span>";
    }

    // Construct the wikipedia link.
    if ($this->awardCategory->wikipedia) {
      if ($info) {
        $info .= "<br>";
      }
      $info .= "<span class='ico ico-wikipedia'></span><a href='{$this->award->wikipedia}' itemprop='sameAs' target='_blank'>{$i18n->t("Wikipedia Article")}</a>";
    }

    $this->headingBefore = "<div class='r'><div class='s s10'>";
    $this->headingAfter = "<p>{$info}</p></div></div>";


    // ----------------------------------------------------------------------------------------------------------------- Build page sections.


    $content = null;
    // Description section
    if ($this->awardCategory->description) {
      $content .=
        $this->getSection("description", $i18n->t("Description"), $this->htmlDecode($this->awardCategory->description))
      ;
    }

    if ($content) {
      return $content;
    }

    return new Alert(
      $i18n->t("{sitename} has no further details about this award category.", [ "sitename"    => $kernel->siteName ]),
      $i18n->t("No Data Available"),
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