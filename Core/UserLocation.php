<?php

namespace Core;

/**
 * This class is used to obtain every information related to the user's location when visiting our website.
 *
 * Class Ip
 * @package Core
 *
 * @author Miranda Meza César
 * DATE September 22, 2018
 */
class UserLocation
{

    /**
     * This method is in charge of returning, on a string, the real ip address of the user.
     * This is very helpful when banning ip's since this way we make a definite/absolute ban,
     * but it's useless for geo-ip-localization.
     *
     * @return mixed
     *
     * @author Miranda Meza César
     * DATE September 22, 2018
     */
    public function getUserRealIpAddress()
    {
        if(isset($_SERVER['HTTP_X_SUCURI_CLIENTIP']))
        {
            return $_SERVER["REMOTE_ADDR"] = $_SERVER['HTTP_X_SUCURI_CLIENTIP'];
        }
        /*
        elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            //check ip from share internet
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            //to check ip is pass from proxy
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        */
        else {
            return (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR']: '');
        }
    }
}
