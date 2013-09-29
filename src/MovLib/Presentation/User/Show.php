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

use \MovLib\Exception\UserException;
use \MovLib\Data\User;

/**
 * Description of Show
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\AbstractSecondaryNavigationPage {
  use \MovLib\Presentation\User\TraitUser;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user we are currently displaying.
   *
   * @var \MovLib\Data\User
   */
  protected $user;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  public function __construct() {
    try {
      $this->user = new User(User::FROM_NAME, $_SERVER["USER_NAME"]);
      $this->init($this->checkPlain($this->user->name));
    }
    catch (UserException $e) {

    }
  }

  protected function getPageContent(){
    global $i18n;
    return "<pre>" . print_r($this->user, true) . "</pre>";
  }

}
