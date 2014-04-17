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
    foreach ([
      [ "movie", "movies", $this->intl->t("Movies"), $this->entity->movieCount ],
      [ "series", "series", $this->intl->t("{0,plural,one{Series}other{Series}}"), $this->entity->seriesCount ],
      [ "release separator", "releases", $this->intl->t("Releases"), $this->entity->releaseCount ],
    ] as list($icon, $plural, $title, $count)) {
      $items[] = [
        $this->intl->rp("/company/{0}/{$plural}", $this->entity->id),
        "{$title} <span class='fr'>{$this->intl->format("{0,number}", $count)}</span>",
        [ "class" => "ico ico-{$icon}" ]
      ];
    }
    return $items;
  }

}
