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
namespace MovLib\Tool\Console\Command\Admin;

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
   * @global \MovLib\Tool\Kernel $kernel
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    global $kernel;

    // Fixing permissions of all files only works reliable if executed as privileged user.
    $this->checkPrivileges();

    // Remove trailing directory separators.
    $directory = rtrim(str_replace("\\", "/", $input->getArgument("directory")), "\\/");

    // Ensure that the given directory is within the globally defined document root. At this point, we don't even trust
    // the person who's calling this method as it would simply break way too many things.
    if (strpos($directory, $kernel->documentRoot) === false) {
      $directory = "{$kernel->documentRoot}/{$directory}";
    }

    // If this isn't a valid directory abort.
    if (is_dir($directory) === false) {
      throw new \InvalidArgumentException("Given directory '{$directory}' doesn't exist!");
    }
    $this->writeVerbose("Fixing permissions in '{$directory}' ...", self::MESSAGE_TYPE_COMMENT);

    $fMode = FileSystem::FILE_MODE;
    $this->writeDebug("Fixing file mode to {$fMode} and ownership to {$kernel->systemUser}:{$kernel->systemGroup}");
    FileSystem::changeOwner($directory, $kernel->systemUser, $kernel->systemGroup, true);
    $this->writeVerbose("Fixed file ownership...");

    $dMode = FileSystem::DIRECTORY_MODE;
    $this->writeDebug("Fixing directory mode to {$dMode} and ownership to {$kernel->systemUser}:{$kernel->systemGroup}");
    FileSystem::changeModeRecursive($directory);
    $this->writeVerbose("Fixed file permissions...");

    // Only attempt to fix the permissions of our executables if we're working with the document root.
    if ($directory == $kernel->documentRoot) {
      $bin[] = "{$kernel->documentRoot}/bin/*.php";
      $bin[] = "{$kernel->documentRoot}/bin/*.sh";

      foreach ([ "/conf" => "/*/*.sh", "/etc" => "/*/*.sh", "/vendor/bin" => "/*" ] as $dir => $pattern) {
        $dir = "{$kernel->documentRoot}{$dir}";
        if (is_dir($dir)) {
          $bin[] = "{$dir}{$pattern}";
        }
      }

      $bin = implode(" ", $bin);
      $this->writeDebug("Fixing modes to 0755 of the following executables (glob patterns): {$bin}");
      Shell::execute("chmod --recursive 0775 {$bin}");
      $this->writeVerbose("Executable permissions fixed...");
    }
    else {
      $this->writeVerbose("Binaries are only fixed if executed against document root.", self::MESSAGE_TYPE_ERROR);
    }

    $this->writeVerbose("Fixed all file permissions in '{$directory}'!", self::MESSAGE_TYPE_INFO);

    return 0;
  }

}
