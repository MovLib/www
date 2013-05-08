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

use \Exception;
use \MovLib\Entity\Language;
use \MovLib\View\HTML\ErrorView;

/**
 * The error presenter is used to display an error if something unknown goes wrong.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class ErrorPresenter {

  /**
   * String buffer used to concatenate all the output and send the whole shebang at once.
   *
   * @var string
   */
  protected $output = '';

  /**
   * Present an error to the user.
   *
   * @param \Exception $exception
   *   The exception that caused the error.
   */
  public function __construct(Exception $exception) {
    try {
      $this->init($exception);
    } catch (LanguageException $e) {
      $_SERVER['LANGUAGE_CODE'] = 'en';
      $this->init($e);
    }
  }

  /**
   * Initialize the error view and export the output.
   *
   * @param \Exception $exception
   *   The exception that caused the error.
   * @return \MovLib\Presenter\ErrorPresenter
   * @throws \MovLib\Exception\LanguageException
   */
  private function init(Exception $exception) {
    $this->output = (new ErrorView(new Language(), $exception))->getRenderedView();
    return $this;
  }

  /**
   * Get the whole output of this presenter.
   *
   * @return string
   */
  public final function getOutput() {
    return $this->output;
  }

}
