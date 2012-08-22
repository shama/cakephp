<?php
/**
 * CakePHP : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc.
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc.
 * @link          http://cakephp.org CakePHP Project
 * @package       Cake.Test.Case.Event
 * @since         CakePHP v 3.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Cake\Test\TestCase\Meta;

use Cake\Core\App;
use Cake\Meta\MetaClass;
use Cake\TestSuite\TestCase;
use Cake\Utility\File;

/**
 * MetaClass Test
 */
class MetaClassTest extends TestCase {

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		App::build(array(
			'Controller' => array(CAKE . 'Test' . DS . 'TestApp' . DS . 'Controller' . DS)
		), App::RESET);
	}

	/**
	 * tearDown
	 */
	public function tearDown() {
		parent::tearDown();
		App::build();
	}

	/**
	 * testReadClass
	 */
	public function testReadClass() {
		$class = new MetaClass('SomePagesController', 'Controller');
		$this->assertEquals('SomePagesController', $class->name);
		$this->assertEquals('Controller', $class->extends);
		$this->assertEquals('TestApp\\Controller', $class->namespace);
		$this->assertEquals(array(
			'Cake\\Controller\\Controller',
			'Cake\\Network\\Response',
		), $class->uses);
		$this->assertEquals(false, $class->interface);
		$this->assertEquals(false, $class->static);
		$this->assertEquals(false, $class->trait);
		// TODO: Test docblocks
	}

	/**
	 * testReadMethods
	 */
	public function testReadMethods() {
		$class = new MetaClass('SomePagesController', 'Controller');
		$this->assertEquals('display', $class->methods['display']->name);
		$this->assertEquals('index', $class->methods['index']->name);
		$this->assertEquals('responseGenerator', $class->methods['responseGenerator']->name);
		//debug($class->methods);
	}

	/**
	 * Tests creating a class out of thin air
	 */
	public function testCreateClass() {
		$class = new MetaClass();
		$class
			->name('SomePagesController')
			->namespace('TestApp\\Controller')
			->extends('Controller')
			->uses(array(
				'Cake\\Controller\\Controller',
				'Cake\\Network\\Response',
			))
			->docblock(array(
				'SomePagesController class',
				'',
				'@package       Cake.Test.Case.Routing',
			))
			->createProperty('paginate', array('limit' => 25), array(
				'docblock' => 'Paginate',
			))
			->createMethod('display', "\t\t" . '$this->set("slug", $slug);', array(
				'parameters' => array(
					array('name' => 'slug', 'default' => 'testing')
				),
				'docblock' => 'display method'
			));
		debug((string) $class);
	}

	/**
	 * testWrite
	 */
	public function testWrite() {
		//$class = new MetaClass('PagesController', 'Controller');
		//debug($class);
	}

	/**
	 * testReturnCode
	 */
	public function testReturnCode() {
		//$class = new MetaClass('PagesController', 'Controller');
		//debug("\n" . (string) $class);
	}

	/**
	 * testMerge
	 */
	public function testMerge() {
		$class = new MetaClass('PagesController', 'Controller');
		$class2 = new MetaClass('PagesController', 'Controller');
		$class2->name('Test');
		$class = $class->merge($class2);
		$this->assertEquals('Test', $class->name);
		//debug((string) $class);
	}
}