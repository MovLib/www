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
namespace MovLib\Data\Movie;

/**
 * Defines the poster set object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class PosterSet extends \MovLib\Data\AbstractEntitySet {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "PosterSet";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public static $tableName = "posters";

  /**
   * {@inheritdoc}
   */
  public function __construct(\MovLib\Core\Container $container) {
    parent::__construct($container, "Posters", "Poster", $container->intl->tp(-1, "Posters", "Poster"));
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->entityKey   = "movie";
    $this->pluralKey   = "posters";
    $this->singularKey = "poster";
    return parent::init();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesQuery($where = null, $orderBy = null) {
    return <<<SQL
SELECT
  `id`,
  `movie_id` AS `entityId`,
  `changed`,
  `created`,
  `deleted`,
  HEX(`cache_buster`) AS `imageCacheBuster`,
  `extension` AS `imageExtension`,
  `filesize` AS `imageFilesize`,
  `height` AS `imageHeight`,
  `width` AS `imageWidth`,
  `country_code` AS `countryCode`,
  `styles` AS `imageStyles`
FROM `posters`
{$where} {$orderBy}
SQL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitySetsQuery(\MovLib\Data\AbstractEntitySet $set, $in) {
    return <<<SQL
SQL;
  }

}
