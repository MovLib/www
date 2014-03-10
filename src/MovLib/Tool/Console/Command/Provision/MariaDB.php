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
namespace MovLib\Tool\Console\Command\Provision;

/**
 * Install MariaDB.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MariaDB extends \MovLib\Tool\Console\Command\Provision\AbstractProvision {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * debconf set selection preseed data for unattended MariaDB installation.
   *
   * @var string
   */
  protected $preseed = <<<EOT
mariadb-server-10.0	mysql-server/root_password	password
mariadb-server-10.0	mysql-server/root_password_again	password
mariadb-server-10.0	mysql-server-5.1/start_on_boot	boolean	true
mariadb-server-10.0	mysql-server-5.1/nis_warning	note
mariadb-server-10.0	mysql-server-5.1/postrm_remove_databases	boolean	false
mariadb-server-10.0	mysql-server/password_mismatch	error
mariadb-server-10.0	mysql-server/error_setting_password	error
mariadb-server-10.0	mariadb-server-10.0/really_downgrade	boolean	false
mariadb-server-10.0	mysql-server/no_upgrade_when_using_ndb	error
EOT;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function provision() {
    $this->aptSource("http://tweedo.com/mirror/mariadb/repo/10.0/debian", "wheezy", "main", "0xcbcb082a1bb943db");
    $this->aptPreseed($answers, "mariadb");
    $this->aptInstall("mariadb-server");
    // @todo MariaDB configuration.
    return $this;
  }

}
