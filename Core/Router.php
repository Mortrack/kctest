<?php

namespace Core;     //The namespace must match the father directory oh the .php file were it will be used

/**
 * Router
 *
 * PHP version 5.4
 */
class Router
{
    // ------------------------------------ //
    //Declaring The VARIABLES of THIS CLASS
    // ------------------------------------ //
    /**
     * Associative array of routes (the routing table)
     * @var array
     */
    protected $routes = [];

    /**
     * Parameters from the matched route
     * @var array
     */
    protected $params=[];


    // ------------------------------------- //
    //  Declaring the METHODS of THIS CLASS
    // ------------------------------------- //
    /**
     * Add a route to the routing table
     *
     * @param string $route The route URL
     * @param array $params Parameters (controller, action, etc.)
     *
     * @return void
     */
    //NOTA: Definir a nuestra variable $params de la manera "$params=[]" nos permite decirle a esta funcion que agregar
    //este parametro es completamente opcional. Esto para poder agregar las rutas:
    public function add($route, $params=[])
    {
        //Convert the route to a regular expression: escape forward slashes
        $route=preg_replace('/\//', '\\/', $route);

        //Convert variables e.g. {controller}
        //e.g.  $router->add('{controller}/{action}');     =  /productos/baratos     (controller=productos   &   action=baratos)
        $route=preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);

        //Convert variables with custom regular expressions e.g. {id:\d+}
        $route=preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

        //Add start and end delimiters, and case insensitive flag to route
        $route='/^' . $route . '$/i';

