<?php

/**
 * Front controller
 *
 * PHP version 7.2
 */


/**
 * Packages
 */
//With "require" applied like this, it will serve to any package that has an autoloader within
require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Error and Exception handling
 */
//"error_reporting()" nos permite definir cuales errores de PHP seran notificados solamente
    //"E_ALL" allows us to see every error if they occur
error_reporting(E_ALL);
//With this function we define the method that will take in command of the error handling
set_error_handler('Core\Error::errorHandler');
//With this function we define que method that will take in command of the exceptions handling
set_exception_handler('Core\Error::exceptionHandler');


/**
 * Routing
 */
$router = new Core\Router();      //creamos un objeto a partir de la clase "Router()", pero especificando su ubicacion,
                                  //de acuerdo al formato de namespaces
// ------------- //
// Add the routes
// ------------- //
//Dentro de la clase Router(cuya direccion esta contenida dentro de la variable "$router", en donde guardamos la
//creacion del objeto de la clase "Router"), busca la funcion "add" y llama a dicha funcion con los valores
//especificados
$router->add('', ['controller'=>'Home', 'action'=>'index']);
//Cuando ponemos un nombre dentro de llaves {}, esto significa que ese elemento, en el url obtenido, estara contenido en
//el valor literal del nombre que hayamos definido, que en este caso seria "controller"
$router->add('{controller}/{action}');
//$router->add('admin/{action}/{controller}');
$router->add('{controller}/{id:\d+}/{action}');
$router->add('admin/{controller}/{action}', ['namespace' => 'Admin']);


// ----------------------- //
// Match the requested route
// ----------------------- //
$router->dispatch($_SERVER['QUERY_STRING']);
