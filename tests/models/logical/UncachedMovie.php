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

use Ngmy\CachedObject\Tests\Models\Physical\Movie as PhysicalMovie;

class UncachedMovie {

	public static function get($movieId)
	{
		$m = PhysicalMovie::find($movieId);

		if (is_null($m)) {
			return null;
		} else {
			$movie = new Movie(
				$m->id,
				$m->name,
				$m->length_minutes
			);
			return $movie;
		}
	}

	public static function getAll()
	{
		$ms = PhysicalMovie::all();
		if (is_null($ms)) {
			return array();
		} else {
			$movies = array();
			foreach ($ms as $m) {
				$movies[] = new Movie(
					$m->id,
					$m->name,
					$m->length_minutes
				);
			}
			return $movies;
		}
	}

}
