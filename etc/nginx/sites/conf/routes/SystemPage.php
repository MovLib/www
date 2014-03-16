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
 * SystemPage routes
 *
 * @todo The presenter shouldn't be stored in the database, instead we should apply convention over configuration.
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */

/* @var $this \MovLib\Tool\Console\Command\Production\NginxRoutes */
/* @var $db \MovLib\Data\Database */
/* @var $i18n \MovLib\Data\I18n */
/* @var $stmt \mysqli_stmt */
$stmt = $db->query(
  "SELECT `id`, COLUMN_GET(`dyn_titles`, ? AS CHAR(255)) AS `title`, `presenter` FROM `system_pages`",
  "s",
  [ $i18n->defaultLanguageCode ]
);

/* @var $systemPages \mysqli_result */
$systemPages = $stmt->get_result();

/* @var $systemPage \MovLib\Data\SystemPage */
while ($systemPage = $systemPages->fetch_object()):
  $systemPage->title = \MovLib\Data\FileSystem::sanitizeFilename($systemPage->title);
?>

location = <?= $this->r("/{$systemPage->title}") ?> {
  <?= $this->set($systemPage->presenter) ?>
  <?= $this->set($systemPage->id, "system_page_id") ?>
  <?= $this->cache() ?>
}

location = <?= $this->r("/{$systemPage->title}/edit") ?> {
  <?= $this->set("Edit") ?>
  <?= $this->set($systemPage->id, "system_page_id") ?>
  <?= $this->cache() ?>
}

<?php
endwhile;
$stmt->close();
