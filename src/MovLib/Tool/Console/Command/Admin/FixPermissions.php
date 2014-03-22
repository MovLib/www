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

use \MovLib\Data\StreamWrapper\StreamWrapperFactory;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to fix permissions on directories and files.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class FixPermissions extends \MovLib\Tool\Console\Command\AbstractCommand {

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setName("fix-permissions");
    $this->setDescription("Fix permissions on a directory and its contents.");
    $this->addArgument("uri", InputArgument::OPTIONAL, "The URI to fix, either a directory or file, all stream wrappers are available.", "dr://");
  }

  /**
   * @inheritdoc
   * @global \MovLib\Tool\Kernel $kernel
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    // Fixing permissions of all files only works reliable if executed as privileged user.
    if ($this->checkPrivileges(false) === false) {
      $this->write(
        "You're executing this command as non privileged user which should work, if you get permission denied errors " .
        "execute as root or via sudo, but you should definitely check which files caused the problem.",
        self::MESSAGE_TYPE_ERROR
      );
    }

    $uri = str_replace("\\", "/", $input->getArgument("uri"));
    $this->writeDebug("URI: <comment>{$uri}</comment>...");

    if (strpos($uri, "://") === false) {
      $this->write("Not a URI, assuming path relative to document root...");
      $uri = "dr://{$uri}";
      $this->writeVerbose("New URI is <comment>{$uri}</comment>");
    }

    if (StreamWrapperFactory::create($uri)->realpath() === false) {
      throw new \InvalidArgumentException("The passed URI '{$uri}' doesn't exist");
    }

    if (is_file($uri)) {
      $this->writeVeryVerbose("Passed URI is a single file...");
      chmod($uri, 0664);
    }
    else {
      $this->writeVerbose("Fixing file and directory permission...");
      $dir  = "0775";
      $file = "0664";
      /* @var $fileinfo \SplFileInfo */
      foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($uri, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $fileinfo) {
        $path = $fileinfo->getPathname();
        $mode = $fileinfo->isDir() ? 0775 : 0664;
        $this->writeDebug(sprintf("Fixing <comment>{$path}</comment> [<comment>%04o</comment>]", $mode));
        chmod($path, $mode);
      }
      $this->writeVeryVerbose("Fixed file and directory permissions...");

      // Only attempt to fix the permissions of our executables if we're working with the document root.
      if ($uri == "dr://") {
        $this->writeVerbose("Fixing binary permissions...");
        foreach (new \DirectoryIterator("dr://bin") as $fileinfo) {
          if (in_array($fileinfo->getExtension(), [ "php", "sh" ])) {
            $path = $fileinfo->getPathname();
            $this->writeDebug("Fixing <comment>{$path}</comment> [<comment>0775</comment>]");
            chmod($path, 0775);
          }
        }
        $this->writeVeryVerbose("Fixed binary permissions...");
      }
      else {
        $this->writeVerbose("Binaries are only fixed if executed against document root.", self::MESSAGE_TYPE_ERROR);
      }
      $this->writeVerbose("Fixed permissions in <comment>{$uri}</comment>", self::MESSAGE_TYPE_INFO);
    }

    return 0;
  }

}
