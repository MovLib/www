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
namespace MovLib\Presenter\User;

use \MovLib\Model\UserModel;
use \MovLib\Presenter\User\AbstractUserPresenter;
use \MovLib\View\HTML\User\UserShowView;

/**
 * Description of UserShowPresenter
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserShowPresenter extends AbstractUserPresenter {

  /**
   * Instantiate new user show presenter.
   *
   * Please note that there is theoretically no possibility that the exception is ever thrown. If the user has a valid
   * session, the user has a profile.
   *
   * @global \MovLib\Model\SessionModel $user
   * @throws \MovLib\Exception\UserException
   */
  public function __construct() {
    global $user;
    $this->checkAuthorization()->profile = new UserModel(UserModel::FROM_ID, $user->id);
    new UserShowView($this);
  }

}
