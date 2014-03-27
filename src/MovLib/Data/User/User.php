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
namespace MovLib\Data\User;

use \MovLib\Data\Image\Style;

/**
 * @todo Description of TestUser
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class User extends \MovLib\Core\Database {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Load the user from ID.
   *
   * @var string
   */
  const FROM_ID = "id";

  /**
   * Load the user from name.
   *
   * @var string
   */
  const FROM_NAME = "name";

  /**
   * Load the user from mail.
   *
   * @var string
   */
  const FROM_EMAIL = "email";

  /**
   * Maximum attempts for actions like signing in, reseting password, ...
   *
   * @var integer
   */
  const MAXIMUM_ATTEMPTS = 5;

  /**
   * Maximum username length (chracter count, not bytes).
   *
   * @var integer
   */
  const NAME_MAXIMUM_LENGTH = 40;

  /**
   * Characters which aren't allowed within a username.
   *
   * @var string
   */
  const NAME_ILLEGAL_CHARACTERS = "/_@#<>|()[]{}?\\=:;,'\"&$*~";

  /**
   * Special style for the avatar in the site header.
   *
   * @var string
   */
  const STYLE_HEADER_USER_NAVIGATION = 50;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user's unique ID.
   *
   * @var integer
   */
  public $id;

  /**
   * The directory name within the uploads folder.
   *
   * @var string
   */
  protected $directory = "user";

  /**
   * The user's unique name sanitized for routes.
   *
   * @var string
   */
  public $filename;

  /**
   * The user's unique name.
   *
   * @var string
   */
  public $name;

  /**
   * The user's translated route.
   *
   * @var string
   */
  public $route;

  /**
   * @inheritdoc
   */
  protected $placeholder = "avatar";

  /**
   * The user's time zone ID (e.g. <code>"Europe/Vienna"</code>).
   *
   * @var null|string
   */
  public $timeZoneIdentifier;

  /**
   * The MySQLi bind param types of the columns.
   *
   * @var array
   */
  protected $types = [
    self::FROM_ID    => "d",
    self::FROM_EMAIL => "s",
    self::FROM_NAME  => "s",
  ];


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Commit the current state of the object to the database.
   *
   * @return this
   */
  public function commit() {
    $this->query(
      "UPDATE `users` SET `image_changed` = FROM_UNIXTIME(?), `image_extension` = ? WHERE `id` = ?",
      "ssd",
      [ $this->changed, $this->extension, $this->id ]
    );
    return $this;
  }

  /**
   * Get random user name.
   *
   * @return integer|null
   *   Random user name or null in case of failure.
   * @throws \MovLib\Exception\DatabaseException
   */
  public static function getRandomUserName() {
    $query = "SELECT `name` FROM `users` ORDER BY RAND() LIMIT 1";
    if ($result = $this->query($query)->get_result()) {
      return $result->fetch_assoc()["name"];
    }
  }

  /**
   * Get the <var>$style</var> for this image.
   *
   * @param mixed $style
   *   The desired style, use the objects <var>STYLE_*</var> class constants. Defaults to <var>STYLE_SPAN_02</var>.
   * @return \MovLib\Data\Image\Style
   *   The image's desired style object.
   */
  public function getStyle($style = self::STYLE_SPAN_02) {
    if (!isset($this->stylesCache[$style])) {
      $this->stylesCache[$style] = new Style(
        $i18n->t("Avatar image of {0}.", [ $this->name ]),
        $this->getURL($style),
        $style,
        $style,
        !$this->imageExists,
        $this->route
      );
    }
    return $this->stylesCache[$style];
  }

  /**
   * Instantiate new user.
   *
   * If no <var>$from</var> or <var>$value</var> is given, an empty user model will be created.
   *
   * @param string $from [optional]
   *   Defines how the object should be filled with data, use the various <var>FROM_*</var> class constants.
   * @param mixed $value [optional]
   *   Data to identify the user, see the various <var>FROM_*</var> class constants.
   * @throws \OutOfBoundsException
   */
  public function init($from, $value) {
    $stmt = $this->query(
      "SELECT `id`, `name`, `time_zone_identifier`, UNIX_TIMESTAMP(`image_changed`), `image_extension` FROM `users` WHERE `{$from}` = ?",
      $this->types[$from],
      [ $value ]
    );
    $stmt->bind_result($this->id, $this->name, $this->timeZoneIdentifier, $this->changed, $this->extension);
    if (!$stmt->fetch()) {
      throw new \OutOfBoundsException("Couldn't find user for {$from} '{$value}'");
    }
    $stmt->close();

    $this->initFetchObject();
  }

  /**
   * Initialize image properties and user page route.
   *
   * @return this
   */
  public function initFetchObject() {
    $this->imageExists   = (boolean) $this->changed;
    $this->filename = mb_strtolower($this->name);
    $this->route    = $i18n->r("/user/{0}", [ $this->filename ]);
    return $this;
  }

  /**
   * Upload the <var>$source</var>, overriding any existing image.
   *
   * @param string $source
   *   Absolute path to the uploaded image.
   * @param string $extension
   *   The three letter image extension (e.g. <code>"jpg"</code>).
   * @param integer $height
   *   <b>Unused!</b>
   * @param integer $width
   *   <b>Unused!</b>
   * @return this
   * @throws \RuntimeException
   */
  public function upload($source, $extension, $height, $width) {
    $this->changed     = $_SERVER["REQUEST_TIME"];
    $this->imageExists = true;
    $this->extension   = $extension;
    $this->stylesCache = null;

    $this->convert($source, self::STYLE_SPAN_02, self::STYLE_SPAN_02, self::STYLE_SPAN_02, true);
    // Generate the small ones based on the span2 result, this will give us best results.
    $this->convert($this->getPath(self::STYLE_SPAN_02), self::STYLE_SPAN_01);
    $this->convert($this->getPath(self::STYLE_SPAN_02), self::STYLE_HEADER_USER_NAVIGATION);

    $session->userAvatar = $this->getStyle(self::STYLE_HEADER_USER_NAVIGATION);

    return $this;
  }

}
