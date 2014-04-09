<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link https://movlib.org/ MovLib}.
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
namespace MovLib\Presentation;

use \MovLib\Exception\RedirectException\SeeOtherException;
use \MovLib\Presentation\Partial\Alert;

/**
 * Defines the base class for all random presenters.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractRandomPresenter {

  /**
   * Used to collect alerts if no random entity could be found, these alerts are automatically exported to cookies by
   * the thrown exception.
   *
   * @var null|string
   */
  public $alerts;

  /**
   * Instantiate new random presentation.
   *
   * @param \MovLib\Core\HTTP\DIContainerHTTP
   *   The dependency injection container.
   * @param \MovLib\Data\AbstractSet $set
   *   The set that will give us the random identifier.
   * @throws \MovLib\Exception\SeeOtherException
   */
  public function __construct(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP, \MovLib\Data\AbstractSet $set) {
    if (($id = $set->getRandom())) {
      throw new SeeOtherException($diContainerHTTP->intl->r("/{$set->singularKey}/{0}", $id));
    }
    $this->alerts = new Alert(
      $diContainerHTTP->intl->t("We couldn’t find a single random page for you…"),
      $diContainerHTTP->intl->t("Check back later"),
      "info"
    );
    throw new SeeOtherException($set->route);
  }

}
