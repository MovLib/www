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

/**
 * Route configuration for the
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */

?>


# ---------------------------------------------------------------------------------------------------------------------- help


location = <?= $rp("/help") ?> {
  set $movlib_presenter "Help\\Categories";
  try_files $movlib_cache @php;
}

<?php
$stmt           = $db->query("SELECT `id`, COLUMN_GET(`dyn_titles`, ? AS CHAR(255)) AS `title` FROM `help_categories`", "s", [ $i18n->defaultLanguageCode ]);
$helpCategories = $stmt->get_result();
while ($helpCategory = $helpCategories->fetch_assoc()):
  $helpCategory["title"] = \MovLib\Data\FileSystem::sanitizeFilename($helpCategory["title"]);
?>

location = <?= $r("/help/{$helpCategory["title"]}") ?> {
  set $movlib_presenter "Help\\Category";
  set $movlib_help_category <?= $helpCategory["id"] ?>;
  try_files $movlib_cache @php;
}
<?php
endwhile;
$stmt->close();

$stmt = $db->query(
  "SELECT
    `help_articles`.`id` AS `article_id`,
    COLUMN_GET(`help_articles`.`dyn_titles`, ? AS CHAR(255)) AS `article_title`,
    `help_articles`.`category_id` AS `category_id`,
    COLUMN_GET(`help_categories`.`dyn_titles`, ? AS CHAR(255)) AS `category_title`
  FROM `help_articles`
  INNER JOIN `help_categories`
    ON `help_articles`.`category_id` = `help_categories`.`id`",
  "ss",
  [ $i18n->defaultLanguageCode, $i18n->defaultLanguageCode ]
);
$helpArticles = $stmt->get_result();
while ($helpArticle = $helpArticles->fetch_assoc()):
  $helpArticle["category_title"] = \MovLib\Data\FileSystem::sanitizeFilename($helpArticle["category_title"]);
  $helpArticle["article_title"]  = \MovLib\Data\FileSystem::sanitizeFilename($helpArticle["article_title"]);
?>

location = <?= $r("/help/{$helpArticle["category_title"]}/{$helpArticle["article_title"]}") ?> {
  set $movlib_presenter "Help\\Article";
  set $movlib_help_category <?= $helpArticle["category_id"] ?>;
  set $movlib_help_article <?= $helpArticle["article_id"] ?>;
  try_files $movlib_cache @php;
}

location = <?= $r("/help/{$helpArticle["category_title"]}/{$helpArticle["article_title"]}/edit") ?> {
  set $movlib_presenter "Help\\Edit";
  set $movlib_help_category <?= $helpArticle["category_id"] ?>;
  set $movlib_id <?= $helpArticle["article_id"] ?>;
  try_files $movlib_cache @php;
}
<?php
endwhile;
$stmt->close();
?>


# ---------------------------------------------------------------------------------------------------------------------- Country(ies)


location = <?= $rp("/countries") ?> {
  set $movlib_presenter "Country\\Index";
  try_files $movlib_cache @php;
}

location ^~ <?= $r("/country") ?> {

  location ~* "^<?= $r("/country/{0}", [ $isoAlpha2RegExp ]) ?>$" {
    set $movlib_presenter "Country\\Show";
    set $movlib_id $1;
    try_files $movlib_cache @php;
  }
  <?php foreach (\MovLib\Presentation\Country\Filter::$filters as $id => $name): ?>

  location ~* "^<?= $rp("/country/{0}/{$name}", [ $isoAlpha2RegExp ]) ?>$" {
    set $movlib_presenter "Country\\Filter";
    set $movlib_id $1;
    set $movlib_filter <?= $id ?>;
    try_files $movlib_cache @php;
  }
  <?php endforeach ?>

  rewrite .* /error/NotFound last;
}


# ---------------------------------------------------------------------------------------------------------------------- Year(s)


location = <?= $rp("/years") ?> {
  set $movlib_presenter "Year\\Index";
  try_files $movlib_cache @php;
}

location ^~ <?= $r("/year") ?> {

  location ~* "^<?= $r("/year/{0}", [ "([0-9]{4})" ]) ?>$" {
    set $movlib_presenter "Year\\Show";
    set $movlib_id $1;
    try_files $movlib_cache @php;
  }
  <?php foreach (\MovLib\Presentation\Year\Filter::$filters as $id => $name): ?>

  location ~* "^<?= $rp("/year/{0}/{$name}", [ "([0-9]{4})" ]) ?>$" {
    set $movlib_presenter "Year\\Filter";
    set $movlib_id $1;
    set $movlib_filter <?= $id ?>;
    try_files $movlib_cache @php;
  }
  <?php endforeach ?>

  rewrite .* /error/NotFound last;
}


# ---------------------------------------------------------------------------------------------------------------------- Deletion(s)


location = <?= $rp("/deletion-requests") ?> {
  set $movlib_presenter "DeletionRequests";
  try_files $movlib_cache @php;
}
