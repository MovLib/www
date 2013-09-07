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

/**
 * Interface for presenters that implement forms.
 *
 * We know how to use interfaces and we'd love to use them, but the performance impact is simplay to high. Instantiating
 * an object of a class that implements a single interface takes four times longer than the same object without the
 * interface. This interface is not implemented by a single presenter, but any presenter that wants to provide a form
 * would have to be compatible with this interface. Take it as a reference!
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
interface InterfaceFormPresenter {

  /**
   * Every presenter that provides forms has to have a validate method which is called automagically after the form
   * has finished validating it's own input elements (at least the ones that have a validate method).
   *
   * @param \MovLib\View\HTML\Form $form
   *   The form that was previously created and already validated.
   * @return this
   */
  public function validate($form);

}
