<?php
// routes files
require 'locations-routes.php';
require 'users-routes.php';
require 'registration-routes.php';

///// Public pages (no login required)
$app->get('/', 'HomeController@index');
$app->get('/about', 'HomeController@about');
$app->get('/contact', 'HomeController@contact');

// login page
$app->get('/login', 'AuthController@login');
$app->post('/login', 'AuthController@loginPost');

// Protected routes (login required)
$app->group(['AuthMiddleware'], function($app) {
    $app->get('/dashboard', 'HomeController@dashboard');
    $app->post('/keepalive', 'AuthController@keepAlive');
    $app->get('/logout', 'AuthController@logout');

    // Profile routes
    $app->get('/profile', 'ProfileController@index');
    $app->get('/profile/edit', 'ProfileController@edit');
    $app->post('/profile/update', 'ProfileController@update');
    $app->post('/profile/change-password', 'ProfileController@changePassword');
    $app->get('/profile/image', 'ProfileController@image');

    // lookups
    $app->get('/getAllLookup', 'LookupController@getAllLookup');
    $app->get('/getCivilStatus', 'LookupController@getCivilStatus');
    $app->get('/getGovId', 'LookupController@getGovId');
    $app->get('/getEducation', 'LookupController@getEducation');

    // Questionnaires (User only)
    $app->group(['RoleMiddleware:requiredRole=ENCODER'], function($app) {
        $app->get('/questionnaires', 'QuestionnaireController@index');
        $app->get('/api/questionnaires', 'QuestionnaireController@apiList');
        $app->get('/questionnaires/file', 'QuestionnaireController@viewFile');
        $app->get('/questionnaires/{id}', 'QuestionnaireController@show');
        $app->get('/questionnaires/{id}/download', 'QuestionnaireController@downloadQuestionnaire');
        $app->get('/questionnaires/{id}/dh-verification-form', 'QuestionnaireController@downloadDhVerificationForm');
        $app->get('/questionnaires/{id}/answer', 'QuestionnaireController@answer');
        $app->post('/questionnaires/{id}/answer', 'QuestionnaireController@answerAction');
        $app->post('/questionnaires/{id}/verify-dh', 'QuestionnaireController@uploadVerificationForm');
    });

    // Admin routes (ADMIN role required)
    $app->group(['RoleMiddleware:requiredRole=ADMIN'], function($app) {

        // USERS
        $app->get('/admin/users',               'AdminController@users');
        $app->post('/admin/users/store',        'AdminController@storeUser');
        $app->post('/admin/users/update',       'AdminController@updateUser');
        $app->post('/admin/users/delete',       'AdminController@deleteUser');
        $app->post('/admin/users/change-password', 'AdminController@changeUserPassword');
        $app->get('/admin/users/export',        'AdminController@exportUsers');

        // USERS – Standalone pages
        $app->get('/admin/users/create',        'AdminController@createUserPage');
        $app->get('/admin/users/edit',          'AdminController@editUserPage');
        $app->get('/admin/users/view',          'AdminController@viewUserPage');

        // ADMINS
        $app->get('/admin/admins',              'AdminController@admins');
        $app->post('/admin/admins/store',       'AdminController@storeAdmin');
        $app->post('/admin/admins/update',      'AdminController@updateAdmin');
        $app->post('/admin/admins/delete',      'AdminController@deleteAdmin');
        $app->get('/admin/admins/export',       'AdminController@exportAdmins');

        // ADMINS – Standalone pages
        $app->get('/admin/admins/create',       'AdminController@createAdminPage');
        $app->get('/admin/admins/edit',         'AdminController@editAdminPage');
        $app->get('/admin/admins/view',         'AdminController@viewAdminPage');

        // EVENTS
        $app->get('/admin/events',              'AdminController@events');
        $app->post('/admin/events/store',       'AdminController@storeEvent');
        $app->post('/admin/events/update',      'AdminController@updateEvent');
        $app->post('/admin/events/delete',      'AdminController@deleteEvent');
        $app->get('/admin/events/image',        'AdminController@eventImage');

        // EVENTS – Standalone pages
        $app->get('/admin/events/create',       'AdminController@createEventPage');
        $app->get('/admin/events/edit',         'AdminController@editEventPage');
        $app->get('/admin/events/view',         'AdminController@viewEventPage');


        // QUESTIONNAIRES (Multi-step Flow)
        $app->get('/admin/questionnaires',              'AdminQuestionnaireController@index');
        $app->get('/admin/questionnaires/export',       'AdminQuestionnaireController@exportIndex');
        $app->get('/admin/questionnaire/create',        'AdminQuestionnaireController@create');
        $app->post('/admin/questionnaire',              'AdminQuestionnaireController@store');
        
        $app->get('/admin/questionnaire/show',          'AdminQuestionnaireController@show');
        $app->get('/admin/questionnaire/export',        'AdminQuestionnaireController@exportShow');
        
        $app->get('/admin/questionnaire/edit',          'AdminQuestionnaireController@edit');
        $app->post('/admin/questionnaire/edit',         'AdminQuestionnaireController@update');
        $app->post('/admin/questionnaire/delete',       'AdminQuestionnaireController@delete');

        // Questionnaire Assignment & Checking/Scoring
        $app->get('/admin/questionnaire/assign',        'AdminQuestionnaireController@assign');
        $app->post('/admin/questionnaire/assign-create','AdminQuestionnaireController@assignCreate');
        $app->post('/admin/questionnaire/assign-delete','AdminQuestionnaireController@assignDelete');
        $app->get('/admin/questionnaire/check',         'AdminQuestionnaireController@check');
        $app->get('/admin/questionnaire/file',          'AdminQuestionnaireController@viewUploadedFile');
        $app->get('/admin/questionnaire/criteria-file', 'AdminQuestionnaireController@viewCriteriaFile');
        $app->post('/admin/questionnaire/checked',      'AdminQuestionnaireController@checked');
        $app->post('/admin/questionnaire/checked-update','AdminQuestionnaireController@checkedUpdate');

        // Sections
        $app->get('/admin/questionnaire/sections',        'AdminQuestionnaireController@sections');
        $app->get('/admin/questionnaire/sections/create', 'AdminQuestionnaireController@sections');
        $app->post('/admin/questionnaire/sections',       'AdminQuestionnaireController@storeSection');
        $app->get('/admin/questionnaire/sections/edit',   'AdminQuestionnaireController@editSection');
        $app->post('/admin/questionnaire/sections/edit',  'AdminQuestionnaireController@updateSection');
        $app->post('/admin/questionnaire/sections/delete','AdminQuestionnaireController@deleteSection');
        
        // Questions
        $app->get('/admin/questionnaire/questions',        'AdminQuestionnaireController@questions');
        $app->get('/admin/questionnaire/questions/create', 'AdminQuestionnaireController@createQuestion');
        $app->post('/admin/questionnaire/questions',       'AdminQuestionnaireController@storeQuestion');
        $app->get('/admin/questionnaire/questions/edit',   'AdminQuestionnaireController@editQuestion');
        $app->post('/admin/questionnaire/questions/edit',  'AdminQuestionnaireController@updateQuestion');
        $app->post('/admin/questionnaire/questions/delete','AdminQuestionnaireController@deleteQuestion');
    });
});

