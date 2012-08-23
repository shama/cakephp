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

use Cake\Meta\MetaProperty;
use Cake\TestSuite\TestCase;

/**
 * MetaProperty Test
 */
class MetaPropertyTest extends TestCase {

	/**
	 * testCreate
	 */
	public function testCreate() {
		$property = new MetaProperty('paginate', [
			'limit' => 25,
			'order' => ['Page.sort' => 'asc'],
		]);
		$expected = [
			"\t" . 'public $paginate = array(',
			"\t\t" . "'limit' => 25,",
			"\t\t" . "'order' => array(",
			"\t\t\t" . "'Page.sort' => 'asc',",
			"\t\t" . "),",
			"\t" . ');',
			'',
		];
		debug("\n" . (string) $property);
		//$this->assertEquals(implode("\n", $expected), (string) $property);
	}
}