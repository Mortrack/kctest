<?php

namespace Core;

use App\Config;
use App\Models\SessionKeys;
use App\Models\Users;

/**
 * Base controller
 *
 * PHP version 5.4
 */
//NOTE: An "abstract class" means that we are going to create a class on which we will not create objects
//of this class directly. What we will do is to crate objects of classes that will extend this class
abstract class Controller
{

    /**
     * Parameters from the matched route
     * @var array
     */
    protected $route_params = [];

    /**
     * Class constructor
     *
     * @param array $route_params  Parameters from the route
     *
     * @return void
     */
    //Cuando empleamos una funcion magica "__construct" dentro de una clase en particular, esto hace que
    //cuando creemos un objeto  de dicha clase, entonces se ejecutara de manera inmediata _todo lo que este
    // dentro de dicho metodo "__construct"

    //EN ESTE CASO: lo que pretendemos es que cuando creemos un objeto de uno de nuestros controladores, dicho
    //objeto cargara dentro de sus propiedades los valores que hayamos asignado en la variable local "route_params"
    //del metodo "__construct", los cuales seran los parametros que el usuario haya demandado a traves del URL
    //("controlador deseado", "id" e "accion deseada"). De esta manera, con un simple empleo de "->" en el
    //objeto creado, podremos accesar al los parametros especificados facil y rapidamente
    public function __construct($route_params)
    {
        $this->route_params = $route_params;
    }


    /**
     * Magic method called when a non-existent or inaccessible method is
     * called on an object of this class. Used to execute before and after
     * filter methods on action methods. Action methods need to be named
     * with an "Action" suffix, e.g. indexAction, showAction etc.
     *
     * @param $name
     * @param $args
     * @throws \Exception
     */
    public function __call($name, $args)
    {
        $method = $name . 'Action';

        if (method_exists($this, $method)) {
            if ($this->before() !== false) {
                call_user_func_array([$this, $method], $args);
                $this->after();
            }
        } else {
//            echo "Method $method not found in controller " . get_class($this);
            //Si se reporta un error, en lugar de emitir dicho error a traves de un "echo", mejor emite una
            //excepcion con la informacion detallada de dicho error (por nuestro error handler que hicimos en
            // "\Core\Error.php"
            throw new \Exception("Method $method not found in controller " .
                                get_class($this));
        }
    }

