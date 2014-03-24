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
namespace MovLib\Console\Command\Admin;

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Manage various caches of the MovLib software.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class PurgeCache extends \MovLib\Console\Command\AbstractCommand {

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setName("purge-cache");
    $this->setDescription("Purge the presentation cache.");
  }

  /**
   * {@inheritdoc}
   * @global \MovLib\Core\FileSystem $fs
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    global $fs;
    $this->writeVerbose("Purging presentation cache...", self::MESSAGE_TYPE_COMMENT);
    /* @var $fileinfo \SplFileInfo */
    foreach ($fs->getRecursiveIterator("dr://var/cache") as $fileinfo) {
      $path = $fileinfo->getPathname();
      if ($fileinfo->isDir()) {
        $this->writeDebug("Deleting directory <comment>{$path}</comment>");
        rmdir($path);
      }
      else {
        $this->writeDebug("Deleting file <comment>{$path}</comment>");
        unlink($path);
      }
    }
    $this->writeVerbose("Successfuly purged the presentation cache!", self::MESSAGE_TYPE_INFO);
    return 0;
  }

}
