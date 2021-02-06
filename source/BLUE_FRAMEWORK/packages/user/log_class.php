<?php
/**
 * allow to log on, log off and check that user is logged on
 *
 * @category    BlueFramework
 * @package     user
 * @subpackage  benchmark
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.2.0
 */
class log_class
{
    /**
     * name for $_SESSION global array model
     */
    const SESSION_ARRAY = 'session_array';

    /**
     * name for session class model
     */
    const SESSION_CLASS = 'session_class';

    /**
     * contains session model
     * @var array|session
     */
    protected static $_sessionModel;

    /**
     * set in variable correct session model
     * allowed is $_SESSION global array or session object
     * 
     * @param array|session $model
     */
    public static function setSessionModel(&$model)
    {
        self::$_sessionModel = $model;
    }

    /**
     * return variable value from correct session model
     * 
     * @param string $varName
     * @return mixed
     */
    public static function getSessionVar($varName)
    {
        if (self::_checkModelType() === self::SESSION_ARRAY) {
            if (isset(self::$_sessionModel[$varName])) {
                return self::$_sessionModel[$varName];
            }
        }

        if (self::_checkModelType() === self::SESSION_CLASS) {
            $userData = self::$_sessionModel->returns('user');

            if (isset($userData[$varName])) {
                return $userData[$varName];
            }
        }

        return FALSE;
    }

    /**
     * set a variable in correct session model
     * 
     * @param string $varName
     * @param mixed $varValue
     * @return bool
     */
    public static function setSessionVar($varName, $varValue)
    {
        if (self::_checkModelType() === self::SESSION_ARRAY) {
            self::$_sessionModel[$varName] = $varValue;
            return TRUE;
        }

        if (self::_checkModelType() === self::SESSION_CLASS) {
            self::$_sessionModel->set($varName, $varValue, 'user');
            return TRUE;
        }

        return FALSE;
    }

    /**
     * check given model type and return its name or false if incorrect model
     * 
     * @return bool|string
     */
    protected static function _checkModelType()
    {
        if (is_array(self::$_sessionModel)) {
            return self::SESSION_ARRAY;
        }

        if (self::$_sessionModel instanceof session) {
            return self::SESSION_CLASS;
        }

        return FALSE;
    }

    /**
     * set user as logged in, and save his option in session
     * @param integer $uid
     * @param string $options user options in eg format 00101100
     * @param string $group
     */
    public static function logOn($uid, $options, $group)
    {
        if (!self::getSessionVar('log_class_session_id')) {
            self::setSessionVar('log_class_session_id', session_id());
        }
        $code                             = self::_code();

        self::setSessionVar('log_class_log', TRUE);
        self::setSessionVar('log_class_uid', $uid);
        self::setSessionVar('log_class_code', $code);
        self::setSessionVar('log_class_options', $options);
        self::setSessionVar('log_class_group', $group);
        self::setSessionVar('log_class_time', time() + 60*60);
    }

    /**
     * destroy user information in session
     */
    public static function logOff()
    {
        if (self::_checkModelType() === self::SESSION_ARRAY) {
            unset(self::$_sessionModel['log_class_log']);
            unset(self::$_sessionModel['log_class_uid']);
            unset(self::$_sessionModel['log_class_code']);
            unset(self::$_sessionModel['log_class_options']);
            unset(self::$_sessionModel['log_class_group']);
            unset(self::$_sessionModel['log_class_time']);
            unset(self::$_sessionModel['log_class_session_id']);
        }

        if (self::_checkModelType() === self::SESSION_CLASS) {
            self::$_sessionModel->clear('user');
        }
    }

    /**
     * check that user is logged in
     * 
     * @return bool
     * @throws LibraryException
     */
    public static function verifyUser()
    {
        if (   !self::getSessionVar('log_class_log')
            || !self::getSessionVar('log_class_uid')
            || !self::getSessionVar('log_class_code')
            || !self::getSessionVar('log_class_options')
            || !self::getSessionVar('log_class_group')
            || !self::getSessionVar('log_class_time')
        ){
            return FALSE;

        } else {
            if (self::getSessionVar('log_class_code') === self::_code()) {
                $options = self::getSessionVar('log_class_options');

                if ($options{0} === '0') {
                    throw new LibraryException('no_reg');
                }

                if ($options{1} === '0') {
                    throw new LibraryException('blocked');
                }

                if (self::getSessionVar('log_class_time') < time()) {
                    return FALSE;
                }

                @session_regenerate_id();
                self::setSessionVar('log_class_time', time() + 60 * 60);
                self::setSessionVar('log_class_session_id', session_id());
                self::setSessionVar('log_class_code', self::_code());

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
        $sessionId   = self::getSessionVar('session_id');
        $client      = $_SERVER['HTTP_USER_AGENT'];
        $ip          = $_SERVER['REMOTE_ADDR'];
        $language    = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

        $code        = hash(
            'sha256',
            $client . $ip . $sessionId . $language
        );

        return $code;
    }
}
