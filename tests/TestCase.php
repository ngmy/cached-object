<?php namespace Ngmy\CachedObject\Tests;
/**
 * Part of the CachedObject package.
 *
 * Licensed under MIT License.
 *
 * @package    CachedObject
 * @version    0.1.0
 * @author     Ngmy <y.nagamiya@gmail.com>
 * @license    http://opensource.org/licenses/MIT MIT License
 * @copyright  (c) 2014, Ngmy <y.nagamiya@gmail.com>
 * @link       https://github.com/ngmy/cached-object
 */

abstract class TestCase extends \Orchestra\Testbench\TestCase {

	public function setUp()
	{
		parent::setUp();

		$this->setUpDatabase();
	}

	protected function setUpDatabase()
	{
		$this->app['config']->set('database.default', 'sqlite');
		$this->app['config']->set('database.connections.sqlite', array(
			'driver'   => 'sqlite',
			'database' => ':memory:',
			'prefix'   => '',
		));

		\Schema::create('movies', function($table)
		{
			$table->increments('id');
			$table->string('name');
			$table->integer('length_minutes')->unsigned();
			$table->timestamps();
		});
	}

}
