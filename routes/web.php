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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'auth'], function () use ($router) {
        $router->post('register', 'AuthController@register');
        $router->post('register/google', 'AuthController@registerWithGoogle');
        $router->post('login', 'AuthController@login');
        $router->post('login/google', 'AuthController@loginWithGoogle');
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->group(['prefix' => 'auth'], function () use ($router) {
        $router->get('me', 'AuthController@me');
    });

    $router->group(['prefix' => 'user'], function () use ($router) {
        $router->put('update', 'UserController@updateProfile');      // Update an existing user
    });

    $router->group(['prefix' => 'modules'], function () use ($router) {
        $router->get('/', 'ModuleController@index');          // Get all modules
        $router->get('{id}', 'ModuleController@show');        // Get a single module
        $router->post('/', 'ModuleController@store');         // Create a new module 
        $router->put('{id}', 'ModuleController@update');      // Update an existing module
        $router->delete('{id}', 'ModuleController@destroy');  // Delete a module
    });

    $router->group(['prefix' => 'lessons'], function () use ($router) {
        $router->get('/', 'LessonController@index');          // Get all lessons
        $router->get('{id}', 'LessonController@show');        // Get a single lesson
        $router->post('/', 'LessonController@store');         // Create a new lesson
        $router->put('{id}', 'LessonController@update');      // Update an existing lesson
        $router->delete('{id}', 'LessonController@destroy');  // Delete a lesson
        $router->post('track/{id}', 'LessonController@track');  // Track a lesson
    });

    $router->group(['prefix' => 'activities'], function () use ($router) {
        $router->get('/', 'ActivityController@index');        // Get all activities
        $router->get('{id}', 'ActivityController@show');      // Get a single activity
        $router->post('/', 'ActivityController@store');       // Create a new activity
        $router->put('{id}', 'ActivityController@update');    // Update an activity
        $router->delete('{id}', 'ActivityController@destroy'); // Delete an activity
        $router->post('submit/{id}', 'ActivityController@saveUserAnswers');
    });

    $router->group(['prefix' => 'questions'], function () use ($router) {
        $router->get('/', 'QuestionController@index');        // Get all questions
        $router->get('{id}', 'QuestionController@show');      // Get a single question
        $router->post('/', 'QuestionController@store');       // Create a new question
        $router->put('{id}', 'QuestionController@update');    // Update an existing question
        $router->delete('{id}', 'QuestionController@destroy'); // Delete a question
    });

    $router->group(['prefix' => 'choices'], function () use ($router) {
        $router->get('/', 'ChoiceController@index');          // Get all choices
        $router->get('{id}', 'ChoiceController@show');        // Get a single choice
        $router->post('/', 'ChoiceController@store');         // Create a new choice
        $router->put('{id}', 'ChoiceController@update');      // Update an existing choice
        $router->delete('{id}', 'ChoiceController@destroy');  // Delete a choice
    });

    $router->group(['prefix' => 'bookmark'], function () use ($router) {
        $router->get('', 'BookmarkModuleController@index');    // Get index
        $router->post('{id}', 'BookmarkModuleController@store');    // Update an existing bookmark
    });

    $router->group(['prefix' => 'activity-history'], function () use ($router) {
        $router->get('', 'ActivityHistoryController@index');   // Get Index
        $router->get('leaderboards', 'ActivityHistoryController@leaderboards');   // Get Leaderboards
        $router->get('engagements', 'ActivityHistoryController@engagements');   // Get Engagement
        $router->get('getTotalModuleHours', 'ActivityHistoryController@getTotalModuleHours');   // Get Total Hours
    });
});