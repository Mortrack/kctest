<?php

namespace App;

/**
 * Application configuration
 *
 * PHP version 5.4
 */
class Config
{
    /**
     * Development base root
     * @var string
     */
    const APP_DEV_URL = 'http://localhost/';

    /**
     * Production base root
     * @var string
     */
    const APP_PROD_URL = '';

    /**
     * Development base root
     * @var string
     */
    const APP_USERLIST_URL = 'Userlist/index';

    /**
     * Database host
     * @var string
     */
    const DB_HOST = '127.0.0.1';

    /**
     * Database name
     * @var string
     */
    const DB_NAME = 'kctest';

    /**
     * Database user
     * @var string
     */
    const DB_USERNAME = 'root';

    /**
     * Database password
     * @var string
     */
    const DB_PASSWORD = '';

    /**
     * AES Encryption IV key
     * @var string
     */
    const AES_IV = '3494670502353630';

    /**
     * AES Encryption Input Key
     * @var string
     */
    const AES_INPUT_KEY = '8545305693022529';

    /**
     * Show or hide detailed error messages on web page
     * TRUE = will show detailed errors on web and wont save them on log file (Development)
     * FALSE = will show friendly errors to users and detailed errors to devs through log file (Production)
     * @var boolean
     */
    const APP_ENV = true;
}
