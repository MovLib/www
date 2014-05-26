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
class SystemPage extends \MovLib\Data\AbstractEntity implements \MovLib\Core\Revision\OriginatorInterface {
  use \MovLib\Core\Revision\OriginatorTrait;


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
   * The page's unique identifier.
   *
   * @var integer
   */
  public $id;

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
   * Instantiate new company object.
   *
   * @param \MovLib\Core\DIContainer $diContainer
   *   {@inheritdoc}
   * @param integer $id [optional]
   *   The system page's unique identifier to instantiate, defaults to <code>NULL</code> (no system page will be loaded).
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer, $id = null) {
    parent::__construct($diContainer);
    if ($id) {
      $connection = Database::getConnection();
      $stmt = $connection->prepare(<<<SQL
SELECT
  `id`,
  IFNULL(
    COLUMN_GET(`dyn_titles`, '{$this->intl->languageCode}' AS CHAR(255)),
    COLUMN_GET(`dyn_titles`, '{$this->intl->defaultLanguageCode}' AS CHAR(255))
  ),
  IFNULL(
    COLUMN_GET(`dyn_texts`, '{$this->intl->languageCode}' AS BINARY),
    COLUMN_GET(`dyn_texts`, '{$this->intl->defaultLanguageCode}' AS BINARY)
  ),
  COLUMN_GET(`dyn_titles`, '{$this->intl->defaultLanguageCode}' AS CHAR(255))
FROM `system_pages`
WHERE `id` = ?
LIMIT 1
SQL
      );
      $stmt->bind_param("d", $id);
      $stmt->execute();
      $stmt->bind_result(
        $this->id,
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
    if ($this->id) {
      $this->init();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    // The plural key isn't used anywhere.
    $this->pluralKey   = "system_pages";
    if ($this->routeKey == "About {$this->config->sitename}") {
      $this->singularKey = "about";
      $this->routeKey    = "/about";
    }
    else {
      $this->singularKey = sanitize_filename($this->routeKey);
      $this->routeKey    = "/{$this->singularKey}";
    }
    $this->routeArgs   = [];
    $this->routeIndex  = "/";
    return parent::init();
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   * @param \MovLib\Data\SystemPage\SystemPageRevision $revision {@inheritdoc}
   * @return \MovLib\Data\SystemPage\SystemPageRevision {@inheritdoc}
   */
  protected function doCreateRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    $revision->texts[$this->intl->languageCode]  = $this->text;
    $revision->titles[$this->intl->languageCode] = $this->title;

    return $revision;
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\SystemPage\SystemPageRevision $revision {@inheritdoc}
   * @return this {@inheritdoc}
   */
  protected function doSetRevision(\MovLib\Core\Revision\RevisionInterface $revision) {
    if (isset($revision->texts[$this->intl->languageCode])) {
      $this->text = $revision->texts[$this->intl->languageCode];
    }
    if (empty($revision->titles[$this->intl->languageCode])) {
      $this->title = $revision->titles[$this->intl->defaultLanguageCode];
    }
    else {
      $this->title = $revision->titles[$this->intl->languageCode];
    }
    return $this;
  }

}
