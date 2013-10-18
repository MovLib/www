<?php


// Server: localhost
$i = 1;
$cfg["Servers"][$i]["verbose"] = "";
$cfg["Servers"][$i]["host"] = "localhost";
$cfg["Servers"][$i]["port"] = "";
$cfg["Servers"][$i]["socket"] = "/var/run/mysqld/mysqld.sock";
$cfg["Servers"][$i]["connect_type"] = "socket";
$cfg["Servers"][$i]["extension"] = "mysqli";
$cfg["Servers"][$i]["nopassword"] = true;
$cfg["Servers"][$i]["auth_type"] = "cookie";
$cfg["Servers"][$i]["user"] = "root";
$cfg["Servers"][$i]["password"] = "";
$cfg["Servers"][$i]["only_db"] = array("movlib", "test");
$cfg["Servers"][$i]["AllowNoPassword"] = true;

// End of servers configuration
$cfg["blowfish_secret"] = "526104f4355473.00015191";
$cfg["ShowAll"] = true;
$cfg["Export"]["charset"] = "utf-8";
$cfg["Export"]["sql_dates"] = true;
$cfg["Export"]["sql_relation"] = true;
$cfg["Export"]["sql_structure_or_data"] = "data";
$cfg["Export"]["sql_procedure_function"] = false;
$cfg["Export"]["sql_create_table_statements"] = false;
$cfg["Export"]["sql_if_not_exists"] = false;
$cfg["Export"]["sql_auto_increment"] = false;
$cfg["Export"]["sql_hex_for_blob"] = false;
$cfg["DefaultLang"] = "en";
$cfg["ServerDefault"] = 1;
$cfg["UploadDir"] = "";
$cfg["SaveDir"] = "";
