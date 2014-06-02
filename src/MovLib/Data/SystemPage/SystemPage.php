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
namespace MovLib\Data\SystemPage;

use \MovLib\Core\Database\Database;
use \MovLib\Core\Revision\OriginatorTrait;
use \MovLib\Core\Search\RevisionTrait;
use \MovLib\Exception\ClientException\NotFoundException;

/**
 * Handling of one system page.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class SystemPage extends \MovLib\Data\AbstractEntity implements \MovLib\Core\Revision\OriginatorInterface {
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
  const name = "SystemPage";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The page's localized title.
   *
   * @var string
   */
  public $title;

  /**
   * The page's localized text.
   *
   * @var string
   */
  public $text;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new system page object.
   *
   * @param \MovLib\Core\Container $container
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The system page's unique identifier to instantiate, defaults to <code>NULL</code> (no system page will be loaded).
   * @param array $values [optional]
   *   An array of values to set, keyed by property name, defaults to <code>NULL</code>.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\Container $container, $id = null, array $values = null) {
    $this->lemma =& $this->title;
    if ($id) {
      $connection = Database::getConnection();
      $stmt = $connection->prepare(<<<SQL
SELECT
  `id`,
  `changed`,
  `created`,
  `deleted`,
  IFNULL(
    COLUMN_GET(`dyn_titles`, '{$container->intl->languageCode}' AS CHAR(255)),
    COLUMN_GET(`dyn_titles`, '{$container->intl->defaultLanguageCode}' AS CHAR(255))
  ),
  IFNULL(
    COLUMN_GET(`dyn_texts`, '{$container->intl->languageCode}' AS BINARY),
    COLUMN_GET(`dyn_texts`, '{$container->intl->defaultLanguageCode}' AS BINARY)
  ),
  COLUMN_GET(`dyn_titles`, '{$container->intl->defaultLanguageCode}' AS CHAR(255))
FROM `system_pages`
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
        $this->title,
        $this->text,
        $this->routeKey
      );
      $found = $stmt->fetch();
      $stmt->close();
      if (!$found) {
        throw new NotFoundException("Couldn't find Systempage {$id}");
      }
    }
    parent::__construct($container, $values);
  }

  /**
   * {@inheritdoc}
   */
  public function init(array $values = null) {
    parent::init($values);
    if ($this->routeKey == "About {$this->container->config->sitename}") {
      $this->route->route = "/about";
    }
    else {
      $route = sanitize_filename($this->routeKey);
      $this->route->route = "/{$route}";
    }
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function defineSearchIndex(\MovLib\Core\Search\SearchIndexer $search, \MovLib\Core\Revision\RevisionInterface $revision) {
    return $search->indexSimpleSuggestion($revision->titles);
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\SystemPage\SystemPageRevision $revision {@inheritdoc}
   * @return \MovLib\Data\SystemPage\SystemPageRevision {@inheritdoc}
   */
  protected function doCreateRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    $this->setRevisionArrayValue($revision->texts, $this->text);

    return $revision;
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\SystemPage\SystemPageRevision $revision {@inheritdoc}
   * @return this {@inheritdoc}
   */
  protected function doSetRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    $this->text  = $this->getRevisionArrayValue($revision->texts);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function lemma($locale) {
    static $titles = null;
    $languageCode = "{$locale{0}}{$locale{1}}";
    if (!$titles) {
      $titles = json_decode(Database::getConnection()->query("SELECT COLUMN_JSON(`dyn_titles`) FROM `system_pages` WHERE `id` = {$this->id} LIMIT 1")->fetch_all()[0][0], true);
    }
    return isset($titles[$languageCode]) ? $titles[$languageCode] : $titles[$this->intl->defaultLanguageCode];
  }

}
