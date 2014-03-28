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
namespace MovLib\Presentation\Genre;

use \MovLib\Data\Genre;

/**
 * Presentation of a single genre.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\Genre\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new genre presentation.
   *
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct() {
    $this->genre = new Genre((integer) $_SERVER["GENRE_ID"]);
    $this->initPage($this->genre->name);
    $this->initLanguageLinks("/genre/{0}", [ $this->genre->id]);
    $this->initBreadcrumb([[ $this->intl->rp("/genres"), $this->intl->t("Genres") ]]);
    $this->sidebarInit();

    $kernel->stylesheets[] = "genre";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    // Enhance the page title with microdata.
    $this->schemaType = "Intangible";
    $this->pageTitle  = "<span property='name'>{$this->genre->name}</span>";

    if ($this->genre->deleted === true) {
      return $this->goneGetContent();
    }


    // ----------------------------------------------------------------------------------------------------------------- Build page sections.


    $content = null;

    // Biography section.
    if ($this->genre->description) {
      $content .= $this->getSection("description", $this->intl->t("Description"), $this->htmlDecode($this->genre->description));
    }

    if ($content) {
      return $content;
    }

    return new Alert(
      $this->intl->t(
        "{sitename} has no further details about this genre.",
        [ "sitename"    => $this->config->sitename ]
      ),
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
