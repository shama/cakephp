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

use Cake\Core\App;
use Cake\Meta\MetaProperty;
use Cake\Meta\MetaMethod;
use Cake\Utility\File;
use Cake\Utility\Folder;

/**
 * MetaClass
 *
 * @package       Cake.Meta
 */
class MetaClass {
	use CodeFormat\CakePHP;

	/**
	 * Data about the MetaClass
	 *
	 * @var array
	 */
	protected $_data = array(
		'name' => '',
		'path' => '',
		'namespace' => '',
		'uses' => array(),
		'traits' => array(),
		'docblock' => '',
		'final' => false,
		'static' => false,
		'interface' => false,
		'abstract' => false,
		'trait' => false,
		'extends' => false,
		'implements' => array(),
	);

	/**
	 * Properties this MetaClass has
	 *
	 * @var array
	 */
	protected $_properties = array();

	/**
	 * Methods this MetaClass has
	 *
	 * @var array
	 */
	protected $_methods = array();

	/**
	 * Identify what I am to the CodeFormat
	 *
	 * @var string
	 */
	protected $_iama = 'class';

	/**
	 * Instance of our ReflectionClass for our fake class
	 *
	 * @var ReflectionClass
	 */
	protected $_Reflection;

	/**
	 * Contents of our fake class
	 *
	 * @var string
	 */
	protected $_fakeFileContents;

	/**
	 * Instance of our fake class
	 *
	 * @var mixed
	 */
	protected $_FakeClass;

	/**
	 * Read an existing class
	 *
	 * @param string $class
	 * @param string $path
	 */
	public function __construct($class = null, $path = null) {
		if (!empty($class) && !empty($path)) {
			$this->read($class, $path);
		}
	}

	/**
	 * Create or add a MetaMethod
	 *
	 * @param string|MetaMethod $name Method name or MetaMethod object
	 * @param string $value Contents of the method
	 * @param array $options Additional parameters to set
	 */
	public function method($name, $value = null, $options = array()) {
		if (is_object($name)) {
			$this->_methods[$name->name] = $name;
		} else {
			$this->_methods[$name] = new MetaMethod($name, $value, $options);
		}
		return $this;
	}

	/**
	 * Create or add a MetaProperty
	 *
	 * @param string $name Property name
	 * @param string $value Value of the property
	 * @param array $options Additional parameters to set
	 */
	public function property($name, $value = null, $options = array()) {
		if (is_object($name)) {
			$this->_properties[$name->name] = $name;
		} else {
			$this->_properties[$name] = new MetaProperty($name, $value, $options);
		}
		return $this;
	}

	/**
	 * Read code and fill in the MetaClass
	 *
	 * @param string $class Class name to read
	 * @param string $path CakePHP path to class
	 *
	 * TODO: Refactor to not use App?
	 */
	public function read($class = null, $path = null) {
		if (!empty($class)) {
			$this->_data['name'] = $class;
		}
		if (!empty($path)) {
			$this->_data['path'] = $path;
		}

		// Find the class
		$paths = App::path($this->_data['path']);
		$filename = $this->_data['name'] . '.php';
		$path = false;
		foreach ($paths as $p) {
			if (file_exists($p . $filename)) {
				$path = $p;
				break;
			}
		}

		// Do nothing if class doesn't exist
		if (!$path) {
			return $this;
		}

		// Reflect a copy of the file to avoid fatal errors with duplicate class names
		$tmpdir = new Folder(TMP . 'bake', true);
		$realFile = new File($path . $filename);
		$fakeClass = uniqid($this->_data['name']);
		$fakeFile = $tmpdir->pwd() . DS . $fakeClass . '.php';
		$realFile->copy($fakeFile);
		$fakeFile = new File($fakeFile);
		$this->_fakeFileContents = preg_replace('/(class|trait) ' . $this->_data['name'] . '/i', '$1 ' . $fakeClass, $fakeFile->read());
		preg_match('/^namespace ([A-Za-z0-9_\\\\]+);$/im', $this->_fakeFileContents, $namespace);
		$this->_data['namespace'] = !empty($namespace[1]) ? $namespace[1] : '';
		$fakeFile->write($this->_fakeFileContents);

		include $fakeFile->pwd();

		// TODO: Figure some way to neuter the construct
		$fakeClassName = $this->_data['namespace'] . '\\' . $fakeClass;
		$this->_FakeClass = new $fakeClassName;
		$this->_Reflection = new \ReflectionClass($fakeClassName);
		$fakeFile->delete();

		return $this->_readClass();
	}

