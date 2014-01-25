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
namespace MovLib\Data\Image;

/**
 * Represents a single lobby card of a movie.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieLobbyCard extends \MovLib\Data\Image\AbstractMovieImage {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The movie backdrop's database table name.
   *
   * @var string
   */
  const TABLE_NAME = "lobby_cards";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie poster.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param null|integer $movieId [optional]
   *   The movie's unique identifier this poster belongs to, defaults to no movie identifier which is reserved for
   *   instantiation via fetch object.
   * @param null|string $movieTitle [optional]
   *   The movie's display title (and year) this poster belongs to, defaults to no movie title which is reserved for
   *   instantiation via fetch object.
   * @param integer $id [optional]
   *   The poster's unique identifier, if not passed (default) an empty poster is created ready for creation of a
   *   new movie poster.
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Preentation\Error\NotFound
   */
  public function __construct($movieId = null, $movieTitle = null, $id = null) {
    global $i18n;
    parent::__construct($id, $movieId, $movieTitle, $i18n->t("Lobby Card"), $i18n->t("Lobby Cards"), "lobby-card", "lobby-cards");
  }

}
