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

