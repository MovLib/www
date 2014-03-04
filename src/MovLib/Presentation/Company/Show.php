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

use \MovLib\Data\Company\Company;
use \MovLib\Presentation\Partial\Place;
use \MovLib\Presentation\Partial\Date;
use \MovLib\Presentation\Partial\Lists\Ordered;

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
   * @throws \LogicException
   */
  public function __construct() {
    parent::__construct();
    $this->initPage($this->company->name);
    $this->initLanguageLinks("/company/{0}", [ $this->company->id ]);

    // Enhance the page title with microdata.
    $this->pageTitle = "<span itemprop='name'>{$this->company->name}</span>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getPageContent() {
    global $i18n;

    $editLinkOpen = [ "<a href='{$this->routeEdit}'>", "</a>" ];

    // Put the company information together.
    $info = null;
    if ($this->company->foundingDate && $this->company->defunctDate) {
      if ($this->company->foundingDate) {
        $info .= (new Date($this->company->foundingDate))->format([ "itemprop" => "foundingDate", "title" => $i18n->t("Founding Date") ]);
        $info .= " – " . (new Date($this->company->defunctDate))->format([ "title" => $i18n->t("Defunct Date") ]);
      }
    }
    else if ($this->company->foundingDate) {
      $info .= "{$i18n->t("Founded")}: " . (new Date($this->company->foundingDate))->format([ "itemprop" => "foundingDate", "title" => $i18n->t("Founding Date") ]);
    }
    if ($this->company->place) {
      $info .= "<br><span itemprop='location'>". new Place($this->company->place) . "</span>";
    }

    // Construct the wikipedia link.
    if ($this->company->wikipedia) {
      if ($info) {
        $info .= "<br>";
      }
      $info .= "<span class='ico ico-wikipedia'></span><a href='{$this->company->wikipedia}' itemprop='sameAs' target='_blank'>{$i18n->t("Wikipedia Article")}</a>";
    }

    $headerImage = $this->getImage($this->company->getStyle(Company::STYLE_SPAN_02), true, [ "itemprop" => "image" ]);
    $this->headingBefore = "<div class='r'><div class='s s10'>";
    $this->headingAfter = "<p>{$info}</p></div><div id='company-photo' class='s s2'>{$headerImage}</div></div>";

    $content = null;

    // Description section.
    $description = empty($this->company->description)
      ? $i18n->t("No description available, {0}write one{1}?", $editLinkOpen)
      : $this->htmlDecode($this->company->description);
    $content .= $this->getSection("description", $i18n->t("Description"), $description);

    // Additional names section.
    $aliases = new Ordered(
      $this->company->aliases,
      $i18n->t("No additional names available, {0}add some{1}?", $editLinkOpen),
      [ "class" => "grid-list no-list r" ], [ "class" => "mb10 s s3", "itemprop" => "alternateName" ]
    );
    $content .= $this->getSection("aliases", $i18n->t("Also Known As"), $aliases);

    // External links section.
    $links = null;
    if (empty($this->company->links)) {
      $links = $i18n->t("No links available, {0}add some{1}?", $editLinkOpen);
    }
    else {
      $links .= "<ul class='grid-list no-list r'>";
      $c = count($this->company->links);
      for ($i = 0; $i < $c; ++$i) {
        $hostname = str_replace("www.", "", parse_url($this->company->links[$i], PHP_URL_HOST));
        $links .= "<li class='mb10 s s3'><a href='{$this->company->links[$i]}' itemprop='url' rel='nofollow' target='_blank'>{$hostname}</a></li>";
      }
      $links .= "</ul>";
    }
    $content .= $this->getSection("links", $i18n->t("External Links"), $links);

    return $content;
  }

}
