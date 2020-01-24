<?php

namespace App\Controllers;

use App\Config;
use App\Models\SessionKeys;
use App\Models\Users;
use Core\Aes;
use Core\UserLocation;
use \Core\View;
use Core\Visitors;

/**
 * Home controller
 *
 * PHP version 5.4
 */
class Home extends \Core\Controller
{

    /**
     * Before filter
     *
     * @throws \Exception
     */
    protected function before()
    {
        session_start();

        // If user has an active session, then redirect to userlist page.
        if (isset($_SESSION["user"]) || isset($_SESSION["key"])) {
            //user is loged in
            if (Config::APP_ENV) {
                header("LOCATION: " . Config::APP_DEV_URL . Config::APP_USERLIST_URL);
            } else {
                header("LOCATION: " . Config::APP_PROD_URL . Config::APP_USERLIST_URL);
            }
        }

        // Call once getUser method just to make the user go through credentials validations and through
        // a hack-attempt validation aswell. If nothing goes wrong, then the user hasn't tried anything
        // he shoulnd't and he can proceed. Otherwise he won't be able to.
        $user = $this->getUser();

        // If user has a session, we then update the date of the last activity of the user and his
        // session_key if needed
        if (isset($_SESSION["user"]) && isset($_SESSION["key"])) {
            $actualDate = new \dateTime('now', new \dateTimeZone('America/Los_Angeles'));
            $user['last_activity_at'] = $actualDate->format('Y-m-d H:i:s');
            $users = new Users();
            $users::persistAndFlush($user);
        }
    }

    /**
     * After filter
     *
     * @return void
     */
    protected function after()
    {
    }

