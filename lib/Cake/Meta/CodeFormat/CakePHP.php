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
 * @package		  Cake.Meta.CodeFormat
 * @since		  CakePHP(tm) v 3.0
 * @license		  MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace Cake\Meta\CodeFormat;

use Cake\Utility\File;
use Cake\Utility\Hash;

/**
 * CakePHP Code Format
 * Does the actual merging and writing in the CakePHP format for the Meta classes
 *
 * @package       Cake.Meta.CodeFormat
 */
trait CakePHP {

	/**
	 * Options for code formatting
	 *
	 * @var array
	 */
	protected $_codeFormatOptions = array(
		'indent' => "\t",
		'newline' => "\n",
	);

	/**
	 * Merge other Meta objects with this one
	 *
	 * @return $this
	 */
	public function merge() {
		$args = func_get_args();
		foreach ($args as $merge) {
			// TODO: Check if a Meta[Type]
			$this->_data = Hash::merge($this->_data, $merge->data);
			// TODO: This could be DRYer
			if ($this->_iama === 'class') {
				foreach ($merge->methods as $key => $method) {
					if (array_key_exists($key, $this->_methods)) {
						// Merge method into found this methods
						$this->_methods[$key]->merge($method);
					} else {
						// Add new method to this
						$this->_methods[$key] = $method;
					}
				}
				foreach ($merge->properties as $key => $property) {
					if (array_key_exists($key, $this->_properties)) {
						// Merge property into found this properties
						$this->_properties[$key]->merge($property);
					} else {
						// Add new property to this
						$this->_property[$key] = $property;
					}
				}
			} else if ($this->_iama === 'method') {
				foreach ($merge->parameters as $key => $parameter) {
					if (array_key_exists($key, $this->_parameters)) {
						// Merge parameters into found this paramters
						$this->_parameters[$key]->merge($parameter);
					} else {
						// Add new parameter to this
						$this->_parameters[$key] = $parameter;
					}
				}
			}
		}
		
		return $this;
	}

	/**
	 * Write code for the given _iama
	 *
	 * @return $this
	 */
	public function write() {
		$code = $this->{'_write' . ucfirst($this->_iama)}();
		// TODO: Write the file
		return $this;
	}

	/**
	 * Write a class
	 *
	 * @return string
	 */
	protected function _writeClass() {
		$i = $this->indent;
		$nl = $this->newline;
		$out = '';

		if (!empty($this->_data['namespace'])) {
			$out .= 'namespace ' . $this->_data['namespace'] . ';' . $nl . $nl;
		}

		if (!empty($this->_data['uses'])) {
			$uses = array_unique($this->_data['uses']);
			foreach ($uses as $use) {
				$out .= 'use ' . $use . ';' . $nl;
			}
			$out .= $nl;
		}

		$out .= $this->_docblock();

		if ($this->_data['trait']) {
			$out .= 'trait ';
		} else {
			$out .= 'class ';
		}
		$out .= $this->_data['name'];
		if ($this->_data['extends']) {
			$out .= ' extends ' . $this->_data['extends'];
		}
		$out .= ' {' . $nl . $nl;

		foreach ($this->_properties as $property) {
			$out .= $property->write() . $nl;
		}

		foreach ($this->_methods as $method) {
			$out .= $method->write() . $nl;
		}

		return $out . '}';
	}

	/**
	 * Write a property
	 *
	 * @return string
	 */
	protected function _writeProperty() {
		$nl = $this->newline;
		$i = $this->indent;

		$out = $this->_docblock();
		$out .= $i . $this->_data['access'] . ' $' . $this->_data['name'] . ' = ';
		$out .= str_replace($nl, $nl . $i, str_replace('  ', $i, var_export($this->_data['value'], true)));
		$out .= ';' . $nl;

		return $out;
	}

	/**
	 * Write a method
	 *
	 * @return string
	 */
	protected function _writeMethod() {
		$nl = $this->newline;
		$i = $this->indent;

		$out = $this->_docblock();

		$out .= $i . $this->_data['access'] . ' function ' . $this->_data['name'] . '(';
		$params = array();
		foreach ($this->parameters as $parameter) {
			$params[] = $parameter->write();
		}
		$out .= implode(', ', $params);
		$out .= ') {' . $nl;
		$out .= $this->_data['value'] . $nl;
		$out .= $i . '}' . $nl;

		return $out;
	}

	/**
	 * Write a parameter
	 *
	 * @return string
	 */
	protected function _writeParameter() {
		$out = '';
		$scalar = array('integer', 'float', 'string', 'boolean');
		if (!empty($this->_data['type']) && !in_array(strtolower($this->_data['type']), $scalar)) {
			$out .= $this->type . ' ';
		}
		$out .= '$' . $this->_data['name'];
		if ($this->_data['hasDefault']) {
			$out .= ' = ' . var_export($this->_data['default'], true);
		}

		return $out;
	}

	/**
	 * Return a docblock
	 *
	 * @return string
	 */
	protected function _docblock() {
		if (!empty($this->_data['docblock'])) {
			$docblock = $this->_data['docblock'];
			if (!is_array($docblock)) {
				$docblock = explode($this->newline, $docblock);
			}
			if (strpos($docblock[0], '/*') === false) {
				array_walk($docblock, function(&$line) {
					$line = ' * ' . $line;
				});
				array_unshift($docblock, '/**');
				$docblock[] = ' */';
			}

			return implode($this->newline, $docblock) . $this->newline;
		}

		return '';
	}

	/**
	 * For setting properties of Meta classes
	 *
	 * @param string $name
	 * @param array $args
	 * @return $this
	 */
	public function __call($name, $args) {
		if ($name === 'data') {
			$this->_data = Hash::merge($this->_data, $args[0]);
		}
		if ($name === 'methods' && isset($this->_methods)) {
			$this->_methods = array_merge($this->_methods, $args[0]);
		}
		if ($name === 'properties' && isset($this->_properties)) {
			$this->_properties = array_merge($this->_properties, $args[0]);
		}
		if ($name === 'parameters' && isset($this->_parameters)) {
			$this->_parameters = array_merge($this->_parameters, $args[0]);
		}
		if (isset($this->_data[$name])) {
			if (isset($args[0])) {
				$this->_data[$name] = $args[0];
			}
		}
		if (isset($this->_codeFormatOptions[$name])) {
			$this->_codeFormatOptions[$name] = $args[0];
		}
		return $this;
	}

	/**
	 * For returning properties of Meta classes
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name) {
		if ($name === 'data') {
			return $this->_data;
		}
		if ($name === 'methods' && isset($this->_methods)) {
			return $this->_methods;
		}
		if ($name === 'properties' && isset($this->_properties)) {
			return $this->_properties;
		}
		if ($name === 'parameters' && isset($this->_parameters)) {
			return $this->_parameters;
		}
		if (isset($this->_data[$name])) {
			return $this->_data[$name];
		}
		if (isset($this->_codeFormatOptions[$name])) {
			return $this->_codeFormatOptions[$name];
		}
		return false;
	}

	/**
	 * For returning code instead of writing
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->{'_write' . ucfirst($this->_iama)}();
	}
}