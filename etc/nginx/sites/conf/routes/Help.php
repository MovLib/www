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

use \MovLib\Data\Help\HelpArticle;
use \MovLib\Data\Help\HelpCategory;
use \MovLib\Data\Help\HelpSubCategory;

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
?>


# ---------------------------------------------------------------------------------------------------------------------- Help


location = <?= $this->r("/help") ?> {
  <?= $this->set("Index") ?>
  <?= $this->cache() ?>
}


# ---------------------------------------------------------------------------------------------------------------------- Help Categories


<?php
/* @var $result \mysqli_result */
$categoryResult = HelpCategory::getHelpCategoryIds();
$this->setRoutesNamespace("Help\\Category");
while ($row = $categoryResult->fetch_object()):
  /* @var $category \MovLib\Data\Help\HelpCategory */
  $category = new HelpCategory($row->id);
?>

location = <?= $category->route ?> {
  <?= $this->set("Index") ?>
  <?= $this->set($category->id, "help_category_id") ?>
  <?= $this->cache() ?>
}
<?php endwhile; ?>


# ---------------------------------------------------------------------------------------------------------------------- Help Sub-Categories


<?php
/* @var $result \mysqli_result */
$subCategoryResult = HelpSubCategory::getHelpSubCategoryIds();
$this->setRoutesNamespace("Help\\Category\\Subcategory");
while ($row = $subCategoryResult->fetch_object()):
  /* @var $subCategory \MovLib\Data\Help\HelpSubCategory */
  $subCategory = new HelpSubCategory($row->id);
?>

location = <?= $subCategory->route ?> {
  <?= $this->set("Index") ?>
  <?= $this->set($subCategory->id, "help_subcategory_id") ?>
  <?= $this->set($subCategory->category->id, "help_category_id") ?>
  <?= $this->cache() ?>
}
<?php endwhile; ?>


# ---------------------------------------------------------------------------------------------------------------------- Help Articles


<?php
/* @var $result \mysqli_result */
$articleResult = HelpArticle::getHelpArticleIds();
while ($row = $articleResult->fetch_object()):
  /* @var $article \MovLib\Data\Help\HelpArticle */
  $article = new HelpArticle($row->id);

  if (isset($article->subCategory)) {
    $this->setRoutesNamespace("Help\\Category\\Subcategory");
  }
  else {
    $this->setRoutesNamespace("Help\\Category");
  }
?>

location = <?= $article->route ?> {
  <?= $this->set("Show") ?>
  <?= $this->set($article->id, "help_article_id") ?>
  <?= $this->cache() ?>
}

location = <?= "{$article->route}/edit" ?> {
  <?= $this->set("Edit") ?>
  <?= $this->set($article->id, "help_article_id") ?>
  <?= $this->cache() ?>
}

<?php endwhile;
