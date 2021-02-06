<?php
/**
 * @category    BlueFramework
 * @package     BlueFramework Core
 * @subpackage  error
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     2.3.1
 */

 /**
 * main class for all exceptions
 */
abstract class exception_class 
    extends Exception
{
    /**
     * contains error code
     * @var string
     */
    protected $_errorCode;

    /**
     * contain some other error information
     * @var string
     */
    protected $_errorMessage;

    /**
     * contain path and name of file with error
     * @var string
     */
    protected $_errorFile;

    /**
     * contains line with error
     * @var integer
     */
    protected $_errorLine;

    /**
     * contain integer error code
     * @var integer
     */
    protected $_integerCode;

    /**
     * contain optional module name that report error
     * @var string 
     */
    protected $_moduleName = '';

    /**
     * get messages and write them to variables
     * 
     * @param string $code
     * @param string $message
     * @param string $mod
    */
    public function __construct($code, $message = '', $mod = '')
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'create exception class',
                debug_backtrace(),
                '#4E0000'
            ));
        }

        $this->_errorCode     = $code;
        $this->_errorMessage  = $message;
        $this->_errorFile     = $this->getFile();
        $this->_errorLine     = $this->getLine();
        $this->_integerCode   = $this->getCode();

        if (!$mod) {
            $mod = 'core';
        }

        $this->_moduleName = $mod;
    }

    /**
     * add error to error list
     *
     * @param $error error_class
     * @param $moduleName string
     * @abstract
     */
    abstract public function show(error_class $error, $moduleName = '');

    /**
     * zwraca tablice informacji o bledzie
     * @return array tablica informacji o bledzie 
     */
    public function returns()
    {
        return array(
            $this->_errorCode,
            $this->_errorMessage,
            $this->_errorFile,
            $this->_errorLine,
            $this->_integerCode,
            $this->_moduleName,
        );
    }
}

/**
 * handle framework exceptions, stops whole framework and display an error
 */
class coreException 
    extends exception_class
{
    /**
     * add error to error list, checking module that call error
     * 
     * @param $error error_class
     * @param $moduleName
    */
    public function show(error_class $error, $moduleName = '')
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'show error',
                debug_backtrace(),
                '#4E0000'
            ));
        }

        if ($moduleName) {
            $this->_moduleName = $moduleName;
        }
        
        $formatError = $error->addError(
            'critic',
            $this->_integerCode,
            $this->_errorCode,
            $this->_errorLine,
            $this->_errorFile,
            $this->_errorMessage,
            $this->_moduleName
        );
        $bool = core_class::options('errors_log');
        
        if ((bool)$bool{0}) {
            error_class::log('critic_coreException', $formatError);
        }
    }

    /**
     * show core error, stops framework and display error
     */
    public function showCore()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'show core error',
                debug_backtrace(),
                '#4E0000'
            ));
        }

        $error        = new error_class();
        $formatError  = $error->addError(
            'critic',
            $this->_integerCode,
            $this->_errorCode,
            $this->_errorLine,
            $this->_errorFile,
            $this->_errorMessage,
            $this->_moduleName
        );
        $bool = core_class::options('errors_log');

        if ((bool)$bool{0}) {
            error_class::log('critic_coreException', $formatError);
        }

        echo $error->render();
        exit;
    }
}

/**
 * class for handling module exceptions
 * stops current module, allow working of other modules
 */
class modException 
    extends exception_class
{
    /**
     * add error to errors list, check module that throw error
     * 
     * @param $error error_class
     * @param $moduleName string
     */
    public function show(error_class $error, $moduleName = '')
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'show mod error',
                debug_backtrace(),
                '#4E0000'
            ));
        }

        if ($moduleName) {
            $this->_moduleName = $moduleName;
        }

        $formatError = $error->addError(
            'critic',
            $this->_integerCode,
            $this->_errorCode,
            $this->_errorLine,
            $this->_errorFile,
            $this->_errorMessage,
            $this->_moduleName
        );
        $bool = core_class::options('errors_log');

        if ((bool)$bool{0}) {
            error_class::log('critic_modException', $formatError);
        }
    }
}

/**
 * class for handling libraries exceptions
 * stops current library, allow working of other libraries and modules
 */
class libException 
    extends exception_class
{
    /**
     * add error to errors list, check module that throw error
     * 
     * @param $error error_class
     * @param $moduleName string
     */
    public function show(error_class $error, $moduleName = '')
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'show lib error',
                debug_backtrace(),
                '#4E0000'
            ));
        }

        if ($moduleName) {
            $this->_moduleName = $moduleName;
        }

        $formatError = $error->addError(
            'critic',
            $this->_integerCode,
            $this->_errorCode,
            $this->_errorLine,
            $this->_errorFile,
            $this->_errorMessage,
            $this->_moduleName
        );
        $bool = core_class::options('errors_log');

        if ((bool)$bool{0}) {
            error_class::log('critic_libException', $formatError);
        }
    }
}

/**
 * class for handling warning exceptions
 */
class warningException 
    extends exception_class
{
    /**
     * add warning to errors list, check module that throw error
     * @param $error error_class
     * @param $moduleName string
     */
    public function show(error_class $error, $moduleName = '')
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'show warning',
                debug_backtrace(),
                '#4E0000'
            ));
        }

        $formatError = $error->addError(
            'warning',
            $this->_integerCode,
            $this->_errorCode,
            $this->_errorLine,
            $this->_errorFile,
            $this->_errorMessage,
            $moduleName
        );
        $bool = core_class::options('errors_log');

        if ((bool)$bool{1}) {
            error_class::log('warning', $formatError);
        }
    }
}

/**
 * class for handling info exceptions
 */
class infoException 
    extends exception_class
{
    /**
     * add information to list
     * @param $error error_class
     * @param $moduleName string
     */
    public function show(error_class $error, $moduleName = '')
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'show info',
                debug_backtrace(),
                '#4E0000'
            ));
        }

        $error->addError(
            'info',
            $this->_integerCode,
            $this->_errorCode,
            $this->_errorLine,
            $this->_errorFile,
            $this->_errorMessage,
            $moduleName
        );
    }
}

/**
 * class for handling ok exceptions
 */
class okException 
    extends exception_class
{
    /**
     * add information to list
     * @param $error error_class
     * @param $moduleName string
     */
    public function show(error_class $error, $moduleName = '')
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'show ok',
                debug_backtrace(),
                '#4E0000'
            ));
        }

        $error->addError(
            'ok',
            $this->_integerCode,
            $this->_errorCode,
            $this->_errorLine,
            $this->_errorFile,
            $this->_errorMessage,
            $moduleName
        );
    }
}

/**
 * class for handling package exceptions
 */
class packageException 
    extends exception_class
{
    /**
     * obluga bledu
     * @param $error error_class
     * @param $moduleName string
     */
    public function show(error_class $error, $moduleName = '')
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'show package error',
                debug_backtrace(),
                '#4E0000'
            ));
        }

        if ($moduleName) {
            $this->_moduleName = $moduleName;
        }

        $formatError = $error->addError(
            'critic',
            $this->_integerCode,
            $this->_errorCode,
            $this->_errorLine,
            $this->_errorFile,
            $this->_errorMessage,
            $this->_moduleName
        );
        $bool = core_class::options('errors_log');

        if ((bool)$bool{0}) {
            error_class::log('critic_packageException', $formatError);
        }
    }
}
