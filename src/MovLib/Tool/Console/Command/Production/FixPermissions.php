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
class FixPermissions extends \MovLib\Tool\Console\Command\Development\AbstractDevelopmentCommand {

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
    global $config;
    parent::execute($input, $output);
    $this->checkPrivileges();
    $directory = "{$config->documentRoot}/{$input->getArgument("directory")}";
    $this->write("Fixing permissions on all directories and files in <info>'{$directory}'</info> ...");
    $this->exec("chown -R {$config->phpUser}:{$config->phpGroup} '{$directory}'");
    $this->exec("find '{$directory}' -type d -exec chmod 2770 {} \;");
    $this->exec("find '{$directory}' -type f -exec chmod 2660 {} \;");
    $this->exec("find '{$directory}' -type f -regextype posix-egrep -regex '.*(bin/[a-zA-Z0-9\._-]+|conf/.*\.sh)$' -exec chmod 2770 {} \;");
    $this->write("Permissions Fixed!", self::MESSAGE_TYPE_INFO);
  }

}
