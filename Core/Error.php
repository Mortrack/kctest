<?php

namespace Core;
use App\Config;

/**
 * Error and exception handler
 *
 * PHP version 5.4
 */
class Error
{

    /**
     * Error handler. Convert all errors to Exceptions by throwing an ErrorException.
     *
     * @param int $level  Error level
     * @param string $message  Error message
     * @param string $file  Filename the error was raised in
     * @param int $line  Line number in the file
     *
     * @return void
     */
    //"errorHandler(...)" nos permite convertir cualquier error a una excepcion
    public static function errorHandler($level, $message, $file, $line)
    {
        if (error_reporting() !== 0) {
            //Si se reporta un error, en lugar de emitir dicho error literal, mejor emite una
            //excepcion con la informacion detallada de dicho error (por nuestro error handler que hicimos en
            // "\Core\Error.php"
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Exception handler.
     *
     * @param Exception $exception  The exception
     *
     * @return void
     */
    //"exceptionHandler" nos permite, de manera personalizada,  emitir los detalles de cualquier excepcion que haya
    //                   surgido. Esto lo realiza a traves de una vista que se desplegaria en pantalla, en la pagina web
    //exceptionHandler("Recepcion y almacenamiento del objeto de Exception a una variable que nosotros definamos");
    public static function exceptionHandler($exception)
    {
        //"getCode()" se encarga de obtener el numero de codigo de error obtenido de un objeto de "Exception" que
        //            en este caso nosotros definimos en la variable "$exception"
        $code = $exception->getCode();
        //Basicamente:
        //      *Code #404 = lo que se estaba buscando (URL) no pudo ser encontrado
        //      *Code #500 = Se obtuvo un error general
        if ($code != 404)
        {
            $code = 500;
        }
        //"http_response_code()" se encarga de imprimir el codigo de error HTTP, que se le transfiera a esta funcion,
        //                       al explorador
        http_response_code($code);
        if (Config::APP_ENV) //If we enable to show detailed errors on web browser, do it
        {
            echo "<h1>Fatal error</h1>";
            //"get_class()" = regresa el string del nombre de la clase de un objeto. En este caso podriamos tener 2
            //                nombres
            //              de clases:
            //                  "ErrorExeption" = es un error literal, hablando en terminos teorico-tecnicos.
            //                  "Exception" = es una excepcion literal, hablando en terminos teorico-tecnicos.
            echo "<p>Uncaught exception: '" . get_class($exception) . "'</p>";
            //"getMessage()" = nos regresa un String del mensaje literal que describe el error que haya ocurrido
            echo "<p>Message: '" . $exception->getMessage() . "'</p>";
            //"getTraceAsString()" nos devuelve la ubicacion del archivo, linea de codigo y funcion/metodo,etc a partir
            //                      del cual, se nos genero el error.
            echo "<p>Stack trace:<pre>" . $exception->getTraceAsString() . "</pre></p>";
            //"getFile()"   nos devuelve la ubicacion del archivo en donde especificamente se hiso trigger del error y
            //              no el archivo, a partir del cual se genero dicho error
            //              ej. codigo que hace trigger de error "throw new \Exception('No route matched.');"
            //"getLine()"   nos devuelve la linea de codigo en donde especificamente se hiso trigger del error y no la
            //              linea de codigo, a partir del cual se genero dicho error
            //              ej. codigo que hace trigger de error "throw new \Exception('No route matched.');"
            echo "<p>Thrown in '" . $exception->getFile() . "' on line " . $exception->getLine() . "</p>";
        }
        //if we disable to show detailed errors on web browser, show detailed errors to devs and friendly
        //errors to users
        else
        {
            //"date_default_timezone_set()" sets the "date()" functions timezone
                        //NOTE: go to the next url to find the list of timezones:
                        //http://php.net/manual/es/timezones.php
            date_default_timezone_set('Mexico/BajaNorte');
            //"$log" will have the location and name of the error log file that will be created
                        //NOTE: the name of the log file will be the date when the error ocurred.
            $log = dirname(__DIR__) . '/logs/' . date('Y-m-d') . '.txt';
            //"ini_set()" establece el valor de una directiva de configuracion del servidor que se este usndo
            //ini_set("directiva del servidor que deseamos modificar","nuevo valor a aplicar a la directiva");
                        //"error_log" = establece que almacenaremos los errores generados en un archivo
                        //              tipo .txt, pero en formato "log".
            ini_set('error_log', $log);
            //"$message" en esta variable almacenaremos el mensaje de error detallado que almacenaremos en el log file.
            $message = "\n-------------------------------------------------------------------------------------------";
            $message .= "\n-------------------------------------------------------------------------------------------";
            $message .= "\n----------------------------------      ERROR      ----------------------------------------";
            $message .= "\n-------------------------------------------------------------------------------------------";
            $message .= "\n-------------------------------------------------------------------------------------------";
            $message .= "\nUncaught exception: '" . get_class($exception) . "'";
            $message .= "\nWith message: '" . $exception->getMessage() . "'";
            $message .= "\n\nStack trace: " . $exception->getTraceAsString();
            $message .= "\n\nThrown in '" . $exception->getFile() . "' on line " . $exception->getLine();
            $message .= "\n-------------------------------------------------------------------------------------------";
            $message .= "\n-------------------------------------------------------------------------------------------";
            $message .= "\n\n\n";
            //"error_log()" nos permite emitir el mensaje de error a traves del medio que se le haya especificado al
            //              servidor, a traves de la configuracion de "ini_set()", que en este caso seria en un log file
            error_log($message);
            View::renderTemplate("$code.html.twig");    //render the corresponding Template for the
                                                                 //corresponding error HTTP code.
        }
    }
}

