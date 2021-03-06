<?php
/**
 * ClassLoader class
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2011, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Core
 * @since         CakePHP(tm) v 3.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Cake\Core;

/**
 * ClassLoader
 *
 * @package       Cake.Core
 */
class ClassLoader {

/**
 * File extension
 *
 * @var string
 */
	protected $_fileExtension = '.php';

/**
 * Registered namespace
 *
 * @var string
 */
	protected $_namespace;

/**
 * Store the namespace length for performance
 *
 * @var integer
 */
	protected $_namespaceLength;

/**
 * Path with the classes
 *
 * @var string
 */
	protected $_includePath;

/**
 * Constructor
 *
 * @param string $ns The _namespace to use.
 */
	public function __construct($ns = null, $includePath = null) {
		$this->_namespace = rtrim($ns, '\\') . '\\';
		$this->_namespaceLength = strlen($this->_namespace);
		$this->_includePath = $includePath;
	}

/**
 * Sets the base include path for all class files in the _namespace of this class loader.
 *
 * @param string $includePath
 * @return void
 */
	public function setIncludePath($includePath) {
		$this->_includePath = $includePath;
	}

/**
 * Gets the base include path for all class files in the _namespace of this class loader.
 *
 * @return string
 */
	public function getIncludePath() {
		return $this->_includePath;
	}

/**
 * Sets the file extension of class files in the _namespace of this class loader.
 *
 * @param string $fileExtension
 * @return void
 */
	public function setFileExtension($fileExtension) {
		$this->_fileExtension = $fileExtension;
	}

/**
 * Gets the file extension of class files in the _namespace of this class loader.
 *
 * @return string
 */
	public function getFileExtension() {
		return $this->_fileExtension;
	}

/**
 * Installs this class loader on the SPL autoload stack.
 *
 * @return void
 */
	public function register() {
		spl_autoload_register(array($this, 'loadClass'));
	}

/**
 * Uninstalls this class loader from the SPL autoloader stack.
 *
 * @return void
 */
	public function unregister() {
		spl_autoload_unregister(array($this, 'loadClass'));
	}

/**
 * Loads the given class or interface.
 *
 * @param string $className The name of the class to load.
 * @return boolean
 */
	public function loadClass($className) {
		if (substr($className, 0, $this->_namespaceLength) !== $this->_namespace) {
			return false;
		}
		$path = $this->_includePath . DS . str_replace('\\', DS, $className) . $this->_fileExtension;
		if (!file_exists($path)) {
			return false;
		}
		return require $path;
	}
}