    /**
     * This method is in charge of receiving the data from the login form that the user submits to be able to log in.
     * Also, this method will validate that data and if any error occurs while its validation, a JSON will be send to
     * Javascript so that it reads it and displays the corresponding error message. But if everything goes ok, then the
     * user will be granted to start a session in the web site.
     * On the other hand, if the user happens to already be logged in, then he will be re-directed to the userlist page.
     * Note that both BCRYPT and AES-256 encryptions are used to settle a very fortified and secured session.
     *
     * RETURNS JsonResponse
     *
     * @author Miranda Meza César
     * DATE JANUARY 14, 2020
     */
    public function ajaxLoginAction()
    {
        // If a log in has been requested, then validate the data and if data is correct, start session. Else send error
        if (isset($_POST)) {
            $username = $_POST['usernameLoginInput'];
            $password = $_POST['passwordLoginInput'];
            $rememberMe = $_POST["rememberMeCheckbox"];

            $errors = '';
            // ----- User's first name sanitize and validation ----- //
            if (!empty($username)) {
                if (filter_var($username, FILTER_SANITIZE_STRING) != false) {
                    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
                } else {
                    $errors .= 'Please do not add tags or special characters to your username.' . '<br>';
                }
            } else {
                $errors .= 'Please enter your username.' . '<br>';
            }

            // ----- User's password sanitize and validation ----- //
            $password = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');
            if (empty($password)) {
                $errors .= 'Please enter your password.' . '<br>';
            }

            // ----- User's credentials validation ----- //
            if($errors == '') {
                $users = new Users();
                $accountsExists = $users->findBy(["username"=>$username]);
                // ----- We validate if the account's actual status (active/delete) or if it literally exists on the database ----- //
                $isAccountActivated = false;
                foreach ($accountsExists as $accountExists) {
                    if ($accountsExists==null || $accountExists["status"]=='DELETED') {
                        $isAccountActivated = false;
                    } else {
                        $isAccountActivated = true;
                        $user = $accountExists;
                        break;
                    }
                }
                // ----- If account exists and is active, validate credentials ----- //
                if ($isAccountActivated == true) {
                    if (!empty($user)) {
                        $databaseRegisteredPassword = $user["password"];
                        $inputMessage = $databaseRegisteredPassword;
                        $inputKey = Config::AES_INPUT_KEY;
                        $blockSize = '256';
                        $aes = new Aes($inputMessage, $inputKey, $blockSize);
                        $decrypt = $aes->decrypt();
                        if (!password_verify($password, $decrypt)) {
                            $errors .= '<li>The email and/or password that you entered are not correct</li>';
                        }
                    } else {
                        $errors .= '<li>The email and/or password that you entered are not correct</li>';
                    }
                } else {
                    $errors .= '<li>The email and/or password that you entered are not correct</li>';
                }
            }

            // if there aren't errors, then start a session for the user. Otherwise, send a JSON with the error(s)
            if ($errors == '') {
                // We generate a unique key for this session
                $isSessionKeyUnique = false;
                $sessionKeys = new SessionKeys();
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
                $sessionKey = $sessionKeys::findBy(["user_id"=>$user["id"]]);

                // We assign a new session key if none exists. Otherwise, we just update the old session key with the new one.
                $visitors = new Visitors();
                $browser = $visitors->getUserBrowser();
                if ($browser == false) {
                    $browser = '';
                }
                if ($sessionKey!=null) {
                    $sessionKeyIsMatch = false;
                    for ($sessionMatches=0; $sessionMatches<count($sessionKey); $sessionMatches++) {
                        if ($sessionKey[$sessionMatches]['browser'] == $browser) {
                            $sessionKey = $sessionKey[$sessionMatches];
                            $sessionKeyIsMatch = true;
                            break;
                        }
                    }
                    if ($sessionKeyIsMatch) {
                        $sessionKey['session_key'] = $session_key;
                    } else {
                        $sessionKey = [];
                        $sessionKey['id'] = null;
                        $sessionKey['user_id'] = $user["id"];
                        $sessionKey['session_key'] = $session_key;
                        $sessionKey['browser'] = $browser;
                    }
                } else {
                    $sessionKey['id'] = null;
                    $sessionKey['user_id'] = $user["id"];
                    $sessionKey['session_key'] = $session_key;
                    $sessionKey['browser'] = $browser;
                }

                // We save the current IP address of the user and we define an expiration time for the currently generated session Key
                $uL = new UserLocation();
                $sessionKey['ip'] = $uL->getUserRealIpAddress();
                $actualDate = new \dateTime('now', new \dateTimeZone('America/Los_Angeles'));
                $actualDateTime = strtotime($actualDate->format('Y-m-d H:i:s'));
                $expirationDateTime = $actualDateTime + 1*60*60;
                $expirationDate = date('Y-m-d H:i:s', $expirationDateTime);
                $expirationDateObject = new \dateTime($expirationDate, new \dateTimeZone('America/Los_Angeles'));
                $sessionKey['expiration_at'] = $expirationDateObject->format('Y-m-d H:i:s');
                $sessionKeys::persistAndFlush($sessionKey);

                // We define the date of this last session start and we update the database with this new data
                $actualDate = new \dateTime('now', new \dateTimeZone('America/Los_Angeles'));
                $user["connected_at"] = $actualDate->format('Y-m-d H:i:s');
                $users::persistAndFlush($user);

                // We start the session
                $inputMessage = $user["id"];
                $inputKey = Config::AES_INPUT_KEY;
                $blockSize = '256';
                $aes = new Aes($inputMessage, $inputKey, $blockSize);
                $encrypt = $aes->encrypt();
                $user_id = $encrypt;
                $_SESSION['user'] = $user_id; // user id
                $inputMessage = $randomString;
                $inputKey = Config::AES_INPUT_KEY;
                $blockSize = '256';
                $aes = new Aes($inputMessage, $inputKey, $blockSize);
                $encrypt = $aes->encrypt();
                $sessionKeyEncripted = $encrypt;
                $_SESSION["key"] = $sessionKeyEncripted; // session key

                // We Create a cookie on the user's device with his credentials only if he checked the "remember me" box
                $rememberMe = filter_var($rememberMe, FILTER_SANITIZE_STRING);
                $rememberMe = htmlspecialchars($rememberMe, ENT_QUOTES, 'UTF-8');
                if ($rememberMe == "true") {
                    // "1K0u3C6l5e_615m3U0d1" will hold the encrypted username
                    if (isset($_COOKIE["1K0u3C6l5e_615m3U0d1"])) {
                        setcookie("1K0u3C6l5e_615m3U0d1",'',time()- (365 * 24 * 60 * 60), '/', '', 0);
                    }
                    // "PL1m0U3d516_615K3u0C1lp" cookie will hold the encrypted password
                    if (isset($_COOKIE["PL1m0U3d516_615K3u0C1lp"])) {
                        setcookie("PL1m0U3d516_615K3u0C1lp",'',time()- (365 * 24 * 60 * 60), '/', '', 0);
                    }
                    $inputMessage = $username;
                    $inputKey = Config::AES_INPUT_KEY;
                    $blockSize = '256';
                    $aes = new Aes($inputMessage, $inputKey, $blockSize);
                    $encrypt = $aes->encrypt();
                    setcookie("1K0u3C6l5e_615m3U0d1",$encrypt,time()+ (365 * 24 * 60 * 60), '/', '', 0);
                    $inputMessage = $password;
                    $inputKey = Config::AES_INPUT_KEY;
                    $blockSize = '256';
                    $aes = new Aes($inputMessage, $inputKey, $blockSize);
                    $encrypt = $aes->encrypt();
                    setcookie("PL1m0U3d516_615K3u0C1lp",$encrypt,time()+ (365 * 24 * 60 * 60), '/', '', 0);
                } elseif ($rememberMe == "false") {
                    if (isset($_COOKIE["1K0u3C6l5e_615m3U0d1"])) {
                        setcookie("1K0u3C6l5e_615m3U0d1",'',time()- (365 * 24 * 60 * 60), '/', '', 0);
                    }
                    if (isset($_COOKIE["PL1m0U3d516_615K3u0C1lp"])) {
                        setcookie("PL1m0U3d516_615K3u0C1lp",'',time()- (365 * 24 * 60 * 60), '/', '', 0);
                    }
                }

                // Send JSON response
                $message = "You have successfully logged in.";
                echo json_encode([
                    'status' => 202, //202=Accepted
                    'message' => $message,
                    'data' => []
                ]);
            } else {
                echo json_encode([
                    'status' => 406, //406=Not Acceptable
                    'message' => $errors,
                    'data' => []
                ]);
            }
        }
    }

