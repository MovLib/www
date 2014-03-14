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
 * Series routes
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */

/* @var $this \MovLib\Tool\Console\Command\Production\NginxRoutes */
?>

location = <?= $this->rp("/series") ?> {
  <?= $this->set("Index") ?>
  <?= $this->cache() ?>
}

<?= $this->redirectSingularToPlural("/series", "/series") ?>

location = <?= $this->rp("/series/charts") ?> {
  <?= $this->set("Charts") ?>
  <?= $this->cache() ?>
}

location = <?= $this->r("/series/create") ?> {
  <?= $this->set("Create") ?>
  <?= $this->cache() ?>
}

location = <?= $this->r("/series/random") ?> {
  <?= $this->set("Random") ?>
  <?= $this->cache(false) ?>
}

location ^~ <?= $this->r("/series") ?> {

  location ~* '^<?= $this->r("/series/{0}") ?>$' {
    <?= $this->set("Show") ?>
    <?= $this->set('$1', "series_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/series/{0}/discussion") ?>$' {
    <?= $this->set("Discussion") ?>
    <?= $this->set('$1', "series_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/series/{0}/edit") ?>$' {
    <?= $this->set("Edit") ?>
    <?= $this->set('$1', "series_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/series/{0}/history") ?>$' {
    <?= $this->set("History") ?>
    <?= $this->set('$1', "series_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/series/{0}/delete") ?>$' {
    <?= $this->set("Delete") ?>
    <?= $this->set('$1', "series_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/series/{0}/cast") ?>$' {
    <?= $this->set("Cast") ?>
    <?= $this->set('$1', "series_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/series/{0}/crew") ?>$' {
    <?= $this->set("Crew") ?>
    <?= $this->set('$1', "series_id") ?>
    <?= $this->cache() ?>
  }


  # -------------------------------------------------------------------------------------------------------------------- Season
  <?php $this->setRoutesNamespace("Series\\Season") ?>


  location ~* '^<?= $this->rp("/series/{0}/seasons") ?>$' {
    <?= $this->set("Index") ?>
    <?= $this->set('$1', "series_id") ?>
    <?= $this->cache() ?>
  }

  <?= $this->redirectSingularToPlural("/series/{0}/season", "/series/{0}/seasons") ?>

  location ~* '^<?= $this->r("/series/{0}/season/{1}") ?>$' {
    <?= $this->set("Show") ?>
    <?= $this->set('$1', "series_id") ?>
    <?= $this->set('$1', "season_id") ?>
    <?= $this->cache() ?>
  }


  # -------------------------------------------------------------------------------------------------------------------- Episode
  <?php $this->setRoutesNamespace("Series\\Episode") ?>


  location ~* '^<?= $this->r("/series/{0}/episode/create") ?>$' {
    <?= $this->set("Create") ?>
    <?= $this->set('$1', "series_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/series/{0}/season/{1}/episode/{2}") ?>$' {
    <?= $this->set("Index") ?>
    <?= $this->set('$1', "series_id") ?>
    <?= $this->set('$2', "season_id") ?>
    <?= $this->set('$3', "episode_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/series/{0}/season/{1}/episode/{2}/edit") ?>$' {
    <?= $this->set("Edit") ?>
    <?= $this->set('$1', "series_id") ?>
    <?= $this->set('$2', "season_id") ?>
    <?= $this->set('$3', "episode_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/series/{0}/season/{1}/episode/{2}/history") ?>$' {
    <?= $this->set("History") ?>
    <?= $this->set('$1', "series_id") ?>
    <?= $this->set('$2', "season_id") ?>
    <?= $this->set('$3', "episode_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/series/{0}/season/{1}/episode/{2}/delete") ?>$' {
    <?= $this->set("Delete") ?>
    <?= $this->set('$1', "series_id") ?>
    <?= $this->set('$2', "season_id") ?>
    <?= $this->set('$3', "episode_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/series/{0}/season/{1}/episode/{2}/cast") ?>$' {
    <?= $this->set("Cast") ?>
    <?= $this->set('$1', "series_id") ?>
    <?= $this->set('$2', "season_id") ?>
    <?= $this->set('$3', "episode_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/series/{0}/season/{1}/episode/{2}/crew") ?>$' {
    <?= $this->set("Crew") ?>
    <?= $this->set('$1', "series_id") ?>
    <?= $this->set('$2', "season_id") ?>
    <?= $this->set('$3', "episode_id") ?>
    <?= $this->cache() ?>
  }

  <?= $this->notFound() ?>
}
