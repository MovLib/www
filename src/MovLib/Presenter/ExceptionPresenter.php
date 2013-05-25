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

use \MovLib\Entity\Language;
use \MovLib\Entity\User;
use \MovLib\Presenter\AbstractPresenter;
use \MovLib\View\HTML\Error\ExceptionView;

/**
 * The error presenter is used to tell the user about an unknown error.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class ExceptionPresenter extends AbstractPresenter {

  /**
   * The exception that was thrown.
   *
   * @var \Exception
   */
  private $exception;

  /**
   * Present unknown error view.
   *
   * @param \Exception $exception
   *   The exception that was thrown.
   */
  public function __construct($exception) {
    $this->exception = $exception;
    $this->language = new Language();
    $this->user = new User();
    $this->output = (new ExceptionView($this, $this->exception))->getRenderedView();
  }

  /**
   * {@inheritdoc}
   */
  protected function init() {
    // Nothing to do here!
  }

}
