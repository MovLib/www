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
 * Generate autocompletion for Symfony console application.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Autocomplete extends \MovLib\Tool\Console\Command\AbstractCommand {

  /**
   * @inheritdoc
   */
  protected function configure() {
    $this->setName("gen-autocompletion");
    $this->setDescription("Generate autocompletion for Symfony Console Applications. Note that this application has to be executed as privileged user because the generated autocompletion files have to moved to a protected directory.");
    $this->addArgument(
      "application",
      InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
      str_replace("'all'", "<comment>all</comment>", wordwrap(
        "The Symfony Console Applications for which autocompletions should be generated. Note that the default value " .
        "'all' is a special keyword, if 'all' is part of your supplied arguments any other argument is simply ignored.",
        120
      )),
      [ "all" ]
    );
  }

  /**
   * @inheritdoc
   * @global \MovLib\Tool\Kernel $kernel
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->checkPrivileges();
    $apps = $input->getArgument("application");

    if (in_array("all", $apps)) {
      $this->writeVerbose("Found special keyword <comment>all</comment>, generating translations for all system locales");
      $apps = [];
      foreach (new \RegexIterator(new \DirectoryIterator("dr://bin"), "/mov[a-z]+\.php$/") as $fileinfo) {
        $apps[] = $fileinfo->getBasename(".php");
      }
    }

    $vendor = StreamWrapperFactory::create("dr://vendor")->realpath();
    $tmp    = StreamWrapperFactory::create("tmp://");

    foreach ($apps as $app) {
      $this->writeVerbose("Generating autocompletion for <comment>{$app}</comment>");

      // Create the autocompletion project if it doesn't exist yet.
      $autocomplete = "{$vendor}/symfony-console-autocomplete/bin/autocomplete";
      $this->exec("which '{$app}'");
      if (is_file($autocomplete) === false) {
        $this->exec("composer create-project bamarni/symfony-console-autocomplete -s dev", $vendor);
      }

      // Create the autocompletion dump of the desired application.
      $this->exec("php {$autocomplete} dump '{$app}' > '{$app}'", "tmp://");
      $bashCompletion = "/etc/bash_completion.d";

      // We have to call realpath at this point, because it's not possible to move a file around wrapper types.
      rename($tmp->realpath("tmp://{$app}"), "{$bashCompletion}/{$app}");
    }

    // Although our process is running as the user who started it, it's still a different session and we can't simply
    // reload it for the user.
    $this->write("Run the command <fg=black;bg=cyan>source ~/.bashrc</fg=black;bg=cyan> to enjoy auto-completion.");
    $this->writeVerbose("Successfully generated autocompletion for '{$app}'!", self::MESSAGE_TYPE_INFO);

    return 0;
  }

}
