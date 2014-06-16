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

use \MovLib\Partial\Date;

/**
 * Contains utility methods for companies.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait CompanyTrait {

  /**
   * Format a single listing's item.
   *
   * @param \MovLib\Data\Company\Company $company
   *   The company to format.
   * @param integer $id
   *   The current loops delta.
   * @return string
   *   A formated list item.
   */
  protected function formatListingItem(\MovLib\Core\Entity\EntityInterface $company, $id) {
    $companyDates = (new Date($this->intl, isset($this->presenter) ? $this->presenter : $this))->formatFromTo(
      $company->foundingDate,
      $company->defunctDate,
      [ "property" => "foundingDate", "title" => $this->intl->t("Founding Date") ],
      [ "property" => "defunctDate",  "title" => $this->intl->t("Defunct Date")  ],
      true
    );
    if ($companyDates) {
      $companyDates = "<small>{$companyDates}</small>";
    }
    $route = $company->route;
    return
      "<li class='hover-item r'>" .
        "<article typeof='Company'>" .
          "<a class='no-link s s1' href='{$route}'>" .
            "<img alt='' src='{$this->fs->getExternalURL("asset://img/logo/vector.svg")}' width='60' height='60'>" .
          "</a>" .
          "<div class='s s9'>" .
            "<div class='fr'>" .
              "<a class='ico ico-movie label' href='{$this->intl->r("/company/{0}/movies", $id)}' title='{$this->intl->t("Movies")}'>{$company->movieCount}</a>" .
              "<a class='ico ico-series label' href='{$this->intl->r("/company/{0}/series", $id)}' title='{$this->intl->tp(-1, "Series")}'>{$company->seriesCount}</a>" .
              "<a class='ico ico-release label' href='{$this->intl->r("/company/{0}/releases", $id)}' title='{$this->intl->t("Releases")}'>{$company->releaseCount}</a>" .
            "</div>" .
            "<h2 class='para'><a href='{$route}' property='url'><span property='name'>{$company->name}</span></a></h2>" .
            $companyDates .
          "</div>" .
        "</article>" .
      "</li>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  protected function getPlural() {
    return $this->intl->t("Companies");
  }

  /**
   * {@inheritdoc}
   */
  protected function getSingular() {
    return $this->intl->t("Company");
  }

  /**
   * {@inheritdoc}
   */
  protected function getSidebarItems() {
    $items = [];
    if ($this->entity->deleted) {
      return $items;
    }
    $navItems = [
      [ "movie", "movies", $this->intl->t("Movies"), $this->entity->movieCount ],
      [ "series", "series", $this->intl->tp(-1, "Series"), $this->entity->seriesCount ],
      [ "release separator", "releases", $this->intl->t("Releases"), $this->entity->releaseCount ],
    ];
    foreach ($navItems as list($icon, $plural, $title, $count)) {
      $items[] = [
        $this->intl->r("/company/{0}/{$plural}", $this->entity->id),
        "{$title} <span class='fr'>{$this->intl->formatInteger($count)}</span>",
        [ "class" => "ico ico-{$icon}" ]
      ];
    }
    return $items;
  }

}
