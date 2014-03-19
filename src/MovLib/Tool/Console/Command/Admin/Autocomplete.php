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
    $this->setDescription("Generation autocompletion for Symfony Console Application.");
    $this->addArgument(
      "application(s)",
      InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
      "The Symfony Console Application for which the autocompletion should be generated.",
      [ "all" ]
    );
  }

  /**
   * @inheritdoc
   * @global \MovLib\Tool\Kernel $kernel
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    global $kernel;

    $apps = $input->getArgument("application(s)");

    if (count($apps) === 1 && $apps[0] == "all") {
      $apps = [];
      foreach (new \DirectoryIterator("glob://{$kernel->documentRoot}/bin/mov*.php") as $app) {
        $apps[] = $app->getBasename(".php");
      }
    }

    foreach ($apps as $app) {
      $this->generateAutocompletion($app);
    }

    $this->write("Run the command <fg=black;bg=cyan>source ~/.bashrc</fg=black;bg=cyan> to enjoy auto-completion.");
    $this->writeVerbose("Successfully generated autocompletion for '{$app}'!", self::MESSAGE_TYPE_INFO);

    return 0;
  }

  /**
   * Generate bash autocompletion for given Symfony Console CLI application.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @param type $app
   *   The name of the Symfony Console CLI application.
   * @return this
   */
  protected function generateAutocompletion($app) {
    global $kernel;

    $this->writeVerbose(
      "Generating autocompletion for '{$app}', this may take several minutes...",
      self::MESSAGE_TYPE_COMMENT
    );

    $vendor       = "{$kernel->documentRoot}/vendor";
    $autocomplete = "{$vendor}/symfony-console-autocomplete/bin/autocomplete";
    $this->exec("which '{$app}'");
    if (is_file($autocomplete) === false) {
      $this->exec("composer create-project bamarni/symfony-console-autocomplete -s dev", $vendor);
    }

    $this->exec("php {$autocomplete} dump '{$app}' > '{$app}'", "{$kernel->documentRoot}/tmp");
    $bashCompletion = "/etc/bash_completion.d";
    if ($this->checkPrivileges(false) === true) {
      FileSystem::move("{$kernel->documentRoot}/tmp/{$app}", "{$bashCompletion}/{$app}");
      $this->exec("source ~/.bashrc");
    }
    else {
      $this->write(
        "Cannot move generated autocompletion dump to {$bashCompletion} because command wasn't executed as privileged user.",
        self::MESSAGE_TYPE_ERROR
      );
    }

    return $this;
  }

}
