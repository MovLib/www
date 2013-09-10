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

namespace MovLib\Test\Utility;

use \MovLib\Utility\FileSystem;

/**
 * Description of FileSystemTest
 *
 * @author Markus
 */
class FileSystemTest extends \PHPUnit_Framework_TestCase {
  
  /**
   * Path containing all the files created by this test class.
   * 
   * @var string
   */
  const TEST_DIRECTORY = "/tmp/FileSystemTest";
  
  public static function setUpBeforeClass() {
//    mkdir(self::TEST_DIRECTORY);
  }
  
  public static function tearDownAfterClass() {
    exec("rm -rf " . self::TEST_DIRECTORY);
  }
  
  public function unlinkRecursiveProvider() {
    $emptyDir = self::TEST_DIRECTORY . "/empty";
    $this->assertTrue(mkdir($emptyDir, 0777, true));
    $fullDir = self::TEST_DIRECTORY . "/full";
    $this->assertTrue(mkdir($fullDir, 0777, true));
    $symlinkDir = self::TEST_DIRECTORY . "/symlinkDir";
    $this->assertTrue(symlink($fullDir, $symlinkDir));
    
    $file = self::TEST_DIRECTORY . "/file";
    $this->assertTrue(touch($file));
    $symlink = self::TEST_DIRECTORY . "/symlink";
    $this->assertTrue(symlink($file, $symlink));
    
    $inaccessible = self::TEST_DIRECTORY . "/inaccessible";
    $this->assertTrue(touch($inaccessible));
    chown($inaccessible, "root");
    chmod($inaccessible, 000);
    return [
      [ self::TEST_DIRECTORY . "/not-existing", true ],
      [ $file, true ],
      [ $symlink, true ],
      [ $emptyDir, true ],
      [ $fullDir, true ],
//      [ $inaccessible, false ],
    ];
  }
  
  /**
   * Test of the <code>FileSystem::unlinkRecursive()</code> method.
   * 
   * @dataProvider unlinkRecursiveProvider
   * @param string $path
   *   The path to delete.
   * @param type $expected
   *   The expected return value from <code>FileSystem::unlinkRecursive()</code>
   */
  public function testUnlinkRecursive($path, $expected) {
    $this->assertEquals($expected, FileSystem::unlinkRecursive($path));
    $this->assertFalse(file_exists($path));
  }
}
