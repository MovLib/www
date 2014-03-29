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
namespace MovLib\Console\Command\Dev;

use \MovLib\Console\MySQLi;
use \MovLib\Data\User\FullUser;
use \Symfony\Component\Console\Input\InputArgument;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to create random user(s).
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class RandomUser extends \MovLib\Console\Command\AbstractCommand {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * The default amount of random users to generate.
   *
   * @var integer
   */
  const DEFAULT_AMOUNT = 100;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The actual amount of users to generate.
   *
   * @var integer
   */
  protected $amount = self::DEFAULT_AMOUNT;

  /**
   * All characters that are available for the random username generator.
   *
   * We have 62 characters in this array and it was generated with the following code:
   * <pre>array_merge(range("A", "Z"), range("a", "z"), range("0", "9"))</pre>
   *
   * @see RandomUser::getRandomUsername()
   * @todo Should we include some unicode as well?
   * @var string
   */
  protected $characters = [
    "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z",
    "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z",
    "0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
  ];

  /**
   * The <var>RandomUser::$characters</var> count minus one, or in code: <code>count($this->characters) - 1</code>.
   *
   * @see RandomUser::getRandomUsername()
   * @var integer
   */
  protected $charactersCount = 61;

  /**
   * Numeric array containing <b>all</b> usernames, we need this to make sure that we don't generate a username that's
   * already in use.
   *
   * @var array
   */
  protected $usernames = [];


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function configure() {
    $this->setName("create-random-users");
    $this->setDescription("Create one or more random users.");
    $this->addArgument(
      "amount",
      InputArgument::OPTIONAL,
      "The amount of random users to create, defaults to " . self::DEFAULT_AMOUNT . ".",
      self::DEFAULT_AMOUNT
    );
  }

  /**
   * Get a randomly generated username.
   *
   * @return string
   *   The randomly generated username.
   */
  protected function getRandomUsername() {
    $username = null;

    do {
      // 1 to NAME_MAXIMUM_LENGTH, all variations are valid!
      $length   = mt_rand(1, FullUser::NAME_MAXIMUM_LENGTH);
      for ($i = 0; $i < $length; ++$i) {
        $username .= $this->characters[mt_rand(0, $this->charactersCount)];
      }
    }
    // If this username is already in use generate another one.
    while (in_array($username, $this->usernames));

    $this->usernames[] = $username;
    return $username;
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $this->amount = (integer) $input->getArgument("amount");
    $db = new MySQLi($this->diContainer);

    $this->write("Preparing to generate <comment>{$this->amount}</comment> random users ...");
    $this->setUsernames($db);
    $values = $params = $usersWithAvatar = null;

    $this->progressStart($this->amount);
    $user = new FullUser($this->diContainer);
    $min  = strtotime("-1 year");
    $max  = time();

    $this->write("Creating <comment>{$this->amount}</comment> random users ...");
    for ($i = 0; $i < $this->amount; ++$i) {
      $created = mt_rand($min, $max);
      $name    = $this->getRandomUsername();
      $values .= "(FROM_UNIXTIME(?), ?, FROM_UNIXTIME(?), '', ?, ?, ?, '{$this->intl->defaultLanguageCode}'),";
      if ($i % 6 !== 0) {
        $params[]          = $created;
        $params[]          = "jpg";
        $usersWithAvatar[] = $name;
      }
      else {
        $params[] = $params[] = null;
      }
      $params[] = $created;
      $params[] = "{$name}@{$this->config->hostname}";
      $params[] = $name;
      $params[] = $user->hashPassword($name);
      $this->progressAdvance();
    }
    $values = rtrim($values, ",");
    $this->progressFinish();

    $this->write("Inserting <comment>{$this->amount}</comment> random users into database ...");
    $db->query(
      "INSERT INTO `users` (
        `image_changed`,
        `image_extension`,
        `created`,
        `dyn_about_me`,
        `email`,
        `name`,
        `password`,
        `system_language_code`
      ) VALUES {$values}",
      str_repeat("s", count($params)),
      $params
    );

    if (($c = count($usersWithAvatar))) {
      $this->write("Generating avatar images (every 6th user has none) ...");
      $dim = FullUser::STYLE_SPAN_02;
      $tmp = ini_get("upload_tmp_dir") . "/movdev-command-create-users.jpg";
      $this->exec("convert -size {$dim}x{$dim} xc: +noise Random {$tmp}");
      $ext = new \ReflectionProperty($user, "extension");
      $ext->setAccessible(true);
      $ext->setValue($user, "jpg");
      $this->progressStart($c);
      $in = rtrim(str_repeat("?,", $c), ",");
      $result = $db->query("SELECT `id`, `name` FROM `users` WHERE `name` IN ({$in})", str_repeat("s", $c), $usersWithAvatar)->get_result();
      while ($row = $result->fetch_assoc()) {
        $user->filename = $this->fs->sanitizeFilename($row["name"]);
        $convert = new \ReflectionMethod($user, "convert");
        $convert->setAccessible(true);
        $convert->invoke($user, $tmp, FullUser::STYLE_SPAN_02);
        $convert->invoke($user, $tmp, FullUser::STYLE_SPAN_01);
        $this->progressAdvance();
      }
      unlink($tmp);
      $this->progressFinish();
    }

    $this->write("Successfully created {$this->amount} of random users!", self::MESSAGE_TYPE_INFO);

    return 0;
  }

  /**
   * Fetch all usernames from the database.
   *
   * The usernames we generate have to be absolutely unique, therefor we have to know all the usernames that are in use.
   *
   * @param \MovLib\Console\MySQLi $db
   *   Admin database instance.
   * @return this
   * @throws \MovLib\Exception\DatabaseException
   */
  protected function setUsernames(MySQLi $db) {
    if (($result = $db->query("SELECT `name` FROM `users`")->get_result())) {
      while ($user = $result->fetch_row()) {
        $this->usernames[] = $user[0];
      }
    }
    return $this;
  }

}
