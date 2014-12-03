<?php

// app/models

class Post extends Eloquent {

    public function comments()
    {
        return $this->hasMany('Comment');
    }
}
