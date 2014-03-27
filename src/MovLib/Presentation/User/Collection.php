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
namespace MovLib\Presentation\User;

use \MovLib\Presentation\Partial\Alert;

/**
 * The user's movie collection page.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Collection extends \MovLib\Presentation\User\AbstractUserPage {

  /**
   * Instantiate new user collection presentation.
   *
   */
  public function __construct(){
    $this->init();
    $this->initPage($this->intl->t("Collection {0}", [ $this->user->name ]));
    $this->initLanguageLinks("/user/{0}/collection", [ $this->user->name ]);
    $this->pageTitle       = $this->intl->t("Collection of {username}", [ "username" => "<a href='{$this->user->route}'>{$this->user->name}</a>" ]);
    $this->breadcrumbTitle = $this->intl->t("Collection");
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent(){
    $this->alerts .= new Alert(
      $this->intl->t("The user collection feature isn’t implemented yet."),
      $this->intl->t("Check back later"),
      Alert::SEVERITY_INFO
    );
  }

}
