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

use Cake\Meta\MetaParameter;
use Cake\TestSuite\TestCase;

/**
 * MetaParameter Test
 */
class MetaParameterTest extends TestCase {

	/**
	 * testCreate
	 */
	public function testCreate() {
		$parameter = new MetaParameter('testing');
		$this->assertEquals('$testing', (string) $parameter);

		$parameter = new MetaParameter('id', 2);
		$this->assertEquals('$id = 2', (string) $parameter);
	}
}