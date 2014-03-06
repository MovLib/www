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
class Provision extends \MovLib\Tool\Console\Command\AbstractCommand {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The default environment identifier.
   *
   * @var string
   */
  const ENV_DEFAULT = "production";

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
      static::ENV_DEFAULT
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
   * Execute the provisioning command.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @return integer
   *   <code>0</code> if successfully provisioned, otherwise <code>1</code>.
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    global $kernel;

    // Output a list of all available software and exit successfully.
    if ($input->getOption("list") === true) {
      $output->writeln("<comment>Available Software:</comment>");
      foreach ($this->getSoftware() as $software) {
        $output->writeln("  {$software}");
      }
      return 0;
    }

    // Validate the given environment.
    $environment = $input->getOption("environment");
    if (!in_array($environment, $this->environments)) {
      throw new \InvalidArgumentException("Invalid environment '{$environment}', must be one of: " . implode(", ", $this->environments));
    }

    // Ensure the user has the proper privileges to install software on this machine.
    $this->checkPrivileges();

    // Read the configuration and allow the environment to override any default settings.
    //
    // @todo Write validator for configuration file!
    $config = parse_ini_file("{$kernel->documentRoot}/conf/env.ini", true);
    $envConfig = "{$kernel->documentRoot}/conf/env.{$environment}.ini";
    if (is_file($envConfig)) {
      $config = array_merge($config, parse_ini_file($envConfig, true));
    }
    if ($output->isDebug()) {
      $output->writeln(print_r($config, true), Output::OUTPUT_RAW);
    }
    $config = (object) $config;

    // Export global configuration options to the kernel.
    $kernel->systemGroup = $config->group;
    $kernel->systemUser  = $config->user;

    $force = $input->getOption("force");

    if ($input->getOption("all") === true) {
      $software = $this->getSoftware();
    }
    else {
      $software = $input->getArgument("software");
    }

    if (!empty($software)) {
      foreach ($software as $softwareName) {
        $class = "\\MovLib\\Tool\\Console\\Command\\Provision\\{$softwareName}";
        if (class_exists($class)) {
          $output->writeln("Provisioning {$softwareName}, this may take a few minutes...");
          new $class($config, $environment, $force, $output);
          $output->writeln("<info>Successfully provisioned {$softwareName}!</info>");
        }
        else {
          $output->writeln("<error>Software '{$softwareName}' doesn't exist!</error>");
        }
      }
    }
    else {
      $output->writeln("<error>Nothing to do, use --help for usage information.</error>");
    }

    return 0;
  }

  /**
   * Get a list of all available software for provisioning.
   *
   * @staticvar array $software
   *   Used to cache the software.
   * @return array
   *   Numeric array containing all available software for provisioning.
   */
  protected function getSoftware() {
    static $software = null;
    if (!$software) {
      foreach (glob(dirname(__DIR__) . "/Provision/*.php") as $filename) {
        $basename = basename($filename, ".php");
        if ((new \ReflectionClass("\\MovLib\\Tool\\Console\\Command\\Provision\\{$basename}"))->isInstantiable()) {
          $software[] = strtolower($basename);
        }
      }
    }
    return $software;
  }

}
