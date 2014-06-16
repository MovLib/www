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
use \MovLib\Data\Award\Award;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Defines the award category entity object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Category extends \MovLib\Data\AbstractEntity implements \MovLib\Core\Revision\OriginatorInterface {
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
  const name = "Category";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The category's award.
   *
   * @var \Movlib\Data\Award\Award
   */
  public $award;

  /**
   * The category's award identifier.
   *
   * @var integer
   */
  public $awardId;

  /**
   * The category's company count.
   *
   * @var null|integer
   */
  public $companyCount;

  /**
   * The category's name in our default language.
   *
   * @var null|string
   */
  public $defaultName;

  /**
   * The category's description in the current locale.
   *
   * @var null|string
   */
  public $description;

  /**
   * The category's first year.
   *
   * @var null|\MovLib\Component\Date
   */
  public $firstYear;

  /**
   * Flag determining if an award should be instanitated
   * (needed for preventing unnecessary instantiations in fetch_object()).
   *
   * @var boolean
   */
  protected $initAward;

  /**
   * The category's last year.
   *
   * @var null|\MovLib\Component\Date
   */
  public $lastYear;

  /**
   * The category's person count.
   *
   * @var null|integer
   */
  public $personCount;


  /**
   * The category's movie count.
   *
   * @var null|integer
   */
  public $movieCount;

  /**
   * The category's name in the current display language.
   *
   * @var string
   */
  public $name;

  /**
   * The category's series count.
   *
   * @var null|integer
   */
  public $seriesCount;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new award category object.
   *
   * @param \MovLib\Core\Container $container
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The category's unique identifier to instantiate, defaults to <code>NULL</code> (no category will be loaded).
   * @param array $values [optional]
   *   An array of values to set, keyed by property name, defaults to <code>NULL</code>.
   * @param mixed $initAward [optional]
   *   Whether to instantiate an award object or not, defaults to <code>TRUE</code>
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\Container $container, $id = null, array $values = null, $initAward = true) {
    $this->initAward = $initAward;
    $this->lemma =& $this->name;
    if ($id) {
      $stmt = Database::getConnection()->prepare(<<<SQL
SELECT
  `id`,
  `award_id`,
  `changed`,
  `created`,
  COLUMN_GET(`dyn_names`, '{$container->intl->defaultCode}' AS CHAR),
  `deleted`,
  IFNULL(
    COLUMN_GET(`dyn_names`, '{$container->intl->code}' AS CHAR),
    COLUMN_GET(`dyn_names`, '{$container->intl->defaultCode}' AS CHAR)
  ),
  `first_year`,
  `last_year`,
  COLUMN_GET(`dyn_descriptions`, '{$container->intl->code}' AS CHAR),
  COLUMN_GET(`dyn_wikipedia`, '{$container->intl->code}' AS CHAR),
  `count_movies`,
  `count_series`,
  `count_persons`,
  `count_companies`
FROM `awards_categories`
WHERE `id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->id,
        $this->awardId,
        $this->changed,
        $this->created,
        $this->defaultName,
        $this->deleted,
        $this->name,
        $this->firstYear,
        $this->lastYear,
        $this->description,
        $this->wikipedia,
        $this->movieCount,
        $this->seriesCount,
        $this->personCount,
        $this->companyCount
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
    return $search->indexSimpleSuggestion($revision->names);
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Award\CategoryRevision $revision {@inheritdoc}
   * @return \MovLib\Data\Award\CategoryRevision {@inheritdoc}
   */
  protected function doCreateRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    $this->setRevisionArrayValue($revision->descriptions, $this->description);
    $this->setRevisionArrayValue($revision->names, $this->name);
    $this->setRevisionArrayValue($revision->wikipediaLinks, $this->wikipedia);
    $revision->awardId   = $this->awardId;
    $revision->firstYear = $this->firstYear->year;
    $revision->lastYear  = $this->lastYear->year;
    // Don't forget that we might be a new genre and that we might have been created via a different system locale than
    // the default one, in which case the user was required to enter a default name. Of course we have to export that
    // as well to our revision.
    if (isset($this->defaultName)) {
      $revision->names[$this->intl->defaultCode] = $this->defaultName;
    }

    return $revision;
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Award\CategoryRevision $revision {@inheritdoc}
   * @return this {@inheritdoc}
   */
  protected function doSetRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    $this->description   = $this->getRevisionArrayValue($revision->descriptions);
    $this->name          = $this->getRevisionArrayValue($revision->names);
    $this->wikipedia     = $this->getRevisionArrayValue($revision->wikipediaLinks);
    $revision->awardId   && $this->awardId   = $revision->awardId;
    $revision->firstYear && $this->firstYear = $revision->firstYear;
    $revision->lastYear  && $this->lastYear  = $revision->lastYear;
    return $this;
  }


  /**
   * {@inheritdoc}
   */
  public function init(array $values = null) {
    parent::init($values);
    if ($this->initAward === true && isset($this->awardId)) {
      $this->award = new Award($this->container, $this->awardId);
    }
    if (isset($this->firstYear) && !$this->firstYear instanceof \stdClass) {
      $this->firstYear = new Date($this->firstYear);
    }
    if (isset($this->lastYear) && !$this->lastYear instanceof \stdClass) {
      $this->lastYear  = new Date($this->lastYear);
    }
    $this->route->route = "/award/{0}/category/{1}";
    $this->route->args  = [ $this->awardId, $this->id ];
    $this->set->route->route = "/award/{0}/categories";
    $this->set->route->args  = [ $this->awardId ];
    return $this;
  }

  /**
   * {@inheritdoc}
   */
public function lemma($locale) {
    static $names = null;

    // No need to ask the database if the requested locale matches the loaded locale.
    if ($locale == $this->intl->locale) {
      return $this->name;
    }

    // Extract the language code from the given locale.
    $languageCode = "{$locale{0}}{$locale{1}}";

    // Load all names for this genre if we haven't done so yet.
    if (!$names) {
      $names = json_decode(Database::getConnection()->query("SELECT COLUMN_JSON(`dyn_names`) FROM `awards_categories` WHERE `id` = {$this->id} LIMIT 1")->fetch_all()[0][0], true);
    }

    return isset($names[$languageCode]) ? $names[$languageCode] : $names[$this->intl->defaultCode];
  }

}
