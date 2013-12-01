<?php

/*!
 *  This file is part of {@link https://github.com/MovLib MovLib}.
 *
 *  Copyright © 2013-present {@link https://movlib.org/ MovLib}.
 *
 *  MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 *  License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 *  version.
 *
 *  MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 *  of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License along with MovLib.
 *  If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */

/**
 * phpMyAdmin configuration.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */

// Server: localhost
$i = 1;
$cfg["Servers"][$i]["verbose"]      = "MovLib";
$cfg["Servers"][$i]["host"]         = "localhost";
$cfg["Servers"][$i]["port"]         = "";
$cfg["Servers"][$i]["socket"]       = "/run/mysqld/mysqld.sock";
$cfg["Servers"][$i]["connect_type"] = "socket";
$cfg["Servers"][$i]["extension"]    = "mysqli";
$cfg["Servers"][$i]["auth_type"]    = "config";
$cfg["Servers"][$i]["user"]         = "root";
$cfg["Servers"][$i]["password"]     = "rootpass";
$cfg["Servers"][$i]["only_db"]      = [ "movlib", "test", "FlughafenDB" ];

// Control User (for Advanced Features)
//$cfg["Servers"][$i]["controlhost"] = "localhost";
//$cfg["Servers"][$i]["controluser"] = "pma";
//$cfg["Servers"][$i]["controlpass"] = "pmapass";

// Advanced Features
//$cfg["Servers"][$i]["bookmarktable"]    = "pma__bookmark";
//$cfg["Servers"][$i]["column_info"]      = "pma__column_info";
//$cfg["Servers"][$i]["designer_coords"]  = "pma__designer_coords";
//$cfg["Servers"][$i]["history"]          = "pma__history";
//$cfg["Servers"][$i]["navigationhiding"] = "pma__navigationhiding";
//$cfg["Servers"][$i]["pdf_pages"]        = "pma__pdf_pages";
//$cfg["Servers"][$i]["pmadb"]            = "phpmyadmin";
//$cfg["Servers"][$i]["recent"]           = "pma__recent";
//$cfg["Servers"][$i]["relation"]         = "pma__relation";
//$cfg["Servers"][$i]["table_coords"]     = "pma__table_coords";
//$cfg["Servers"][$i]["table_info"]       = "pma__table_info";
//$cfg["Servers"][$i]["table_uiprefs"]    = "pma__table_uiprefs";
//$cfg["Servers"][$i]["tracking"]         = "pma__tracking";
//$cfg["Servers"][$i]["userconfig"]       = "pma__userconfig";
//$cfg["Servers"][$i]["usergroups"]       = "pma__usergroups";
//$cfg["Servers"][$i]["users"]            = "pma__users";

// End of servers configuration
$cfg["DefaultLang"]                           = "en";
$cfg["Export"]["charset"]                     = "utf-8";
$cfg["Export"]["sql_auto_increment"]          = false;
$cfg["Export"]["sql_create_table_statements"] = false;
$cfg["Export"]["sql_dates"]                   = true;
$cfg["Export"]["sql_hex_for_blob"]            = false;
$cfg["Export"]["sql_if_not_exists"]           = false;
$cfg["Export"]["sql_procedure_function"]      = false;
$cfg["Export"]["sql_relation"]                = true;
$cfg["Export"]["sql_structure_or_data"]       = "data";
$cfg["ForceSSL"]                              = true;
//$cfg["PersistentConnections"]                 = true; // Seems to be causing trouble
$cfg["SaveDir"]                               = "";
$cfg["ServerDefault"]                         = 1;
$cfg["ShowAll"]                               = true;
$cfg["TitleDefault"]                          = "";
$cfg["TitleServer"]                           = "@VSERVER@";
$cfg["TitleDatabase"]                         = "@DATABASE@ / @VSERVER@";
$cfg["TitleTable"]                            = "@TABLE@ / @DATABASE@ / @VSERVER@";
$cfg["UploadDir"]                             = "";
