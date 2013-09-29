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
namespace MovLib\Presentation\User;

/**
 * Shared methods for pages in the User namespace.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitUser {

  /**
   * @inheritdoc
   */
  protected function getBreadcrumbs() {
    global $i18n;
    return [[ $i18n->r("/users"), $i18n->t("Users") ]];
  }

  /**
   * @inheritdoc
   */
  protected function getSecondaryNavigationMenuitems() {
    global $i18n;
    return [
      [
        $i18n->r("/user/{0}", [ $_SERVER["USER_NAME"] ]),
        $this->checkPlain($_SERVER["USER_NAME"]),
        [ "class" => "separator" ],
      ],
      [
        $i18n->r("/user/{0}/collection", [ $_SERVER["USER_NAME"] ]),
        $i18n->t("Collection"),
      ],
      [
        $i18n->r("/user/{0}/contact", [ $_SERVER["USER_NAME"] ]),
        $i18n->t("Contact"),
      ],
    ];
  }

}
