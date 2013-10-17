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
namespace MovLib\Presentation\Users;

use \MovLib\Data\User\Users;
use \MovLib\Presentation\Partial\Lists\Images;

/**
 * Description of Show
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\Page {

  /**
   * The users database instance.
   *
   * @var \MovLib\Data\User\Users
   */
  protected $users;

  /**
   * The translated route to user page's.
   *
   * @var string
   */
  protected $userRoute;

  /**
   * Instantiate new users show presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;
    $this->init($i18n->t("Users"));
    $this->users     = (new Users())->orderByCreated();
    $this->userRoute = "{$i18n->r("/user")}/";
  }

  /**
   * @inheritdoc
   */
  protected function getContent() {
    $list = new Images($this->users);
    $list->closure = [ $this, "renderAvatar" ];
    return "<div class='container'><div class='row'>{$list}</div></div>";
  }

  /**
   *
   * @param \MovLib\Data\Stub\User $user
   * @param type $attributes
   * @param type $i
   * @return type
   */
  public function renderAvatar($user, $attributes, $i) {
    $routeName = rawurlencode($user->imageName);
    if ($user->imageExists == false) {
      $attributes["src"] = "https://alpha.movlib.org/img/logo/vector.svg";
    }
    return "<a class='ia' href='{$this->userRoute}{$routeName}'><img{$this->expandTagAttributes($attributes)}><br><small>{$user->imageName}</small></a>";
  }

}
