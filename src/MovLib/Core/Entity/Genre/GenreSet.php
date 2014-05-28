<?php

// Fast reference implementation for other developers to understand what it's about.

namespace MovLib\Core\Entity\Genre;

class GenreSet extends AbstractEntitySet {

  const name = "GenreSet";

  public static $tableName = "genres";

  public $bundle = "Genres";

  public function __construct(\MovLib\Core\Container $container) {
    parent::__construct($container);
  }

  protected function doLoad(\MovLib\Core\Database\Query\Select $select) {
    return $select;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitiesQuery($where = null, $orderBy = null) {
    return <<<SQL
SELECT
  `genres`.`id` AS `id`,
  `genres`.`changed` AS `changed`,
  `genres`.`created` AS `created`,
  `genres`.`deleted` AS `deleted`,
  IFNULL(
    COLUMN_GET(`genres`.`dyn_names`, '{$this->intl->languageCode}' AS CHAR),
    COLUMN_GET(`genres`.`dyn_names`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ) AS `name`,
  `genres`.`count_movies` AS `countMovies`,
  `genres`.`count_series` AS `countSeries`
FROM `genres`
{$where}
{$orderBy}
SQL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntitySetsQuery(\MovLib\Data\AbstractEntitySet $set, $in) {
    return <<<SQL
SELECT
  `{$set->tableName}_genres`.`{$set->singularKey}_id` AS `entityId`,
  `genres`.`id`,
  IFNULL(
    COLUMN_GET(`genres`.`dyn_names`, '{$this->intl->languageCode}' AS CHAR),
    COLUMN_GET(`genres`.`dyn_names`, '{$this->intl->defaultLanguageCode}' AS CHAR)
  ) AS `name`
FROM `{$set->tableName}_genres`
  INNER JOIN `genres` ON `genres`.`id` = `{$set->tableName}_genres`.`genre_id`
WHERE `{$set->tableName}_genres`.`{$set->singularKey}_id` IN ({$in})
ORDER BY `name` {$this->collations[$this->intl->languageCode]} DESC
SQL;
  }

}
