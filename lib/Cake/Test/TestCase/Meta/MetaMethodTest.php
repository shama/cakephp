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

use Cake\Meta\MetaMethod;
use Cake\TestSuite\TestCase;

/**
 * MetaMethod Test
 */
class MetaMethodTest extends TestCase {

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
	 * testCreate
	 */
	public function testCreate() {
		$method = new MetaMethod(
			'index',
			"\t\t" . '$this->set("data", $this->paginate());',
			array('parameters' => array(
				array('name' => 'id'),
			))
		);
		$expected = array(
			"\t" . 'public function index($id) {',
			"\t\t" . '$this->set("data", $this->paginate());',
			"\t" . '}',
			'',
		);
		$this->assertEquals(implode("\n", $expected), (string) $method);
	}
}