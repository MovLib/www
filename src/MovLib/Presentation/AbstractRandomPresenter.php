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

/**
 * Defines the base class for all random presenters.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractRandomPresenter extends \MovLib\Presentation\AbstractPresenter {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AbstractRandomPresenter";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public function init() {
    // Nothing to do!
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    // Extract the entity class name from the namespace of the concrete presentation class.
    $entityName = explode("\\", static::class);
    array_pop($entityName); // Last element is the name of the concrete class.
    $entityName = end($entityName);

    // Build absolute class name of the set and instantiate it.
    $setClass = "\\MovLib\\Data\\{$entityName}\\{$entityName}Set";
    $set = new $setClass($this->container);

    // Try to fetch a random entity identifier.
    if (($id = $set->getRandom())) {
      throw new SeeOtherException($this->intl->r("/{$set->singularKey}/{0}", $id));
    }

    // We couldn't find one ...
    $this->alertInfo($this->intl->t("Check back later"), $this->intl->t("We couldn’t find a single random page for you…"));
    throw new SeeOtherException($set->route);
  }

}
