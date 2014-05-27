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
namespace MovLib\Data\Genre;

use \MovLib\Core\Database\Database;
use \MovLib\Core\Revision\OriginatorTrait;
use \MovLib\Core\Search\RevisionTrait;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the genre entity object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Genre extends \MovLib\Data\AbstractEntity implements \MovLib\Core\Revision\OriginatorInterface {
  use OriginatorTrait, RevisionTrait {
    RevisionTrait::postCommit insteadof OriginatorTrait;
    RevisionTrait::postCreate insteadof OriginatorTrait;
  }


  //-------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Genre";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The genre's movie count.
   *
   * @var null|integer
   */
  public $countMovies;

  /**
   * The genre's series count.
   *
   * @var null|integer
   */
  public $countSeries;

  /**
   * The genre's name in default language.
   *
   * @var string
   */
  public $defaultName;

  /**
   * The genre's description in the current locale.
   *
   * @var null|string
   */
  public $description;

  /**
   * The genre's name in the current locale (default locale as fallback).
   *
   * @var string
   */
  public $name;

  /**
   * {@inheritdoc}
   */
  public $pluralKey = "genres";

  /**
   * {@inheritdoc}
   */
  public $singularKey = "genre";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new genre object.
   *
   * @param \MovLib\Core\Container $container
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The genre's unique identifier to instantiate, defaults to <code>NULL</code> (no genre will be loaded).
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\Container $container, $id = null) {
    parent::__construct($container);
    if ($id) {
      $connection = Database::getConnection();
      $stmt = $connection->prepare(<<<SQL
SELECT
  `id`,
  `changed`,
  `created`,
  `deleted`,
  IFNULL(
    COLUMN_GET(`dyn_names`, '{$this->intl->languageCode}' AS CHAR),
    COLUMN_GET(`dyn_names`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ),
  COLUMN_GET(`dyn_descriptions`, '{$this->intl->languageCode}' AS CHAR),
  COLUMN_GET(`dyn_wikipedia`, '{$this->intl->languageCode}' AS CHAR),
  `count_movies`,
  `count_series`
FROM `genres`
WHERE `id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->id,
        $this->changed,
        $this->created,
        $this->deleted,
        $this->name,
        $this->description,
        $this->wikipedia,
        $this->countMovies,
        $this->countSeries
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find Genre {$id}");
      }
    }
    if ($this->id) {
      $this->init();
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function defineSearchIndex(\MovLib\Core\Search\SearchIndexer $search, \MovLib\Core\Revision\RevisionInterface $revision) {
    return $search->indexSimpleSuggestion($revision->names);
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Genre\GenreRevision $revision {@inheritdoc}
   * @return \MovLib\Data\Genre\GenreRevision {@inheritdoc}
   */
  protected function doCreateRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    $revision->descriptions[$this->intl->languageCode] = $this->description;
    $revision->names[$this->intl->languageCode]        = $this->name;

    // Don't forget that we might be a new genre and that we might have been created via a different system locale than
    // the default one, in which case the user was required to enter a default name. Of course we have to export that
    // as well to our revision.
    if (isset($this->defaultName)) {
      $revision->names[$this->intl->defaultLanguageCode] = $this->defaultName;
    }

    return $revision;
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Genre\GenreRevision $revision {@inheritdoc}
   * @return this {@inheritdoc}
   */
  protected function doSetRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    if (isset($revision->descriptions[$this->intl->languageCode])) {
      $this->description = $revision->descriptions[$this->intl->languageCode];
    }
    if (empty($revision->names[$this->intl->languageCode])) {
      $this->name = $revision->names[$this->intl->defaultLanguageCode];
    }
    else {
      $this->name = $revision->names[$this->intl->languageCode];
    }
    return $this;
  }

}
