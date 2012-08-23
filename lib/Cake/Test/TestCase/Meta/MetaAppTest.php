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
use Cake\Meta\MetaApp;
use Cake\Meta\MetaClass;
use Cake\TestSuite\TestCase;
use Cake\Utility\Folder;

/**
 * MetaMethod Test
 */
class MetaAppTest extends TestCase {

	/**
	 * setUp
	 */
	public function setUp() {
		parent::setUp();
		$appDir = TMP . 'TestApp' . DS;
		$this->tmpApp = new Folder($appDir, true, 0777);
		App::build([
			'Controller' => [$appDir . 'Controller' . DS],
			'Model' => [$appDir . 'Model' . DS],
		], App::RESET);
	}

	/**
	 * tearDown
	 */
	public function tearDown() {
		parent::tearDown();
		App::build();
		$this->tmpApp->delete();
	}

	/**
	 * testCreate
	 */
	public function testCreate() {
		$app = new MetaApp([
			'Config' => [
				'Schema' => [],
			],
			'Controller' => [
				'Component' => [],
				new MetaClass('PagesController', 'Controller'),
			],
			'Model' => [
				'Behavior' => [],
				new MetaClass('Page', 'Model'),
			],
			'View' => [
				'Helper' => [],
			],
			'webroot' => [],
		]);
		$app
			->path($this->tmpApp->pwd())
			->write();

		$expected = [
			['Config', 'Controller', 'Model', 'View', 'webroot'],
			[]
		];
		$this->assertEquals($expected, $this->tmpApp->read(true));
	}

	/**
	 * testMerge
	 */
	public function testMerge() {
		$app1 = new MetaApp([
			'Controller' => [
				'Component',
				new MetaClass('PagesController', 'Controller'),
			],
		]);
		$app2 = new MetaApp([
			'Controller' => [
				'Component',
				new MetaClass('PostsController', 'Controller'),
			],
			'Model' => [
				'Behavior',
				new MetaClass('Page', 'Model'),
			],
		]);
		$app1
			->path($this->tmpApp->pwd())
			->merge($app2)
			->write();
		$expected = [
			['Controller', 'Model'],
			[]
		];
		$this->assertEquals($expected, $this->tmpApp->read(true));
	}
}