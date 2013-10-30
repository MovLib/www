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
use \MovLib\Exception\ConsoleException;
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
   */
  public function __construct() {
    parent::__construct("fix-permissions");
  }

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setDescription("Fix permissions on a directory and its contents.");
    $this->addArgument(
      "directory",
      InputArgument::OPTIONAL,
      "Specify the directory in which the permissions should be fixed (relative to the document root).",
      ""
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
  public function fixPermissions($directory = "") {
    global $kernel;
    if (strpos($directory, $kernel->documentRoot) === false) {
      $directory = "{$kernel->documentRoot}/{$directory}";
    }
    if (!is_dir($directory)) {
      throw new \InvalidArgumentException("Given directory '{$directory}' doesn't exist!");
    }
    $this->write("Fixing permissions on all directories and files in <info>'{$directory}'</info> ...");
    foreach ([
      "chown -R {$kernel->phpUser}:{$kernel->phpGroup} '{$directory}'" => "User and group ownership fixed!",
      "find '{$directory}' -type d -exec chmod 2770 {} \;"             => "Directory permissions fixed!",
      "find '{$directory}' -type f -exec chmod 2660 {} \;"             => "File permissions fixed!",
      "find '{$directory}' -type f -regextype posix-egrep -regex '.*(bin/[a-zA-Z0-9\._-]+|conf/.*\.sh|(/?apigen){3}\.php)[^\.md]$' -exec chmod 2770 {} \;"
        => "Executable permissions fixed!"
    ] as $cmd => $msg) {
      if (sh::execute($cmd) === false) {
        throw new ConsoleException("Failed to execute '{$cmd}'!");
      }
      $this->write($msg, self::MESSAGE_TYPE_INFO);
    }
    return $this;
  }

}
