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
namespace MovLib\Data\Help;

/**
 * Defines the help subcategory set object.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class SubCategorySet extends \MovLib\Data\AbstractEntitySet {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "SubCategorySet";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * {@inheritdoc}
   */
  public static $tableName = "help_subcategories";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * {@inheritdoc}
   */
  public function __construct(\MovLib\Core\Container $container) {
    parent::__construct($container, "Subcategories", "Subcategory", $container->intl->tp(-1, "Subcategories", "Subcategory"));
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function getEntitiesQuery($where = null, $orderBy = null) {
    return <<<SQL
SELECT
  `help_subcategories`.`help_category_id` AS `category`,
  `help_subcategories`.`id` AS `id`,
  `help_subcategories`.`changed` AS `changed`,
  `help_subcategories`.`created` AS `created`,
  `help_subcategories`.`deleted` AS `deleted`,
  `help_subcategories`.`icon` AS `icon`,
  `help_subcategories`.`title` AS `title`
FROM `help_subcategories`
{$where}
{$orderBy}
SQL;
  }

  /**
   * Get all subcategories belonging to a certain category.
   *
   * @param integer $id
   *   The unique category identifier.
   * @return null|array
   *   All sub categories, <code>NULL</code> if no entities were found.
   */
  public function getAllBelongingToCategory($id) {
    return $this->loadEntities("WHERE `help_subcategories`.`deleted` = false AND `help_category_id` = {$id}");
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitySetsQuery(\MovLib\Data\AbstractEntitySet $set, $in) {
    return "";
  }

}
