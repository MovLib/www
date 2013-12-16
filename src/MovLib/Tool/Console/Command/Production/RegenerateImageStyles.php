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
   * @param \Symfony\Component\Console\Input\InputInterface $input
   *   {@inheritdoc}
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   {@inheritdoc}
   * @return array
   *   The passed options.
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    global $db;
    $options = parent::execute($input, $output);

    // Regenerate all avatar images.
    $stmt  = $db->query("SELECT `id`, `name`, UNIX_TIMESTAMP(`image_changed`) AS `changed`, `image_extension` AS `extension` FROM `users` WHERE `email` IS NOT NULL AND `image_changed` IS NOT NULL");
    $users = $stmt->get_result();
    /* @var $user \MovLib\Tool\Data\User */
    while ($user = $users->fetch_object("\\MovLib\\Tool\\Data\\User")) {
      $user->regenerateImageStyles();
    }
    $stmt->close();

    return $options;
  }

}
