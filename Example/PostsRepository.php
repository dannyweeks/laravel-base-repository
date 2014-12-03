<?php namespace Acme\Repositories;

// app/Acme/Repositories

class PostsRepository extends BaseRepository{
    protected $model = 'Post';
    protected $relationships = ['comments'];
}