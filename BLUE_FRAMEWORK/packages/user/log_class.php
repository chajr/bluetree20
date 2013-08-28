<?php
/**
 * allow to log on, log off and check that user is logged on
 *
 * @category    BlueFramework
 * @package     user
 * @subpackage  benchmark
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.1.0
 * 
 * @todo add session id to code
 * @todo handling of multi hashing password and code
 */
class log_class {
    public static function logOn($uid, $opcje, $grupa)
    {
        $kod                            = self::kod();
        $_SESSION['log_class']['log']   = TRUE;
        $_SESSION['log_class']['uid']   = $uid;
        $_SESSION['log_class']['kod']   = $kod;
        $_SESSION['log_class']['opcje'] = $opcje;
        $_SESSION['log_class']['grupa'] = $grupa;
        $_SESSION['log_class']['czas']  = time() + 60*60;
    }

    public static function logOff()
    {
        $_SESSION['log_class'] = array();
        unset($_SESSION['log_class']);
    }

    public static function verifyUser()
    {
        if (!isset($_SESSION['log_class']['log']) ||
            !isset($_SESSION['log_class']['uid']) ||
            !isset($_SESSION['log_class']['kod']) ||
            !isset($_SESSION['log_class']['opcje']) ||
            !isset($_SESSION['log_class']['grupa']) ||
            !isset($_SESSION['log_class']['czas']) ||
            !$_SESSION['log_class']['log']) {

            return FALSE;
        } else {
            if ($_SESSION['log_class']['kod'] == self::code()) {
                if ($_SESSION['log_class']['opcje']{0} == '0') {
                    throw new LibraryException('no_reg');
                }

                if ($_SESSION['log_class']['opcje']{1} == '0') {
                    throw new LibraryException('blocked');
                }

                if ($_SESSION['log_class']['czas'] < time()) {
                    return FALSE;
                }

                $_SESSION['log_class']['czas'] = time() + 60*60;
                @session_regenerate_id();
                $_SESSION['log_class']['kod'] = self::code();

                return TRUE;
            } else {
                return FALSE;
            }
        }
    }

    private static function code()
    {
        $client     = $_SERVER['HTTP_USER_AGENT'];
        $ip         = $_SERVER['REMOTE_ADDR'];
        $kod        = hash(
            'sha256',
            $client.
                $ip
        );

        return $kod;
    }
}
