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
use Cake\Meta\MetaMethod;
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
		App::build([
			'Controller' => [CAKE . 'Test' . DS . 'TestApp' . DS . 'Controller' . DS],
		], App::RESET);
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
		$this->assertEquals([
			'Cake\\Controller\\Controller',
			'Cake\\Network\\Response',
		], $class->uses);
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
		$adminEdit = new MetaMethod('admin_edit');
		$class
			->name('SomePagesController')
			->namespace('TestApp\\Controller')
			->extends('Controller')
			->uses([
				'Cake\\Controller\\Controller',
				'Cake\\Network\\Response',
			])
			->docblock([
				'SomePagesController class',
				'',
				'@package       Cake.Test.Case.Routing',
			])
			->property('paginate', ['limit' => 25], [
				'docblock' => 'Paginate',
			])
			->method('display', "\t\t" . '$this->set("slug", $slug);', [
				'parameters' => [
					['name' => 'slug', 'default' => 'testing'],
				],
				'docblock' => 'display method'
			])
			->method($adminEdit);
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

		$class2 = new MetaClass('DontOverwrite', 'Controller');
		$class2->method('index');

		$class = $class->merge($class2);
		$this->assertEquals('PagesController', $class->name);
		$this->assertEquals('index', $class->methods['index']->name);
	}
}