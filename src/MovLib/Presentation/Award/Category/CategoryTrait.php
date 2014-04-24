<?php

/* !
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

/**
 * Provides properties and methods that are used by several award category presenters.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait CategoryTrait {

  /**
   * {@inheritdoc}
   */
  protected function getSidebarItems() {
    $items = [];
    if ($this->entity->deleted) {
      return $items;
    }
    foreach ([
      [ "movie", "movies", $this->intl->t("Movies"), $this->entity->movieCount ],
      [ "series", "series", $this->intl->tp("Series"), $this->entity->seriesCount ],
      [ "person", "persons", $this->intl->t("Persons"), $this->entity->personCount ],
      [ "company separator", "companies", $this->intl->t("Companies"), $this->entity->companyCount ],
    ] as list($icon, $plural, $title, $count)) {
      $items[] = [
        $this->intl->r("/award/{0}/category/{1}/{$plural}", [ $this->entity->award->id, $this->entity->id ]),
        "{$title} <span class='fr'>{$this->intl->format("{0,number}", $count)}</span>",
        [ "class" => "ico ico-{$icon}" ]
      ];
    }
    return $items;
  }

  /**
   * Get all breadcrumbs for a category entity.
   *
   * @return array
   */
  protected function getBreadCrumbs() {
    return [
      [ $this->intl->r("/awards"), $this->intl->t("Awards") ],
      [ $this->intl->r("/award/{0}/", [ $this->entity->award->id ]), $this->entity->award->name ],
      [ $this->intl->r("/award/{0}/categories", [ $this->entity->award->id ]), $this->intl->t("Categories") ],
      [ $this->entity->route, $this->entity->name ]
    ];
  }

}
