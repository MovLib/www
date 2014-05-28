<?php

// Fast reference implementation for other developers to understand what it's about.

namespace MovLib\Core\Entity\Genre;

class GenreSet extends \MovLib\Core\Entity\AbstractEntitySet {

  const name = "GenreSet";

  public static $tableName = "genres";

  public $bundle = "Genres";

  public function __construct(\MovLib\Core\Container $container) {
    parent::__construct($container);
  }

  protected function doLoad(\MovLib\Core\Database\Query\Select $select) {
    return $select;
  }

}
