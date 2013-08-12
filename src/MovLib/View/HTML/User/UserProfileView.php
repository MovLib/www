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

use \MovLib\Model\UserModel;
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
   * @param \MovLib\Presenter\UserPresenter $userPresenter
   *   The user presenter controlling this view.
   */
  public function __construct($userPresenter) {
    parent::__construct($userPresenter, $userPresenter->profile->name);
    $this->stylesheets[] = "modules/user.css";
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    global $i18n;
    ob_start();
    var_dump($this->presenter->profile);
    $varDump = ob_get_clean();
    return
      "<div class='container'>" .
        "<div class='row'>" .
          "<div class='span span--3'>" .
            "<h2>Avatar</h2>" .
            $this->getImage($this->presenter->profile, UserModel::IMAGESTYLE_BIG) .
          "</div>" .
          "<div class='span span--9'>" .
            "<h2>\$this->presenter->profile</h2>" .
            "<pre>" . print_r($this->presenter->profile, true) . "</pre>" .
            "<pre>" . $varDump . "</pre>" .
          "</div>" .
        "</div>" .
      "</div>"
    ;
  }

}
