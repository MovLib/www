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
 * Event routes
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */

/* @var $this \MovLib\Tool\Console\Command\Production\NginxRoutes */
?>

<?php $this->setRoutesNamespace("Event\\Event") ?>

location = <?= $this->rp("/events") ?> {
  <?= $this->set("Index") ?>
  <?= $this->cache() ?>
}

<?= $this->redirectSingularToPlural("/event", "/events") ?>

location = <?= $this->r("/event/create") ?> {
  <?= $this->set("Create") ?>
  <?= $this->cache() ?>
}

location = <?= $this->r("/event/random") ?> {
  <?= $this->set("Random") ?>
  <?= $this->cache(false) ?>
}

location ^~ <?= $this->r("/event") ?> {

  location ~* '^<?= $this->r("/event/{0}") ?>$' {
    <?= $this->set("Show") ?>
    <?= $this->set('$1', "award_event_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/event/{0}/discussion") ?>$' {
    <?= $this->set("Discussion") ?>
    <?= $this->set('$1', "award_event_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/event/{0}/edit") ?>$' {
    <?= $this->set("Edit") ?>
    <?= $this->set('$1', "award_event_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/event/{0}/history") ?>$' {
    <?= $this->set("History") ?>
    <?= $this->set('$1', "award_event_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->r("/event/{0}/delete") ?>$' {
    <?= $this->set("Delete") ?>
    <?= $this->set('$1', "award_event_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->rp("/event/{0}/movies") ?>$' {
    <?= $this->set("Movies") ?>
    <?= $this->set('$1', "award_event_id") ?>
    <?= $this->cache() ?>
  }

  location ~* '^<?= $this->rp("/event/{0}/series") ?>$' {
    <?= $this->set("Series") ?>
    <?= $this->set('$1', "award_event_id") ?>
    <?= $this->cache() ?>
  }

  <?= $this->notFound() ?>
}
