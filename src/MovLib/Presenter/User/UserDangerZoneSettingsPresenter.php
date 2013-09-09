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

use \MovLib\Presenter\User\AbstractUserPresenter;
use \MovLib\HTML\User\UserDangerZoneSettingsView;

/**
 * Takes care of user danger zone settings.
 *
 * Danger zone settings include user session management and disabling of the own account.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserDangerZoneSettingsPresenter extends AbstractUserPresenter {

  /**
   * Instantiate new user danger zone settings presenter.
   */
  public function __construct() {
    $this->checkAuthorization();
    new UserDangerZoneSettingsView($this, [

    ]);
  }

  /**
   * Continue validation after the basic form elements where validated by the view itself.
   *
   * @return this
   */
  public function validate() {
    return $this;
  }

}
