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

use \MovLib\Data\Help\CategorySet;
use \MovLib\Data\Help\SubCategorySet;

/**
 * Help routes
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */

/* @var $this \MovLib\Tool\Console\Command\Production\NginxRoutes */
$diContainer = $this->diContainer;
$diContainer->intl = new \MovLib\Core\Intl($diContainer->config);

?>


# ---------------------------------------------------------------------------------------------------------------------- Help


location = <?= $this->r("/help") ?> {
  <?= $this->set("Index") ?>
  <?= $this->cache() ?>
}

location = <?= $this->r("/help/search") ?> {
  <?= $this->set("Search") ?>
  <?= $this->cache(false) ?>
}


# ---------------------------------------------------------------------------------------------------------------------- Help Categories


<?php
$categorySet = new CategorySet($diContainer);
$loadEntities = new \ReflectionMethod($categorySet, "loadEntities");
$loadEntities->setAccessible(true);
foreach ($loadEntities->invoke($categorySet) as $category):
  $routeKey = "/help/{$this->fs->sanitizeFilename($category->title)}";
  $this->setRoutesNamespace("Help\\Category");
?>

location = <?= $this->r($routeKey) ?> {
  <?= $this->set("Index") ?>
  <?= $this->set($category->id, "help_category_id") ?>
  <?= $this->cache() ?>
}

location ^~ <?= $this->r($routeKey) ?> {

  <?php
  $subCategorySet = new subCategorySet($diContainer);
  $subLoadEntities = new \ReflectionMethod($subCategorySet, "loadEntities");
  $subLoadEntities->setAccessible(true);
  foreach ($subLoadEntities->invoke($subCategorySet, "WHERE `help_category_id` = {$category->id}") as $subCategory):
    $subRouteKey = "{$routeKey}/{$this->fs->sanitizeFilename($subCategory->title)}";
    $this->setRoutesNamespace("Help\\Category\\SubCategory");
  ?>

  location = <?= $this->r($subRouteKey) ?> {
    <?= $this->set("Index") ?>
    <?= $this->set($category->id, "help_category_id") ?>
    <?= $this->set($subCategory->id, "help_subcategory_id") ?>
    <?= $this->cache() ?>
  }

  location ^~ <?= $this->r($subRouteKey) ?> {
<?php $this->setRoutesNamespace("Help\\Category\\SubCategory") ?>
    location ~* '^<?= $this->r("{$subRouteKey}/{0}") ?>$' {
      <?= $this->set("Show") ?>
      <?= $this->set('$1', "help_article_id") ?>
      <?= $this->cache() ?>
    }

    <?= $this->notFound() ?>
  }

<?php endforeach; ?>
<?php $this->setRoutesNamespace("Help\\Category") ?>
  location ~* '^<?= $this->r("{$routeKey}/{0}") ?>$' {
    <?= $this->set("Show") ?>
    <?= $this->set('$1', "help_article_id") ?>
    <?= $this->cache() ?>
  }

  <?= $this->notFound() ?>
}
<?php endforeach; ?>
