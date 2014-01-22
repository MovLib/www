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
namespace MovLib\Presentation\Profile;

use \MovLib\Presentation\Partial\Alert;

/**
 * Allows a user to manage her or his wantlist.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Wantlist extends \MovLib\Presentation\Profile\Show {

  /**
   * Instantiate new wantlist presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\Session $session
   * @throws \MovLib\Presentation\Error\Unauthorized
   */
  public function __construct() {
    global $i18n, $session;
    $session->checkAuthorization($i18n->t("You must be signed in to view your wantlist."));
    $this->init($i18n->t("My Wantlist"), "/profile/wantlist", [[ $i18n->r("/profile"), $i18n->t("Profile") ]]);
  }

  /**
   * @inhertidoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getBreadcrumbs() {
    global $i18n;
    return [[ $i18n->r("/profile"), $i18n->t("Profile") ]];
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    global $i18n;
    return new Alert(
      $i18n->t("The wantlist isn’t implemented yet."),
      $i18n->t("Check back later"),
      Alert::SEVERITY_INFO
    );
  }

}