    /**
     * This method is in charge of receiving the register data inputed by the users that filled the register form modal.
     * The data is sended by Javascript to this method and, after receiving such data, this method sanitizes and
     * validates the data. If there are some errors during that process, an error message will be send to Javascript
     * to inform what went wrong. But if the validation and sanitize were successful, then the data that was received
     * from the register modal is inserted into the database on "users" table.
     * Note that both BCRYPT and AES-256 encryptions are used to settle a very fortified and secured session.
     *
     * RETURNS JsonResponse
     *
     * @author Miranda Meza César
     * DATE JANUARY 13, 2020
     */
    public function ajaxRegisterAction()
    {
        $errors = '';
        // ----------------------------------------------------- //
        // ----- SANITIZE AND VALIDATION OF REGISTER FORM  ----- //
        // ----------------------------------------------------- //
        if (isset($_POST)) {
            $username = $_POST['usernameRegisterInput'];
            $firstName = $_POST['firstNameRegisterInput'];
            $lastName = $_POST['lastNameRegisterInput'];
            $password1 = htmlspecialchars($_POST['passwordRegisterInput'], ENT_QUOTES, 'UTF-8');
            $password2 = htmlspecialchars($_POST['repeatPasswordRegisterInput'], ENT_QUOTES, 'UTF-8');

            // ----- User's first name sanitize and validation ----- //
            if (!empty($username)) {
                if (filter_var($username, FILTER_SANITIZE_STRING) != false) {
                    $username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
                    $users = new Users();
                    $accountsExists = $users->findBy(["username" => $username]);
                    // We validate if the account, that wants to be activated, is already an existing account //
                    $isAccountActivated = false;
                    foreach ($accountsExists as $accountExists) {
                        if ($accountsExists==null || $accountExists["status"]=='DELETED') {
                            $isAccountActivated = false;
                        } else {
                            $isAccountActivated = true;
                            break;
                        }
                    }
                    // If validation determined that the request of the account to register already exists as an
                    // ACTIVE account, then send error
                    if ($isAccountActivated == true) {
                        $errors .= 'The username that you used has already been registered.' . '<br>';
                    }
                } else {
                    $errors .= 'Please do not add tags or special characters to your username.' . '<br>';
                }
            } else {
                $errors .= 'Please enter your username.' . '<br>';
            }

            // ----- User's first name sanitize and validation ----- //
            if (!empty($firstName)) {
                $firstName = filter_var($firstName, FILTER_SANITIZE_STRING);
                $firstName = htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8');
                $firstName = strtolower($firstName);
                $firstName = str_replace(' ', ' ', ucwords(str_replace('-', ' ', $firstName)));
            } else {
                $errors .= 'Please enter your name(s)' . '<br>';
            }

            // ----- User's last name sanitize and validation ----- //
            if (!empty($lastName)) {
                $lastName = filter_var($lastName, FILTER_SANITIZE_STRING);
                $lastName = htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8');
                $lastName = strtolower($lastName);
                $lastName = str_replace(' ', ' ', ucwords(str_replace('-', ' ', $lastName)));
            } else {
                $errors .= 'Please enter your Last name.' . '<br>';
            }

            // ----- User's password sanitize and validation ----- //
            if (!empty($password1) && !empty($password2)) {
                if ($password1 === $password2) {
                    if (strlen($password1)>5) {
                        preg_match_all("/[0-9]/", $password1, $numberMatches);
                        preg_match_all("/[a-z A-Z]/", $password1, $characterMatches);
                        if ($numberMatches[0]!='' && $numberMatches[0]!=null && $characterMatches[0]!='' && $characterMatches[0]!=null) {
                            $options = [
                                'cost' => 13,
                            ];
                            $password = password_hash($password1, PASSWORD_BCRYPT, $options);
                            $inputMessage = $password;
                            $inputKey = Config::AES_INPUT_KEY;
                            $blockSize = '256';
                            $aes = new Aes($inputMessage, $inputKey, $blockSize);
                            $password = $aes->encrypt();
                        } else {
                            $errors .= 'Your password must have at least one character of the alphabet and at least one numeric character.' . '<br>';
                        }
                    } else {
                        $errors .= 'Your password must have at least 6 characters.' . '<br>';
                    }
                } else {
                    $errors .= 'The passwords that you entered are not identical.' . '<br>';
                }
            } else {
                $errors .= 'Please enter your password in both boxes.' . '<br>';
            }

            // -------------------------------------------------------------------------------- //
            // ----- Server response with regard of the Register Modal Validation process ----- //
            // -------------------------------------------------------------------------------- //
            if ($errors === '') {
                $actualDate = new \dateTime('now', new \dateTimeZone('America/Los_Angeles'));

                // ----- We save the record of the registry of this new account ----- //
                $users = new Users();
                $users->setUsername($username);
                $users->setFirstName($firstName);
                $users->setLastName($lastName);
                $users->setRole("user");
                $users->setPassword($password);
                $users->setStatus("ACTIVE"); //ACTIVE=this is an existing active record //DELETED=this is no longer an active record because it has been requested to be deleted
                $users->setCreatedBy("server api");
                $users->setCreatedAt($actualDate->format('Y-m-d H:i:s'));
                $users::persistAndFlush($users);

                // ----- We send the status results to javascript in JSON ----- //
                $message = "Successful Account Registration!";
                echo json_encode([
                    'status' => 201, //201 = Created
                    'message' => $message,
                    'data' => []
                ]);
            } else {
                echo json_encode([
                    'status' => 400, //400=Bad Request
                    'message' => $errors,
                    'data' => []
                ]);
            }
        }
    }

