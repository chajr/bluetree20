<?php
/**
 * return information about user
 *
 * @category    BlueFramework
 * @package     user
 * @subpackage  benchmark
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.1.0
 */
class clientData_class
{
    /**
     * return user IP address
     * 
     * @return string
    */
    static function ip()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * return name and browser version
     * 
     * @return array
    */
    static function browser()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];

        if (strstr($agent, 'MSIE 8.0')) {
            return 'IE8';
        }

        if (strstr($agent, 'MSIE 7.0')) {
            return 'IE7';
        }

        if (strstr($agent, 'MSIE 6.0')) {
            return 'IE6';
        }

        $bool = preg_match(
            '#(Safari|Opera|Firefox|Chrome)/[\\d\.]*#',
            $agent,
            $array
        );

        if ($bool) {
            return $array[0];
        } else {
            return $agent;
        }
    }
}
