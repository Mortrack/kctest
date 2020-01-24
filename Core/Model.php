<?php

namespace Core;

use PDO;
use App\Config;

/**
 * Base model
 *
 * PHP version 5.4
 */
abstract class Model
{

    /**
     * Get the PDO database connection
     *
     * @return mixed
     */
//By declaring a function member as STATIC, you make it independent of any particular object of the class. A static
//member function can be called even if no objects of the class exist and the static functions are accessed using only
//the class name and the scope resolution operator ::

//PROTECTED members that are also declared as static are accessible to any friend or member function of a derived class.
//Protected members that are not declared as static are accessible to friends and member functions in a derived class
//only through a pointer to, reference to, or object of the derived class

    protected static function getDB()
    {
        //In computer programming, a STATIC variable is a variable that has been allocated "statically", meaning that
        //its lifetime (or "extent") is the entire run of the program

        //PUBLIC variables, are variables that are visible to all classes. PRIVATE variables, are variables that are
        //visible only to the class to which they belong. PROTECTED variables, are variables that are visible only to
        //the class to which they belong, and any subclasses
        static $db = null;

        if ($db === null) {
            $dsn = 'mysql:host=' . Config::DB_HOST . ';dbname=' .
                Config::DB_NAME . ';charset=utf8';
            $db = new PDO($dsn, Config::DB_USERNAME, Config::DB_PASSWORD);
            //"setAttribute()" nos permite agregar un atributo a un objeto
            //setAttribute("atributo que queremos agregar al objeto", "valor que queremos agregar para el atributo")
                //"PDO::ATTR_ERRMODE" nos permite tener/cambiar un modo de reportar errores por parte del objeto PDO
                //"PDO::ERRMODE_EXCEPTION" este valor, a diferencia de los otros 2 existentes (PDO;ERRMODE_SILENT y
                //                         PDO::ERRMODE_WARNING), nos permite arrojar excepciones al momento de haber
                //                         algun error en el objeto PDO. Ademas, si hay un error en SQL, entonces el
                //                         objeto PDO nos arrojara excepciones y los scripts dejaran de correr
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $db;   //We return the conection to the database by just conecting to it once (thanks to our conditional)
    }
}