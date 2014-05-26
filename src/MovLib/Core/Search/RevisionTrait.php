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
namespace MovLib\Core\Search;

/**
 * Defines the revision trait.
 *
 * This trait provides a default implementation for search indexing for a class that implements the
 * {@see \MovLib\Core\Revision\OriginatorInterface}. Note that the inclusion of this trait and the
 * {@see \MovLib\Core\Revision\OriginatorTrait} will result in a fatal error because both traits define the same
 * methods. But this is actually what we want. You can use the following snippet to manually resolve this.
 *
 * <pre>
 * use \MovLib\Core\Revision\OriginatorTrait;
 * use \MovLib\Core\Search\RevisionTrait;
 *
 * class MyClass implements \MovLib\Core\Revision\OriginatorInterface {
 *   use OriginatorTrait, RevisionTrait {
 *     RevisionTrait::postCommit insteadof OriginatorTrait;
 *     RevisionTrait::postCreate insteadof OriginatorTrait;
 *   }
 * </pre>
 *
 * This will resolve the conflict and you're good to go.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait RevisionTrait {


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Define the search index.
   *
   * The passed search indexer has the index set to the plural key, type to singular key and identifier to the concrete
   * classes unique identifier. The implementing concrete class has to call the appropriate indexing methods and index
   * its properties as needed. The {@see \MovLib\Core\Search\Search::execute()} is called in this trait after this
   * method has returned.
   *
   * @return \MovLib\Core\Search\Search
   *   The search with the search index defined.
   */
  abstract protected function defineSearchIndex(\MovLib\Core\Search\SearchIndexer $search, \MovLib\Core\Revision\RevisionInterface $revision);


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Callback for the {@see RevisionTrait::postCreate()} and {@see RevisionTrait::postCommit()} methods.
   *
   * @param \MovLib\Core\Revision\RevisionInterface $revision
   *   The revision to index.
   * @return this
   */
  protected function indexSearch(\MovLib\Core\Revision\RevisionInterface $revision) {
    // Create new search indexer instance and set default values, a concrete class can overwrite them via the dedicated
    // setters of the indexer.
    $search = new SearchIndexer($this->pluralKey, $this->singularKey, $this->id);

    // Let the concrete class define which properties should be indexed and how.
    $this->defineSearchIndex($search, $revision);

    // We execute the last returned definition. Note that a concrete class might have called execute several times. The
    // indexer only stacks his definitions. In case a concrete class already called execute nothing will happen, because
    // the definition of the instance is null.
    $search->execute($this->container->kernel, $this->container->log, $this->deleted);

    return $this;
  }

  /**
   * @see \MovLib\Core\Revision\OriginatorTrait::postCommit()
   */
  protected function postCommit(\MovLib\Core\Database\Connection $connection, \MovLib\Core\Revision\RevisionInterface $revision, $oldRevisionId) {
    return $this->indexSearch($revision);
  }

  /**
   * @see \MovLib\Core\Revision\OriginatorTrait::postCreate()
   */
  protected function postCreate(\MovLib\Core\Database\Connection $connection, \MovLib\Core\Revision\RevisionInterface $revision) {
    return $this->indexSearch($revision);
  }

}
