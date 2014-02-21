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

use \MovLib\Data\Image\CompanyImage;
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
    $routeArgs = [ $this->company->id ];
    $this->initLanguageLinks("/company/{0}", $routeArgs);
    array_pop($this->breadcrumb->menuitems);

    // Enhance the page title with microdata.
    $this->pageTitle = "<span itemprop='name'>{$this->company->name}</span>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Construct the header with formatted company information.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return $this
   */
  protected function buildHeader() {
    global $i18n;
    // Enhance the header, insert row and span before the title.
    $this->headingBefore = "<div class='r'><div class='s s10'>";

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

    // Put all header information together after the closing title.
    $this->headingAfter =
          "<p>{$info}</p>" .
        "</div>" . // close .s
        "<div id='company-photo' class='s s2'>{$this->getImage($this->company->displayPhoto->getStyle(CompanyImage::STYLE_SPAN_02), true, [ "itemprop" => "image" ])}</div>" .
      "</div>" // close .r
    ;

    return $this;
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getPageContent() {
    global $i18n;

    $editLinkOpen = "<a href='{$this->routeEdit}'>";

    // Construct personal information and put them into the header.
    $this->buildHeader();

    // Description section.
    $sections["description"] = [
      $i18n->t("Description"),
      empty($this->company->description)
        ? $i18n->t("No description available, {0}write one{1}?", [ $editLinkOpen, "</a>" ])
        : $this->htmlDecode($this->company->description)
      ,
    ];

    // Additional names section.
    $sections["aliases"] = [
      $i18n->t("Also Known As"),
      new Ordered($this->company->aliases, $i18n->t("No additional names available, {0}add some{1}?", [ $editLinkOpen, "</a>" ]), [ "class" => "grid-list no-list r" ], [ "class" => "mb10 s s3", "itemprop" => "alternateName" ]),
    ];

    // External links section.
    $links = null;
    if (empty($this->company->links)) {
      $links = $i18n->t("No links available, {0}add some{1}?", [ $editLinkOpen, "</a>" ]);
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
    $sections["links"] = [
      $i18n->t("External Links"),
      $links,
    ];

    // Construct content and sidebar.
    $content = null;
    foreach ($sections as $id => $section) {
      $this->sidebarNavigation->menuitems[] = [ "#{$id}", $section[0] ];
      $content .= "<div id='{$id}'><h2>{$section[0]}</h2>";
      if (is_array($section[1])) {
        foreach ($section[1] as $subId => $subSection) {
          $this->sidebarNavigation->menuitems[] = [ "#{$id}-{$subId}", $subSection[0] ];
          $attributes = isset($subSection[2]) ? $this->expandTagAttributes($subSection[2]) : null;
          $content .= "<div id='{$id}-{$subId}'><h3{$attributes}>{$subSection[0]}</h3>{$subSection[1]}</div>";
        }
      }
      else {
        $content .= $section[1];
      }
      $content .= "</div>";
    }
    return $content;
  }

}
