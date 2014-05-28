<?php

// Fast reference implementation for other developers to understand what it's about.

namespace MovLib\Core\Entity\Genre;

use \MovLib\Core\Database\Database;

final class Genre extends AbstractEntity {

  const name = "Genre";

  public function __construct(\MovLib\Core\Container $container, $id = null, array $values = null) {
    if ($id) {
      $connection = Database::getConnection();
      $stmt = $connection->prepare(<<<SQL
SELECT
  `id`,
  `changed`,
  `created`,
  `deleted`,
  IFNULL(
    COLUMN_GET(`dyn_names`, '{$container->intl->languageCode}' AS CHAR),
    COLUMN_GET(`dyn_names`, '{$container->intl->defaultLanguageCode}' AS CHAR)
  ),
  COLUMN_GET(`dyn_descriptions`, '{$container->intl->languageCode}' AS CHAR),
  COLUMN_GET(`dyn_wikipedia`, '{$container->intl->languageCode}' AS CHAR),
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
    parent::__construct($container, $values);
  }

  public function init(array $values = null) {
    parent::init($values);
    $this->lemma = $this->name;
    return $this;
  }

  public function lemma($locale) {
    static $names = null;
    $languageCode = "{$locale{0}}{$locale{1}}";
    if (!$names) {
      $names = json_decode(Database::getConnection()->query("SELECT COLUMN_JSON(`dyn_names`) FROM `genres` WHERE `id` = {$this->id} LIMIT 1")->fetch_all()[0][0], true);
    }
    return isset($names[$languageCode]) ? $names[$languageCode] : $names[$this->intl->defaultLanguageCode];
  }

}
