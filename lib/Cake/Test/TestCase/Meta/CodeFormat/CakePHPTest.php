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

namespace Cake\Test\TestCase\Meta\CodeFormat;

use Cake\Meta\CodeFormat\CakePHP;
use Cake\Meta\MetaClass;
use Cake\TestSuite\TestCase;

/**
 * CakePHP Test
 */
class CakePHPTest extends TestCase {

	/**
	 * testRead
	 */
	public function testRead() {
		$controller = new MetaClass('PagesController', 'Controller');
	}

	/**
	 * testWrite
	 */
	public function testWrite() {
		//$controller = new MetaClass('PagesController', 'Controller');
	}
}