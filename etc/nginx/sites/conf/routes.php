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
