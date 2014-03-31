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
namespace MovLib\Data\Award;

/**
 * Defines the award entity object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Award extends \MovLib\Data\AbstractEntity {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The award’s aliases.
   *
   * @var array
   */
  public $aliases = [];

  /**
   * The award's deletion state.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The award's description in the current display language.
   *
   * @var string
   */
  public $description;

  /**
   * The award's first event year.
   *
   * @var integer
   */
  public $firstEventYear;

  /**
   * The award's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The award's last event year.
   *
   * @var integer
   */
  public $lastEventYear;

  /**
   * The award’s weblinks.
   *
   * @var array
   */
  public $links = [];

  /**
   * The award's name in the current display language.
   *
   * @var string
   */
  public $name;

  /**
   * The award's route in the current locale.
   *
   * @var string
   */
  public $route;

  /**
   * The award’s translated Wikipedia link.
   *
   * @var string
   */
  public $wikipedia;


  // ------------------------------------------------------------------------------------------------------------------- Initialize


  /**
   * Initialize after instantiation via PHP's built in <code>\mysqli_result::fetch_object()}
   */
  public function initFetchObject() {
    $this->route = $this->intl->r("/award/{0}", $this->id);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function getPluralName() {
    return "awards";
  }

  /**
   * {@inheritdoc}
   */
  public function getSingularName() {
    return "award";
  }

}
