# Laravel-BaseRepository
An abstract class for your repositories in Laravel providing multiple nessasary methods to be used with Eloquent.

## Usage
Your repositories must extend the `BaseRepository` class and have two properties; `protected $model`, the name of your model (including it's namespace) and `protected $relationships`, an array of the methods available to be included when retrieving items. Be sure to check out the [example repository](#examples).

```php
    $posts = new App\Repositories\PostsRepository();
    $firstPost = $posts->getById(1);
    $allPosts = $posts->getAll();
    $allPostsIncludingComments = $posts->with('comments')->getAll();
```

## Relationships

Relationships are defined in the repositiory but are not eagerly loaded automatically. Relationships can be loaded in the following three ways using the `with()` method:

* `$repository->with('all')->getAll(); ` retrieve all relationships defined in the repository class
* `$repository->with(['comments', 'author'])->getAll(); ` retrieve relationships using an array
* `$repository->with('comments')->getAll(); ` retrieve relationship using a string

## Examples

*app\Models*

```php
    class Post extends Eloquent {

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

*app\Repositories*

```php
    // BaseRepository is located in the same folder
    class PostsRepository extends BaseRepository {
        protected $model = 'App\Models\Post';
        protected $relationships = ['comments', 'author'];
    }
```
