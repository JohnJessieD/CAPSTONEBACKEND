<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->post('/api/req', 'Home::requestMoney');
$routes->get('/api/PWD', 'Home::PWD');
$routes->post('/api/updateRequest', 'Home::updateRequest');
$routes->delete('/api/deleteRequest/(:num)', 'Home::deleteRequest/$1');
$routes->match(['post', 'get'], '/api/register', 'UserController::register');
$routes->match(['post', 'get'], '/api/login', 'UserController::login');
$routes->get('api/getAgeChartData', 'OverviewController::getAgeChartData');
$routes->get('/api/appointment', 'AppointmentController::index');
$routes->get('/api/users', 'UserController::users');
$routes->get('/api/overview', 'OverviewController::index');
$routes->post('/api/create', 'UserController::create_user');
$routes->delete('/api/delete/(:num)', 'UserController::delete_user/$1');
$routes->post('/api/update_user/(:num)', 'UserController::update_user/$1');
$routes->post('/api/submit-application', 'SoloParentController::submitApplication');
$routes->post('/api/acceptRequest', 'Home::acceptRequest');
$routes->post('/api/rejectRequest', 'Home::rejectRequest');
$routes->post('/api/membership', 'Home::Membership');
$routes->post('/api/Feedback', 'Feedback::submitFeedback');
$routes->get('email-test', 'EmailTest::index');

// Email verification route
$routes->get('verify-email/(:segment)', 'UserController::verifyEmail/$1');

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
$routes->put('/api/editschedules/(:num)', 'Home::updateSchedule/$1');
$routes->delete('/api/deleteschedules/(:num)', 'Home::deleteSchedule/$1');