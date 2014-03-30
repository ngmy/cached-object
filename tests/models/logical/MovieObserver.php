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

class MovieObserver {

	public function saved($m)
	{
		Movie::rebuild($m->id);
		Movie::rebuildAll();
	}

	public function deleted($m)
	{
		Movie::clear($m->id);
		Movie::rebuildAll();
	}

}
