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
namespace MovLib\Tool;

use \MovDev\Database;
use \MovLib\Tool\Configuration;
use \Composer\Script\Event;

/**
 * React on composer execution.
 *
 * Note that any exceptions are catched by composer and the program is automatically aborted.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Composer {
  use \MovLib\Data\TraitUtilities;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The fired composer event.
   *
   * @var \Composer\Script\Event
   */
  protected $event;

  /**
   * Absolute path to composer packages (without trailing slash).
   *
   * @var string
   */
  protected $vendorPath;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new Composer object.
   *
   * @global \MovLib\Tool\Configuration $config
   * @param \Composer\Script\Event $event
   *   The fired composer event.
   */
  public function __construct(Event $event) {
    global $config;
    if (!$config) {
      $config = new Configuration();
    }
    $this->event      = $event;
    $this->vendorPath = "{$_SERVER["DOCUMENT_ROOT"]}/{$event->getComposer()->getConfig()->get("vendor-dir")}";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Fix permissions on all files within the composer vendor folder.
   */
  public function fixPermissions() {
    $this->exec("sudo movcli fixperm {$this->vendorPath}");
  }

  /**
   * Install phpMyAdmin.
   *
   * @global \MovDev\Database $db
   * @param string $fullName
   *   The packages full name including the name and slash.
   */
  public function phpmyadmin($fullName) {
    global $db;
    if (!$db) {
      $db = new Database();
    }
    symlink("{$_SERVER["DOCUMENT_ROOT"]}/conf/phpmyadmin/config.inc.php", "{$this->vendorPath}/{$fullName}/config.inc.php");
    $db->queries(file_get_contents("{$this->vendorPath}/{$fullName}/examples/create_table.sql"));
  }

  /**
   * Install VisualPHPUnit.
   *
   * @param string $fullName
   *   The packages full name including the name and slash.
   */
  public function visualphpunit($fullName) {
    $path = "{$this->vendorPath}/{$fullName}/app";

    // The symbolic link is for correct routing.
    symlink("{$path}/public", "{$path}/visualphpunit");

    // Replace some vendor files with custom ones.
    copy("{$_SERVER["DOCUMENT_ROOT"]}/conf/visualphpunit/index.php", "{$path}/public/index.php");
    copy("{$_SERVER["DOCUMENT_ROOT"]}/conf/visualphpunit/bootstrap.php", "{$path}/config/bootstrap.php");
  }


  // ------------------------------------------------------------------------------------------------------------------- Static Methods


  /**
   * Automatically called after `composer install` execution.
   *
   * @param \Composer\Script\Event $event
   *   The event fired by composer.
   */
  public static function postInstall(Event $event) {
    (new Composer($event))->fixPermissions();
  }

  /**
   * Automatically called after composer installed a package.
   *
   * @param \Composer\Script\Event $event
   *   The event fired by composer.
   */
  public static function postPackageInstall(Event $event) {
    $operation = $event->getOperation();
    if (method_exists($operation, "getPackage")) {
      $composer         = new Composer($event);
      $fullPackageName  = $operation->getPackage()->getName();
      $packageName      = substr($fullPackageName, strrpos($fullPackageName, "/") + 1);
      if (method_exists($composer, $packageName)) {
        $composer->{$packageName}($fullPackageName);
      }
    }
  }

  /**
   * Automatically called after composer updated a package.
   *
   * @param \Composer\Script\Event $event
   *   The event fired by composer.
   */
  public static function postPackageUpdate(Event $event) {
    self::postPackageInstall($event);
  }

  /**
   * Automatically called after `composer update` execution.
   *
   * @param \Composer\Script\Event $event
   *   The event fired by composer.
   */
  public static function postUpdate(Event $event) {
    self::postInstall($event);
  }

}
