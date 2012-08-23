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
 * MetaProperty
 *
 * @package       Cake.Meta
 */
class MetaProperty {
	use CodeFormat\CakePHP;

	/**
	 * Data about the MetaParameter
	 *
	 * @var array
	 */
	protected $_data = [
		'name' => null,
		'type' => null,
		'value' => null,
		'docblock' => '',
		'access' => 'public',
	];

	/**
	 * Identify what I am
	 *
	 * @var string
	 */
	protected $_iama = 'property';

	/**
	 * Create a MetaProperty
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param array $options
	 */
	public function __construct($name, $value = null, $options = []) {
		$this->_data['name'] = $name;
		$this->_data['value'] = $value;
		foreach ($options as $key => $val) {
			$this->_data[$key] = $val;
		}
	}

}