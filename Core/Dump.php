<?php

namespace Core;

/**
 * Pretty Var dump for development applications only.
 * MAKE SURE YOU COMPLETELY REMOVE THIS CLASS ON PRODUCTION MODE.
 *
 * Class Ip
 * @package Core
 *
 * @author Miranda Meza César
 * DATE November 04, 2018
 */
class Dump
{

    /**
     * @param $data
     *
     * CODE EXAMPLE:
     * return new \Core\Dump($_POST);
     *
     * @author Miranda Meza César
     * DATE November 04, 2018
     */
    public function __construct($data)
    {
        var_dump("<pre>".htmlentities(print_r($data, true))."</pre>");
    }
}
