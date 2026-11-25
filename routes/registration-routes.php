<?php
///// Action-syle routing
// Protected routes (login required)
$app->group(['AuthMiddleware'], function($app) {

    ///// AJAX NCFRS Users Count // for dashboard, about page
    $app->get('/getTotalUsers', 'UserController@getTotalUsers');

    ///// Web pages 
    $app->get('/dashboard', 'HomeController@dashboard');
    $app->get('/farmers', 'FarmerController@index');

    // AJAX farmers
    $app->get('/getFarmers', 'FarmerController@getFarmers');
    $app->get('/getFarmersDataTables', 'FarmerController@getFarmersDataTables');
    $app->get('/getFarmerProfile', 'FarmerController@getFarmerProfile');

    // AJAX Farm
    $app->get('/getFarmList', 'FarmController@getFarmList');

    $app->group(['RoleMiddleware:requiredRole=ENCODER'], function($app) {

        // farmer registration/form page
        $app->get('/registration', 'HomeController@viewRegistration');
        $app->post('/register/farmer', 'FormController@viewRegistrationForm');
        $app->get('/editFarmer', 'FarmerController@editFarmer');
        $app->post('/updateFarmer', 'FarmerController@updateFarmer');
        $app->post('/createFarmer', 'FarmerController@createFarmer');
        $app->post('/deleteFarmer', 'FarmerController@deleteFarmer');

        // AJAX Attachments POST requests
        $app->post('/updateFarmerPhoto', 'AttachmentController@updateFarmerPhoto');
        $app->post('/deleteAttachment', 'AttachmentController@deleteAttachment');
        $app->post('/createAttachment', 'AttachmentController@createAttachment');
        $app->post('/createGovid', 'GovidController@createGovid');
        $app->post('/deleteGovid', 'GovidController@deleteGovid');

    });

});