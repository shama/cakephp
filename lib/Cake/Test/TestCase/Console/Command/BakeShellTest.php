<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Test.Case.Console.Command
 * @since         CakePHP(tm) v 1.3
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Cake\Test\TestCase\Console\Command;

use Cake\Console\Command\BakeShellShell;
use Cake\Controller\Controller;
use Cake\Core\App;
use Cake\Core\Configure;
use Cake\TestSuite\TestCase;

class BakeShellTest extends TestCase {

/**
 * fixtures
 *
 * @var array
 */
	public $fixtures = array('core.comment');

/**
 * setup test
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$out = $this->getMock('Cake\Console\ConsoleOutput', [], [], '', false);
		$in = $this->getMock('Cake\Console\ConsoleInput', [], [], '', false);

		$this->Shell = $this->getMock(
			'Cake\Console\Command\BakeShell',
			['in', 'out', 'hr', 'err', 'createFile', '_stop', '_checkUnitTest'],
			[$out, $out, $in]
		);
		Configure::write('App.namespace', 'TestApp');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Dispatch, $this->Shell);
	}

/**
 * test bake all
 *
 * @return void
 */
	public function testAllWithModelName() {
		$this->Shell->Model = $this->getMock(
			'Cake\Console\Command\Task\ModelTask',
			[],
			[&$this->Dispatcher]
		);
		$this->Shell->Controller = $this->getMock(
			'Cake\Console\Command\Task\ControllerTask',
			[],
			[&$this->Dispatcher]
		);
		$this->Shell->View = $this->getMock(
			'Cake\Console\Command\Task\ModelTask',
			[],
			[&$this->Dispatcher]
		);
		$this->Shell->DbConfig = $this->getMock(
			'Cake\Console\Command\Task\DbConfigTask',
			[],
			[&$this->Dispatcher]
		);

		$this->Shell->DbConfig->expects($this->once())
			->method('getConfig')
			->will($this->returnValue('test'));

		$this->Shell->Model->expects($this->never())
			->method('getName');

		$this->Shell->Model->expects($this->once())
			->method('bake')
			->will($this->returnValue(true));

		$this->Shell->Controller->expects($this->once())
			->method('bake')
			->will($this->returnValue(true));

		$this->Shell->View->expects($this->once())
			->method('execute');

		$this->Shell->expects($this->once())
			->method('_stop');

		$this->Shell->expects($this->at(0))
			->method('out')
			->with('Bake All');

		$this->Shell->expects($this->at(4))
			->method('out')
			->with('<success>Bake All complete</success>');

		$this->Shell->connection = '';
		$this->Shell->params = array();
		$this->Shell->args = array('Comment');
		$this->Shell->all();

		$this->assertEquals('Comment', $this->Shell->View->args[0]);
	}
}