        $this->routes[$route]=$params;
    }

    /**
     * Get all the routes from the routing table
     *
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }


    /**
     * Dispatch the route, creating the controller object and running the
     * action method
     *
     * @param string $url The route URL
     *
     * @return void
     */
    //"dispatch" nos permitira accesar al controlador y accion que hayamos especificado a traves del URL.
    //En caso de no poder acceder al URL deseado, se emitira un mensaje de error correspondiente, segun el error
    //originado
    public function dispatch($url)
    {
        $url = $this->removeQueryStringVariables($url);

        if($this->match($url))
        {
            //Esto nos iguala, en nuestra variable "$controller", el valor asociado "controller", que tengamos en
            //nuestra variable asociativa "$params" (perteneciente a la clase Router), la cual debera coincidir con lo
            //que hayamos solicitado en el URL.
            $controller = $this->params['controller'];
            $controller = $this->convertToStudlyCaps($controller);
            //NOTA: "$controller" ahorita es igual a "el nombre del controlador solicitado en el URL" (en este caso
            //"posts")

                //NOTA: Lo que queremos hacer ahora es asignar al contenido de "$controller", la ruta para accesar a ese
                //      controlador correspondiente pero a traves de namespaces
                //OJO: se usa "\\" porque recuerda que un backslash hace literal el siguiente caracter. Esto es para
                //poder imprimir una sola backslash "\" (ej. "App\Controllers\Posts)

            //Since we have crated the method "getNamespace", we no longer need to define the controllers namespace's
            //directory the hardcoded way because we do this through "getNamespace()"
            $controller = $this->getNamespace() . $controller;

            if(class_exists($controller))
            {
                //Creamos un nuevo objeto (y lo almacenamos en la variable "$controller_object") a partir de la ruta del
                //controlador que se desea accesar y que almacenamos, anteriormente, en la variable "$controller". Esta
                //ruta que posee "$controller", debera coincidir con una de nuestras clases ya existentes.
                //NOTA: Recuerde que hicimos que las clases de nuestros controladores (ej. Home, Posts,etc.) hicieran
                //"extend" con nuestra clase "Controller" (Core/Controller.php), lo que significa que adqueriran las
                //propiedades de la clase "Controller" (Core/Controller.php). Ademas, siendo que teniamos que en
                //"Controller" (Core/Controller.php) tenemos un "__construct()" esto implica que cuando creemos un
                //objeto de las clases pertinentes a nuestros controladores (ej. Home, Posts,etc.), al momento de crear
                //dicho objeto mencionado, se ejecutaran las instrucciones especificadas dentro de dicho "__contruct()"
                //de manera automatica justo despues de crear dicho objeto.
                $controller_object= new $controller($this->params);

                $action=$this->params['action'];    //Almacenamos, en nuestra variable "$action" el valor string que
                                                    //tengamos en el valor asociado "action" dentro de nuestro array
                                                    //"params"
                $action=$this->convertToCamelCase($action);

                if (is_callable([$controller_object, $action]))
                {
                    $controller_object->$action();
                }
                else
                {
                    throw new \Exception("Method $action (in controller $controller) not found");

                }
            }
            else
            {
                throw new \Exception("Controller class $controller not found");
            }
        }
        else
        {
            throw new \Exception('No route matched.', 404);
        }
    }

    /**
     * Convert the string with hyphens to StudlyCaps,
     * e.g. post-authors => PostAuthors
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    //"convertToStudlyCaps" nos devolvera un string donde tendremos que cada palabra de dicho string, tendra su letra
    //inicial en mayuscula y, _todos los espacios/"-" dentro de dicho string, seran suprimidos.
    //EJEMPLO:
    //$string=new-posts naval
    //convertToStudlyCaps($string) = NewPostsNaval
    protected function convertToStudlyCaps($string)
    {
        //"str_replace" nos permite reemplazar caracteres especificados por otros caracteres que deseemos
        //str_replace("valor a buscar para reemplazarlo", "valor que reemplazara a los valores deseados, "string en el
        //              que se buscara y reemplazaran valores" );

        //"ucwords" nos permite hacer upper-case de la primer letra de cada palabra de un string en particular
        //ucwords("string en el que haremos upper-case de la primer letra de cada palabra");

        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    /**
     * Convert the string with hyphens to camelCase,
     * e.g. add-new => addNew
     *
     * @param string $string The string to convert
     *
     * @return string
     */
    //"convertToCamelCase" nos devolvera un string, que le hayemos ingresado, pero con su primer caracter en minuscula
    protected function convertToCamelCase($string)
    {
        //"lcfirst" nos permite hacer a minuscula unicamente al primer caracter de un string completo que hayamos
        //          especificado
        //lcfirst("string al que deseamos hacerle minuscula unicamente a su primer caracter");
        return lcfirst($this->convertToStudlyCaps($string));
    }





    /**
     * Match the route to the routes in the routing table, setting the $params
     * property if a route is found.
     *
     * @param string $url The route URL
     * @return boolean true if a match found, false otherwise
     */
    public function match($url)
    {
        //hacemos un foreach para la variable protegida "routes" de la clase "Router"
        foreach($this->routes as $route=>$params)
        {
            if(preg_match($route, $url, $matches))
            {
                //Get named capture group values
                //NOTA: Tan solo como referencia, "key" corresponderia a controller/action y "match" a su valor
                foreach($matches as $key=>$match)
                {
                    //como "preg_match", por default, arroja todos los matches que haga de tal manera que el
                    //valor asociativo (del array del match correspondiente) es un numero desde 0 hasta "n" matches
                    //correspondientes y aparte arroja los valores asociativos con los nombres tipo variables que
                    //hayamos definimo (ej. "controller" y "action" $router->add('{controller}/{action}');).
                    //NOTA: En cierta manera, preg_match en este caso duplica los valores matcheados para cuando les
                    //definimos un nombre a los valores asociativos.

                    //POR LO TANTO: con este siguiente condicional, filtramos y excluimos a todos los valores
                    //asociativos generados por default por parte de "preg_match" y solo guardamos los valores
                    //asociativos de interes junto con sus valores asociados
                    if(is_string($key))
                    {
                        $params[$key]=$match;
                    }
                }
                //guardamos los valores matcheados ya filtrados desde la variable local "params" hasta la variable
                //protegida "params" perteneciente al objeto que estamos trabajando actualmente, que seria la clase
                //"Router"
                $this->params=$params;
                return true;
            }
        }

        return false;
    }

    /**
     * Get the currently matched parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }



    /**
     * Remove the query string variables from the URL (if any). As the full
     * query string is used for the route, any variables at the end will need
     * to be removed before the route is matched to the routing table. For
     * example:
     *
     *   URL                           $_SERVER['QUERY_STRING']  Route
     *   -------------------------------------------------------------------
     *   localhost                     ''                        ''
     *   localhost/?                   ''                        ''
     *   localhost/?page=1             page=1                    ''
     *   localhost/posts?page=1        posts&page=1              posts
     *   localhost/posts/index         posts/index               posts/index
     *   localhost/posts/index?page=1  posts/index&page=1        posts/index
     *
     * A URL of the format localhost/?page (one variable name, no value) won't
     * work however. (NB. The .htaccess file converts the first ? to a & when
     * it's passed through to the $_SERVER variable).
     *
     * @param string $url The full URL
     *
     * @return string The URL with the query string variables removed
     */
    //Esta funcion cumple el proposito de remover aquello que no sea un controlador y/o accion en el url
    protected function removeQueryStringVariables($url)
    {
        if ($url != '')
        {
            //"explode" nos devuelve un array de strings separados, en valores asociados (dentro de un array), por un delimitador que hayamos especificado
            //explode(' "delimitador especificado" ', "string a separar en valores asociados", (opcional)"el numero de valores asociados maximos que queremos obtener");
            $parts = explode('&', $url, 2);

            //"strpos" encuentra la posicion numerica de la primera ocurrencia especificada para algun caracter(es) especifico(s)
            //strpos("variable que contiene el string a analizar, pero en modo array, donde su valor asociado es la ubicacion de los caracteres contenidos dentro de dicho string de izquierda a derecha", ' "caracter a buscar" ');
            if (strpos($parts[0], '=') === false)
            {//Si no hay un caracter "=" en el primer valor asociado de "$parts", entonces estos serian el controlador y/o accion y "$url" seria igual a esto.
                $url = $parts[0];
            }
            else
            {//Y si hay un caracter "=", entonces estamos en home, por lo tanto, "$url" seria igual a ningun caracter
             //(Recuerde que usamos "="/"&" para variables en el URL y no para controladores/acciones).
                $url = '';
            }
        }
        return $url;
    }

    /**
     * Get the namespace for the controller class. The namespace defined in the
     * route parameters is added if present.
     *
     * @return string The request URL
     */
    //"getNamespace" nos permite obtener la direccion del namespace de nuestros controladores y, para el caso de que se
    //               haya identificado que una ruta solicitada posee el parametro de un namespace de tipo subdirectorio,
    //               (      Ej. $router->add('admin/{controller}/{action}', ['namespace' => 'Admin']);     )
    //               dentro de nuestro folder "Controllers" (Ej. La clase "Users" ubicada en el folder "Admin", la cual
    //               se encuentra dentro del folder "Controllers"), entonces se le anexara la direccion contenida dentro
    //               de dicho parametro "namespace" a la direccion de namespace devuelta por este metodo a traves de su return
    protected function getNamespace()
    {
        $namespace = 'App\Controllers\\';
        if(array_key_exists('namespace',$this->params))
        {
            $namespace .= $this->params['namespace'] . '\\';
        }
        return $namespace;
    }


}