// Events (Public)
$app->get('/events/api/loadMoreEvents', 'EventController@loadMoreEvents');
$app->get('/events/api/loadMorePhotos', 'EventController@loadMorePhotos');
$app->get('/events/image', 'EventController@image');
$app->get('/events', 'EventController@index');
$app->get('/events/{id}', 'EventController@show');

/* Sample Protected pages (must be logged in)
$app->get('/', 'HomeController@index', [ 'AuthMiddleware' ]);
$app->get('/logout', 'AuthController@logout');
$app->get('/about', 'HomeController@about', [ 'AuthMiddleware' ]);
$app->get('/users', 'UserController@index', [ 'AuthMiddleware' ]);
$app->get('/user', 'UserController@user', [ 'AuthMiddleware' ]);
//or
$app->group(['AuthMiddleware'], function($app) {
    $app->get('/', 'HomeController@index');
    $app->get('/farmers', 'FarmerController@index');
    $app->get('/about', 'HomeController@viewAbout');
});
*/

/* SAMPLE Role Middleware usage

// Minimum role hierarchy (ENCODER and above)
$app->get('/encoder/dashboard', 'EncoderController@index', [
    'AuthMiddleware',
    'RoleMiddleware:requiredRole=ENCODER'
]);

// Specific roles only
$app->get('/special/report', 'ReportController@index', [
    'AuthMiddleware',
    'RoleMiddleware:allowedRoles=VALIDATOR,REGION'
]);

*/

/* AJAX Example
// from view file
const postData = {
    name: "Test",
    email: "test@example.com"
};
$.ajax({
    url: '/save-data',
    type: 'POST',
    data: postData,
    success: function(res) {
        const data = JSON.parse(res);
        alert(data.message); // show confirmation
    },
    error: function() {
        alert('Something went wrong!');
    }
});
//
$app->post('/save-data', 'DataController@saveData', ['AuthMiddleware']);

///////////////////////// REST-Style Routes and Controller Example //////////////////////////
// Route
$app->get('/farmers', 'FarmerController@index');
$app->post('/farmers', 'FarmerController@create');
$app->patch('/farmers/{id}', 'FarmerController@update');
$app->delete('/farmers/{id}', 'FarmerController@delete');

$app->get('/farmers/{id}', 'FarmerController@show');

// Routes with middleware
$app->group(['RoleMiddleware:requiredRole=REGION'], function($app) {

    $app->get('/users', 'UserController@index');

    $app->get('/getAllUsers', 'UserController@getAllUsers');
    $app->get('/getUsersDataTables', 'UserController@getUsersDataTables');

    // REST-style example
    $app->patch('/users/{id}', 'UserController@update');
});

// Controler
public function update($id)
{
    echo "Updating farmer: " . $id;
}

// AJAX
$.ajax({
    url: '/farmers/123',
    type: 'PATCH',
    data: {
        name: 'Juan Dela Cruz'
    }
});

*/
