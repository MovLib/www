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
namespace MovLib\Presentation\Movie\Gallery;

/**
 * Movie lobby cards gallery presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class LobbyCards extends \MovLib\Presentation\Movie\Gallery\Images {

  /**
   * Instantiate new movie images presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct() {
    global $i18n;
    $this->imageClassName      = "LobbyCard";
    $this->imageTypeId         = \MovLib\Data\Image\MovieLobbyCard::TYPE_ID;
    $this->imageTypeName       = $i18n->t("Lobby Card");
    $this->imageTypeNamePlural = $i18n->t("Lobby Cards");
    $this->routeKey            = "lobby-card";
    $this->routeKeyPlural      = "lobby-cards";
    $this->initImagePage();
  }

}
