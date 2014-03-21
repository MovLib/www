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
namespace MovLib\Data;

use \MovLib\Data\FileSystem;

/**
 * @coversDefaultClass \MovLib\Data\FileSystem
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class FileSystemTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The test directory.
   *
   * @var string
   */
  protected $d;

  /**
   * The test file.
   *
   * @var string
   */
  protected $f;

  /**
   * Absolute path to the system's temporary directory.
   *
   * @var string
   */
  protected $tmp;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Overriding constructor to set properties once.
   */
  public function __construct($name = null, array $data = [], $dataName = "") {
    parent::__construct($name, $data, $dataName);
    $this->tmp = sys_get_temp_dir();
    $this->d   = "{$this->tmp}/phpunit";
    $this->f   = "{$this->d}/phpunit";
  }

  /**
   * Called before each test.
   */
  protected function setUp() {
    exec("mkdir {$this->d} && touch {$this->f}");
  }

  /**
   * Called before all tests.
   */
  public static function setUpBeforeClass() {
    (new FileSystemTest())->tearDown();
  }

  /**
   * Called after each test.
   */
  protected function tearDown() {
    exec("chmod 0775 {$this->d} 2>&1");
    exec("chmod 0664 {$this->f} 2>&1");
    exec("rm --recursive --force {$this->d}");
  }

  /**
   * Called after all tests.
   */
  public static function tearDownAfterClass() {
    (new FileSystemTest())->tearDown();
  }


  // ------------------------------------------------------------------------------------------------------------------- Data Provider


  /**
   * Get various file modes.
   *
   * <b>NOTE</b><br>
   * The <code>000</code> modes make no sense, but are valid!
   *
   * @return array
   */
  public static function dataProviderModes() {
    static $modes = null;

    return [[ "0777" ]];

    if (!isset($modes)) {
      for ($u = 0; $u < 5; $u += 2) {
        for ($x = 0; $x < 8; ++$x) {
          for ($w = 0; $w < 8; ++$w) {
            for ($r = 0; $r < 8; ++$r) {
              $modes[] = (array) "{$u}{$x}{$w}{$r}";
            }
          }
        }
      }
    }

    return $modes;
  }


  // ------------------------------------------------------------------------------------------------------------------- Helpers


  /**
   * Execute code as non-privileged user.
   *
   * @global \MovLib\Tool\Kernel $kernel
   * @param callable $callable
   *   The code to execute as non-privileged user.
   */
  public function execNonPrivileged($callable) {
    global $kernel;
    $uid = posix_getuid() === 0;
    if ($uid === true) {
      Shell::execute("su {$kernel->systemUser}");
    }
    try {
      $callable();
    }
    finally {
      if ($uid === true) {
        Shell::execute("exit");
      }
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::validateMode
   * @dataProvider dataProviderModes
   */
  public function testValidateMode($mode) {
    $this->assertTrue(intval($mode, 8) === FileSystem::validateMode($mode));
  }

  /**
   * @covers ::validateMode
   * @expectedException \InvalidArgumentException
   */
  public function testValidateModeNull() {
    FileSystem::validateMode(null);
  }

  /**
   * @covers ::validateMode
   * @expectedException \InvalidArgumentException
   */
  public function testValidateModeEmpty() {
    FileSystem::validateMode("");
  }

  /**
   * @covers ::validateMode
   * @expectedException \InvalidArgumentException
   */
  public function testValidateModeNoString() {
    FileSystem::validateMode(1234);
  }

  /**
   * @covers ::validateMode
   * @expectedException \InvalidArgumentException
   */
  public function testValidateModeInvalid() {
    FileSystem::validateMode("g+w");
  }

  /**
   * @covers ::getPermissions
   * @dataProvider dataProviderModes
   * @depends testValidateMode
   */
  public function testGetPermissions($mode) {
    chmod($this->f, FileSystem::validateMode($mode));
    $this->assertEquals($mode, FileSystem::getPermissions($this->f));
  }

  /**
   * @covers ::changeMode
   * @depends testGetPermissions
   */
  public function testChangeMode() {
    chmod($this->f, 0777);
    FileSystem::changeMode($this->f);
    $this->assertEquals(FileSystem::FILE_MODE, FileSystem::getPermissions($this->f));
  }

  /**
   * @covers ::changeMode
   * @depends testGetPermissions
   */
  public function testChangeModeDirectory() {
    chmod($this->d, 0777);
    FileSystem::changeMode($this->d);
    $this->assertEquals(FileSystem::DIRECTORY_MODE, FileSystem::getPermissions($this->d));
  }

  /**
   * @covers ::changeMode
   * @dataProvider dataProviderModes
   * @depends testGetPermissions
   */
  public function testChangeModeFileCustom($mode) {
    FileSystem::changeMode($this->f, $mode);
    $this->assertEquals($mode, FileSystem::getPermissions($this->f));
  }

  /**
   * @covers ::changeMode
   * @dataProvider dataProviderModes
   * @depends testGetPermissions
   */
  public function testChangeModeDirectoryCustom($mode) {
    FileSystem::changeMode($this->d, $mode);
    $this->assertEquals($mode, FileSystem::getPermissions($this->d));
  }

  /**
   * @covers ::changeModeRecursive
   * @depends testGetPermissions
   */
  public function testChangeModeRecursive() {
    // We create two directories with one file each.
    $d1 = "{$this->d}/phpunitdir1";
    $d2 = "{$this->d}/phpunitdir2";
    $f1 = "{$this->d}/phpunitdir1/phpunit";
    $f2 = "{$this->d}/phpunitdir2/phpunit";

    // Create them with the default system umask.
    for ($i = 1; $i < 3; ++$i) {
      mkdir(${"d{$i}"});
      touch(${"f{$i}"});
    }

    // Change the permissions to the default FileSystem permissions.
    FileSystem::changeModeRecursive($this->d);

    // Go through all files and check if the permissions were applied correctly.
    foreach (glob("{$this->d}/*") as $path) {
      if (is_dir($path)) {
        $mode = FileSystem::DIRECTORY_MODE;
      }
      elseif (is_file($path)) {
        $mode = FileSystem::FILE_MODE;
      }
      if ($mode) {
        $this->assertEquals($mode, FileSystem::getPermissions($path));
      }
    }
  }

  /**
   * @covers ::changeModeRecursive
   * @depends testGetPermissions
   */
  public function testChangeModeRecursiveCustom() {
    // The custom modes to test.
    $fMode = "0600";
    $dMode = "0700";

    // We create two directories with one file each.
    $d1 = "{$this->d}/phpunitdir1";
    $d2 = "{$this->d}/phpunitdir2";
    $f1 = "{$this->d}/phpunitdir1/phpunit";
    $f2 = "{$this->d}/phpunitdir2/phpunit";

    // Create them with the default system umask.
    for ($i = 1; $i < 3; ++$i) {
      mkdir(${"d{$i}"});
      touch(${"f{$i}"});
    }

    // Change the permissions to the default FileSystem permissions.
    FileSystem::changeModeRecursive($this->d, $fMode, $dMode);

    // Go through all files and check if the permissions were applied correctly.
    foreach (glob("{$this->d}/*") as $path) {
      if (is_dir($path)) {
        $mode = $dMode;
      }
      elseif (is_file($path)) {
        $mode = $fMode;
      }
      if ($mode) {
        $this->assertEquals($mode, FileSystem::getPermissions($path));
      }
    }
  }

  /**
   * @covers ::changeMode
   * @expectedException \ErrorException
   */
  public function testChangeModeFailsOnExistingFile() {
    $this->execNonPrivileged(function () {
      FileSystem::changeMode("/root", "0777");
    });
  }

  /**
   * @covers ::changeMode
   */
  public function testChangeModeNoFile() {
    FileSystem::changeMode(str_repeat("/phpunit", 10), "0777");
  }

  /**
   * @covers ::changeOwner
   */
  public function testChangeOwner() {
    $this->execNonPrivileged(function () {
      $this->assertNull(FileSystem::changeOwner(str_repeat("/phpunit", 10), "foo", "bar"));
    });
  }

  /**
   * @covers ::changeOwner
   */
  public function testChangeOwnerKernelFallbacks() {
    if (posix_getuid() !== 0) {
      $this->markTestSkipped("This method can only be tested if executed as root or via sudo");
    }
    else {
      FileSystem::changeOwner($this->f);
      $this->assertEquals(posix_geteuid(), fileowner($this->f));
      $this->assertEquals(posix_getegid(), filegroup($this->f));
    }
  }

  /**
   * @covers ::changeOwner
   * @todo Implement variations of changeOwner if executed as root or via sudo.
   */
  public function testChangeOwnerRecursive() {
    if (posix_getuid() !== 0) {
      $this->markTestSkipped("The functionality of chown and chgrp can only be tested as root or via sudo.");
    }
    else {
      $this->markTestIncomplete("This test has not been implemented yet.");
    }
  }

  /**
   * @covers ::createDirectory
   */
  public function testCreateDirectory() {
    $dir = "{$this->d}/directory";
    FileSystem::createDirectory($dir);
    $this->assertTrue(is_dir($dir));
  }

  /**
   * @covers ::createDirectory
   */
  public function testCreateDirectoryParents() {
    $dir = $this->d . str_repeat("/directory", 10);
    FileSystem::createDirectory($dir, true);
    $this->assertTrue(is_dir($dir));
  }

  /**
   * @covers ::createDirectory
   */
  public function testCreateDirectoryExists() {
    $this->assertNull(FileSystem::createDirectory($this->d));
  }

  /**
   * @covers ::createSymbolicLink
   */
  public function testCreateSymbolicLink() {
    $link = "{$this->d}/phpunitlink";
    FileSystem::createSymbolicLink($this->f, $link);
    $this->assertTrue(is_link($link));
  }

  /**
   * @covers ::createSymbolicLink
   */
  public function testCreateSymbolicLinkForce() {
    $link = "{$this->d}/phpunitlink";
    touch($link);
    FileSystem::createSymbolicLink($this->f, $link, true);
    $this->assertTrue(is_link($link));
  }

  /**
   * @covers ::createSymbolicLink
   * @expectedException \ErrorException
   */
  public function testCreateSymbolicLinkExists() {
    $link = "{$this->d}/phpunitlink";
    touch($link);
    FileSystem::createSymbolicLink($this->f, $link);
  }

  /**
   * @covers ::delete
   */
  public function testDelete() {
    FileSystem::delete($this->f);
    $this->assertFalse(file_exists($this->f));
  }

  /**
   * @covers ::getContent
   * @todo Implement getContent
   */
  public function testGetContent() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::putJSON
   */
  public function testPutJSON() {
    $data = [ "phpunit" => "phpunit" ];
    FileSystem::putJSON($this->f, $data);
    $this->assertEquals($data, json_decode(file_get_contents($this->f), true));
  }

  /**
   * @covers ::getJSON
   * @depends testPutJSON
   */
  public function testGetJSON() {
    $data = [ "phpunit" => "phpunit" ];
    FileSystem::putJSON($this->f, $data);
    $this->assertEquals((object) $data, FileSystem::getJSON($this->f));
  }

  /**
   * @covers ::getRealPath
   */
  public function testGetRealPath() {
    $this->assertEquals(dirname(getcwd()), FileSystem::realpath("../"));
  }

  /**
   * @covers ::getRealPath
   */
  public function testGetRealPathInvalidExists() {
    // @todo How can we force realpath() to return FALSE for an existing file?
    $this->markTestSkipped("How can we force realpath() to return FALSE for an existing file?");
  }

  /**
   * @covers ::getRealPath
   */
  public function testGetRealPathDoesntExist() {
    $f = str_repeat("/phpunit", 10);
    $this->assertEquals($f, FileSystem::realpath($f));
  }

  /**
   * @covers ::putContent
   */
  public function testPutContent() {
    $data = "Lorem Ipsum Dolor";
    FileSystem::putContent($this->f, $data);
    $this->assertEquals($data, file_get_contents($this->f));
  }

  /**
   * @covers ::sanitizeFilename
   */
  public function testSanitizeFilename() {
    $this->assertEquals("phpunit", FileSystem::sanitizeFilename("PHPUnit"));
  }

  /**
   * @covers ::sanitizeFilename
   */
  public function testSanitizeFilenameSpecialCharacters() {
    $this->assertEmpty(FileSystem::sanitizeFilename("?[]/\\=<>:;,'\"&$#*()|-"));
  }

  /**
   * @covers ::sanitizeFilename
   */
  public function testSanitizeFilenameWhitespace() {
    $this->assertEquals("php-unit", FileSystem::sanitizeFilename(("php unit")));
  }

  /**
   * @covers ::sanitizeFilename
   */
  public function testSanitizeFilenameTrim() {
    $this->assertEquals("phpunit", FileSystem::sanitizeFilename("__..--phpunit--..__"));
  }

  /**
   * @covers ::withinDocumentRoot
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function testWithinDocumentRoot() {
    global $kernel;
    $f = "{$kernel->documentRoot}/phpunit-unittest-testfile-with-an-impossible-name-to-avoid-conflicts-with-existing-files";
    touch($f);
    $this->assertNull(FileSystem::withinDocumentRoot($f));
    unlink($f);
  }

  /**
   * @covers ::withinDocumentRoot
   */
  public function testWithinDocumentRootCLI() {
    $this->assertNull(FileSystem::withinDocumentRoot($this->f));
  }

  /**
   * @covers ::withinDocumentRoot
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function testWithinDocumentRootInside() {
    global $kernel;
    $f = "{$kernel->documentRoot}/phpunit-unittest-testfile-with-an-impossible-name-to-avoid-conflicts-with-existing-files";
    try {
      $kernel->fastCGI = true;
      touch($f);
      $this->assertNull(FileSystem::withinDocumentRoot($f));
    }
    finally {
      unlink($f);
      $kernel->fastCGI = false;
    }
  }

  /**
   * @covers ::withinDocumentRoot
   * @expectedException \LogicException
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function testWithinDocumentRootOutside() {
    global $kernel;
    try {
      $kernel->fastCGI = true;
      FileSystem::withinDocumentRoot($this->f);
    }
    finally {
      $kernel->fastCGI = false;
    }
  }


}
