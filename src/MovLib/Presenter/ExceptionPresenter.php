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

namespace MovLib\Presenter;

use \MovLib\View\HTML\Error\ExceptionView;

/**
 * Present an exception to the user.
 *
 * @todo This presenter has to be extend to work for API requestes as well.
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class ExceptionPresenter {

  /**
   * The current view.
   *
   * @var \MovLibe\View\HTML\Error\ExceptionView
   */
  public $view;

  /**
   * Instantiate new exception presentation.
   *
   * @param \Exception $exception
   *   Any exception that extends PHP's base exception class.
   */
  public function __construct($exception) {
    new ExceptionView($this, $exception);
  }

  /**
   * Any error view never has a breadcrumb, we need to implement this to ensure that our interface is equal to the
   * interface of the abstract presenter, as this method is automatically called.
   *
   * @return array
   */
  public function getBreadcrumb() {}

  /**
   * Get the presentation of this presenter.
   *
   * @return string
   */
  public function __toString() {
    return $this->view->__toString();
  }

}
