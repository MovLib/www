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

use \MovLib\Component\Date;
use \MovLib\Core\Database\Database;
use \MovLib\Core\Revision\OriginatorTrait;
use \MovLib\Core\Search\RevisionTrait;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the award entity object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Award extends \MovLib\Data\AbstractEntity implements \MovLib\Core\Revision\OriginatorInterface {
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
  const name = "Award";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The award's aliases.
   *
   * @var null|array
   */
  public $aliases;

  /**
   * The award's category count.
   *
   * @var null|integer
   */
  public $categoryCount;

  /**
   * The award's company count.
   *
   * @var null|integer
   */
  public $companyCount;

  /**
   * The award's description in the current locale.
   *
   * @var null|string
   */
  public $description;

  /**
   * The award's event count.
   *
   * @var null|integer
   */
  public $eventCount;

  /**
   * The award's first event year.
   *
   * @var null|\MovLib\Component\Date
   */
  public $firstEventYear;

  /**
   * The award image's localized description.
   *
   * @var string
   */
  public $imageDescription;

  /**
   * The award's last event year.
   *
   * @var null|\MovLib\Component\Date
   */
  public $lastEventYear;

  /**
   * The award's person count.
   *
   * @var null|integer
   */
  public $personCount;

  /**
   * The award's weblinks.
   *
   * @var null|array
   */
  public $links;

  /**
   * The award's movie count.
   *
   * @var null|integer
   */
  public $movieCount;

  /**
   * The award's name.
   *
   * @var string
   */
  public $name;

  /**
   * The award's series count.
   *
   * @var null|integer
   */
  public $seriesCount;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new award object.
   *
   * @param \MovLib\Core\Container $container
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The award's unique identifier to instantiate, defaults to <code>NULL</code> (no award will be loaded).
   * @param array $values [optional]
   *   An array of values to set, keyed by property name, defaults to <code>NULL</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\Container $container, $id = null, array $values = null) {
    $this->lemma =& $this->name;
    if ($id) {
      $stmt = Database::getConnection()->prepare(<<<SQL
SELECT
  `id` AS `id`,
  `changed`,
  `created`,
  `deleted`,
  `name`,
  `first_event_year`,
  `last_event_year`,
  COLUMN_GET(`dyn_descriptions`, '{$container->intl->code}' AS CHAR),
  COLUMN_GET(`dyn_image_descriptions`, '{$container->intl->code}' AS CHAR),
  `links`,
  COLUMN_GET(`dyn_wikipedia`, '{$container->intl->code}' AS CHAR),
  `aliases`,
  `count_movies`,
  `count_series`,
  `count_persons`,
  `count_companies`,
  `count_categories`,
  `count_events`
FROM `awards`
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
        $this->firstEventYear,
        $this->lastEventYear,
        $this->description,
        $this->imageDescription,
        $this->links,
        $this->wikipedia,
        $this->aliases,
        $this->movieCount,
        $this->seriesCount,
        $this->personCount,
        $this->companyCount,
        $this->categoryCount,
        $this->eventCount
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find Award {$id}");
      }
    }
    parent::__construct($container, $values);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function defineSearchIndex(\MovLib\Core\Search\SearchIndexer $search, \MovLib\Core\Revision\RevisionInterface $revision) {
    return $search->indexSimpleSuggestion($revision->name);
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Award\AwardRevision $revision {@inheritdoc}
   * @return \MovLib\Data\Award\AwardRevision {@inheritdoc}
   */
  protected function doCreateRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    $this->setRevisionArrayValue($revision->descriptions, $this->description);
    $this->setRevisionArrayValue($revision->imageDescriptions, $this->imageDescription);
    $this->setRevisionArrayValue($revision->wikipediaLinks, $this->wikipedia);
    $revision->aliases = $this->aliases;
    $revision->links   = $this->links;
    $revision->name    = $this->name;
    return $revision;
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Award\AwardRevision $revision {@inheritdoc}
   * @return this {@inheritdoc}
   */
  protected function doSetRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    $this->description      = $this->getRevisionArrayValue($revision->descriptions);
    $this->imageDescription = $this->getRevisionArrayValue($revision->imageDescriptions);
    $this->wikipedia        = $this->getRevisionArrayValue($revision->wikipediaLinks);
    $revision->aliases      && $this->aliases = $revision->aliases;
    $revision->links        && $this->links   = $revision->links;
    $revision->name         && $this->name    = $revision->name;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function init(array $values = null) {
    parent::init($values);
    if (isset($this->aliases) && !is_array($this->aliases)) {
      $this->aliases = unserialize($this->aliases);
    }
    if (isset($this->links) && !is_array($this->links)) {
      $this->links = unserialize($this->links);
    }
    $this->firstEventYear && ($this->firstEventYear = new Date($this->firstEventYear));
    $this->lastEventYear  && ($this->lastEventYear  = new Date($this->lastEventYear));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function lemma($locale) {
    return $this->name;
  }

}
