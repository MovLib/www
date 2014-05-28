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

use \MovLib\Core\Config as DefaultConfig;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Input\InputOption;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Configuration management command.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Config extends \MovLib\Console\Command\AbstractCommand {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Config";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName("config");
    $this->setDescription("Manage global configuration settings.");
    $this->setHelp(
      "This command allows you to manage the global runtime configuration installed on this server. Please note that " .
      "fully-fledged configurations should be created in <comment>" . dirname(DefaultConfig::URI) . "</comment> as PHP classes that " .
      "extend the default configuration <comment>\\MovLib\\Core\\Config</comment>. Please note that some options will " .
      "only have an effect after reloading specific services.\n"
    );
    $this->addArgument("config-key", InputArgument::OPTIONAL, "Setting key");
    $this->addArgument("config-value", InputArgument::OPTIONAL | InputArgument::IS_ARRAY, "Setting value");
    $this->addOption("class", "c", InputOption::VALUE_REQUIRED, "Load given configuration class and use it as new global configuration.");
    $this->addOption("delete", "d", InputOption::VALUE_NONE, "Delete the current global configuration and restore the default configuration as global configuration.");
    $this->addOption("list", "l", InputOption::VALUE_NONE, "Show all configuration settings and their values.");
    $this->addOption("reset", "r", InputOption::VALUE_REQUIRED, "Reset the given setting's key to its default value.");
    $this->addOption("save", "s", InputOption::VALUE_NONE, "Save the current configuration as new global configuration.");
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $configClone = clone $this->config;

    if ($input->getOption("delete")) {
      $this->delete();
    }

    if (($className = $input->getOption("class"))) {
      $this->loadClass($className);
    }

    if (($configKey = $input->getOption("reset"))) {
      $this->reset($configKey);
    }

    $configKey = $input->getArgument("config-key");
    if (!empty($configKey)) {
      $values = $input->getArgument("config-value");
      if (empty($values)) {
        $this->show($configKey);
      }
      else {
        $this->set($configKey, $values);
      }
    }

    if ($input->getOption("save") && $configClone !== $this->config) {
      $this->save();
    }

    if ($input->getOption("list")) {
      $this->show(array_keys(get_object_vars($this->config)));
    }

    return 0;
  }

  /**
   * Delete the current configuration object and re-create default configuration.
   *
   * @return this
   */
  protected function delete() {
    $this->writeDebug("Deleting global configuration and reseting to default configuration");
    $this->config = new DefaultConfig();
    return $this;
  }

  /**
   * Load given configuration file and set as new global configuration.
   *
   * @param string $className
   *   The name of the class that should be loaded.
   * @return this
   * @throws \InvalidArgumentException
   * @throws \LogicException
   */
  protected function loadClass($className) {
    $className = "\\MovLib\\Core\\Config\\" . ucfirst($className);
    if (!class_exists($className)) {
      throw new \InvalidArgumentException("Configuration '{$className}' doesn't exist.");
    }
    if (!(new \ReflectionClass($className))->isSubclassOf("\\MovLib\\Core\\Config")) {
      throw new \LogicException("Configuration '{$className}' must extend '\\MovLib\\Core\\Config'");
    }
    $this->writeDebug("Loading new configuration from class <comment>{$className}</comment>");
    $this->config = new $className();
    return $this;
  }

  /**
   * Reset a configuration setting to its default value.
   *
   * @param string $configKey
   *   The configuration setting's key to reset.
   * @return this
   */
  protected function reset($configKey) {
    if (!property_exists($this->config, $configKey)) {
      throw new \InvalidArgumentException("Configuration setting '{$configKey}' doesn't exist.");
    }
    $this->config->$configKey = (new DefaultConfig())->$configKey;
    return $this;
  }

  /**
   * Save the current configuration object.
   *
   * @return this
   */
  protected function save() {
    mkdir(dirname(DefaultConfig::URI));
    $this->writeVerbose("Saving current global configuration to <comment>" . DefaultConfig::URI . "</comment>");
    file_put_contents(DefaultConfig::URI, serialize($this->config));
    $this->writeDebug("Successfully saved current global configuration to <comment>" . DefaultConfig::URI. "</comment>");
    return $this;
  }

  /**
   * Set configuration setting.
   *
   * @param string $configKey
   *   The configuration key to set.
   * @param mixed $values
   *   The configuration key's new value.
   * @return this
   */
  protected function set($configKey, $values) {
    $value = trim(var_export($values, true));
    $this->writeVerbose("Setting configuration key <comment>{$configKey}</comment> to value <info>{$value}</info>");
    switch (($type = gettype($this->config->$configKey))) {
      case "array":
        break;

      case "object":
        $values = (object) $values;
        break;

      default:
        $this->writeVerbose("Ignoring all values but the first, type of setting is scalar...");
        $values = $values[0];
        settype($values, $type);
    }

    if (method_exists($this->config, "set{$configKey}")) {
      $this->config->{"set{$configKey}"}($values);
    }
    else {
      $this->config->$configKey = $values;
    }

    return $this;
  }

  /**
   * Display value of key from current configuration.
   *
   * @param array|string $configKeys
   *   Either a string with a single key or an array with multiple keys.
   * @return this
   */
  protected function show($configKeys) {
    $xdebug = function_exists("xdebug_var_dump");
    $rows   = [];
    foreach ((array) $configKeys as $configKey) {
      if ($xdebug) {
        ob_start();
        xdebug_var_dump($this->config->$configKey);
        $rows[] = [ "<comment>{$configKey}</comment>", trim(ob_get_clean()) ];
      }
      else {
        $value = trim(var_export($this->config->$configKey, true));
        switch (gettype($this->config->$configKey)) {
          case "boolean":
            if ($this->config->$configKey === true) {
              $color = "green";
            }
            else {
              $color = "red";
            }
            $value = "<fg={$color}>{$value}</fg={$color}>";
            break;

          case "integer":
          case "double":
            $value = "<fg=cyan>{$value}</fg=cyan>";
            break;

          case "string";
            $value = "<fg=yellow>{$value}</fg=yellow>";
            break;
        }
        $rows[] = [ $configKey, $value ];
      }
    }

    /* @var $table \Symfony\Component\Console\Helper\Table */
    $table = $this->getHelper("table");
    $table->setHeaders([ "Key", "Value" ]);
    $table->setRows($rows);
    $table->render($this->output);

    return $this;
  }

}
