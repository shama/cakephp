<?php
/**
 * A factory class to manage the life cycle of test fixtures
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.TestSuite.Fixture
 * @since         CakePHP(tm) v 2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace Cake\TestSuite\Fixture;

use Cake\Core\App;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Model\ConnectionManager;
use Cake\TestSuite\TestCase;
use Cake\Utility\ClassRegistry;
use Cake\Utility\Inflector;

/**
 * A factory class to manage the life cycle of test fixtures
 *
 * @package       Cake.TestSuite.Fixture
 */
class FixtureManager {

/**
 * Was this class already initialized?
 *
 * @var boolean
 */
	protected $_initialized = false;

/**
 * Default datasource to use
 *
 * @var DataSource
 */
	protected $_db = null;

/**
 * Holds the fixture classes that where instantiated
 *
 * @var array
 */
	protected $_loaded = array();

/**
 * Holds the fixture classes that where instantiated indexed by class name
 *
 * @var array
 */
	protected $_fixtureMap = array();

/**
 * Inspects the test to look for unloaded fixtures and loads them
 *
 * @param Cake\TestSuite\TestCase $test the test case to inspect
 * @return void
 */
	public function fixturize($test) {
		if (!$this->_initialized) {
			ClassRegistry::config(array('ds' => 'test', 'testing' => true));
		}
		if (empty($test->fixtures) || !empty($this->_processed[get_class($test)])) {
			$test->db = $this->_db;
			return;
		}
		$this->_initDb();
		$test->db = $this->_db;
		if (!is_array($test->fixtures)) {
			$test->fixtures = array_map('trim', explode(',', $test->fixtures));
		}
		if (isset($test->fixtures)) {
			$this->_loadFixtures($test->fixtures);
		}

		$this->_processed[get_class($test)] = true;
	}

/**
 * Initializes this class with a DataSource object to use as default for all fixtures
 *
 * @return void
 */
	protected function _initDb() {
		if ($this->_initialized) {
			return;
		}
		$db = ConnectionManager::getDataSource('test');
		$db->cacheSources = false;
		$this->_db = $db;
		$this->_initialized = true;
	}

/**
 * Looks for fixture files and instantiates the classes accordingly
 *
 * @param array $fixtures the fixture names to load using the notation {type}.{name}
 * @return void
 * @throws UnexpectedValueException when a referenced fixture does not exist.
 */
	protected function _loadFixtures($fixtures) {
		foreach ($fixtures as $fixture) {
			$fixtureFile = null;
			$fixtureIndex = $fixture;
			if (isset($this->_loaded[$fixture])) {
				continue;
			}

			if (strpos($fixture, 'core.') === 0) {
				list($core, $base) = explode('.', $fixture, 2);
				$baseNamespace = 'Cake';
			} elseif (strpos($fixture, 'app.') === 0) {
				list($app, $base) = explode('.', $fixture, 2);
				$baseNamespace = Configure::read('App.namespace');
			} elseif (strpos($fixture, 'plugin.') === 0) {
				list($p, $plugin, $base) = explode('.', $fixture);
				$baseNamespace = Plugin::getNamespace($plugin);
			} else {
				$base = $fixture;
			}
			$base = Inflector::camelize($base);
			$className = implode('\\', array($baseNamespace, 'Test\Fixture', $base . 'Fixture'));

			if (class_exists($className)) {
				$this->_loaded[$fixture] = new $className();
				$this->_fixtureMap[$base] = $this->_loaded[$fixture];
			} else {
				throw new \UnexpectedValueException(__d('cake_dev', 'Referenced fixture class %s not found', $className));
			}
		}
	}

/**
 * Runs the drop and create commands on the fixtures if necessary.
 *
 * @param Cake\TestSuite\Fixture\TestFixture $fixture the fixture object to create
 * @param DataSource $db the datasource instance to use
 * @param boolean $drop whether drop the fixture if it is already created or not
 * @return void
 */
	protected function _setupTable($fixture, $db = null, $drop = true) {
		if (!$db) {
			if (!empty($fixture->useDbConfig)) {
				$db = ConnectionManager::getDataSource($fixture->useDbConfig);
			} else {
				$db = $this->_db;
			}
		}
		if (!empty($fixture->created) && in_array($db->configKeyName, $fixture->created)) {
			return;
		}

		$sources = (array)$db->listSources();
		$table = $db->config['prefix'] . $fixture->table;
		$exists = in_array($table, $sources);

		if ($drop && $exists) {
			$fixture->drop($db);
			$fixture->create($db);
		} elseif (!$exists) {
			$fixture->create($db);
		} else {
			$fixture->created[] = $db->configKeyName;
		}
	}

/**
 * Creates the fixtures tables and inserts data on them.
 *
 * @param Cake\TestSuite\TestCase $test the test to inspect for fixture loading
 * @return void
 */
	public function load(TestCase $test) {
		if (empty($test->fixtures)) {
			return;
		}
		$fixtures = $test->fixtures;
		if (empty($fixtures) || !$test->autoFixtures) {
			return;
		}

		foreach ($fixtures as $f) {
			if (!empty($this->_loaded[$f])) {
				$fixture = $this->_loaded[$f];
				$db = ConnectionManager::getDataSource($fixture->useDbConfig);
				$db->begin();
				$this->_setupTable($fixture, $db, $test->dropTables);
				$fixture->insert($db);
				$db->commit();
			}
		}
	}

/**
 * Truncates the fixtures tables
 *
 * @param Cake\TestSuite\TestCase $test the test to inspect for fixture unloading
 * @return void
 */
	public function unload(TestCase $test) {
		$fixtures = !empty($test->fixtures) ? $test->fixtures : array();
		foreach (array_reverse($fixtures) as $f) {
			if (isset($this->_loaded[$f])) {
				$fixture = $this->_loaded[$f];
				if (!empty($fixture->created)) {
					foreach ($fixture->created as $ds) {
						$db = ConnectionManager::getDataSource($ds);
						$fixture->truncate($db);
					}
				}
			}
		}
	}

/**
 * Creates a single fixture table and loads data into it.
 *
 * @param string $name of the fixture
 * @param DataSource $db DataSource instance or leave null to get DataSource from the fixture
 * @return void
 * @throws UnexpectedValueException if $name is not a previously loaded class
 */
	public function loadSingle($name, $db = null) {
		$name = Inflector::camelize($name);
		if (isset($this->_fixtureMap[$name])) {
			$fixture = $this->_fixtureMap[$name];
			if (!$db) {
				$db = ConnectionManager::getDataSource($fixture->useDbConfig);
			}
			$this->_setupTable($fixture, $db);
			$fixture->truncate($db);
			$fixture->insert($db);
		} else {
			throw new \UnexpectedValueException(__d('cake_dev', 'Referenced fixture class %s not found', $name));
		}
	}

/**
 * Drop all fixture tables loaded by this class
 *
 * @return void
 */
	public function shutDown() {
		foreach ($this->_loaded as $fixture) {
			if (!empty($fixture->created)) {
				foreach ($fixture->created as $ds) {
					$db = ConnectionManager::getDataSource($ds);
					$fixture->drop($db);
				}
			}
		}
	}

}
