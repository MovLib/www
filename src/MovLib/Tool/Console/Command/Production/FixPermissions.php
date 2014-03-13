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

use \MovLib\Data\FileSystem;
use \MovLib\Data\Shell;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to fix permissions on directories and files.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class FixPermissions extends \MovLib\Tool\Console\Command\AbstractCommand {

  /**
   * @inheritdoc
   * @global \MovLib\Tool\Kernel $kernel
   */
  protected function configure() {
    global $kernel;
    $this->setName("fix-permissions");
    $this->setDescription("Fix permissions on a directory and its contents.");
    $this->addArgument(
      "directory",
      InputArgument::OPTIONAL,
      "Specify the directory in which the permissions should be fixed (relative to the document root).",
      $kernel->documentRoot
    );
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $options = parent::execute($input, $output);
    $this->checkPrivileges();
    $this->fixPermissions($input->getArgument("directory"));
    return $options;
  }

  /**
   * Fix permissions of all directories and files.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @param string $directory [optional]
   *   If given only the directories and files in that directory will be fixed, defaults to empty string which fixes the
   *   permissions on all directories and files in the document root.
   * @return this
   * @throws \InvalidArgumentException
   */
  public function fixPermissions($directory = null) {
    global $kernel;

    // No directory? No problem!
    if (!isset($directory)) {
      $directory = $kernel->documentRoot;
    }
    // Ensure that the given directory is within the globally defined document root. At this point, we don't even trust
    // the person who's calling this method as it would simply break way too many things.
    elseif (strpos($directory, $kernel->documentRoot) === false) {
      $directory = "{$kernel->documentRoot}/{$directory}";
    }
    $directory = rtrim($directory, "/");

    // If this isn't a valid directory abort.
    if (is_dir($directory) === false) {
      throw new \InvalidArgumentException("Given directory '{$directory}' doesn't exist!");
    }
    $this->write("Fixing permissions in '{$directory}' ...");

    FileSystem::changeOwner($directory, $kernel->systemUser, $kernel->systemGroup, true);
    $this->write("Fixed file ownership!", self::MESSAGE_TYPE_INFO);

    FileSystem::changeModeRecursive($directory);
    $this->write("Fixed file permissions!", self::MESSAGE_TYPE_INFO);

    // Only attempt to fix the permissions of our executables if we're working with the document root.
    if ($directory == $kernel->documentRoot) {
      $bin[] = "{$kernel->documentRoot}/bin/movlib.php";
      $bin[] = "{$kernel->documentRoot}/bin/*.sh";

      foreach ([ "/conf" => "/*/*.sh", "/etc" => "/*/*.sh", "/vendor/bin" => "/*" ] as $dir => $pattern) {
        $dir = "{$kernel->documentRoot}{$dir}";
        if (is_dir($dir)) {
          $bin[] = "{$dir}{$pattern}";
        }
      }

      Shell::execute("chmod -R 0775 " . implode(" ", $bin));
      $this->write("Executable permissions fixed!", self::MESSAGE_TYPE_INFO);
    }

    return $this;
  }

}
