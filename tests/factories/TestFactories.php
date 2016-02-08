<?php
$factory->define(Post::class, function ($faker) {
    return [
        'title' => $faker->sentence(5),
        'body'  => $faker->paragraph,
    ];
});