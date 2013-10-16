<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Data;

use \MovLib\Data\Delayed\MethodCalls as DelayedMethodCalls;
use \MovLib\Data\Image\Style;
use \MovLib\Exception\UserException;

/**
 * Description of TestUser
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class User extends \MovLib\Data\Image\AbstractImage {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Load the user from ID.
   *
   * @var string
   */
  const FROM_ID = "user_id";

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


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user's unique ID.
   *
   * @var int
   */
  public $id;

  /**
   * The directory name within the uploads folder.
   *
   * @var string
   */
  protected $imageDirectory = "user";

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
   * @global \MovLib\Data\I18n $i18n
   * @param string $from [optional]
   *   Defines how the object should be filled with data, use the various <var>FROM_*</var> class constants.
   * @param mixed $value [optional]
   *   Data to identify the user, see the various <var>FROM_*</var> class constants.
   * @throws \MovLib\Exception\UserException
   */
  public function __construct($from = null, $value = null) {
    global $i18n;
    if ($from && $value) {
      $stmt = $this->query(
        "SELECT
          `user_id`,
          `name`,
          `avatar_name`,
          UNIX_TIMESTAMP(`avatar_changed`),
          `avatar_extension`,
          `avatar_changed` IS NOT NULL
        FROM `users`
        WHERE `{$from}` = ?",
        $this->types[$from],
        [ $value ]
      );
      $stmt->bind_result($this->id, $this->name, $this->imageName, $this->imageChanged, $this->imageExtension, $this->imageExists);
      if (!$stmt->fetch()) {
        throw new UserException("Could not find user for {$from} '{$value}'!");
      }
      // The image name already has all unsave characters removed.
      $this->route = $i18n->r("/user/{0}", [ rawurlencode($this->imageName) ]);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function commit() {
    return $this->query(
      "UPDATE `users` SET `avatar_changed` = ?, `avatar_extension` = ? WHERE `user_id` = ?",
      "ssd",
      [ $this->imageChanged, $this->imageExtension, $this->id ]
    );
  }

  /**
   * {@inheritdoc}
   *
   * @internal
   *   No need to delete the directory, all avatars are in the same directory and at least one is always present.
   * @return this
   */
  protected function deleteImage() {
    if ($this->imageExists == true) {
      foreach ([ self::IMAGE_STYLE_SPAN_01, self::IMAGE_STYLE_SPAN_02 ] as $style) {
        $path = $this->getImagePath($style);
        if (is_file($path) && unlink($path) === false) {
          throw new File;
        }
      }
      $this->imageExists  = false;
      $this->imageChanged = $this->imageExtension = $this->imageStylesCache = null;
      DelayedMethodCalls::stack($this, "commit");
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   * @internal
   *   We override the <code>parent::uploadImage()</code> method and can handle everything there, no need to implement
   *   this method like other image instances have to.
   */
  protected function generateImageStyles($source) {
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getImageStyle($style = self::IMAGE_STYLE_SPAN_02) {
    global $i18n;
    if (!isset($this->imageStylesCache[$style])) {
      $this->imageStylesCache[$style] = new Style(
        $i18n->t("Avatar image of {0}.", [ $this->name ]),
        $this->getImageURL($style),
        $style,
        $style,
        $this->route
      );
    }
    return $this->imageStylesCache[$style];
  }

  /**
   * {@inheritdoc}
   *
   * @global \MovLib\Data\I18n $i18n
   * @internal
   *   The user's avatar is different from other images, we don't keep the original file and directly generate all
   *   styles (instead of a delayed call to ImageMagick as in other image classes). This is because avatar's are small
   *   images and not those huge monsters as we get them if someone uploads a poster or lobby card.
   * @param string $source
   *   {@inheritdoc}
   * @param string $extension
   *   {@inheritdoc}
   * @param int $height
   *   <b>UNUSED</b>
   * @param int $width
   *   <b>UNUSED</b>
   * @return this
   * @throws \MovLib\Exception\ImageException
   */
  public function uploadImage($source, $extension, $height, $width) {
    $this->imageChanged   = $_SERVER["REQUEST_TIME"];
    $this->imageExists    = true;
    $this->imageExtension = $extension;
    // Generate the span1 style from the converted span2 image (better quality and performance).
    $this->convertImage($this->convertImage($source, self::IMAGE_STYLE_SPAN_02, self::IMAGE_STYLE_SPAN_02, self::IMAGE_STYLE_SPAN_02, true), self::IMAGE_STYLE_SPAN_01);
    DelayedMethodCalls::stack($this, "commit");
    return $this;
  }

}
