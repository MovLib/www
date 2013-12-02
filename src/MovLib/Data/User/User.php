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
class User extends \MovLib\Data\Image\AbstractBaseImage {


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
   * Maximum attempts for actions like registration, login, ...
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
   * The user's unique name.
   *
   * @var string
   */
  public $name;

  /**
   * The user's route.
   *
   * @var string
   */
  public $route;

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


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new user.
   *
   * If no <var>$from</var> or <var>$value</var> is given, an empty user model will be created.
   *
   * @global \MovLib\Data\Database $db
   * @global \MovLib\Data\I18n $i18n
   * @param string $from [optional]
   *   Defines how the object should be filled with data, use the various <var>FROM_*</var> class constants.
   * @param mixed $value [optional]
   *   Data to identify the user, see the various <var>FROM_*</var> class constants.
   * @throws \OutOfBoundsException
   */
  public function __construct($from = null, $value = null) {
    global $db, $i18n;
    if ($from && $value) {
      $stmt = $db->query(
        "SELECT
          `id`,
          `name`,
          UNIX_TIMESTAMP(`image_changed`),
          `image_extension`
        FROM `users`
        WHERE `{$from}` = ?",
        $this->types[$from],
        [ $value ]
      );
      $stmt->bind_result($this->id, $this->name, $this->changed, $this->extension);
      if (!$stmt->fetch()) {
        throw new \OutOfBoundsException("Couldn't find user for {$from} '{$value}'");
      }
      $stmt->close();
      $this->exists   = (boolean) $this->changed;
      $this->filename = rawurlencode(mb_strtolower($this->name));
      $this->route    = $i18n->r("/user/{0}", [ $this->filename ]);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Commit the current state of the object to the database.
   *
   * @global \MovLib\Data\Database $db
   * @return this
   */
  public function commit() {
    global $db;
    $db->query(
      "UPDATE `users` SET `image_changed` = FROM_UNIXTIME(?), `image_extension` = ? WHERE `id` = ?",
      "ssd",
      [ $this->changed, $this->extension, $this->id ]
    );
    return $this;
  }

  /**
   * Delete the user's avatar image and all styles of it.
   *
   * @global \MovLib\Kernel $kernel
   * @internal
   *   No need to delete the directory, all avatars are in the same directory and at least one is always present.
   * @return this
   */
  protected function delete() {
    global $kernel;
    if ($this->exists == true) {
      foreach ([ self::STYLE_SPAN_01, self::STYLE_SPAN_02 ] as $style) {
        try {
          $path = $this->getPath($style);
          unlink($path);
        }
        catch (\ErrorException $e) {
          error_log("Couldn't delete '{$path}'.");
        }
      }
      $this->exists  = false;
      $this->changed = $this->extension = null;
      $kernel->delayMethodCall([ $this, "commit" ]);
    }
    return $this;
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
    global $i18n;
    if (!isset($this->stylesCache[$style])) {
      $this->stylesCache[$style] = new Style(
        $i18n->t("Avatar image of {0}.", [ $this->name ]),
        $this->getURL($style),
        $style,
        $style,
        $this->route
      );
    }
    return $this->stylesCache[$style];
  }

}
