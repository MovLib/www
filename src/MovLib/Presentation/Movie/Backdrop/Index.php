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
namespace MovLib\Presentation\Movie\Backdrop;

/**
 * Defines the movie backdrop index presentation.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Index extends \MovLib\Presentation\AbstractIndexPresenter {

  /**
   * {@inheritdoc}
   */
  protected function formatListingItem(\MovLib\Data\AbstractEntity $item, $delta) {

  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    return $this->callout(
      "<p>{$this->intl->t("We couldn’t find any posters for this movie.")}</p>",
      $this->intl->t("No Backdrops"),
      "info"
    );
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->initIndex(new \MovLib\Stub\Data\Dummy\DummySet($this->diContainerHTTP), $this->intl->t("Backdrops"), null);
  }

}
