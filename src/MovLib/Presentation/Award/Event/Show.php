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
namespace MovLib\Presentation\Award\Event;

use \MovLib\Data\Award;
use \MovLib\Data\AwardEvent;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Date;
use \MovLib\Presentation\Partial\Place;
use \MovLib\Presentation\Redirect\SeeOther as SeeOtherRedirect;

/**
 * Presentation of a single award event.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\Award\Event\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new award event presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @throws \MovLib\Presentation\Error\NotFound
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  public function __construct() {
    global $i18n, $kernel;
    $this->award      = new Award((integer) $_SERVER["AWARD_ID"]);
    $this->awardEvent = new AwardEvent((integer) $_SERVER["AWARD_EVENT_ID"]);

    if ($this->award->id != $this->awardEvent->awardId) {
      throw new SeeOtherRedirect($this->awardEvent->route);
    }

    $this->initPage($this->awardEvent->name);
    $this->initLanguageLinks("/award/{0}/event/{1}", [ $this->award->id, $this->awardEvent->id ]);
    $this->initBreadcrumb([
      [ $i18n->rp("/awards"), $i18n->t("Awards") ],
      [ $this->award->route, $this->award->name ],
      [ $i18n->rp("/award/{0}/events", [ $this->award->id ]), $i18n->t("Events") ],
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
    $this->pageTitle  = "<span property='name'>{$this->awardEvent->name}</span>";

    if ($this->awardEvent->deleted === true) {
      return $this->goneGetContent();
    }

    // Put the award event information together.
    $info = null;
    if (($this->awardEvent->startDate && $this->awardEvent->endDate) && ($this->awardEvent->startDate != $this->awardEvent->endDate)) {
      $info .= "{$i18n->t("from {0} to {1}", [
        (new Date($this->awardEvent->startDate))->format(),
        (new Date($this->awardEvent->endDate))->format()
      ])} ";
    }
    else if ($this->awardEvent->startDate) {
      $info .= "{$i18n->t("on {0}", [ (new Date($this->awardEvent->startDate))->format() ])} ";
    }
    if ($this->awardEvent->place) {
      if ($info) {
        $info .= "<br>";
      }
      $info .= $i18n->t("in {0}", [ new Place($this->awardEvent->place) ]);
    }

    // Construct the wikipedia link.
    if ($this->awardEvent->wikipedia) {
      if ($info) {
        $info .= "<br>";
      }
      $info .= "<span class='ico ico-wikipedia'></span><a href='{$this->awardEvent->wikipedia}' itemprop='sameAs' target='_blank'>{$i18n->t("Wikipedia Article")}</a>";
    }

    $this->headingBefore = "<div class='r'><div class='s s10'>";
    $this->headingAfter = "<p>{$info}</p></div></div>";


    // ----------------------------------------------------------------------------------------------------------------- Build page sections.


    $content = null;
    // Description section
    if ($this->awardEvent->description) {
      $content .=
        $this->getSection("description", $i18n->t("Description"), $this->htmlDecode($this->awardEvent->description))
      ;
    }

    // External links section.
    $awardLinks = $this->awardEvent->links;
    if ($awardLinks) {
      $links = null;
      $c     = count($awardLinks);
      for ($i = 0; $i < $c; ++$i) {
        $hostname = str_replace("www.", "", parse_url($awardLinks[$i], PHP_URL_HOST));
        $links .= "<li class='mb10 s s10'><a href='{$awardLinks[$i]}' property='url' rel='nofollow' target='_blank'>{$hostname}</a></li>";
      }
      $content .= $this->getSection("links", $i18n->t("External Links"), "<ul class='grid-list r'>{$links}</ul>");
    }

    if ($content) {
      return $content;
    }

    return new Alert(
      $i18n->t("{sitename} has no further details about this award event.", [ "sitename"    => $kernel->siteName ]),
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
