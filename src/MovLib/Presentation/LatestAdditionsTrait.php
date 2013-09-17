<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Presentation;

/**
 * Description of LatestAdditionsTrait
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
trait LatestAdditionsTrait {

  /**
   * @inheritdoc
   */
  protected function init($title) {
    parent::init($title);
    $this->stylesheets[] = "modules/latest-additions.css";
  }

  /**
   * @inheritdoc
   */
  public function getSecondaryNavigationMenuItems() {
    global $i18n;
    return [
      [
        $i18n->r("/movies"),
        "<i class='icon icon--film'></i> {$i18n->t("Movies")}",
        [ "title" => $i18n->t("View the latest {0} additions to the database.", [ $i18n->t("movie") ]) ]
      ],
      [
        $i18n->r("/persons"),
        "<i class='icon icon--user'></i> {$i18n->t("Persons")}",
        [ "title" => $i18n->t("View the latest {0} additions to the database.", [ $i18n->t("person") ]) ]
      ],
      [
        $i18n->r("/help"),
        "<i class='icon icon--help-circled'></i> {$i18n->t("Help")}",
        [ "title" => $i18n->t("View the latest {0} additions to the database.", [ $i18n->t("help") ]) ]
      ]
    ];
  }

}