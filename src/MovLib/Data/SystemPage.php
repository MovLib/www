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

use \MovLib\Presentation\Error\NotFound;

/**
 * Handling of one system page.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class SystemPage extends \MovLib\Data\AbstractEntity {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The page's unique identifier.
   *
   * @var integer
   */
  public $id;

  /**
   * The page's route key.
   *
   * @var string
   */
  public $route;

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
   * @param integer $id
   *   The system page's unique identifier to instantiate.
   * @throws \MovLib\Exception\ClientException\NotFoundException
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer, $id) {
    parent::__construct($diContainer);
    $stmt = $this->getMySQLi()->prepare(<<<SQL
SELECT
  `id`,
  COLUMN_GET(`dyn_titles`, ? AS CHAR(255)) AS `title`,
  COLUMN_GET(`dyn_texts`, ? AS BINARY) AS `text`,
  COLUMN_GET(`dyn_titles`, '{$this->intl->defaultLanguageCode}' AS CHAR(255)) AS `routeKey`
FROM `system_pages`
WHERE `id` = ?
LIMIT 1
SQL
    );
    $stmt->bind_param("ssd", $this->intl->languageCode, $this->intl->languageCode, $id);
    $stmt->execute();
    $stmt->bind_result($this->id, $this->title, $this->text, $this->routeKey);
    $found = $stmt->fetch();
    $stmt->close();
    if (!$found) {
      throw new NotFound;
    }

    $this->init();
  }

  /**
   * {@inheritdoc}
   */
  protected function init() {
    // The plural key isn't used anywhere.
    $this->pluralKey   = "system_pages";
    $this->singularKey = $this->fs->sanitizeFilename($this->routeKey);
    $this->routeKey    = "/{$this->singularKey}";
    $this->routeArgs   = [];
    $this->routeIndex  = "/";
    return parent::init();
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Write changes to the system page to the database.
   *
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  public function commit() {
    $db->query(
      "UPDATE `system_pages` SET
        `dyn_titles` = COLUMN_ADD(`dyn_titles`, ?, ?),
        `dyn_texts` = COLUMN_ADD(`dyn_texts`, ?, ?)
      WHERE `id` = ?
      LIMIT 1",
      "ssssi",
      [
        $i18n->languageCode,
        $this->title,
        $i18n->languageCode,
        $this->text,
        $this->id,
      ]
    )->close();

    return $this;
  }

}
