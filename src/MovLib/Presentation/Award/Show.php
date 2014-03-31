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
namespace MovLib\Presentation\Award;

use \MovLib\Data\Award\Award;

/**
 * Presentation of a single award.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Show extends \MovLib\Presentation\AbstractShowPresenter {
  use \MovLib\Partial\AwardTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    return $this->initShow(new Award($this->diContainerHTTP), "Organization", "name");
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return "";

    // Put the award information together.
    $info = null;

    if ($this->award->firstAwardingYear && $this->award->lastAwardingYear) {
      $info .=
        "<span>{$this->intl->t("from {0} to {1}", [ $this->award->firstAwardingYear, $this->award->lastAwardingYear ])}</span>"
      ;
    }
    else if ($this->award->firstAwardingYear) {
      $info .= "<span>{$this->intl->t("since {0}", [ $this->award->firstAwardingYear ])}</span>";
    }
    else if ($this->award->lastAwardingYear) {
      $info .= "<span>{$this->intl->t("until {0}", [ $this->award->lastAwardingYear ])}</span>";
    }

    // Construct the wikipedia link.
    if ($this->award->wikipedia) {
      if ($info) {
        $info .= "<br>";
      }
      $info .= "<span class='ico ico-wikipedia'></span><a href='{$this->award->wikipedia}' itemprop='sameAs' target='_blank'>{$this->intl->t("Wikipedia Article")}</a>";
    }

    $headerImage = $this->getImage($this->award->getStyle(Award::STYLE_SPAN_02), true, [ "itemprop" => "image" ]);
    $this->headingBefore = "<div class='r'><div class='s s10'>";
    $this->headingAfter = "<p>{$info}</p></div><div id='award-logo' class='s s2'>{$headerImage}</div></div>";


    // ----------------------------------------------------------------------------------------------------------------- Build page sections.


    $content = null;
    // Description section
    if ($this->award->description) {
      $content .= $this->getSection("description", $this->intl->t("Description"), $this->htmlDecode($this->award->description));
    }

    // Additional names section.
    $awardAliases = $this->award->aliases;
    if (!empty($awardAliases)) {
      $aliases = null;
      $c       = count($awardAliases);
      for ($i = 0; $i < $c; ++$i) {
        $aliases .= "<li class='mb10 s s10' property='additionalName'>{$awardAliases[$i]}</li>";
      }
      $content .= $this->getSection("aliases", $this->intl->t("Also Known As"), "<ul class='grid-list r'>{$aliases}</ul>");
    }

     // External links section.
    $awardLinks = $this->award->links;
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
      $this->intl->t("{sitename} has no further details about this award.", [ "sitename"    => $this->config->sitename ]),
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
