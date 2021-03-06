<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 1.2.0.5012
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Cake\Console\Command;

use Cake\Console\Shell;
use Cake\Core\App;
use Cake\Utility\Inflector;

/**
 * API shell to show method signatures of CakePHP core classes.
 *
 * Implementation of a Cake Shell to show CakePHP core method signatures.
 *
 * @package       Cake.Console.Command
 */
class ApiShell extends Shell {

/**
 * Map between short name for paths and real paths.
 *
 * @var array
 */
	public $paths = array();

/**
 * Override initialize of the Shell
 *
 * @return void
 */
	public function initialize() {
		$this->paths = array_merge($this->paths, array(
			'behavior' => CAKE . 'Model/Behavior/',
			'cache' => CAKE . 'Cache/',
			'controller' => CAKE . 'Controller/',
			'component' => CAKE . 'Controller/Component/',
			'helper' => CAKE . 'View/Helper/',
			'model' => CAKE . 'Model/',
			'view' => CAKE . 'View/',
			'core' => CAKE
		));
	}

/**
 * Override main() to handle action
 *
 * @return void
 */
	public function main() {
		if (empty($this->args)) {
			return $this->out($this->OptionParser->help());
		}

		$type = strtolower($this->args[0]);

		if (isset($this->paths[$type])) {
			$path = $this->paths[$type];
		} else {
			$path = $this->paths['core'];
		}

		$count = count($this->args);
		if ($count > 1) {
			$file = Inflector::underscore($this->args[1]);
			$class = Inflector::camelize($this->args[1]);
		} elseif ($count) {
			$file = $type;
			$class = Inflector::camelize($type);
		}
		$path = $path . Inflector::camelize($type);
		$file = $path . '.php';
		$classPath = str_replace(CORE_PATH, '', $path);
		$className = str_replace(DS, '\\', $classPath);

		if (!class_exists($className)) {
			return $this->error(__d('cake_console', '%s not found', $class));
		}

		$parsed = $this->_parseClass($className);

		if (!empty($parsed)) {
			if (isset($this->params['method'])) {
				if (!isset($parsed[$this->params['method']])) {
					$this->err(__d('cake_console', '%s::%s() could not be found', $class, $this->params['method']));
					$this->_stop();
				}
				$method = $parsed[$this->params['method']];
				$this->out($class . '::' . $method['method'] . $method['parameters']);
				$this->hr();
				$this->out($method['comment'], true);
			} else {
				$this->out(ucwords($class));
				$this->hr();
				$i = 0;
				foreach ($parsed as $method) {
					$list[] = ++$i . ". " . $method['method'] . $method['parameters'];
				}
				$this->out($list);

				$methods = array_keys($parsed);
				while ($number = strtolower($this->in(__d('cake_console', 'Select a number to see the more information about a specific method. q to quit. l to list.'), null, 'q'))) {
					if ($number === 'q') {
						$this->out(__d('cake_console', 'Done'));
						return $this->_stop();
					}

					if ($number === 'l') {
						$this->out($list);
					}

					if (isset($methods[--$number])) {
						$method = $parsed[$methods[$number]];
						$this->hr();
						$this->out($class . '::' . $method['method'] . $method['parameters']);
						$this->hr();
						$this->out($method['comment'], true);
					}
				}
			}
		}
	}

/**
 * Get and configure the optionparser.
 *
 * @return ConsoleOptionParser
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser->addArgument('type', array(
			'help' => __d('cake_console', 'Either a full path or type of class (model, behavior, controller, component, view, helper)')
		))->addArgument('className', array(
			'help' => __d('cake_console', 'A CakePHP core class name (e.g: Component, HtmlHelper).')
		))->addOption('method', array(
			'short' => 'm',
			'help' => __d('cake_console', 'The specific method you want help on.')
		))->description(__d('cake_console', 'Lookup doc block comments for classes in CakePHP.'));
		return $parser;
	}

/**
 * Show help for this shell.
 *
 * @return void
 */
	public function help() {
		$head = "Usage: cake api [<type>] <className> [-m <method>]\n";
		$head .= "-----------------------------------------------\n";
		$head .= "Parameters:\n\n";

		$commands = array(
			'path' => "\t<type>\n" .
				"\t\tEither a full path or type of class (model, behavior, controller, component, view, helper).\n" .
				"\t\tAvailable values:\n\n" .
				"\t\tbehavior\tLook for class in CakePHP behavior path\n" .
				"\t\tcache\tLook for class in CakePHP cache path\n" .
				"\t\tcontroller\tLook for class in CakePHP controller path\n" .
				"\t\tcomponent\tLook for class in CakePHP component path\n" .
				"\t\thelper\tLook for class in CakePHP helper path\n" .
				"\t\tmodel\tLook for class in CakePHP model path\n" .
				"\t\tview\tLook for class in CakePHP view path\n",
			'className' => "\t<className>\n" .
				"\t\tA CakePHP core class name (e.g: Component, HtmlHelper).\n"
		);

		$this->out($head);
		if (!isset($this->args[1])) {
			foreach ($commands as $cmd) {
				$this->out("{$cmd}\n\n");
			}
		} elseif (isset($commands[strtolower($this->args[1])])) {
			$this->out($commands[strtolower($this->args[1])] . "\n\n");
		} else {
			$this->out(__d('cake_console', 'Command %s not found', $this->args[1]));
		}
	}

/**
 * Parse a given class (located on given file) and get public methods and their
 * signatures.
 *
 * @param string $class Class name
 * @return array Methods and signatures indexed by method name
 */
	protected function _parseClass($class) {
		$parsed = array();

		$reflection = new \ReflectionClass($class);

		foreach ($reflection->getMethods() as $method) {
			if (!$method->isPublic()) {
				continue;
			}
			if ($method->getDeclaringClass()->getName() != $class) {
				continue;
			}
			$args = array();
			foreach ($method->getParameters() as $param) {
				$paramString = '$' . $param->getName();
				if ($param->isDefaultValueAvailable()) {
					$paramString .= ' = ' . str_replace("\n", '', var_export($param->getDefaultValue(), true));
				}
				$args[] = $paramString;
			}
			$parsed[$method->getName()] = array(
				'comment' => str_replace(array('/*', '*/', '*'), '', $method->getDocComment()),
				'method' => $method->getName(),
				'parameters' => '(' . implode(', ', $args) . ')'
			);
		}
		ksort($parsed);
		return $parsed;
	}

}
