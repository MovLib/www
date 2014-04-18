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
namespace MovLib\Data\Movie;

/**
 * Defines the poster entity object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Poster extends \MovLib\Data\Image\AbstractImageEntity {

  /**
   * {@inheritdoc}
   */
  public function __construct(\MovLib\Core\DIContainer $diContainer, $entityId, $id = null) {
    parent::__construct($diContainer);
    $this->entityId = $entityId;
    if ($id) {
      // @todo Load poster!
    }
    if ($this->id) {
      $this->init();
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function init() {
    //$this->imageAlternativeText = $this->intl->t("{movie_title} poster.", [ "movie_title" => $this->displayTitleAndYear]);
    $this->entityKey            = "movie";
    $this->imageAlternativeText = "Alternative Text";
    $this->imageDirectory       = "upload://movie/{$this->entityId}/poster";
    $this->imageFilename        = $this->id;
    $this->pluralKey            = "posters";
    $this->singularKey          = "poster";
    $this->routeArgs            = [ $this->entityId, $this->id ];
    $this->routeKey             = "/movie/{0}/poster/{1}";
    $this->route                = $this->intl->r($this->routeKey, $this->routeArgs);
    $this->routeIndex           = $this->intl->r("/movie/{0}/posters", $this->entityId);
    return parent::init();
  }

}
