#!/usr/bin/env php
<?php

/*!
 *  This file is part of {@link https://github.com/MovLib MovLib}.
 *
 *  Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
 * Inserts and updates country and language translations in the database.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */

/*DEBUG{{{*/
error_reporting(-1);
ini_set("display_errors", 1);
/*}}}DEBUG*/

// Include the global I18n class.
require dirname(__DIR__) . "/src/MovLib/Model/BaseModel.php";
require dirname(__DIR__) . "/src/MovLib/Model/I18nModel.php";

/**
 * Contains all country and basic language codes that our application shall know about.
 *
 * @var array
 */
$codes = [
  "countries" => [ "AD", "AE", "AF", "AG", "AI", "AL", "AM", "AO", "AQ", "AR", "AS", "AT", "AU", "AW", "AX", "AZ", "BA", "BB", "BD", "BE", "BF", "BG", "BH", "BI", "BJ", "BL", "BM", "BN", "BO", "BQ", "BR", "BS", "BT", "BV", "BW", "BY", "BZ", "CA", "CC", "CD", "CF", "CG", "CH", "CI", "CK", "CL", "CM", "CN", "CO", "CR", "CU", "CV", "CW", "CX", "CY", "CZ", "DE", "DJ", "DK", "DM", "DO", "DZ", "EC", "EE", "EG", "EH", "ER", "ES", "ET", "FI", "FJ", "FK", "FM", "FO", "FR", "GA", "GB", "GD", "GE", "GF", "GG", "GH", "GI", "GL", "GM", "GN", "GP", "GQ", "GR", "GS", "GT", "GU", "GW", "GY", "HK", "HM", "HN", "HR", "HT", "HU", "ID", "IE", "IL", "IM", "IN", "IO", "IQ", "IR", "IS", "IT", "JE", "JM", "JO", "JP", "KE", "KG", "KH", "KI", "KM", "KN", "KP", "KR", "KW", "KY", "KZ", "LA", "LB", "LC", "LI", "LK", "LR", "LS", "LT", "LU", "LV", "LY", "MA", "MC", "MD", "ME", "MF", "MG", "MH", "MK", "ML", "MM", "MN", "MO", "MP", "MQ", "MR", "MS", "MT", "MU", "MV", "MW", "MX", "MY", "MZ", "NA", "NC", "NE", "NF", "NG", "NI", "NL", "NO", "NP", "NR", "NU", "NZ", "OM", "PA", "PE", "PF", "PG", "PH", "PK", "PL", "PM", "PN", "PR", "PS", "PT", "PW", "PY", "QA", "RE", "RO", "RS", "RU", "RW", "SA", "SB", "SC", "SD", "SE", "SG", "SH", "SI", "SJ", "SK", "SL", "SM", "SN", "SO", "SR", "SS", "ST", "SV", "SX", "SY", "SZ", "TC", "TD", "TF", "TG", "TH", "TJ", "TK", "TL", "TM", "TN", "TO", "TR", "TT", "TV", "TW", "TZ", "UA", "UG", "UM", "US", "UY", "UZ", "VA", "VC", "VE", "VG", "VI", "VN", "VU", "WF", "WS", "YE", "YT", "ZA", "ZM", "ZW" ],
  "languages" => [ "ab", "aa", "af", "ak", "sq", "am", "ar", "an", "hy", "as", "av", "ae", "ay", "az", "bm", "ba", "eu", "be", "bn", "bh", "bi", "bs", "br", "bg", "my", "ca", "ch", "ce", "ny", "zh", "cv", "kw", "co", "cr", "hr", "cs", "da", "dv", "nl", "dz", "en", "eo", "et", "ee", "fo", "fj", "fi", "fr", "ff", "gl", "ka", "de", "el", "gn", "gu", "ht", "ha", "he", "hz", "hi", "ho", "hu", "ia", "id", "ie", "ga", "ig", "ik", "io", "is", "it", "iu", "ja", "jv", "kl", "kn", "kr", "ks", "kk", "km", "ki", "rw", "ky", "kv", "kg", "ko", "ku", "kj", "la", "lb", "lg", "li", "ln", "lo", "lt", "lu", "lv", "gv", "mk", "mg", "ms", "ml", "mt", "mi", "mr", "mh", "mn", "na", "nv", "nb", "nd", "ne", "ng", "nn", "no", "ii", "nr", "oc", "oj", "cu", "om", "or", "os", "pa", "pi", "fa", "pl", "ps", "pt", "qu", "rm", "rn", "ro", "ru", "sa", "sc", "sd", "se", "sm", "sg", "sr", "gd", "sn", "si", "sk", "sl", "so", "st", "es", "su", "sw", "ss", "sv", "ta", "te", "tg", "th", "ti", "bo", "tk", "tl", "tn", "to", "tr", "ts", "tt", "tw", "ty", "ug", "uk", "ur", "uz", "ve", "vi", "vo", "wa", "cy", "wo", "fy", "xh", "yi", "yo", "za", "zu" ]
];

