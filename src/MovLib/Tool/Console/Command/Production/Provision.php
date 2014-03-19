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
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\Output;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Intelligent commando that will provision the complete server or specific software.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Provision extends \Symfony\Component\Console\Command\Command {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The default environment identifier.
   *
   * @var string
   */
  const ENV_DISTRIBUTION = "dist";

  /**
   * The special vagrant environment identifier.
   *
   * @var string
   */
  const ENV_VAGRANT = "vagrant";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Environment identifier whitelist.
   *
   * @var array
   */
  protected $environments;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function configure() {
    // Export available environment constants.
    foreach ((new \ReflectionObject($this))->getConstants() as $name => $value) {
      if (strpos($name, "ENV_") !== false) {
        $this->environments[] = $value;
      }
    }

    // Configure the command and add arguments and options.
    $this->setName("provision");
    $this->setDescription("Intelligent command that will provision the complete server or specific software.");
    $this->setHelp(
      "Please note that the --all option will override any given software argument. The option will always lead to a " .
      "full machine provisioning process, which might take a long time."
    );

    $this->addArgument(
      "software",
      InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
      "The software to install."
    );

    $this->addOption(
      "all",
      "a",
      InputOption::VALUE_NONE,
      "Provision everything (will ignore any given software arguments)."
    );

    $this->addOption(
      "force",
      "f",
      InputOption::VALUE_NONE,
      "Force installation, in other words: ignore already installed software and install again."
    );

    $this->addOption(
      "environment",
      "e",
      InputOption::VALUE_REQUIRED,
      "The environment to provision for, possible values are: " . implode(", ", $this->environments),
      static::ENV_DISTRIBUTION
    );

    $this->addOption(
      "list",
      "l",
      InputOption::VALUE_NONE,
      "List all names of software that is available for installation."
    );

    return $this;
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    global $kernel;
    parent::execute($input, $output);

    // Output a list of all available software and exit successfully.
    if ($input->getOption("list") === true) {
      $output->writeln("<comment>Available Software:</comment>");
      array_map([ $output, "writeln" ], array_keys($this->getSoftware()));
      $this->write("list option ignores any other options, exiting...", self::MESSAGE_TYPE_COMMENT, Output::VERBOSITY_DEBUG);
      return 0;
    }

    // Validate the given environment.
    $environment = $input->getOption("environment");
    if (!in_array($environment, $this->environments)) {
      throw new \InvalidArgumentException("Invalid environment '{$environment}', must be one of: " . implode(", ", $this->environments));
    }

    // Ensure the user has the proper privileges to install software on this machine.
    $this->checkPrivileges();

    // If no configuration is present create it now.
    // @todo Create configuration command to manage the currently in use configuration file and force regenration. This
    //       code should also be moved to that command and simply called from here.
    if (empty($kernel->configuration)) {
      // Get the default distribution configuration.
      $configuration = FileSystem::getJSON("{$kernel->documentRoot}/etc/movlib.dist.json", true);

      // Get the environment specific configuration.
      $envConfiguration = "{$kernel->documentRoot}/etc/movlib.{$environment}.json";
      if ($environment != self::ENV_DISTRIBUTION && file_exists($envConfiguration)) {
        $configuration = array_replace_recursive($configuration, FileSystem::getJSON($envConfiguration, true));
      }

      $globalConfiguration = "/etc/movlib/movlib.json";
      FileSystem::createDirectory(dirname($globalConfiguration), true, "0775", "root", "root");
      $kernel->configuration = FileSystem::putJSON($globalConfiguration, $configuration, LOCK_EX);
      FileSystem::changeMode($globalConfiguration);
      FileSystem::changeOwner($globalConfiguration);
    }

    $force = $input->getOption("force");

    if ($input->getOption("all") === true) {
      if ($output->getVerbosity() > Output::VERBOSITY_VERBOSE) {
        $this->write("Option --all was passed, provisioning everything and ignoring additional software arguments...");
      }
      $software = $this->getSoftware();
    }
    else {
      $software = array_intersect($this->getSoftware(), $input->getArgument("software"));
    }

    if (empty($software)) {
      $output->writeln("<error>Nothing to do, use --help for usage information.</error>");
    }
    else {
      $this->provision($software, $force, $output);
    }

    return 0;
  }

  /**
   * Get a list of all available software for provisioning.
   *
   * @staticvar array $software
   *   Used to cache the software.
   * @return array
   *   Associative array where the key is the software's short name and the value the class name.
   */
  protected function getSoftware() {
    static $software = [];
    if (empty($software)) {
      /* @var $file \splFileInfo */
      foreach (new \DirectoryIterator("glob://" . dirname(__DIR__) . "/Provision/*.php") as $file) {
        $basename  = $file->getBasename(".php");
        $class     = "\\MovLib\\Tool\\Console\\Command\\Provision\\{$basename}";
        $reflector = new \ReflectionClass($class);
        if ($reflector->isInstantiable() === true) {
          $software[strtolower($basename)] = $class;
        }
      }
    }
    return $software;
  }

  /**
   * Provision given software.
   *
   * @staticvar array $installed
   *   Used to keep track of already installed software.
   * @param string|array $software
   *   Either a string with a single name or a numeric array of software names.
   * @param boolean $force
   *   Whether to ignore already installed software or not.
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   The current output instance.
   * @return $this
   */
  protected function provision($software, $force, OutputInterface $output) {
    static $installed = [];
    $availableSoftware = $this->getSoftware();

    $software = (array) $software;
    $c = count($software);
    for ($i = 0; $i < $c; ++$i) {
      if (isset($installed[$software[$i]])) {
        continue;
      }
      $class = "\\MovLib\\Tool\\Console\\Command\\Provision\\{$software[$i]}";
      if (class_exists($class)) {
        $output->writeln("Provisioning {$software[$i]}, this may take a few minutes...");
        /* @var $provisioner \MovLib\Tool\Console\Command\Provision\AbstractProvision */
        $provisioner = new $class($force, $output);
        $this->provision($provisioner->dependencies(), $force, $output);
        $provisioner->provision();
        $installed[$software[$i]] = true;
        $output->writeln("<info>Successfully provisioned {$software[$i]}!</info>");
      }
      else {
        $output->writeln("<error>Software '{$software[$i]}' doesn't exist!</error>");
      }
    }
    return $this;
  }

}
