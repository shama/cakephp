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

use Cake\Meta\MetaParameter;

/**
 * MetaMethod
 *
 * @package       Cake.Meta
 */
class MetaMethod {
	use CodeFormat\CakePHP;

	/**
	 * Data about the MetaMethod
	 *
	 * @var array
	 */
	protected $_data = array(
		'name' => null,
		'value' => null,
		'docblock' => null,
		'static' => false,
		'access' => 'public',
	);

	/**
	 * Parameters this MetaMethod has
	 *
	 * @var array
	 */
	protected $_parameters = array();

	/**
	 * Identify what I am
	 *
	 * @var string
	 */
	protected $_iama = 'method';

	/**
	 * Create a MetaMethod
	 */
	public function __construct($name, $value = '', $options = array()) {
		$this->_data['name'] = $name;
		$this->_data['value'] = $value;
		if (!empty($options['parameters'])) {
			foreach ($options['parameters'] as $param) {
				$param = array_merge(array(
					'name' => null,
					'type' => null,
					'default' => null,
					'hasDefault' => false,
				), $param);
				$this->createParameter($param['name'], $param['type'], $param['default']);
			}
			unset($options['parameters']);
		}
		foreach ($options as $key => $val) {
			$this->_data[$key] = $val;
		}
	}

	/**
	 * Create a parameter
	 *
	 * @param string $name
	 * @param string $type
	 * @param mixed $default
	 * @param boolean $hasDefault
	 */
	public function createParameter($name, $type = null, $default = null, $hasDefault = false) {
		$this->addParameter(new MetaParameter($name, $type, $default, $hasDefault));
		return $this;
	}
}