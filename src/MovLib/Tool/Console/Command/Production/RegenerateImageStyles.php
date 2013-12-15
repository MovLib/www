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
namespace MovLib\Tool\Console\Command\Production;

use \MovLib\Data\UnixShell as sh;
use \MovLib\Data\User\User;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Regenerate image styles for all images on the server.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class RegenerateImageStyles extends \MovLib\Tool\Console\Command\AbstractCommand {

  /**
   * @inheritdoc
   */
  public function __construct() {
    parent::__construct("regenerate-image-styles");
  }

  /**
   * @inheritdoc
   */
  public function configure() {
    $this->setDescription("Regenerate image styles for all images on the server.");
  }

  /**
   * @inheritdoc
   * @global \MovLib\Tool\Database $db
   * @global \MovLib\Tool\Kernel $kernel
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    global $db, $kernel;
    $options = parent::execute($input, $output);

    // Purge all styles from all uploaded files but don't touch the 140 styles of the avatar images because they are
    // the source for all styles.
    if (sh::execute("find {$kernel->documentRoot}/public/upload -not -regex '.*\\/user\\/.*\\.140\\.[a-z]+' -type f -delete") === false) {
      throw new \RuntimeException("Couldn't delete all image styles (try fixing permissions and run me again).");
    }

    // Regenerate all avatar images.
    $stmt  = $db->query("SELECT `id`, `name`, UNIX_TIMESTAMP(`image_changed`) AS `changed`, `image_extension` AS `extension` FROM `users` WHERE `image_changed` IS NOT NULL");
    $users = $stmt->get_result();
    /* @var $user \MovLib\Data\User\User */
    while ($user = $users->fetch_object("\\MovLib\\Data\\User\\User")) {
      $rm   = new \ReflectionMethod($user, "getPath");
      $rm->setAccessible(true);
      $path = $rm->invoke($user, User::STYLE_SPAN_02);
      $user->upload($path, pathinfo($path, PATHINFO_EXTENSION), -1, -1);
      $this->write($user->name);
    }
    $stmt->close();

    return $options;
  }

}
