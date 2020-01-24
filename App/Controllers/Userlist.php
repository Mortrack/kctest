<?php

namespace App\Controllers;

use App\Config;
use App\Models\Students;
use App\Models\Users;
use \Core\View;

/**
 * Home controller
 *
 * PHP version 5.4
 */
class Userlist extends \Core\Controller
{

    /**
     * Before filter
     *
     * @throws \Exception
     */
    protected function before()
    {
        session_start();

        // Call once getUser method just to make the user go through credentials validations and through
        // a hack-attempt validation aswell. If nothing goes wrong, then the user hasn't tried anything
        // he shoulnd't and he can proceed. Otherwise he won't be able to.
        $user = $this->getUser();

        // If user has a session, we then update the date of the last activity of the user and his
        // session_key if needed.
        // OTHERWISE: redirect him to the login page.
        if (isset($_SESSION["user"]) && isset($_SESSION["key"])) {
            $actualDate = new \dateTime('now', new \dateTimeZone('America/Los_Angeles'));
            $user['last_activity_at'] = $actualDate->format('Y-m-d H:i:s');
            $users = new Users();
            $users::persistAndFlush($user);
        } else {
            // If user doesnt have an active session, then redirect to login page.
            if (Config::APP_ENV) {
                header("LOCATION: " . Config::APP_DEV_URL);
            } else {
                header("LOCATION: " . Config::APP_PROD_URL);
            }
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
     * This method is in charge of retrieving all the data to be used for the Userlist table, from the database. Then,
     * that data will be send back to javascript so that there it can be used to update the data of the Userlist table
     * in the front-end.
     *
     * RETURNS JsonResponse
     *
     * @author Miranda Meza César
     * DATE JANUARY 14, 2020
     */
    public function ajaxGetUserlistTableDataAction()
    {
        $students = new Students();
        $allRegisteredStudents = $students::findAll();

        // We retrieve the basic information of all the active user accounts registered in the database
        $allStudentsBasicInformation = [];
        $currentStudentsCount = 0;
        foreach ($allRegisteredStudents as $registeredStudent) {
            $currentActiveUser = [];
            $currentActiveUser["username"] = $registeredStudent["username"];
            $currentActiveUser["first_name"] = $registeredStudent["first_name"];
            $currentActiveUser["last_name"] = $registeredStudent["last_name"];
            $allStudentsBasicInformation[$currentStudentsCount] = $currentActiveUser;
            $currentStudentsCount++;
        }

        // if there aren't errors, then send the data to javascript. Otherwise, send an error message.
        if ($allStudentsBasicInformation != null) {
            // Send JSON response
            $data = [];
            $data[0] = count($allStudentsBasicInformation); // number of identified user accounts
            $data[1] = $allStudentsBasicInformation; // all user accounts basic information
            echo json_encode([
                'status' => 202, //202=Accepted
                'message' => 'Data for Userlist table was obtained successfully',
                'data' => $data
            ]);
        } else {
            echo json_encode([
                'status' => 406, //406=Not Acceptable
                'message' => 'No user accounts were detected in the database',
                'data' => []
            ]);
        }
    }

    /**
     * This method is in charge of ending a session started by the user and, afterwords, it will redirect the user to
     * the Login page of the website.
     *
     * REDIRECTS to Login URL
     *
     * @author Miranda Meza César
     * DATE JANUARY 14, 2020
     */
    public function logoutAction()
    {
        if (isset($_SESSION["user"]) && isset($_SESSION["key"])) {
            //Save a record, on the database, of the time when the user logged out of his account
            $actualDate = new \dateTime('now', new \dateTimeZone('America/Los_Angeles'));
            $user = $this->getUser();
            $users = new Users();
            $user["disconnected_at"] = $actualDate->format('Y-m-d H:i:s');
            $users::persistAndFlush($user);

            //"Destroy" (end) the session of the user
            unset($_SESSION['user']);
            unset($_SESSION['key']);
            $_SESSION = array();
            session_destroy();
        }
        // Now that the session has been destroyed, redirect the user to the login web page
        if (Config::APP_ENV) {
            header("LOCATION: " . Config::APP_DEV_URL);
        } else {
            header("LOCATION: " . Config::APP_PROD_URL);
        }
    }

    /**
     * Show the Home page of the entire website.
     *
     * @throws \Exception
     *
     * @author Miranda Meza César
     * DATE JANUARY 14, 2020
     */
    public function indexAction()
    {
        $user = $this->getUser();
        if ($user != null) {
            $user_firstname = $user["first_name"];
        } else {
            $user_firstname = "";
        }
        View::renderTemplate('Userlist/index.html.twig', [
            'isUserLoggedIn' => (isset($_SESSION["user"]) || isset($_SESSION["key"])),
            'user_firstname' => $user_firstname
        ]);
    }
}
