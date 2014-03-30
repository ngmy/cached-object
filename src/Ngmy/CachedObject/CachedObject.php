<?php namespace Ngmy\CachedObject;
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

use Illuminate\Support\Facades\Cache;

/**
 * Caching scheme for an object.
 *
 * @package CachedObject
 */
class CachedObject {

	/**
	 * Get/Clear/Rebuild a cache.
	 *
	 * If a method name starts with 'get', get an object from a cache.
	 * If a method name starts with 'clear', clear an object from a cache.
	 * If a method name starts with 'rebuild', get an object from an uncached class and rebuild a cache.
	 *
	 * @param string $name   Method name.
	 * @param array  $params Arguments.
	 * @static
	 * @access public
	 * @return mixed
	 */
	public static function __callStatic($name, $params)
	{
		$isGet     = false;
		$isClear   = false;
		$isRebuild = false;

		if (preg_match('/^get(.*)/', $name)) {
			$isGet = true;
		} else if (preg_match('/^rebuild(.*)/', $name, $m)) {
			$isRebuild = true;
			$name = 'get'.$m[1];
		} else if (preg_match('/^clear(.*)/', $name, $m)) {
			$isClear = true;
			$name = 'get'.$m[1];
		} else {
			return;
		}

		$key = call_user_func_array('self::cacheKey', array_merge((array) $name, $params));
		$uncachedClass = self::uncachedClass();

		// Clear a cache
		if ($isClear) {
			return Cache::forget($key);
		}

		// Get a cache
		if ($isGet && Cache::has($key)) {
			return Cache::get($key);
		}

		// Rebuild a cache
		self::withLock($key, function () use ($key, $uncachedClass, $name, $params)
		{
			Cache::put($key, call_user_func_array(array($uncachedClass, $name), $params), 1);
		});

		return Cache::get($key);
	}

	/**
	 * Create a unique semaphore key.
	 *
	 * @param string $key Cache key.
	 * @static
	 * @access public
	 * @return string Returns a semaphore key.
	 */
	public static function lockKey($key)
	{
		return 'lock:'.$key;
	}

	/**
	 * Checks if a semaphore exists in a cache.
	 *
	 * @param string  $key            Cache key.
	 * @param integer $timeousSeconds Timeout seconds.
	 * @static
	 * @access public
	 * @return boolean
	 */
	public static function isLocked($key, $timeousSeconds = 10)
	{
		$start = Cache::get(self::lockKey($key));
		return !is_null($start) ? (time() - $start < $timeousSeconds) : false;
	}

	/**
	 * Execute a callback function with a lock.
	 *
	 * Takes a callback function as a parameter, and only execute that callback function
	 * once the lock has been acquired, or a timeout has passed.
	 *
	 * @param string   $key            Cache key.
	 * @param callback $callback       Callback function.
	 * @param integer  $timeousSeconds Timeout seconds.
	 * @static
	 * @access public
	 * @return void
	 */
	public static function withLock($key, $callback, $timeousSeconds = 10)
	{
		$start = time();
		$acquiredLock = false;
	
		while ((time() - $start < $timeousSeconds) && !$acquiredLock) {
			$acquiredLock = Cache::add(self::lockKey($key), time(), 1);
			if (!$acquiredLock) {
				usleep(100000);
			}
		}

		call_user_func($callback);

		Cache::forget(self::lockKey($key));
	}

	/**
	 * Create a new instance from an uncached class.
	 *
	 * @static
	 * @access public
	 * @return object Returns an instance of an uncached class.
	 */
	public static function uncachedClass()
	{
		$className = get_called_class();
		$lastSlashPos = strrpos($className, '\\');

		// Check whether a class exists in a namespace
		if ($lastSlashPos === false) {
			// Outside a namespace
			$uncachedClassName = 'Uncached'.$className;
		} else {
			// Inside a namespace
			$namespace = substr($className, 0, $lastSlashPos);
			$className = substr($className, ($lastSlashPos + 1));
			$uncachedClassName = $namespace.'\Uncached'.$className;
		}

		return new $uncachedClassName();
	}

	/**
	 * Create a unique cache key.
	 *
	 * @param array $params Parameters to create a cache key.
	 * @static
	 * @access public
	 * @return string Returns a cache key.
	 */
	public static function cacheKey()
	{
		$params = func_get_args();

		$className = get_called_class();
		$version   = static::$VERSION;
		$paramsStr = implode('_', $params);

		return "{$className}_{$version}_{$paramsStr}";
	}

}
