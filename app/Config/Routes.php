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
$routes->match(['post','get'],'/api/register', 'UserController::register');
$routes->match(['post','get'],'/api/login', 'UserController::login');
$routes->get('api/getAgeChartData', 'OverviewController::getAgeChartData');
$routes->get( '/api/appointment', 'AppointmentController::index');
$routes->get('/api/users', 'UserController::users');
$routes->get('/api/overview', 'OverviewController::index');
$routes->post('/api/create', 'UserController::create_user'); // Route to create a new user