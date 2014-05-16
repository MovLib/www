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
namespace MovLib\Data;

/**
 * Defines the base class for revisioned database entities.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractRevisionEntity extends \MovLib\Core\AbstractDatabase {


  //-------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The movie entity type identifier.
   *
   * @var integer
   */
  const ENTITY_TYPE_MOVIE = 1;

  /**
   * The series entity type identifier.
   *
   * @var integer
   */
  const ENTITY_TYPE_SERIES = 2;

  /**
   * The release entity type identifier.
   *
   * @var integer
   */
  const ENTITY_TYPE_RELEASE = 3;

  /**
   * The person entity type identifier.
   *
   * @var integer
   */
  const ENTITY_TYPE_PERSON = 4;

  /**
   * The company entity type identifier.
   *
   * @var integer
   */
  const ENTITY_TYPE_COMPANY = 5;

  /**
   * The award entity type identifier.
   *
   * @var integer
   */
  const ENTITY_TYPE_AWARD = 6;

  /**
   * The award category entity type identifier.
   *
   * @var integer
   */
  const ENTITY_TYPE_AWARD_CATEGORY = 7;

  /**
   * The event entity type identifier.
   *
   * @var integer
   */
  const ENTITY_TYPE_EVENT = 8;

  /**
   * The job entity type identifier.
   *
   * @var integer
   */
  const ENTITY_TYPE_JOB = 10;

  /**
   * The help article entity type identifier.
   *
   * @var integer
   */
  const ENTITY_TYPE_HELP_ARTICLE = 11;

  /**
   * The system page entity type identifier.
   *
   * @var integer
   */
  const ENTITY_TYPE_SYSTEMPAGE = 12;

  /**
   * The movie poster entity type identifier.
   *
   * @var integer
   */
  const ENTITY_TYPE_MOVIE_POSTER = 13;


  //-------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The revision's creation time.
   *
   * @var \MovLib\Data\DateTime
   */
  public $created;

  /**
   * The entity's deleted state.
   *
   * @var boolean
   */
  public $deleted;

  /**
   * The entity.
   *
   * @var \MovLib\Data\AbstractEntity
   */
  public $entity;

  /**
   * The revision entity's identifier.
   *
   * @var integer
   */
  public $entityId;

  /**
   * The revision entity's type.
   *
   * One of the <code>\MovLib\Data\AbstractRevisionEntity::ENTITY_TYPE_*</code> constants.
   *
   * @var integer
   */
  public $entityTypeId;

  /**
   * The revision's language code.
   *
   * @var string
   */
  public $languageCode;

  /**
   * The revision's author identifier.
   *
   * @var integer
   */
  public $userId;


  //-------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Return all property names to serialize as numeric array.
   */
  public function __sleep() {
    return [ "deleted" ];
  }

  /**
   * Get the revision's entity.
   *
   * @return \MovLib\Data\AbstractEntity
   */
  abstract public function getEntity();

  /**
   * Update the state of the revision with edit changes.
   *
   * @param \MovLib\Data\AbstractEntity $entity
   *   The entity with the changes.
   */
  public function setEntity(\MovLib\Data\AbstractEntity $entity) {
    $this->entity   = $entity;
    $this->created  = $this->entity->changed;
    $this->deleted  = $this->entity->deleted;
    $this->entityId = $this->entity->id;
  }

}
