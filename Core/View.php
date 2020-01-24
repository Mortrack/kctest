<?php

namespace Core;

/**
 * View
 *
 * PHP version 5.4
 */
class View
{

    /**
     * Render a view file
     *
     * @param string $view  The view file
     *
     * @return void
     */
    //"render" nos permite desplegar en pantalla un archivo visual para el usuario, dentro de nuestro directorio "Views"
    //          a traves de public static function render($view). Sin embargo, "render" solo funciona para archivos
    //          html/"php tipo html" y no para archivos twig
    //render("direccion del template visual a cargar en la pagina web considerando que el directorio root considerado es
    //          ../App/Views", "las variables a cargar en los templates")
public static function render($view, $args = [])
    {
        //"extract" nos permite convertir los valores asociativos de un array a variables individuales con la intencion
        //          final de poder usarlas de esa manera dentro de nuestras vistas, por ejemplo: en los archivos HTML.
        extract($args, EXTR_SKIP);

        $file = "../App/Views/$view";  // relative to Core directory

        if (is_readable($file)) {
            require $file;
        } else {
            throw new \Exception("$file not found");
        }
    }

    /**
     * Render a view template using Twig
     *
     * @param string $template  The template file
     * @param array $args  Associative array of data to display in the view (optional)
     *
     * @return void
     */
    //"renderTemplate" nos permite desplegar en pantalla un archivo visual para el usuario, dentro de nuestro directorio
    //      "Views". Pero este metodo nos permite inicializar al sistema de Twig para el caso de que
    ///     vayamos a mostrar en pantalla un archivo twig y, en caso de no haber archivo twig, se despliega en pantalla
    ///     el template html/php a traves del metodo "render" como haciamos anteriormente
    //renderTemplate("direccion del template visual a cargar en la pagina web considerando que el directorio root
    //                  considerado es ../App/Views", "las variables a cargar en los templates")
    public static function renderTemplate($template, $args = [])
    {
        static $twig = null;

        if ($twig === null) {
            //NOTE: This line of code takes as "root" the App folder and technically it has to work, but on some Twig
            //versions it only works if you specify the root of our framework instead
            //NOTA: "__DIR__" te da el valor del directorio que contine el archivo actual
            //      "dirname" te da el valor del directorio padre del directorio que le ingreses

            //This line of code takes as "root" the root of our framework (/Software)
            $loader = new \Twig_Loader_Filesystem(dirname(__DIR__) . '/App/Views');
            $twig = new \Twig_Environment($loader);
        }

        echo $twig->render($template, $args);
    }
}
