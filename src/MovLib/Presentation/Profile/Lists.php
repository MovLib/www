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
 * Allows a user to manage her or his lists.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Lists extends \MovLib\Presentation\Profile\Show {

  /**
   * Instantiate new lists presentation.
   *
   * @throws \MovLib\Presentation\Error\Unauthorized
   */
  public function __construct() {
    $session->checkAuthorization($this->intl->t("You must be signed in to view your lists."));
    $this->init($this->intl->t("My Lists"), "/profile/lists", [[ $this->intl->r("/profile"), $this->intl->t("Profile") ]]);
  }

  /**
   * @inhertidoc
   */
  protected function getBreadcrumbs() {
    return [[ $this->intl->r("/profile"), $this->intl->t("Profile") ]];
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    return new Alert(
      $this->intl->t("The lists aren't implemented yet."),
      $this->intl->t("Check back later"),
      Alert::SEVERITY_INFO
    );
  }

}
