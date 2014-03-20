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
 * Movie routes
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */

/* @var $this \MovLib\Tool\Console\Command\Production\NginxRoutes */
?>

location = <?= $this->rp("/movies") ?> {
  <?= $this->set("Index") ?>
  <?= $this->cache() ?>
}

<?= $this->redirectSingularToPlural("/movie", "/movies") ?>

location = <?= $this->rp("/movie/charts") ?> {
  <?= $this->set("Charts") ?>
  <?= $this->cache() ?>
}

location = <?= $this->r("/movie/create") ?> {
  <?= $this->set("Create") ?>
  <?= $this->cache() ?>
}

location = <?= $this->r("/movie/random") ?> {
  <?= $this->set("Random") ?>
  <?= $this->cache(false) ?>
}

location ^~ <?= $this->r("/movie") ?> {

  location ~* '^<?= $this->r("/movie/{0}") ?>$' {
    <?= $this->set("Show") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/movie/{0}/discussion") ?>$' {
    <?= $this->set("Discussion") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/movie/{0}/edit") ?>$' {
    <?= $this->set("Edit") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/movie/{0}/history") ?>$' {
    <?= $this->set("History") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/movie/{0}/delete") ?>$' {
    <?= $this->set("Delete") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/movie/{0}/cast") ?>$' {
    <?= $this->set("Cast") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/movie/{0}/crew") ?>$' {
    <?= $this->set("Crew") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->cache() ?>
  }


  # -------------------------------------------------------------------------------------------------------------------- Backdrop
  <?php $this->setRoutesNamespace("Movie\\Backdrop") ?>


  location ~* '^<?= $this->rp("/movie/{0}/backdrops") ?>$' {
    <?= $this->set("Index") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/movie/{0}/backdrop/upload") ?>$' {
    <?= $this->set("Create") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->cache() ?>
  }

  <?= $this->redirectSingularToPlural("/movie/{0}/backdrop", "/movie/{0}/backdrops") ?>

  location ~* '^<?= $this->r("/movie/{0}/backdrop/{1}") ?>$' {
    <?= $this->set("Show") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->set('$2', "image_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/movie/{0}/backdrop/{1}/edit") ?>$' {
    <?= $this->set("Edit") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->set('$2', "image_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/movie/{0}/backdrop/{1}/history") ?>$' {
    <?= $this->set("History") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->set('$2', "image_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/movie/{0}/backdrop/{1}/delete") ?>$' {
    <?= $this->set("Delete") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->set('$2', "image_id") ?>
    <?= $this->cache() ?>
  }


  # -------------------------------------------------------------------------------------------------------------------- Poster
  <?php $this->setRoutesNamespace("Movie\\Poster") ?>


  location ~* '^<?= $this->rp("/movie/{0}/posters") ?>$' {
    <?= $this->set("Index") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/movie/{0}/poster/upload") ?>$' {
    <?= $this->set("Create") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->set("Poster", "image_class") ?>
    <?= $this->cache() ?>
  }

  <?= $this->redirectSingularToPlural("/movie/{0}/poster", "/movie/{0}/posters") ?>

  location ~* '^<?= $this->r("/movie/{0}/poster/{1}") ?>$' {
    <?= $this->set("Show") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->set('$2', "image_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/movie/{0}/poster/{1}/edit") ?>$' {
    <?= $this->set("Edit") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->set('$2', "image_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/movie/{0}/poster/{1}/history") ?>$' {
    <?= $this->set("History") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->set('$2', "image_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/movie/{0}/poster/{1}/delete") ?>$' {
    <?= $this->set("Delete") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->set('$2', "image_id") ?>
    <?= $this->cache() ?>
  }


  # -------------------------------------------------------------------------------------------------------------------- Lobby Card
  <?php $this->setRoutesNamespace("Movie\\LobbyCard") ?>


  location ~* '^<?= $this->rp("/movie/{0}/lobby-cards") ?>$' {
    <?= $this->set("Index") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/movie/{0}/lobby-card/upload") ?>$' {
    <?= $this->set("Create") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->cache() ?>
  }

  <?= $this->redirectSingularToPlural("/movie/{0}/lobby-card", "/movie/{0}/lobby-cards") ?>

  location ~* '^<?= $this->r("/movie/{0}/lobby-card/{1}") ?>$' {
    <?= $this->set("Show") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->set('$2', "image_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/movie/{0}/lobby-card/{1}/edit") ?>$' {
    <?= $this->set("Edit") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->set('$2', "image_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/movie/{0}/lobby-card/{1}/history") ?>$' {
    <?= $this->set("History") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->set('$2', "image_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/movie/{0}/lobby-card/{1}/delete") ?>$' {
    <?= $this->set("Delete") ?>
    <?= $this->set('$1', "movie_id") ?>
    <?= $this->set('$2', "image_id") ?>
    <?= $this->cache() ?>
  }

  <?= $this->notFound() ?>
}
