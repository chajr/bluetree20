<?php
/**
 * allow to log on, log off and check that user is logged on
 *
 * @category    BlueFramework
 * @package     user
 * @subpackage  benchmark
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.1.1
 * 
 * @todo add session id to code session_id();
 * @todo handling of multi hashing password and code
 */
class log_class
{
    /**
     * set user as logged in, and save his option in session
     * @param integer $uid
     * @param string $options user options in eg format 00101100
     * @param string $group
     */
    public static function logOn($uid, $options, $group)
    {
        $code                             = self::_code();
        $_SESSION['log_class']['log']     = TRUE;
        $_SESSION['log_class']['uid']     = $uid;
        $_SESSION['log_class']['code']    = $code;
        $_SESSION['log_class']['options'] = $options;
        $_SESSION['log_class']['group']   = $group;
        $_SESSION['log_class']['time']    = time() + 60*60;
    }

    /**
     * destroy user information in session
     */
    public static function logOff()
    {
        $_SESSION['log_class'] = array();
        unset($_SESSION['log_class']);
    }

    /**
     * check that user is logged in
     * 
     * @return bool
     * @throws LibraryException
     */
    public static function verifyUser()
    {
        if (   !isset($_SESSION['log_class']['log'])
            || !isset($_SESSION['log_class']['uid'])
            || !isset($_SESSION['log_class']['code'])
            || !isset($_SESSION['log_class']['options'])
            || !isset($_SESSION['log_class']['group'])
            || !isset($_SESSION['log_class']['time'])
            || !$_SESSION['log_class']['log']
        ){
            return FALSE;
        } else {
            if ($_SESSION['log_class']['code'] === self::_code()) {
                if ($_SESSION['log_class']['options']{0} === '0') {
                    throw new LibraryException('no_reg');
                }

                if ($_SESSION['log_class']['options']{1} === '0') {
                    throw new LibraryException('blocked');
                }

                if ($_SESSION['log_class']['time'] < time()) {
                    return FALSE;
                }

                $_SESSION['log_class']['time'] = time() + 60*60;
                @session_regenerate_id();
                $_SESSION['log_class']['code'] = self::_code();

                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

    /**
     * return special code to verify user
     * 
     * @return string
     */
    protected static function _code()
    {
        $client     = $_SERVER['HTTP_USER_AGENT'];
        $ip         = $_SERVER['REMOTE_ADDR'];
        $code        = hash(
            'sha256',
            $client . $ip
        );

        return $code;
    }
}
