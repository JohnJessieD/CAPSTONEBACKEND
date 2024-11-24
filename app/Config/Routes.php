<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->post('/api/req', 'Home::requestMoney');
$routes->get('/api/PWD', 'Home::PWD');

$routes->get('/api/fetchRequestMoney', 'Home::fetchRequestMoney');
$routes->post('/api/updateRequest', 'Home::updateRequest');
$routes->delete('/api/deleteRequest/(:num)', 'Home::deleteRequest/$1');
$routes->match(['post', 'get'], '/api/register', 'UserController::register');
$routes->match(['post', 'get'], '/api/login', 'UserController::login');

$routes->post('/api/create_user', 'UserController::create_user');
$routes->delete('/api/delete/(:num)', 'UserController::delete_user/$1');
$routes->post('/api/update_user/(:num)', 'UserController::update_user/$1');
$routes->get('api/getAgeChartData', 'OverviewController::getAgeChartData');
$routes->get('/api/appointment', 'AppointmentController::index');
$routes->get('/api/users', 'UserController::users');

$routes->post('/api/submit-application', 'SoloParentController::submitApplication');
$routes->post('/api/acceptRequest', 'Home::acceptRequest');
$routes->post('/api/rejectRequest', 'Home::rejectRequest');
$routes->post('/api/membership', 'Home::Membership');
$routes->get('/api/overview', 'OverviewController::index');
$routes->get('/api/showmembers', 'Home::Members');

$routes->post('api/membership/update/(:num)', 'Home::updateMembership/$1');
$routes->post('api/membership/edit/(:num)', 'Home::editMembership/$1');
$routes->post('/api/Feedback', 'Feedback::submitFeedback');
$routes->get('email-test', 'EmailTest::index');

// Email verification route
$routes->get('verify-email/(:segment)', 'UserController::verifyEmail/$1');
// In your routes file (e.g., routes.php or web.php)
$routes->get('api/feedback', 'Feedback::getFeedback');
// Forgot password routes
$routes->post('/api/forgot-password', 'UserController::forgotPassword');
$routes->get('reset-password/(:segment)', 'UserController::resetPasswordForm/$1');
$routes->post('reset-password/(:segment)', 'UserController::resetPassword/$1');

//AUTH
$routes->post('login', 'UserController::loginTESTING');
$routes->get('/api/verify-session', 'UserController::verifySession');


$routes->get('/api/getNotifications', 'Home::getNotifications');
$routes->put('/api/markNotificationAsRead/(:num)/read', 'Home::markNotificationAsRead/$1');


$routes->get('/api/schedules', 'Home::getSchedules');
$routes->post('/api/Createschedules', 'Home::addSchedule');
$routes->post('/api/editschedules/(:num)', 'Home::updateSchedule/$1');
$routes->delete('/api/deleteschedules/(:num)', 'Home::deleteSchedule/$1');
$routes->put('/api/reschedule/(:num)/reschedule', 'Home::rescheduleAppointment/$1');
$routes->put('schedules/(:num)/cancel', 'Home::cancelAppointment/$1');
$routes->post('/api/notifyschedules/(:num)/notify', 'Home::sendNotification/$1');
$routes->post('api/schedules/(:num)/notify', 'Home::notifyUser/$1');



$routes->group('api', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('residents', 'ResidentController::index');
    $routes->get('residents/(:num)', 'ResidentController::show/$1');
    $routes->post('addresidents', 'ResidentController::create');
    $routes->post('update/(:num)', 'ResidentController::update/$1');
    $routes->delete('delresidents/(:num)', 'ResidentController::delete/$1');
    $routes->get('Exlresidents/export/excel', 'ResidentController::exportExcel');
    $routes->get('residents/report', 'ResidentController::generateReport');
    $routes->get('barangays', 'ResidentController::getBarangays');
    $routes->get('dashboard/stats', 'ResidentController::getDashboardStats');
});
    
    
    
    $routes->group('api', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('events', 'EventController::index');
    $routes->post('events', 'EventController::create');
    $routes->post('events/(:num)', 'EventController::update/$1');
    $routes->delete('events/(:num)', 'EventController::delete/$1');
});

$routes->post('/api/requestAppointment', 'Home::requestAppointment');
$routes->get('/api/getPendingAppointments', 'Home::getPendingAppointments');
$routes->post('/api/acceptAppointment/(:num)', 'Home::acceptAppointment/$1');
$routes->post('/api/rejectAppointment/(:num)', 'Home::rejectAppointment/$1');


$routes->group('api', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->get('publications', 'PublicationController::index');
    $routes->post('publications', 'PublicationController::create');
    $routes->post('publications/(:num)', 'PublicationController::update/$1');
    $routes->delete('publications/(:num)', 'PublicationController::delete/$1');
});