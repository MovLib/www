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

/**
 * Main PHP script serving all page requests within the MovLib application.
 *
 * The main script contains all bootstrap functionaility that every script needs. We do not include another file for the
 * bootstrap process, simply to keep things easy to understand. But, this file should only contains procedural PHP
 * extensions and no object-oriented code. Additionally developers should think long and hard if the function they are
 * going to implement is really needed by every request. If not, move it to some other place that is more appropriate
 * (like a static class that is automatically loaded if a script needs a method from it).
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright (c) 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/ movlib.org
 * @since 0.0.1-dev
 */

/** Install path */
define('IP', __DIR__);

/** ASCII end of transmission */
define('PHP_EOT', chr(4));

/** The name of the website. */
define('SITENAME', _('MovLib'));

/** The slogan of the website, with trailing dot. */
define('SITESLOGAN', _('the free movie library.'));

/**
 * Ultra fast class autoloader.
 *
 * @param string $class
 *   Fully qualified class name (automatically passed to this magic function by PHP).
 * @return void
 */
function __autoload($class) {
  require IP . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . strtr($class, '\\', DIRECTORY_SEPARATOR) . '.php';
}

/**
 * Plural version of <code>gettext()</code> and alias for <code>ngettext()</code>.
 *
 * Some languages have more than one form for plural messages, this depends on the count (parameter <code>$n</code>).
 *
 * This alias is defined to keep the usage of gettext calls consistent. We always use <code>_()</code> to call the
 * standard <code>gettext()</code> function (which is standard). To call <code>ngettext()</code> developers should use
 * this alias function.
 *
 * @see ngettext
 * @param string $msgid1
 *   The message to use if the count is <em>1</em>.
 * @param string $msgid2
 *   The message to use if the count is <em>>1</em>.
 * @param int $n
 *   The count that defines the plural form to use.
 * @return string
 *   The translated string
 * @since 0.0.1-dev
 */
function n_($msgid1, $msgid2, $n) {
  return ngettext($msgid1, $msgid2, $n);
}

/**
 * Particular gettext function for a message within a specific context.
 *
 * @link http://www.gnu.org/software/gettext/manual/html_node/Contexts.html
 * @see gettext
 * @param string $msgctxt
 *   The message's context identifier. Do not use the class name or full sentences as context. Try to use jQuery like
 *   selectors like <code>html head title</code> or <code>input[type="search"]</code> as they are very unlikely to
 *   change. Another good example of a context which is used very often is <code>route</code> for URLs.
 * @param string $msgid
 *   The message that should be translated.
 * @return string
 *   The translated message.
 * @since 0.0.1-dev
 */
function p_($msgctxt, $msgid) {
  /* @var $msgctxtid string */
  $msgctxtid = $msgctxt . PHP_EOT . $msgid;
  /* @var $translation string */
  $translation = _($msgctxtid);
  if (strcmp($translation, $msgctxtid) === 0) {
    return $msgid;
  }
  return $translation;
}

/**
 * Plural particular gettext function for a message within a specific context.
 *
 * @link http://www.gnu.org/software/gettext/manual/html_node/Contexts.html
 * @link http://www.gnu.org/software/gettext/manual/html_node/Plural-forms.html
 * @see ngettext
 * @see p_
 * @param string $msgctxt
 *   The message's context identifier. Do not use the class name or full sentences as context. Try to use jQuery like
 *   selectors like <code>html head title</code> or <code>input[type="search"]</code> as they are very unlikely to
 *   change. Another good example of a context which is used very often is <code>route</code> for URLs.
 * @param string $msgid1
 *   The message to use if the count is <em>1</em>.
 * @param string $msgid2
 *   The message to use if the count is <em>>1</em>.
 * @param int $num
 *   The count that defines the plural form to use.
 * @return string
 *   The translated message.
 * @since 0.0.1-dev
 */
function np_($msgctxt, $msgid1, $msgid2, $num) {
  /* @var $msgctxtid1 string */
  $msgctxtid1 = $msgctxt . PHP_EOT . $msgid1;
  /* @var $msgctxtid2 string */
  $msgctxtid2 = $msgctxt . PHP_EOT . $msgid2;
  /* @var $translation string */
  $translation = n_($msgctxtid1, $msgctxtid2, $num);
  if (strcmp($translation, $msgctxtid1) === 0) {
    return $msgid1;
  }
  if (strcmp($translation, $msgctxtid2) === 0) {
    return $msgid2;
  }
  return $translation;
}

// This is the outermost place to catch an exception.
// @todo Should we catch all PHP error messages with exceptions to catch them at least here in production?
try {
  /* @var $presenter string */
  $presenter = '\\MovLib\\Presenter\\' . $_SERVER['PRESENTER'] . 'Presenter';
  echo (new $presenter())->getOutput();
}
/* @var $e \Exception */
catch (\Exception $e) {
  echo $e->getMessage();
}
