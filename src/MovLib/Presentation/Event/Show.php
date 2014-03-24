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
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @throws \MovLib\Presentation\Error\NotFound
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  public function __construct() {
    global $i18n, $kernel;

    $this->event = new Event((integer) $_SERVER["EVENT_ID"]);
    $this->award = new Award($this->event->awardId);

    $this->initPage($this->event->name);
    $this->initLanguageLinks("/event/{0}", [ $this->event->id ]);
    $this->initBreadcrumb([
      [ $i18n->rp("/events"), $i18n->t("Events") ],
    ]);
    $this->sidebarInit();

    $kernel->stylesheets[] = "event";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  protected function getPageContent() {
    global $i18n, $kernel;

    $this->getEventHeader();

    $content = null;
    // Description section
    if ($this->event->description) {
      $content .=
        $this->getSection("description", $i18n->t("Description"), $this->htmlDecode($this->event->description))
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
