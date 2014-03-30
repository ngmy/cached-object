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

use Ngmy\CachedObject\Tests\Models\Logical\Movie as LogicalMovie;
use Ngmy\CachedObject\Tests\Models\Physical\Movie as PhysicalMovie;

class CachedObjcectTest extends TestCase {

	protected $ps;

	public function setUp()
	{
		parent::setUp();

		$ps[0] = $this->createPhysicalModel(array(
			'name'          => '500 Days of Summer',
			'lengthMinutes' => 95,
		));

		$ps[1] = $this->createPhysicalModel(array(
			'name'          => 'Ruby Sparks',
			'lengthMinutes' => 104,
		));

		$this->ps = $ps;

		$this->clearCache();
	}

	public function testSingleObjcectCaching()
	{
		// In the first time, a cached value should match
		$l = LogicalMovie::get($this->ps[0]->id);
		$this->assertEquals($l->name, $this->ps[0]->name);
		$this->assertEquals($l->lengthMinutes, $this->ps[0]->length_minutes);

		// After updating a physical model, a cached value should not match
		$l->name          = 'Celeste and Jesse Forever';
		$l->lengthMinutes = 92;
		$l->update();

		$l = LogicalMovie::get($l->id);
		$p = physicalmovie::find($l->id);
		$this->assertNotEquals($l->name, $p->name);
		$this->assertNotEquals($l->lengthMinutes, $p->length_minutes);

		// After rebuilding a cache, a cached value should match again
		LogicalMovie::rebuild($l->id);

		$l = LogicalMovie::get($l->id);
		$this->assertEquals($l->name, $p->name);
		$this->assertEquals($l->lengthMinutes, $p->length_minutes);
	}

	public function testMultipleObjcectsCaching()
	{
		// In the first time, a cached value should match
		$ls = LogicalMovie::getAll();
		$this->assertEquals($ls[0]->name, $this->ps[0]->name);
		$this->assertEquals($ls[0]->lengthMinutes, $this->ps[0]->length_minutes);
		$this->assertEquals($ls[1]->name, $this->ps[1]->name);
		$this->assertEquals($ls[1]->lengthMinutes, $this->ps[1]->length_minutes);

		// After updating a physical model, a cached value should not match
		$ls[0]->name          = 'Celeste and Jesse Forever';
		$ls[0]->lengthMinutes = 92;
		$ls[0]->update();

		$ls = LogicalMovie::getAll();
		$ps[0] = physicalmovie::find($ls[0]->id);
		$ps[1] = physicalmovie::find($ls[1]->id);
		$this->assertNotEquals($ls[0]->name, $ps[0]->name);
		$this->assertNotEquals($ls[0]->lengthMinutes, $ps[0]->length_minutes);
		$this->assertEquals($ls[1]->name, $ps[1]->name);
		$this->assertEquals($ls[1]->lengthMinutes, $ps[1]->length_minutes);

		// After rebuilding a cache, a cached value should match again
		LogicalMovie::rebuildAll();

		$ls = LogicalMovie::getAll();
		$this->assertEquals($ls[0]->name, $ps[0]->name);
		$this->assertEquals($ls[0]->lengthMinutes, $ps[0]->length_minutes);
		$this->assertEquals($ls[1]->name, $ps[1]->name);
		$this->assertEquals($ls[1]->lengthMinutes, $ps[1]->length_minutes);
	}

	public function testPhysicalModelObserver()
	{
		// Set a physical model observer
		PhysicalMovie::observe(new \Ngmy\CachedObject\Tests\Models\Logical\MovieObserver);

		// In the first time, a cached value should match
		$ls = LogicalMovie::getAll();
		$this->assertEquals($ls[0]->name, $this->ps[0]->name);
		$this->assertEquals($ls[1]->name, $this->ps[1]->name);

		// After updating a physical model, a cache is rebuilded and a cached value should match
		$ls[0]->name          = 'Celeste and Jesse Forever';
		$ls[0]->lengthMinutes = 92;
		$ls[0]->update();

		$ls = LogicalMovie::getAll();
		$ps[0] = physicalmovie::find($ls[0]->id);
		$ps[1] = PhysicalMovie::find($ls[1]->id);
		$this->assertEquals($ls[0]->name, $ps[0]->name);
		$this->assertEquals($ls[0]->lengthMinutes, $ps[0]->length_minutes);
		$this->assertEquals($ls[1]->name, $ps[1]->name);
		$this->assertEquals($ls[1]->lengthMinutes, $ps[1]->length_minutes);

		// After deleting a physical model, a cache is rebuilded and a cached value should match
		$ls[0]->delete();

		$ls = LogicalMovie::getAll();
		$ps[0] = physicalmovie::find($ls[0]->id);
		$this->assertEquals($ls[0]->name, $ps[0]->name);
		$this->assertEquals($ls[0]->lengthMinutes, $ps[0]->length_minutes);
	}

	protected function createPhysicalModel($params)
	{
		$p = new PhysicalMovie;
		$p->name           = $params['name'];
		$p->length_minutes = $params['lengthMinutes'];
		$p->save();

		return $p;
	}

	protected function clearCache()
	{
		LogicalMovie::clear($this->ps[0]->id);
		LogicalMovie::clear($this->ps[1]->id);
		LogicalMovie::clearAll();
	}

}
