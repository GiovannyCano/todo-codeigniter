<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Todo::index');
$routes->resource('tasks', ['controller' => 'Tasks']);

