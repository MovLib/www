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
namespace MovLib\View\HTML\User;

use \MovLib\View\HTML\AbstractView;

/**
 * Description of UserProfileView
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserProfileView extends AbstractView {

  /**
   * The user presenter controlling this view.
   *
   * @var \MovLib\Presenter\UserPresenter
   */
  protected $presenter;

  /**
   * Instantiate new user profile view.
   *
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @param \MovLib\Presenter\UserPresenter $userPresenter
   *   The user presenter controlling this view.
   */
  public function __construct($userPresenter) {
    parent::__construct($userPresenter, $userPresenter->profile->name, [ "/assets/css/module/user.css" ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return
      "<div class='container'>" .
        "<pre>" . print_r($_SERVER, true) . "</pre>" .
      "</div>"
    ;
  }

}
