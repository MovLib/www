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
namespace MovLib\Data\Director;

/**
 * Defines a director entity object
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Director extends \MovLib\Data\Job\Job {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The director's alias.
   *
   * @var string
   */
  public $alias;

  /**
   * The director's job identifier.
   *
   * @var integer
   */
  public $jobId;

  /**
   * The director's movie identifier.
   *
   * @var integer
   */
  public $movieId;

  /**
   * The director's person identifier.
   *
   * @var integer
   */
  public $personId;

  /**
   * {@inheritdoc}
   */
  public $pluralKey = "directors";

  /**
   * The director's series identifier.
   *
   * @var integer
   */
  public $seriesId;

  /**
   * {@inheritdoc}
   */
  public $singularKey = "director";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new director object.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   {{@inheritdoc}}
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer) {
    parent::__construct($diContainer);
  }

}
