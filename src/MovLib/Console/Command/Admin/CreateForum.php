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

use \MovLib\Component\String;
use \MovLib\Core\Database\Database;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Defines the create forum command.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class CreateForum extends \MovLib\Console\Command\AbstractCommand {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "CreateForum";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("create-forum");
    $this->setDescription("Create a new forum.");
    $this->addArgument("name", InputArgument::REQUIRED, "The (English) name of the new forum.");
    $this->addArgument("category", InputArgument::OPTIONAL, "The (English) name of the category the forum belongs to.");
    $this->addOption("className", "cs", InputOption::VALUE_OPTIONAL, "The class name of the new forum.");
    $this->addOption("force", "f", InputOption::VALUE_OPTIONAL, "Force overwrite of existing classes.", false);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $name     = $input->getArgument("name");
    $category = $input->getArgument("category");

    // A forum has to belong to a category, if no category was provided ask the admin.
    if (empty($category)) {
      $this->askWithChoices("Please select the category the new forum belongs to.", null, []);
    }

    $className = $input->getOption("className");
    if (empty($className)) {
      // Convert the given name to pascal case for class construction. Ask the admin if the constructed string is really
      // okay, might contain some special characters that we don't want as part of the class name?
      $className = String::pascalCase($name);
      if (!$this->askConfirmation("Data class name will be <comment>{$className}</comment>")) {

      }
    }

    // Now we can build the class names and paths.
    $dataClass = "\\MovLib\\Data\\Forum\\{$className}";
    $presentationClass = "\\MovLib\\Presentation\\Forum\\{$className}";
    $presentationClassIndex = "{$presentationClass}\\Index";
    $presentationClassShow  = "{$presentationClass}\\Show";

    if ($input->getOption("force") !== true) {
      foreach ([ $dataClass, $presentationClassIndex, $presentationClassShow ] as $class) {
        if (class_exists($class)) {
          throw new \InvalidArgumentException("Seems like '{$class}' already exists, use the force option to overwrite existing classes.");
        }
      }
    }

    $this->writeVeryVerbose("New data class will be <comment>{$dataClass}</comment>...");
    $this->writeVeryVerbose("New presentation classes will be <comment>{$presentationClassIndex}</comment> and <comment>{$presentationClassShow}</comment>...");

    $this->write("The new forum <comment>{$name}</comment> has been created, please perform the following actions:");
    $this->write([
      "Download the files from the server",
      "Extract translations",
      "Translate the new strings",
      "Compile new nginx routes",
      "Commit and push the new forum to GitHub"
    ]);

    return 0;
  }

}
