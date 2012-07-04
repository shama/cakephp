<?php
/**
 * ModelTest file
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Test.Case
 * @since         CakePHP(tm) v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Cake\Test\TestCase\Model;
use Cake\TestSuite\TestSuite;

/**
 * ModelTest class
 *
 * This test group will run model class tests
 *
 * @package       Cake.Test.Case
 */
class ModelTest extends \PHPUnit_Framework_TestSuite {

/**
 * suite method, defines tests for this suite.
 *
 * @return void
 */
	public static function suite() {
		$suite = new TestSuite('All Model related class tests');

		$suite->addTestFile(CORE_TEST_CASES . DS . 'Model' . DS . 'Validator' . DS . 'ValidationSetTest.php');
		$suite->addTestFile(CORE_TEST_CASES . DS . 'Model' . DS . 'Validator' . DS . 'ValidationRuleTest.php');
		$suite->addTestFile(CORE_TEST_CASES . DS . 'Model' . DS . 'ModelReadTest.php');
		$suite->addTestFile(CORE_TEST_CASES . DS . 'Model' . DS . 'ModelWriteTest.php');
		$suite->addTestFile(CORE_TEST_CASES . DS . 'Model' . DS . 'ModelDeleteTest.php');
		$suite->addTestFile(CORE_TEST_CASES . DS . 'Model' . DS . 'ModelValidationTest.php');
		$suite->addTestFile(CORE_TEST_CASES . DS . 'Model' . DS . 'ModelIntegrationTest.php');
		$suite->addTestFile(CORE_TEST_CASES . DS . 'Model' . DS . 'ModelCrossSchemaHabtmTest.php');
		return $suite;
	}
}
