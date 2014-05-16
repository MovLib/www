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
 * Defines the revision object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Revision extends \MovLib\Core\AbstractDatabase {


  //-------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The new revision entity, depending on the construction parameters.
   *
   * @var \MovLib\Data\AbstractRevisionEntity
   */
  public $newRevision;

  /**
   * The old revision entity, depending on the construction parameters.
   *
   * @var \MovLib\Data\AbstractRevisionEntity
   */
  public $oldRevision;

  /**
   * The presenting presenter.
   *
   * @var \MovLib\Presentation\AbstractPresenter
   */
  public $presenter;

  /**
   * The class name of the revision class.
   *
   * @var string
   */
  public $revisionClass;

  /**
   * The revision entity's identifier.
   *
   * @var integer
   */
  public $revisionEntityId;

  /**
   * Active request instance.
   *
   * @var \MovLib\Core\HTTP\Request
   */
  public $request;

  /**
   * Active response instance.
   *
   * @var \MovLib\Core\HTTP\Response
   */
  public $response;

  /**
   * Active session instance.
   *
   * @var \MovLib\Core\HTTP\Session
   */
  public $session;


  //-------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Instantiate new Revision.
   *
   * @param \MovLib\Core\HTTP\DIContainerHTTP $diContainer
   *   The dependency injection container for the HTTP context.
   * @param integer $entityTypeId
   *
   */
  public function __construct(\MovLib\Core\HTTP\DIContainerHTTP $diContainer, $entityClassName, $entityId, $oldChanged = null, $newChanged = null) {
    parent::__construct($diContainer);
    $this->presenter = $diContainer->presenter;
    $this->request   = $diContainer->request;
    $this->response  = $diContainer->response;
    $this->session   = $diContainer->session;

    $this->revisionClass = "\\MovLib\\Data\\{$entityClassName}\\{$entityClassName}Revision";
    $this->revisionEntityId = constant("{$this->revisionClass}::ENTITY_ID");
    $this->newRevision = new $this->revisionClass($diContainer, $entityId);
    if ($newChanged) {
      // Patch newRevision back to this datetime.
    }
    if ($oldChanged) {
      // Patch from newRevision back to this datetime and put it into oldRevision.
    }
  }

  /**
   * Save a new entity revision.
   *
   * @param \MovLib\Data\AbstractEntity $entity
   *   The entity with the new values.
   * @return $this
   * @throws \MovLib\Data\Exception
   */
  public function saveRevision(\MovLib\Data\AbstractEntity $entity) {
    $mysqli = $this->getMySQLi();
    // Populate the new state and make the patch.
    $this->oldRevision = $this->newRevision;
    $this->newRevision = new $this->revisionClass($this->diContainer, $entity->id);
    $this->newRevision->setEntity($entity);
    $diff = xdiff_string_bdiff(serialize($this->newRevision), serialize($this->oldRevision));

    try {
      $mysqli->autocommit(false);
      $entity->commit();
      $stmt = $mysqli->prepare(<<<SQL
INSERT INTO `revisions` SET
  `revision_entity_id` = ?,
  `entity_id`      = ?,
  `id`        = ?,
  `user_id`        = ?,
  `data`           = ?
SQL
      );
      $stmt->bind_param(
        "ddsds",
        $this->revisionEntityId,
        $entity->id,
        $entity->changed,
        $this->session->userId,
        $diff
      );
      $stmt->execute();
      $mysqli->commit();
    }
    catch (\Exception $e) {
      $mysqli->rollback();
      throw $e;
    }
    finally {
      $mysqli->autocommit(true);
    }
    return $this;
  }

}
