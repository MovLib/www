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
namespace MovLib\Stub\Configuration;

/**
 * Directory configuration stub.
 *
 * <b>Note:</b> All directories follow the {@link https://wiki.linuxfoundation.org/en/FHS FHS} standard and are only
 * part of the configuration file to allow us easy changes of paths (shotgun surgery). Only change paths if the standard
 * changes or your have profound reasons to do so!
 *
 * <b>Note:</b> Convention over configuration, usually each software that we use / configure creates a sub-directory
 * within these directories as needed. For instance the MovLib software itself utilizes the following paths:
 * <ul>
 *   <li><code>"/usr/local/bin/movlib"</code> for global access to the MovLib CLI</li>
 *   <li><code>"/var/cache/movlib"</code> for the persistent disk cache of pages</li>
 *   <li><code>"/etc/movlib"</code> for the global configuration file</li>
 *   <li><code>"/var/lib/movlib"</code> fot the history repositories (Git)</li>
 * </ul>
 *
 * @see \MovLib\Stub\Configuration
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Directory {

  /**
   * The machine's absolute path to locally compiled binaries.
   *
   * @var string
   */
  public $bin = "/usr/local/bin";

  /**
   * The machine's absolute path to application cache data.
   *
   * @var string
   */
  public $cache = "/var/cache";

  /**
   * The machine's absolute path to host-specific configuration files.
   *
   * <b>Note:</b> You'll have to change this path in {@see \MovLib\Tool\Kernel} as well because it's used there before
   * to load the global configuration and has to assume this path at that point.
   *
   * @var string
   */
  public $etc = "/etc";

  /**
   * The machine's absolute path to variable state information.
   *
   * @var string
   */
  public $lib = "/var/lib";

  /**
   * The machine's absolute path to log files.
   *
   * @var string
   */
  public $log = "/var/log";

  /**
   * The machine's absolute path to run-time variable data.
   *
   * @var string
   */
  public $run = "/run";

  /**
   * The machine's absolute path to local source code.
   *
   * @var string
   */
  public $src = "/usr/local/src";

  /**
   * The machine's absolute path to temporary files.
   *
   * @var string
   */
  public $tmp = "/tmp";

}