	/**
	 * Read the data of class, trait or interface
	 *
	 * @return $this
	 */
	protected function _readClass() {
		// TODO: Check for extra stuff on the top of the namespace, etc
		// and put somewhere

		$this->_data['final'] = $this->_Reflection->isFinal();
		if ($this->_Reflection->isAbstract() && !$this->_Reflection->isInterface()) {
			$this->_data['abstract'] = true;
		}

		$this->_data['interface'] = $this->_Reflection->isInterface();

		$parent = $this->_Reflection->getParentClass();
		if ($parent) {
			// TODO: This should only getShortName if the same as namespace
			$this->_data['extends'] = $parent->getShortName();
		}
		$interfaces = $this->_Reflection->getInterfaces();
		$count = count($interfaces);
		if ($count > 0) {
			foreach ($interfaces as $int) {
				$intName = $int->getName();

				// Ignore EventListener
				if (strpos($intName, 'Cake\Event\EventListener') !== false) {
					continue;
				}

				$this->_data['interfaces'][] = $intName;
			}
		}

		preg_match_all('/^use (.+);$/im', $this->_fakeFileContents, $out);
		$this->_data['uses'] = !empty($out[1]) ? $out[1] : array();

		$this->_data['docblock'] = $this->_Reflection->getDocComment();

		$this->_readProperties();
		$this->_readMethods();

		return $this;
	}

	/**
	 * Read the properties of class
	 *
	 * @return $this
	 */
	protected function _readProperties() {
		$this->_properties = array();
		foreach ($this->_Reflection->getProperties() as $property) {
			if ($property->getDeclaringClass()->name !== $this->_Reflection->name) {
				continue;
			}
			$name = $property->getName();
			$value = $this->_readPropertyValue($property);
			$options = array(
				'docblock' => $property->getDocComment(),
			);
			if ($property->isPrivate()) {
				$options['access'] = 'private';
			}
			if ($property->isProtected()) {
				$options['access'] = 'protected';
			}
			$options['static'] = $property->isStatic();
			$this->_properties[$name] = new MetaProperty($name, $value, $options);
		}
		return $this;
	}

	/**
	 * Read the value of a property
	 *
	 * @param ReflectionProperty $property
	 * @return $this
	 */
	protected function _readPropertyValue(\ReflectionProperty $property) {
		return $property->getValue($this->_FakeClass);
	}

	/**
	 * Read the methods of class
	 *
	 * @return $this
	 */
	protected function _readMethods() {
		$this->_methods = array();
		foreach ($this->_Reflection->getMethods() as $method) {
			if ($method->getDeclaringClass()->name !== $this->_Reflection->name) {
				continue;
			}
			$name = $method->getName();
			$value = $this->_readMethodContents($method);

			$options = array(
				'docblock' => $method->getDocComment(),
			);
			if ($method->isPrivate()) {
				$options['access'] = 'private';
			}
			if ($method->isProtected()) {
				$options['access'] = 'protected';
			}
			$options['static'] = $method->isStatic();
			$this->_methods[$name] = new MetaMethod($name, $value, $options);

			// Add parameters to method
			foreach ($method->getParameters() as $param) {
				$default = null;
				$type = null; // TODO: Get type
				if ($param->isDefaultValueAvailable()) {
					$default = $param->getDefaultValue();
					$hasDefault = true;
				}
				$this->_methods[$name]->parameter($param->name, $type, $default, $hasDefault);
			}
		}
		return $this;
	}

	/**
	 * Read only the contents of a method
	 *
	 * @param ReflectionMethod $method
	 * @return string Contents of the method
	 */
	protected function _readMethodContents(\ReflectionMethod $method) {
		$start = $method->getStartLine() - 1;
		$end = $method->getEndLine();
		$contents = explode("\n", $this->_fakeFileContents);
		$value = implode("\n", array_slice($contents, $start, $end - $start));
		$value = substr($value, strpos($value, "{\n") + 2);
		$value = substr($value, 0, strrpos($value, "}\n"));
		return $value;
	}

}