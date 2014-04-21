<?php

/* !
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
namespace MovLib\Data\Cast;

use \MovLib\Partial\Sex;

/**
 * Defines a cast entity object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Cast extends \MovLib\Data\Job\Job {


  // ------------------------------------------------------------------------------------------------------------------- Constants

  const JOB_ID = 1;

  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The cast's alias.
   *
   * @var string
   */
  public $alias;

  /**
   * The entity this cast member belongs to.
   *
   * @var \MovLib\Data\AbstractEntity
   */
  protected $entity;

  /**
   * The cast's job identifier.
   *
   * @var integer
   */
  public $jobId;

  /**
   * The cast's movie identifier.
   *
   * @var integer
   */
  public $movieId;

  /**
   * The cast's person identifier.
   *
   * @var integer
   */
  public $personId;

  /**
   * The role name (for roles with no further data).
   *
   * @var string
   */
  public $role;

  /**
   * The role identifier (for persons playing other persons).
   *
   * @var integer
   */
  public $roleId;

  /**
   * The role person's name.
   *
   * @var string
   */
  public $roleName;

  public static $roleTitleSelf = [
    Sex::UNKNOWN        => "Self",
    Sex::MALE           => "Himself",
    Sex::FEMALE         => "Herself",
    Sex::NOT_APPLICABLE => null,
  ];

  /**
   * The cast's series identifier.
   *
   * @var integer
   */
  public $seriesId;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new single cast member.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   {@inheritdoc}
   * @param \MovLib\Data\AbstractEntity $entity
   *   The entity this cast member belongs to.
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer, \MovLib\Data\AbstractEntity $entity) {
    parent::__construct($diContainer);
    $this->entity = $entity;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init() {
    return parent::init();
  }


}
