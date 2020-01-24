<?php

namespace Core;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;

/**
 * This class purpose is provide very detailed data about the visitor's characteristics.
 *
 * Class Visitors
 * @package Core
 *
 * @author Miranda Meza César
 * DATE November 20, 2018
 */
class Visitors
{
    /**
     * This method is used to get the name of the browser that the user is using. Once retrived, such
     * value is returned by this method.
     * If the code detects a bot-type-user, then it returns a boolean false.
     *
     * @return bool|string
     *
     * @author Miranda Meza César
     * DATE November 23, 2018
     */
    public function getUserBrowser()
    {
        //We retrieve the device system data of the user (for statistical purposes)
        DeviceParserAbstract::setVersionTruncation(DeviceParserAbstract::VERSION_TRUNCATION_NONE);
        $userAgent = $_SERVER['HTTP_USER_AGENT']; // change this to the useragent you want to parse
        $dd = new DeviceDetector($userAgent);
        $dd->parse();
        if ($dd->isBot()) {
            // handle bots,spiders,crawlers,...
            //botInfo = $dd->getBot();
            return false;
        } else {
            $clientInfo = $dd->getClient(); // holds information about browser, feed reader, media player, ...
            $browser = '';
            if ($clientInfo['type'] == 'browser') {
                $browser = $clientInfo['name'];
            }
            return $browser;
        }
    }
}
