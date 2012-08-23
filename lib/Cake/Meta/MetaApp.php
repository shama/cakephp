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

use Cake\Utility\File;
use Cake\Utility\Folder;
use Cake\Utility\Hash;

/**
 * MetaApp
 *
 * @package       Cake.Meta
 */
class MetaApp {

	/**
	 * Path to the App
	 *
	 * @var string
	 */
	protected $_path = APP;

	/**
	 * Data about the MetaApp
	 *
	 * @var array
	 */
	protected $_data = [];

	/**
	 * Create a new MetaApp
	 *
	 * @param array $data
	 */
	public function __construct($data = []) {
		$this->_data = $data;
	}

	/**
	 * Merge MetaApps
	 *
	 * @return $this
	 */
	public function merge() {
		$args = func_get_args();
		foreach ($args as $arg) {
			$this->_data = Hash::mergeDiff($arg->data, $this->_data);
		}
		return $this;
	}

	/**
	 * Write an MetaApp
	 */
	public function write() {
		$write = function($data = null, $path = null) use (&$write) {
			foreach ($data as $name => $obj) {
				if (is_object($obj)) {
					$obj->write();
					continue;
				}
				if (is_array($obj)) {
					$subs = $obj;
					$obj = $name;
				}
				new Folder($path . $obj, true);
				if (!empty($subs)) {
					$write($subs, $path . $obj . DS);
				}
			}
		};
		$write($this->_data, $this->_path);
		return $this;
	}

	/**
	 * For setting properties of MetaApp
	 *
	 * @param string $name
	 * @param array $args
	 * @return $this
	 */
	public function __call($name, $args) {
		if ($name === 'path') {
			$this->_path = $args[0];
			if (substr($this->_path, -1, 1) !== DS) {
				$this->_path .= DS;
			}
		} else if (isset($this->{'_' . $name})) {
			$this->{'_' . $name} = $args[0];
		}
		return $this;
	}

	/**
	 * For returning properties of MetaApp
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name) {
		if (isset($this->{'_' . $name})) {
			return $this->{'_' . $name};
		}
		return false;
	}
}