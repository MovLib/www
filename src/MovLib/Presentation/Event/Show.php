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
namespace MovLib\Presentation\Event;

use \MovLib\Data\Award;
use \MovLib\Data\Event;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Date;
use \MovLib\Presentation\Partial\Place;

/**
 * Presentation of a single event.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\Event\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new event presentation.
   *
   * @throws \MovLib\Presentation\Error\NotFound
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  public function __construct() {
    $this->event = new Event((integer) $_SERVER["EVENT_ID"]);
    $this->award = new Award($this->event->awardId);

    $this->initPage($this->event->name);
    $this->initLanguageLinks("/event/{0}", [ $this->event->id ]);
    $this->initBreadcrumb([
      [ $this->intl->rp("/events"), $this->intl->t("Events") ],
    ]);
    $this->sidebarInit();

    $kernel->stylesheets[] = "event";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    // Enhance the page title with microdata.
    $this->schemaType = "Intangible";
    $this->pageTitle  = "<span property='name'>{$this->event->name}</span>";

    if ($this->event->deleted === true) {
      return $this->goneGetContent();
    }

    // Put the event information together.
    $info = null;
    if (($this->event->startDate && $this->event->endDate) && ($this->event->startDate != $this->event->endDate)) {
      $info .= "{$this->intl->t("from {0} to {1}", [
        (new Date($this->event->startDate))->format(),
        (new Date($this->event->endDate))->format()
      ])} ";
    }
    else if ($this->event->startDate) {
      $info .= "{$this->intl->t("on {0}", [ (new Date($this->event->startDate))->format() ])} ";
    }
    if ($this->event->place) {
      if ($info) {
        $info .= "<br>";
      }
      $info .= $this->intl->t("in {0}", [ new Place($this->event->place) ]);
    }
    $info   .= "<br>{$this->intl->t("Award")}: <a href='{$this->award->route}'>{$this->award->name}</a>";

    // Construct the wikipedia link.
    if ($this->event->wikipedia) {
      if ($info) {
        $info .= "<br>";
      }
      $info .= "<span class='ico ico-wikipedia'></span><a href='{$this->event->wikipedia}' itemprop='sameAs' target='_blank'>{$this->intl->t("Wikipedia Article")}</a>";
    }

    $headerImage = $this->getImage($this->award->getStyle(Award::STYLE_SPAN_02), $this->award->route, [ "itemprop" => "image" ]);
    $this->headingBefore = "<div class='r'><div class='s s10'>";
    $this->headingAfter = "<p>{$info}</p></div><div id='award-logo' class='s s2'>{$headerImage}</div></div>";


    // ----------------------------------------------------------------------------------------------------------------- Build Content


    $content = null;
    // Description section
    if ($this->event->description) {
      $content .=
        $this->getSection("description", $this->intl->t("Description"), $this->htmlDecode($this->event->description))
      ;
    }

    // External links section.
    $awardLinks = $this->event->links;
    if ($awardLinks) {
      $links = null;
      $c     = count($awardLinks);
      for ($i = 0; $i < $c; ++$i) {
        $hostname = str_replace("www.", "", parse_url($awardLinks[$i], PHP_URL_HOST));
        $links .= "<li class='mb10 s s10'><a href='{$awardLinks[$i]}' property='url' rel='nofollow' target='_blank'>{$hostname}</a></li>";
      }
      $content .= $this->getSection("links", $this->intl->t("External Links"), "<ul class='grid-list r'>{$links}</ul>");
    }

    if ($content) {
      return $content;
    }

    return new Alert(
      $this->intl->t("{sitename} has no further details about this award event.", [ "sitename"    => $this->config->siteName ]),
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