    /**
     * Show the Home page (Login page) of the entire website.
     *
     * @throws \Exception
     *
     * @author Miranda Meza César
     * DATE JANUARY 14, 2020
     */
    public function indexAction()
    {
        // ----- Retrieve and decrypt the email and password of the user if he used the "remember me" checkbox ----- //
        // "1K0u3C6l5e_615m3U0d1" cookie will hold the encrypted username
        // "PL1m0U3d516_615K3u0C1lp" cookie will hold the encrypted password
        if (isset($_COOKIE["1K0u3C6l5e_615m3U0d1"]) && isset($_COOKIE["PL1m0U3d516_615K3u0C1lp"])) {
            $username = htmlspecialchars($_COOKIE['1K0u3C6l5e_615m3U0d1'], ENT_QUOTES, 'UTF-8');
            $password = htmlspecialchars($_COOKIE['PL1m0U3d516_615K3u0C1lp']);
            setcookie("1K0u3C6l5e_615m3U0d1",'',time()- (365 * 24 * 60 * 60), '/', '', 0);
            setcookie("PL1m0U3d516_615K3u0C1lp",'',time()- (365 * 24 * 60 * 60), '/', '', 0);
            setcookie("1K0u3C6l5e_615m3U0d1",$username,time()+ (365 * 24 * 60 * 60), '/', '', 0);
            setcookie("PL1m0U3d516_615K3u0C1lp",$password,time()+ (365 * 24 * 60 * 60), '/', '', 0);
            // We decrypt the user's username contained on the cookie
            $inputMessage = $username;
            $inputKey = Config::AES_INPUT_KEY;
            $blockSize = '256';
            $aes = new Aes($inputMessage, $inputKey, $blockSize);
            $decrypt = $aes->decrypt();
            $username = $decrypt;

            // We decrypt the user's password contained on the cookie
            $inputMessage = $password;
            $inputKey = Config::AES_INPUT_KEY;
            $blockSize = '256';
            $aes = new Aes($inputMessage, $inputKey, $blockSize);
            $decrypt = $aes->decrypt();
            $password = $decrypt;
        } else {
            $username= "";
            $password = "";
        }
        View::renderTemplate('Login/index.html.twig', [
            'username'    => $username,
            'password' => $password
        ]);
    }
}
