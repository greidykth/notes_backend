<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['prefix' => 'notes'], function () use ($router) {
    $router->post('/search', 'NoteController@search');
    $router->get('/{note}', 'NoteController@show');
    $router->post('/', 'NoteController@store');
    $router->put('/{note}', 'NoteController@update');
    $router->delete('/{note}', 'NoteController@destroy');
});

$router->group(['prefix' => 'tags'], function () use ($router) {
    $router->get('/', 'TagController@index');
    $router->post('/', 'TagController@store');
});
