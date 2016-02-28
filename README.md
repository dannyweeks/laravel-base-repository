# Laravel Base Repository
[![Build Status](https://travis-ci.org/dannyweeks/laravel-base-repository.svg?branch=v0.1)](https://travis-ci.org/dannyweeks/laravel-base-repository)

An abstract repository class for your Eloquent repositories that requires minimal config to get started. 

## Features

- 2 minute setup.
- 10 useful methods out of the box such as `getById`.
- Flexible relationship support including eager loading.. 
- Optional easy to use Caching.
- Optional 404 exceptions when items aren't found.

## Quick Start

Install via [Composer](http://getcomposer.org).

`composer require dannyweeks/laravel-base-repository`

Extend your repositories with `Weeks\Laravel\Repositories\BaseEloquentRepository`.

Add the `$model` property to your repository so the base repository knows what model to use.

```php
    namespace App\Repositories;
 
    class PostRepository extends \Weeks\Laravel\Repositories\BaseEloquentRepository
    {
        protected $model = \App\Models\Post::class;
    }
```

That's it! Let's test it out.

```php
    $repo = new App\Repositories\PostRepository();
    var_dump($repo->getById(1)); // Returns the Post with an ID of 1.
    var_dump($repo->getAll()); // Returns a collection of all your posts.
```

## Usage
Your repositories must extend the `BaseEloquentRepository` class and have the properties: 
- `protected $model`: the name of your model (including it's namespace)
- `protected $relationships`: (Optional) an array of the methods available to be included when retrieving items.

```php
    $posts = new App\Repositories\PostRepository();
    $firstPost = $posts->getById(1);
    $allPosts = $posts->getAll();
    $allPostsIncludingComments = $posts->with('comments')->getAll();
```

Be sure to check out the [example](#an-example).

## Available Methods
See the [repository interface](https://github.com/dannyweeks/laravel-base-repository/blob/master/src/RepositoryContract.php) class for the full API.

## Relationships

Relationships are defined in the repository but are not eagerly loaded automatically. 

Relationships can be loaded in the following three ways using the `with()` method:

- `$postRepository->with('all')->getAll(); ` retrieve all relationships defined in the repository class
- `$postRepository->with(['comments', 'author'])->getAll(); ` retrieve relationships using an array
- `$postRepository->with('comments')->getAll(); ` retrieve relationship using a string

## An Example

This example shows how your model, repository and controller could be set up.

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
        protected $model = App\Models\Post::class;
        protected $relationships = ['comments', 'author'];
    }
```

*app\Http\Controllers\PostController.php*

```php

    namespace App\Http\Controllers;
    
    use App\Repositories\PostRepository;
    
    class PostController extends Controller
    {
        protected $posts;
        
        public function __construct(PostRepository $posts) 
        {
            $this->posts = $posts;
        }
        
        public function show($id)
        {
            // get the post and eagerly load the comments for it too.
            $post = $this->posts->with('comments')->getById($id);
            
            return view('posts.show', compact('post'));
        }
    }
```

## HTTP Exceptions
To enable http exceptions (like Eloquent's findOrFail method) on a repository just have it `use \Weeks\Laravel\Repositories\Traits\ThrowsHttpExceptions;`.

If the below methods return null they will throw a 404 error instead of returning null.

```
getById
getItemByColumn
```

An example using the ThrowsHttpExceptions trait.

```php

    namespace App\Repositories;
    
    use Weeks\Laravel\Repositories\BaseEloquentRepository;
    use Weeks\Laravel\Repositories\Traits\ThrowsHttpExceptions;
    
    class PostRepository extends BaseEloquentRepository
    {
        use ThrowsHttpExceptions;
        
        protected $model = App\Models\Post::class;
    }
```

You can temporarily disable HTTP exceptions by chaining `disableHttpExceptions()` before performing a query. For example:

```php
$posts = new PostRepository();

$post = $posts->disableHttpExceptions()->getById(1000); // returns null rather than throwing a 404 error.
```

## Caching

To enable caching on a repository just have it `use \Weeks\Laravel\Repositories\Traits\CacheResults;`.

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
    use Weeks\Laravel\Repositories\Traits\CacheResults;
    
    class PostRepository extends BaseEloquentRepository
    {
        use CacheResults;
        
        protected $model = App\Models\Post::class;
        protected $relationships = ['comments', 'author'];
        protected $nonCacheableMethods = ['getById'];
        protected $cacheTtl = 30;
    }
```

You can force the result of a request not to be cached by adding the method name to the `$nonCacheableMethods` property of your repository. See example above.

By default the [ttl](https://en.wikipedia.org/wiki/Time_to_live) of a cache item is 60 minutes. This can be overwritten by updating the ` $cacheTtl` property of your repository. See example above.
