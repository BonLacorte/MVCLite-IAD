<?php
// region (admin), admin, sadmin roles
$app->group(['RoleMiddleware:requiredRole=REGION'], function($app) {
    ///// Users Page
    $app->get('/users', 'UserController@index');
    $app->get('/user', 'UserController@user');
    // AJAX users
    $app->get('/getAllUsers', 'UserController@getAllUsers');
    $app->get('/getUsersDataTables', 'UserController@getUsersDataTables');

});