/**
 * Contains all language codes that our application supports.
 *
 * @var array
 */
$supported_language_codes = \MovLib\Model\I18nModel::$supportedLanguageCodes;

/**
 * Contains the default ISO 639-1 alpha-2 language code.
 *
 * @var string
 */
$default_language_code = \MovLib\Model\I18nModel::getDefaultLanguageCode();

/**
 * Helper function to translate country names.
 *
 * @global string $default_language_code
 * @param string $country_code
 *   The ISO 3166-1 alpha-2 country code.
 * @param string $locale
 *   The desired locale or ISO 639-1 alpha-2 language code.
 * @return string
 *   The country's name translated to the desired locale.
 */
function translate_countries($country_code, $locale) {
  global $default_language_code;
  return \Locale::getDisplayRegion("{$default_language_code}-{$country_code}", $locale);
}

/**
 * Helper function to translate language names.
 *
 * @param string $language_code
 *   The ISO 639-1 alpha-2 language code.
 * @param string $locale
 *   The desired locale or ISO 639-1 alpha-2 language code.
 * @return string
 *   The language's name translated to the desired locale.
 */
function translate_languages($language_code, $locale) {
  return \Locale::getDisplayLanguage($language_code, $locale);
}

// Get rid of the default language code in the supported language codes array.
foreach ($supported_language_codes as $delta => $language_code) {
  if ($language_code === $default_language_code) {
    unset($supported_language_codes[$delta]); // Remove the default language code.
    $supported_language_codes = array_values($supported_language_codes); // Re-index array
    break;
  }
}

// Connect to the database.
$mysqli = new \mysqli();
$mysqli->real_connect();
$mysqli->select_db("movlib");

// Insert data into database.
foreach ($codes as $table => $data) {
  $data_count = count($data) - 1;
  $values = "";
  $bind_param_args = [ "" ];
  $names = [];
  for ($i = 0; $i <= $data_count; ++$i) {
    $names[$i]["_"] = call_user_func("translate_{$table}", $data[$i], $default_language_code);
    $values .= "(?, ?, COLUMN_CREATE(";
    $bind_param_args[0] .= "ss";
    $bind_param_args[] = &$data[$i];
    $bind_param_args[] = &$names[$i]["_"];
    $comma = "";
    $supported_language_codes_count = count($supported_language_codes);
    for ($j = 0; $j < $supported_language_codes_count; ++$j) {
      $names[$i][$j] = call_user_func("translate_{$table}", $data[$i], $supported_language_codes[$j]);
      $values .= "{$comma}?, ?";
      $bind_param_args[0] .= "ss";
      $bind_param_args[] = &$supported_language_codes[$j];
      $bind_param_args[] = &$names[$i][$j];
      $comma = ", ";
    }
    $values .= "))";
    if ($i < $data_count) {
      $values .= ", ";
    }
  }
  if (($stmt = $mysqli->prepare(
    "INSERT
      INTO `{$table}` (`iso_alpha-2`, `name`, `dyn_translations`)
      VALUES {$values}
      ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `dyn_translations`=VALUES(`dyn_translations`)
    "
  )) === false) {
    $error = "{$mysqli->error} ({$mysqli->errno})";
    $mysqli->close();
    exit($error . PHP_EOL);
  }
  if (call_user_func_array([ $stmt, "bind_param" ], $bind_param_args) === false) {
    $error = "{$stmt->error} ({$stmt->errno})";
    $stmt->close();
    $mysqli->close();
    exit($error . PHP_EOL);
  }
  $stmt->execute();
  $stmt->close();
}
// Insert the "Silent" language, because it is not present in the language list of Intl.
if (($stmt = $mysqli->prepare(
  "INSERT
    INTO `languages` (`iso_alpha-2`, `name`, `dyn_translations`)
    VALUES ('xx', 'Silent', COLUMN_CREATE('de', 'Stumm'))
    ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `dyn_translations`=VALUES(`dyn_translations`)
  "
)) === false) {
  $error = "{$mysqli->error} ({$mysqli->errno})";
  $mysqli->close();
  exit($error . PHP_EOL);
}
$stmt->execute();
$stmt->close();
$mysqli->close();
