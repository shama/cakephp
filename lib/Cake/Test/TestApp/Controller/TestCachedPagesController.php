<?php
namespace TestApp\Controller;

use Cake\Controller\Controller;

/**
 * TestCachedPagesController class
 *
 * @package       Cake.Test.Case.Routing
 */
class TestCachedPagesController extends Controller {

/**
 * name property
 *
 * @var string 'TestCachedPages'
 */
	public $name = 'TestCachedPages';

/**
 * uses property
 *
 * @var array
 */
	public $uses = array();

/**
 * helpers property
 *
 * @var array
 */
	public $helpers = array('Cache', 'Html');

/**
 * cacheAction property
 *
 * @var array
 */
	public $cacheAction = array(
		'index' => '+2 sec',
		'test_nocache_tags' => '+2 sec',
		'view' => '+2 sec'
	);

/**
 * Mock out the response object so it doesn't send headers.
 *
 * @var string
 */
	protected $_responseClass = 'Cake\Test\TestCase\Routing\DispatcherMockResponse';

/**
 * viewPath property
 *
 * @var string 'posts'
 */
	public $viewPath = 'Posts';

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->render();
	}

/**
 * test_nocache_tags method
 *
 * @return void
 */
	public function test_nocache_tags() {
		$this->render();
	}

/**
 * view method
 *
 * @return void
 */
	public function view($id = null) {
		$this->render('index');
	}

/**
 * test cached forms / tests view object being registered
 *
 * @return void
 */
	public function cache_form() {
		$this->cacheAction = 10;
		$this->helpers[] = 'Form';
	}

/**
 * Test cached views with themes.
 */
	public function themed() {
		$this->cacheAction = 10;
		$this->viewClass = 'Theme';
		$this->theme = 'TestTheme';
	}

}

