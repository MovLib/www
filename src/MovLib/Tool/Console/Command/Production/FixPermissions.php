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
use \Symfony\Component\Console\Input\InputOption;
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
  use \MovLib\Data\TraitFileSystem;

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
      ""
    );

    $user = isset($kernel->configuration->user) ? $kernel->configuration->user : null;
    $mode = $user ? InputOption::VALUE_OPTIONAL : InputOption::VALUE_REQUIRED;
    $this->addOption("user", "u", $mode, "Set the files owning user.", $user);

    $group = isset($kernel->configuration->group) ? $kernel->configuration->group : null;
    $mode  = $group ? InputOption::VALUE_OPTIONAL : InputOption::VALUE_REQUIRED;
    $this->addOption("group", "g", $mode, "Set the files owning group.", $group);
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $options = parent::execute($input, $output);
    $this->fixPermissions($input->getArgument("directory"), $options["user"], $options["group"]);
    return $options;
  }

  /**
   * Fix permissions of all directories and files.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @param string $directory [optional]
   *   If given only the directories and files in that directory will be fixed, defaults to empty string which fixes the
   *   permissions on all directories and files in the document root.
   * @param string $user [optional]
   *   The files owner, defaults to <code>NULL</code> (ownership won't be changed).
   * @param string $group [optional]
   *   The files group, defaults to <code>NULL</code> (ownership won't be changed).
   * @return this
   * @throws \InvalidArgumentException
   */
  public function fixPermissions($directory = null, $user = null, $group = null) {
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

    // If this isn't a valid directory abort.
    if (is_dir($directory) === false) {
      throw new \InvalidArgumentException("Given directory '{$directory}' doesn't exist!");
    }

    // Create paths to binary execution files.
    $binPaths = "{$kernel->documentRoot}/bin/movlib.php {$kernel->documentRoot}/bin/*.sh {$kernel->documentRoot}/conf/*/*.sh";

    // Only attempt to update vendor binaries if there are any.
    if (is_dir("{$kernel->documentRoot}/vendor/bin") === true) {
      $binPaths .= " {$kernel->documentRoot}/vendor/bin/*";
    }

    $cmds = [
      "find '{$directory}' -follow -type d -exec chmod 2770 {} \;" => "Directory permissions fixed!",
      "find '{$directory}' -follow -type f -exec chmod 2660 {} \;" => "File permissions fixed!",
      "chmod -R 2770 {$binPaths}"                                  => "Executable permissions fixed!",
    ];

    if ($user && $group) {
      $cmds["chown -R {$user}:{$group} '{$directory}'"] = "Owernship changed!";
    }

    // Looks good so far, start fixing permissions in this directory.
    $this->write("Fixing permissions of all directories and files in <info>'{$directory}'</info> ...");
    foreach ($cmds as $cmd => $msg) {
      $this->shExecute($cmd);
      $this->write($msg, self::MESSAGE_TYPE_INFO);
    }

    return $this;
  }

}
