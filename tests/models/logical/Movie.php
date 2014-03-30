<?php namespace Ngmy\CachedObject\Tests\Models\Logical;
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

use Ngmy\CachedObject\CachedObject;
use Ngmy\CachedObject\Tests\Models\Physical\Movie as PhysicalMovie;

class Movie extends CachedObject {

	public static $VERSION = 1;

	public $id;

	public $name;

	public $lengthMinutes;

	public function __construct($id, $name, $lengthMinutes)
	{
		$this->id            = $id;
		$this->name          = $name;
		$this->lengthMinutes = $lengthMinutes;
	}

	public function update()
	{
		$m = PhysicalMovie::find($this->id);
		$m->name           = $this->name;
		$m->length_minutes = $this->lengthMinutes;
		$m->update();
	}

	public function delete()
	{
		$m = PhysicalMovie::find($this->id);
		$m->delete();
	}

}
