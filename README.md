# Cached Object

[![Build Status](https://travis-ci.org/ngmy/cached-object.png?branch=master)](https://travis-ci.org/ngmy/cached-object)
[![Coverage Status](https://coveralls.io/repos/ngmy/cached-object/badge.png?branch=master)](https://coveralls.io/r/ngmy/cached-object?branch=master)

A caching scheme for an object for Laravel 4,
inspired by [Enterprise Rails](http://enterpriserails.chak.org/).

## Requirements

The Cached Object has the following requirements:

  * PHP 5.3+

  * Laravel 4.0+

## Installation

Add the package to your `composer.json` and run `composer update`:

```json
{
    "require": {
        "ngmy/cached-object": "dev-master"
    }
}
```

Add the following to the list of service providers in `app/config/app.php`:

```php
'Ngmy\CachedObject\CachedObjectServiceProvider',
```

Add the following to the list of class aliases in `app/config/app.php`:

```php
'CachedObject' => 'Ngmy\CachedObject\CachedObject',
```

## Examples

### Basic Usage

1. Create a physical model, which inherits from Eloquent:

  ```php
  namespace App\Models\Physical;
  
  use Illuminate\Database\Eloquent\Model as Eloquent;
  
  class Movie extends Eloquent {}
  ```

2. Create a logical model, which inherits from CachedObject:

  ```php
  namespace App\Models\Logical;
  
  use CachedObject;
  
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
  
  }
  ```

  You need to define `$VERSION`. This property is used to create a unique cache key for a requested object. Please increments this number when you change a structure of a class. 

3. Create a logical model of an uncached version, which a class name starts with **Uncached**:

  ```php
  namespace App\Models\Logical;
  
  class UncachedMovie {
  
      public static function get($id)
      {
          $m = \App\Models\Physical\Movie::find($id);
  
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
  
  }
  ```

  In order to get an object of a logical model, you need to define a method whose name starts with **get**. This method is called only if an object does not exist in a cache.

4. Now, you can get an object from a cache by calling `Movie::get()`.

### Physical Model Observers

1. Create a observer.

  For example, to rebuild a cache when you updated a physical model, and also to delete a cache when you deleted a physical model, define a observer as follows:

  ```php
  namespace App\Models\Logical;
  
  class MovieObserver
  {
      public function saved($m)
      {
          Movie::rebuild($m->id);
      }
  
      public function deleted($m)
      {
          Movie::clear($m->id);
      }
  }
  ```

2. Register a observer to a physical model:

  ```php
  \App\Models\Physical\Movie::observe(new \App\Models\Logical\MovieObserver);
  ```

### More Usage
Please see my unit tests.

