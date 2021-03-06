<?php
/**
 * NumberHelperTest file
 *
 * PHP 5
 *
 * CakePHP(tm) Tests <http://book.cakephp.org/2.0/en/development/testing.html>
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/2.0/en/development/testing.html CakePHP(tm) Tests
 * @package       Cake.Test.Case.View.Helper
 * @since         CakePHP(tm) v 1.2.0.4206
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Cake\Test\TestCase\View\Helper;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\TestSuite\TestCase;
use Cake\View\Helper\NumberHelper;
use Cake\View\View;

/**
 * NumberHelperTestObject class
 */
class NumberHelperTestObject extends NumberHelper {

	public function attach(NumberMock $cakeNumber) {
		$this->_engine = $cakeNumber;
	}

	public function engine() {
		return $this->_engine;
	}

}

/**
 * NumberMock class
 */
class NumberMock {
}

/**
 * NumberHelperTest class
 *
 * @package       Cake.Test.Case.View.Helper
 */
class NumberHelperTest extends TestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->View = new View(null);

		$this->_appNamespace = Configure::read('App.namespace');
		Configure::write('App.namespace', 'TestApp');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		Configure::write('App.namespace', $this->_appNamespace);
		unset($this->View);
	}

/**
 * test CakeNumber class methods are called correctly
 */
	public function testNumberHelperProxyMethodCalls() {
		$methods = array(
			'precision', 'toReadableSize', 'toPercentage', 'format',
			'currency', 'addFormat',
			);
		$CakeNumber = $this->getMock(__NAMESPACE__ . '\NumberMock', $methods);
		$Number = new NumberHelperTestObject($this->View, array('engine' => __NAMESPACE__ . '\NumberMock'));
		$Number->attach($CakeNumber);
		foreach ($methods as $method) {
			$CakeNumber->expects($this->at(0))->method($method);
			$Number->{$method}('who', 'what', 'when', 'where', 'how');
		}
	}

/**
 * test engine override
 */
	public function testEngineOverride() {
		App::build(array(
			'Utility' => array(CAKE . 'Test/TestApp/Utility/')
		), App::REGISTER);
		$Number = new NumberHelperTestObject($this->View, array('engine' => 'TestAppEngine'));
		$this->assertInstanceOf('TestApp\Utility\TestAppEngine', $Number->engine());

		App::build(array(
			'Plugin' => array(CAKE . 'Test/TestApp/Plugin/')
		));
		Plugin::load('TestPlugin');
		$Number = new NumberHelperTestObject($this->View, array('engine' => 'TestPlugin.TestPluginEngine'));
		$this->assertInstanceOf('TestPlugin\Utility\TestPluginEngine', $Number->engine());
		Plugin::unload('TestPlugin');
	}

}
