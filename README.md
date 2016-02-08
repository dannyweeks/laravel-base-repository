# Laravel Base Repository
[![Build Status](https://travis-ci.org/dannyweeks/laravel-base-repository.svg?branch=v0.1)](https://travis-ci.org/dannyweeks/laravel-base-repository)

An abstract repository class implementing a general interface for your Eloquent repositories providing commonly needed repository methods.
 
## Installation
Install via [Composer](http://getcomposer.org).

`composer require dannyweeks/laravel-base-repository`

Update/create your repositories by extending them with `Weeks\Laravel\Repositories\BaseEloquentRepository`. See [Usage](#usage) for more information.

## Usage
Your repositories must extend the `BaseEloquentRepository` class and have two properties: 
- `protected $model`: the name of your model (including it's namespace)
- `protected $relationships`: an array of the methods available to be included when retrieving items. 

Be sure to check out the [example repository](#examples).

```php
    $posts = new App\Repositories\PostRepository();
    $firstPost = $posts->getById(1);
    $allPosts = $posts->getAll();
    $allPostsIncludingComments = $posts->with('comments')->getAll();
```

## Available Methods
See the [BaseEloquentRepository](https://github.com/dannyweeks/laravel-base-repository/blob/master/src/BaseEloquentRepository.php) class for the full API.

## Relationships

Relationships are defined in the repository but are not eagerly loaded automatically. 

Relationships can be loaded in the following three ways using the `with()` method:

- `$postRepository->with('all')->getAll(); ` retrieve all relationships defined in the repository class
- `$postRepository->with(['comments', 'author'])->getAll(); ` retrieve relationships using an array
- `$postRepository->with('comments')->getAll(); ` retrieve relationship using a string

## Examples

*app\Models\Post.php*

```php

    namespace App\Models;

    class Post extends Illuminate\Database\Eloquent\Model
    {
        public function comments()
        {
            return $this->hasMany('App\Models\Comment');
        }

        public function author()
        {
            return $this->hasOne('App\Models\User');
        }
    }
    
```

*app\Repositories\PostRepository.php*

```php

    namespace App\Repositories;
    
    use Weeks\Laravel\Repositories\BaseEloquentRepository;
    
    class PostRepository extends BaseEloquentRepository
    {
        protected $model = 'App\Models\Post';
        protected $relationships = ['comments', 'author'];
    }
```

## Caching

To enable caching on a repository just have it `use \Weeks\Laravel\Repositories\CacheResults;`.

By doing this all the repository ['read'](https://en.wikipedia.org/wiki/Create,_read,_update_and_delete) methods cache their results using Laravel's caching system.

```
// Methods that cache when using the CacheResults trait.
getAll
getPaginated
getForSelect
getById
getItemByColumn
getCollectionByColumn
getActively
```

An example using the CacheResults trait.
```php

    namespace App\Repositories;
    
    use Weeks\Laravel\Repositories\BaseEloquentRepository;
    use Weeks\Laravel\Repositories\CacheResults;
    
    class PostRepository extends BaseEloquentRepository
    {
        use CacheResults;
        
        protected $model = 'App\Models\Post';
        protected $relationships = ['comments', 'author'];
        protected $ignoredMethods = ['getById'];
        protected $cacheTtl = 30;
    }
```

You can force the result of a request not to be cached by adding the method name to the `$ignoredMethods` property of your repository. See example above.

By default the [ttl](https://en.wikipedia.org/wiki/Time_to_live) of a cache item is 60 minutes. This can be overwritten by updating the ` $cacheTtl` property of your repository. See example above.
