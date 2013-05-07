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
namespace MovLib\Test\Model;

use \MovLib\Model\ModelInterface;
use \PHPUnit_Framework_TestCase;

/**
 * Test class for testing the utility functions of the abstract base class of all models.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class AbstractModelTest extends PHPUnit_Framework_TestCase implements ModelInterface {

  /** Fully qualified name of the class we want to test. */
  const FQ_CLASS_NAME = 'MovLib\Model\AbstractModel';

  /** Fully qualified name of the database exception class. */
  const FQ_DB_EXCEPTION = 'MovLib\Exception\DatabaseException';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    // English is the base language for all tests.
    $_SERVER['LANGUAGE_CODE'] = 'en';
  }

  // ------------------------------------------------------------------------------------------------------------------- DatabaseException Tests


  /**
   * Test wrong database and table names raise the DatabaseException.
   */
  public function testGetConnectionWrongDatabaseName() {
    /* @var $getConnection \ReflectionFunction */
    $getConnection = getReflectionFunction(AbstractModelTest::FQ_CLASS_NAME, 'getConnection');
    /* @var $mock \PHPUnit_Framework_MockObject_Generator */
    $mock = $this->getMockForAbstractClass(AbstractModelTest::FQ_CLASS_NAME, [ 'table' ]);
    $this->setExpectedException(
      AbstractModelTest::FQ_DB_EXCEPTION,
      'The desired socket (' . self::SOCKET_PATH . 'database_table' . self::SOCKET_NAME . ') does not exist!'
    );
    $getConnection->invokeArgs($mock, [ 'database', 'table' ]);
  }

  /**
   * Test if a wrong query raises DatabaseException.
   */
  public function testPrepareWrongQuery() {
    /* @var $prepare \ReflectionFunction */
    $prepare = getReflectionFunction(AbstractModelTest::FQ_CLASS_NAME, 'prepare');
    /* @var $mock \PHPUnit_Framework_MockObject_Generator */
    $mock = $this->getMockForAbstractClass(AbstractModelTest::FQ_CLASS_NAME, [ 'movies' ]);
    $this->setExpectedException(AbstractModelTest::FQ_DB_EXCEPTION, 'Preparation of statement failed.');
    $prepare->invokeArgs($mock, [ 'SELECT * FROM table' ]);
  }

  /**
   * Test if a wrong parameter count raises DatabaseException.
   */
  public function testPrepareAndBindWrongParameterCount() {
    /* @var $prepareAndBind \ReflectionFunction */
    $prepareAndBind = getReflectionFunction(AbstractModelTest::FQ_CLASS_NAME, 'prepareAndBind');
    /* @var $mock \PHPUnit_Framework_MockObject_Generator */
    $mock = $this->getMockForAbstractClass(AbstractModelTest::FQ_CLASS_NAME, [ 'movies' ]);
    $this->setExpectedException(AbstractModelTest::FQ_DB_EXCEPTION, 'Wrong parameter count, expected 1 but received 0.');
    $prepareAndBind->invokeArgs($mock, [ 'SELECT * FROM {table} WHERE id = ?', 'i', [] ]);
  }

  /**
   * Test if a wrong query raises DatabaseException.
   */
  public function testPrepareAndBindWrongQuery() {
    /* @var $prepareAndBind \ReflectionFunction */
    $prepareAndBind = getReflectionFunction(AbstractModelTest::FQ_CLASS_NAME, 'prepareAndBind');
    /* @var $mock \PHPUnit_Framework_MockObject_Generator */
    $mock = $this->getMockForAbstractClass(AbstractModelTest::FQ_CLASS_NAME, [ 'movies' ]);
    $this->setExpectedException(AbstractModelTest::FQ_DB_EXCEPTION, 'Preparation of statement failed.');
    $prepareAndBind->invokeArgs($mock, [ 'SELECT * FROM table', 'ss', [ 'value1', 'value2' ] ]);
  }

  /**
   * Test if execution raises the DatabaseException if no prepared statement exists.
   */
  public function testExecuteOnNonObject() {
    /* @var $execute \ReflectionFunction */
    $execute = getReflectionFunction(AbstractModelTest::FQ_CLASS_NAME, 'execute');
    /* @var $mock \PHPUnit_Framework_MockObject_Generator */
    $mock = $this->getMockForAbstractClass(AbstractModelTest::FQ_CLASS_NAME, [ 'table' ]);
    $this->setExpectedException(AbstractModelTest::FQ_DB_EXCEPTION, 'No valid prepared statement instance.');
    $execute->invoke($mock);
  }

  /**
   * Test if closing a non existent prepared statement will not raise a DatabaseException.
   */
  public function testClosePreparedStatementOnNonObject() {
    /* @var $closePreparedStatement \ReflectionFunction */
    $close = getReflectionFunction(AbstractModelTest::FQ_CLASS_NAME, 'close');
    /* @var $mock \PHPUnit_Framework_MockObject_Generator */
    $mock = $this->getMockForAbstractClass(AbstractModelTest::FQ_CLASS_NAME, [ 'table' ]);
    $this->assertSame($mock, $close->invoke($mock));
  }

}
