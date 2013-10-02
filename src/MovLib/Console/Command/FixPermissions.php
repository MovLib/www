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
namespace MovLib\Console\Command;

use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to fix permissions on directories and files.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class FixPermissions extends \MovLib\Console\Command\AbstractCommand {

  /**
   * @inheritdoc
   */
  public function __construct() {
    parent::__construct("fixperm");
  }

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setDescription("Fix permissions on a directory and its contents.");
    $this->addArgument("directory", InputArgument::OPTIONAL, "Specify the directory in which the permissions should be fixed.", $_SERVER["DOCUMENT_ROOT"]);
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->setIO($input, $output)->checkPrivileges();
    $directory = $input->getArgument("directory");
    if (strpos($directory, $_SERVER["DOCUMENT_ROOT"]) === false) {
      $this->exitOnError("This command is only meant for files within the document root of the MovLib software!");
    }
    $this->write("Fixing permissions on all directories and files in '{$directory}' ...");
    $this->exec("chown -R movdev:www-data '{$directory}'", "Could not change ownership!");
    $this->exec("find '{$directory}' -type d -exec chmod 0770 {} \;", "Could not change directory permissions!");
    $this->exec("find '{$directory}' -type f -exec chmod 0660 {} \;", "Could not change file permissions!");
    $this->exec("find '{$_SERVER["DOCUMENT_ROOT"]}' -type f -regextype posix-egrep -regex '.*bin/.*(\.php|phpunit)$' -exec chmod 0770 {} \;", "Could not change executable permissions!");
    $this->write("Permissions Fixed!", self::MESSAGE_TYPE_INFO);
  }

}
