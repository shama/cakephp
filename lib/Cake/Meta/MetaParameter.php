<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright	  Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link		  http://cakephp.org CakePHP(tm) Project
 * @package		  Cake.Meta
 * @since		  CakePHP(tm) v 3.0
 * @license		  MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Cake\Meta;

/**
 * MetaParameter
 *
 * @package       Cake.Meta
 */
class MetaParameter {
	use CodeFormat\CakePHP;

	/**
	 * Data about the MetaParameter
	 *
	 * @var array
	 */
	protected $_data = [
		'name' => null,
		'type' => null,
		'hasDefault' => false,
		'default' => null,
	];

	/**
	 * Identify what I am
	 *
	 * @var string
	 */
	protected $_iama = 'parameter';

	/**
	 * Create a new MetaParameter
	 *
	 * @param string $name
	 * @param string $type
	 * @param mixed $default
	 * @param boolean $hasDefault Set to true if the default is null
	 */
	public function __construct($name, $type = null, $default = null, $hasDefault = false) {
		$this->_data['name'] = $name;
		if (isset($type)) {
			$this->_data['type'] = $type;
		}
		if (isset($default)) {
			$hasDefault = true;
		}
		$this->_data['hasDefault'] = $hasDefault;
		if ($hasDefault) {
			$this->_data['default'] = $default;
		}
	}
}