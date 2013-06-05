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
 * Description of UserShowView
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class UserShowView extends AbstractView {

  /**
   * The user presenter controlling this view.
   *
   * @var \MovLib\Presenter\UserPresenter
   */
  protected $presenter;

  /**
   * Get user profile view.
   *
   * @param \MovLib\Presenter\UserPresenter $presenter
   *   The user presenter controlling this view.
   */
  public function __construct($presenter) {
    parent::__construct($presenter, $presenter->profile->name);
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return
      "<article>" .
        "<header class='row'>" .
          "<div class='span span--1'>" .
            "<div class='page-header'><h1>{$this->presenter->profile->name}</h1></div>" .
          "</div>" .
        "</header>" .
        "<div class='row'>" .
          "<div class='span span--1'>" .
            "<pre>" . print_r($this->presenter->profile, true) . "</pre>" .
          "</div>" .
        "</div>" .
      "</article>"
    ;
  }

}