    /**
     * This method is in charge of retrieving the matching row value of the "users" table between the table's id
     * value and the value stored on "$_SESSION['user']" (this method also decrypts the content of $_SESSION to
     * be able to retrieve the value that we want to match with the database).
     * And if the session_key for such user account happens to be expired, this code will renew the session of
     * the user and will asign him a new session_key (session_keys expire each 1 hour).
     *
     * @return null
     * @throws \Exception
     *
     * @author Miranda Meza César
     * DATE November 28, 2018
     */
    public function getUser()
    {
        if (isset($_SESSION["user"]) && isset($_SESSION["key"])) {
            // We decrypt the Session's user id
            $inputMessage = $_SESSION["user"];
            $inputKey = Config::AES_INPUT_KEY;
            $blockSize = '256';
            $aes = new Aes($inputMessage, $inputKey, $blockSize);
            $decrypt = $aes->decrypt();
            $user_id = $decrypt;

            // We decrypt the Session's session key
            $inputMessage = $_SESSION["key"];
            $inputKey = Config::AES_INPUT_KEY;
            $blockSize = '256';
            $aes = new Aes($inputMessage, $inputKey, $blockSize);
            $decrypt = $aes->decrypt();
            $session_key = $decrypt;

            $users = new Users();
            $user = $users::find($user_id);
            if ($user != null) {
                $user = $user[0];

                // We decrypt the user's session key
                $sessionKeys = new SessionKeys();
                $keyObjects = $sessionKeys::findBy(["user_id"=>$user_id]);
                $visitors = new Visitors();
                $browser = $visitors->getUserBrowser();
                $uL = new UserLocation();
                $ip = $uL->getUserRealIpAddress();
                $usersKey=[];
                if ($browser != false) {
                    $usersKeyMatches = 0;
                    for ($n=0; $n<count($keyObjects); $n++) {
                        if (($keyObjects[$n]["browser"]==$browser) || ($keyObjects[$n]["ip"]==$ip)) {
                            $usersKey[$usersKeyMatches] = $keyObjects[$n]["session_key"];
                            $usersKeyMatches++;
                        }
                    }
                    if (!empty($usersKey)) {
                        $isAuthenticated = false;
                        foreach ($usersKey as $userKey) {
                            $inputMessage = $userKey;
                            $inputKey = Config::AES_INPUT_KEY;
                            $blockSize = '256';
                            $aes = new Aes($inputMessage, $inputKey, $blockSize);
                            $decrypt = $aes->decrypt();
                            $userKey = $decrypt;

                            // We verify the user's session
                            $isAuthenticated = false;
                            if (password_verify($session_key, $userKey)) {
                                if ($user['status'] == 'ACTIVE') {
                                    $isAuthenticated = true;
                                    $authSessionKey = $sessionKeys::findBy(["session_key" => $inputMessage])[0];
                                    $actualDate = new \dateTime('now', new \dateTimeZone('America/Los_Angeles'));
                                    $actualDateTime = strtotime($actualDate->format('Y-m-d H:i:s'));
                                    $expirationDateTime = strtotime($authSessionKey['expiration_at']);
                                    if (($expirationDateTime-$actualDateTime)<0) {
                                        // We generate the unique key for this session, since its actual session_key
                                        // has expired
                                        $isSessionKeyUnique = false;
                                        $allSessionKeys = $sessionKeys::findAll();
                                        while (!$isSessionKeyUnique) {
                                            $options = [
                                                'cost' => 13,
                                            ];
                                            $randomString = uniqid(mt_rand(0, 1000000));
                                            $randomStringHashed = password_hash($randomString, PASSWORD_BCRYPT, $options);
                                            $inputMessage = $randomStringHashed;
                                            $inputKey = Config::AES_INPUT_KEY;
                                            $blockSize = '256';
                                            $aes = new Aes($inputMessage, $inputKey, $blockSize);
                                            $encrypt = $aes->encrypt();
                                            $session_key = $encrypt;
                                            $isSessionKeyUnique = true;
                                            foreach ($allSessionKeys as $existingKey) {
                                                if ($session_key == $existingKey['session_key']) {
                                                    $isSessionKeyUnique = false;
                                                }
                                            }
                                        }
                                        $authSessionKey['session_key'] = $session_key;
                                        $actualDate = new \dateTime('now', new \dateTimeZone('America/Los_Angeles'));
                                        $actualDateTime = strtotime($actualDate->format('Y-m-d H:i:s'));
                                        $expirationDateTime = $actualDateTime + 1*60*60;
                                        $expirationDate = date('Y-m-d H:i:s', $expirationDateTime);
                                        $expirationDateObject = new \dateTime($expirationDate, new \dateTimeZone('America/Los_Angeles'));
                                        $authSessionKey['expiration_at'] = $expirationDateObject->format('Y-m-d H:i:s');
                                        $sessionKeys::persistAndFlush($authSessionKey);

                                        // We re-start the actual session but with the new session_key
                                        $inputMessage = $randomString;
                                        $inputKey = Config::AES_INPUT_KEY;
                                        $blockSize = '256';
                                        $aes = new Aes($inputMessage, $inputKey, $blockSize);
                                        $encrypt = $aes->encrypt();
                                        $sessionKeyEncripted = $encrypt;
                                        $_SESSION["key"] = $sessionKeyEncripted;
                                    }
                                    break;
                                }
                            }
                        }
                        if ($isAuthenticated) {
                            return $user;
                        } else {
//                            $hA = new CoreHackAttempt();
//                            $hA->isHackAttempt("controller", "getUser", $user_id);
                            throw new \Exception("Hack Attempt: \"getUser()\" method made a match with a user account that doesn't have an \"ACTIVE\" status. This happened with the following \"user_id\": ". $_SESSION["user"] . "and the following \"session_key\": ". "<pre>".htmlentities(print_r($usersKey, true))."</pre>");
                        }
                    } else {
                        //TODO: Instead of die'ing the program in here, try looking for an alternative and more proffessional way to do this withouth harming the integrity of the security of the server.
                        throw new \Exception("The server cannot authenticate a user on more than one device at the same time.");
                    }
                } else {
                    throw new \Exception("The server is having security issues to authenticate you on the device you are using.");
                }
            } else {
//                $hA = new CoreHackAttempt();
//                $hA->isHackAttempt("controller", "getUser", $user_id);
                throw new \Exception("Hack Attempt: \"getUser()\" method could't match a valid user account with the following \"user_id\": ". $_SESSION["user"] . "and the following \"session_key\": ". $_SESSION["key"]);
            }
        } else {
            return null;
        }
    }

    /**
     * Before filter - called before an action method.
     *
     * @return void
     */
    protected function before()
    {
    }

    /**
     * After filter - called after an action method.
     *
     * @return void
     */
    protected function after()
    {
    }
}