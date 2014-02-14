<?php
/**
 * @category    BlueFramework
 * @package     BlueFramework Core
 * @subpackage  globals
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     2.4.8
 */

/**
 * abstract class for all global variable class
 * process all data from GRT, POST, COOKIE, SESSION and convert them to objects
 * 
 * @throws coreException
 */
abstract class globals_class
{
    /**
     * start data processing
     */
    abstract function run();

    /**
     * start correct method
     */
    public function __construct()
    {
        $this->run();
    }

    /**
     * return NULL when method variable doesn't  exist
     * @param string $name
     */
    public function __get($name)
    {
        $this->_checkKey($name);
        $this->$name = NULL;
    }

    /**
     * set data in globals_class, if exists will replace value
     * 
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->_checkKey($name);
        $this->$name = $value;
    }

    /**
     * set variable in globals_class
     * if exists will create array from variable, nad add elements in order
     * 
     * @param string $name
     * @param mixed $value
     */
    protected function _add($name, $value)
    {
        $this->_checkKey($name);

        if (isset($this->$name)) {
            if (is_array($this->$name)) {
                $new            = array($value => $value);
                $this->$name    = array_merge($this->$name, $new);

            } else {
                $data           = $this->$name;

                $this->$name    = array(
                    $data  => $data,
                    $value => $value
                );
            }

        } else {
            $this->$name = $value;
        }
    }

    /**
     * destroy all super global arrays
     */
    public static function destroy()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'destroy global arraysr',
                debug_backtrace(),
                '#004396'
            ));
        }

        unset($_GET);
        unset($_POST);

        $_COOKIE    = array();
        $_SESSION   = array();

        unset($_FILES);
        unset($_REQUEST);
    }

    /**
     * check that parameters are correct with regular expression
     * 
     * @param string $uri
     * @throws coreException core_error_4
     */
    protected function _checkParameter($uri)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'check url parameters',
                debug_backtrace(),
                '#004396'
            ));
        }

        if ((bool) core_class::options('rewrite')) {
            $bool = preg_match(core_class::options('reg_exp_rewrite'), $uri);
        } else {
            $bool = preg_match(core_class::options('reg_exp_classic'), $uri);
        }

        if (!$bool) {
            throw new coreException(
                'core_error_4',
                $uri . ' - rewrite: ' . core_class::options('rewrite')
            );
        }
    }

    /**
     * check max number of parameters
     * 
     * @param integer $counter number of given parameter
     * @param string $type get|post|files
     * @throws coreException core_error_5, core_error_7, core_error_8
     */
    protected function _maxParameters($counter, $type)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'check max parameters count',
                debug_backtrace(),
                '#004396'
            ));
        }

        $error          = '';
        $globalArray    = '';
        $option         = '';

        switch ($type) {
            case"get":
                $option         = 'max_get';
                $globalArray    = $_GET;
                $error          = 'core_error_5';
                break;

            case"post":
                $option         = 'max_post';
                $globalArray    = $_POST;
                $error          = 'core_error_7';
                break;

            case"files":
                $option         = 'files_max';
                $globalArray    = $_FILES;
                $error          = 'core_error_8';
                break;
        }

        $option = core_class::options($option);
        if ((bool)$option) {
            if ($counter > $option) {

                $inf = count($globalArray) . ' -> ' . $option;
                throw new coreException($error, $inf);
            }
        }
    }

    /**
     * check length of parameter
     * 
     * @param string $parameter parameter + value to check
     * @throws coreException core_error_6
     */
    protected function _maxLength($parameter)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'show error',
                debug_backtrace(),
                '#004396'
            ));
        }

        if (core_class::options('get_len')) {
            $length = mb_strlen($parameter);

            if ($length > core_class::options('get_len')) {
                throw new coreException('core_error_6', $parameter);
            }
        }
    }

    /**
     * return variables as serialized string
     * 
     * @return string
     */
    public function __toString()
    {
        $dataArray = array();

        foreach ($this as $key => $val) {
            $dataArray[$key] = $val;
        }

        return serialize($dataArray);
    }

    /**
     * check that name for variable has proper chars
     * 
     * @param string $key
     * @throws coreException core_error_25
     */
    protected function _checkKey($key)
    {
        $keyCheck = preg_match(core_class::options('global_var_check'), $key);

        if (!$keyCheck) {
            throw new coreException(
                'core_error_25',
                $key . ' - rewrite: ' . core_class::options('global_var_check')
            );
        }
    }
}

/**
 * process GET array
 */
class get 
    extends globals_class
{
    /**
     * absolute path to page
     * @var string
     */
    private $_corePath;

    /**
     * page type information (default html, other is js or css)
     * @var string
     */
    private $_corePageType = 'html';

    /**
     * contain list of pages/subpages
     * @var array
     */
    private $_corePages = array();

    /**
     * information about language taken from grt, or null if there no was one
     * @var string
     */
    private $_coreLanguage = NULL;

    /**
     * process GET array and save data with specific palace
     * set parameters from get as class variable, pages as array
     * set path, page type and path to page
     */
    public function run()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'start get class',
                debug_backtrace(),
                '#00306A'
            ));
        }

        $this->_subdomain();

        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
            $this->_checkParameter($uri);

            if (core_class::options('rewrite')) {
                $this->_modeRewrite($uri);
            } else {
                $this->_classicUrl($uri);
            }
        }

        $this->_corePath = self::realPath(core_class::options('test'));
        $this->_type();
       }

    /**
     * process page URL as mode rewrite url
     * @param $uri
     */
    protected function _modeRewrite($uri)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'parse mode rewrite url',
                debug_backtrace(),
                '#00306A'
            ));
        }

        $get                = self::convertGet(core_class::options('test'), $uri);
        $counter            = 0;
        $this->_coreLanguage = lang_class::checkLanguage($get);

        foreach ($get as $parameter) {
            if ($parameter === '') {
                continue;
            }

            $counter++;
            $this->_maxParameters($counter, 'get');
            $this->_maxLength($parameter);
            $bool = preg_match(
                '#[\\w]*' . core_class::options('var_rewrite_sep') . '[\\w]*#',
                $parameter
            );

            if ($bool) {
                $parameter = explode(
                    core_class::options('var_rewrite_sep'),
                    $parameter
                );
                $this->_add($parameter[0], $parameter[1]);
            } else {
                $this->_corePages[] = $parameter;
            }
        }
    }

    /**
     * process page URL as classic url
     * @param $uri
     */
    protected function _classicUrl($uri)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'parse classic url',
                debug_backtrace(),
                '#00306A'
            ));
        }

        $this->_coreLanguage = lang_class::checkLanguage($_GET);

        if (!empty($_GET) ) {
            $uri = str_replace(
                array(
                    core_class::options('test'),
                    '/',
                    '?'
                ), '', $uri
            );

            $get        = explode('&', $uri);
            $counter    = 0;

            foreach ($get as $element) {
                $counter++;
                $this->_maxParameters($counter, 'get');
                $tempArray = explode('=', $element);
                $this->_maxLength($tempArray[1]);
                $bool = preg_match('#^p[0-9]+$#', $tempArray[0]);

                if ($bool) {
                    $this->_corePages[] = $tempArray[1];
                } else {
                    $this->_add($tempArray[0], $tempArray[1]);
                }
            }
        }
    }

    /**
     * return repair path for elements
     * 
     * @param string $test if set, give with test path
     * @return string
     */
    static function realPath($test = '')
    {
        $host = $_SERVER['HTTP_HOST'];

        if (isset($_SERVER['SCRIPT_URI'])) {
            $protocol = explode('://', $_SERVER['SCRIPT_URI']);
        } else {
            $protocol[0] = 'http';
        }

        $path = $protocol[0] . '://' . $host . '/';

        if ($test) {
            $path .= $test . '/';
        }

        return $path;
    }

    /**
     * convert URI to array
     * 
     * @param string $test test directory on server
     * @param string $uri
     * @return array
     */
    static function convertGet($test, $uri)
    {
        if ($test) {
            $uri = str_replace('/' . $test . '/', '', $uri);
        } else {
            $uri = trim($uri, '/');
        }

        $get = explode('/', $uri);
        return $get;
    }

    /**
     * return set up language, on NULL if not set up or language support disabled
     * 
     * @return string language code
     */
    public function getLanguage()
    {
        if ((bool)core_class::options('lang_support')) {
            return $this->_coreLanguage;
        } else {
            return NULL;
        }
    }

    /**
     * return name of current page
     * 
     * @return string
     */
    public function getCurrentPage()
    {
        return end($this->_corePages);
    }

    /**
     * return parent of current page, or page given in parameter
     * 
     * @param string $page optional page that parent we wont
     * @return string parent name, or FALSE
     * @example getParentPage()
     * @example getParentPage('some_page')
     */
    public function getParentPage($page = '')
    {
        if ($page) {
            foreach ($this->_corePages as $key => $val) {

                if ($val === $page) {
                    return $key -1;
                }
            }

            return FALSE;
        } else {
            end($this->_corePages);
            return prev($this->_corePages);
        }
    }

    /**
     * get main page
     * 
     * @return string
     */
    public function getMasterPage()
    {
        if (isset($this->_corePages[0])) {
            return $this->_corePages[0];
        }

        return NULL;
    }

    /**
     * return full array with pages/subpages and their GET parameters
     * or only pages/subpages
     * 
     * @param boolean $parameters if TRUE return with GET parameters
     * @return array
     */
    public function fullGetList($parameters = FALSE)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'return all get parameters',
                debug_backtrace(),
                '#00306A'
            ));
        }

        if ($parameters) {
            $array = array(
                'params' => array(),
                'pages'  => $this->_corePages
            );

            foreach ($this as $param => $val) {
                if (
                       $param === '_corePath'
                    || $param === '_corePageType'
                    || $param === '_corePages'
                    || $param === '_coreLanguage'
                ){
                    continue;
                }

                $array['params'][$param] = $val;
            }

            return $array;
        } else {
            return $this->_corePages;
        }
    }

    /**
     * return page type
     * 
     * @return string
     */
    public function pageType()
    {
        return $this->_corePageType;
    }

    /**
     * return main path for page, or complete path with subpages
     * 
     * @param boolean $completePath if FALSE return domain, if TRUE complete path
     * @return string
     */
    public function path($completePath = FALSE)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'return main path',
                debug_backtrace(),
                '#00306A'
            ));
        }

        if ($completePath) {
            if (core_class::options('rewrite')) {

                $path = $this->_corePath;
                if ($this->_coreLanguage) {
                    $path .= $this->_coreLanguage . '/';
                }

                foreach ($this->_corePages as $page) {
                    $path .= "$page/";
                }

                return $path;
                
            } else {
                $host = rtrim($this->_corePath, '/');

                if (core_class::options('test')) {
                    $host = rtrim($host, core_class::options('test'));
                }

                $host = str_replace('//', '/', $host . $_SERVER['REQUEST_URI']);
                return $host;
            }
        } else {
            return $this->_corePath;
        }
    }

    /**
     * check page type, and set variable value
     */
    private function _type()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'checking page type',
                debug_backtrace(),
                '#00306A'
            ));
        }

        if (!empty($this->_corePages)) {

            switch ($this->_corePages[0]) {
                case"core_css":
                    $this->_corePageType = 'css';
                    break;

                case"core_js":
                    $this->_corePageType = 'js';
                    break;

                default:
                    $this->_corePageType = 'html';
                    break;
            }
        } else {
            $this->_corePageType = 'html';
        }
    }

    /**
     * read page name to load from subdomain
     */
    private function _subdomain()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'check subdomain',
                debug_backtrace(),
                '#00306A'
            ));
        }

        if ((bool)core_class::options('subdomain')) {
            $host = preg_replace(
                core_class::options('domain'),
                '',
                $_SERVER['HTTP_HOST']
            );
            $domains = explode('.', $host);

            if (empty($domains)) {
                $start = 1;

                foreach ($domains as $domain) {
                    if ($start > core_class::options('subdomain')) {
                        break;
                    }

                    $this->_corePages[] = $domain;
                    $start++;
                }
            }
        }
    }
}

/**
 * process POST array
 */
class post 
    extends globals_class
{
    /**
     * process POST array and optionally check secure level
     */
    public function run()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'start post class',
                debug_backtrace(),
                '#002046'
            ));
        }

        if (!empty($_POST)) {
            $counter = 0;

            foreach ($_POST as $key => $parameter) {
                $this->_checkKey($key);

                $counter++;
                $this->_maxParameters($counter, 'post');

                if (
                    (bool)core_class::options('post_secure') 
                    && !is_array($parameter)
                ){
                    if (core_class::options('post_secure') == 2) {
                        $parameter = htmlspecialchars($parameter, ENT_NOQUOTES);
                    }
                    $parameter = addslashes($parameter);
                }

                $this->$key = $parameter;
            }
        }
    }
}

/**
 * process COOKIE array
 */
class cookie 
    extends globals_class
{
    /**
     * process COOKIE array
     */
    public function run()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'start cookie class',
                debug_backtrace(),
                '#213A59'
            ));
        }

        if (!empty($_COOKIE)) {
            
            foreach ($_COOKIE as $key => $parameter) {
                $this->_checkKey($key);
                $this->$key = $parameter;
            }
        } else {
            $_COOKIE = array();
        }
    }

    /**
     * set cookie file that exist on object, with default lifetime value
     * regenerate session id
     */
    public function setCookies()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'set cookies form object',
                debug_backtrace(),
                '#213A59'
            ));
        }

        foreach ($this as $key => $val) {
            if ($key === 'PHPSESSID') {
                session_regenerate_id();
                $val = session_id();
            }

            $time = time() + core_class::options('cookielifetime');
            setcookie($key, $val, $time);
        }
    }
}

/**
 * process SESSION array and allows to set data in session
 */
class session 
    extends globals_class
{
    /**
     * contains user dada
     * @var array
     */
    private $_coreUser = array();

    /**
     * contains framework data
     * @var array
     * @private
     */
    private $_coreCore = array();

    /**
     * contains data to display
     * @var array
     * @private
     */
    private $_coreDisplay = array();

    /**
     * process SESSION array and save value in 4 groups (public, core, user, display)
     */
    public function run()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'start session class',
                debug_backtrace(),
                '#374557'
            ));
        }

        if (!empty($_SESSION)) {
            foreach ($_SESSION as $key => $val) {

                if(!$val){
                    $val = array();
                }

                $this->_checkKey($key);

                switch ($key) {
                    case"public":
                        foreach ($val as $key2 => $val2) {
                            $this->$key2 = $val2;
                        }
                        break;

                    case"user":
                        foreach ($val as $key2 => $val2) {
                            $this->_coreUser[$key2] = $val2;
                        }
                        break;

                    case"core":
                        foreach ($val as $key2 => $val2) {
                            $this->_coreCore[$key2] = $val2;
                        }
                        break;

                    case"display":
                        foreach ($val as $key2 => $val2) {
                            $this->_coreDisplay[$key2] = $val2;
                        }
                        break;

                    default:
                        $this->$key = $val;
                        break;
                   }
            }
        } else {
            $_SESSION['user']       = array();
            $_SESSION['core']       = array();
            $_SESSION['public']     = array();
            $_SESSION['display']    = array();
        }
    }

    /**
     * set variables in SESSION object
     *
     * @param string $name variable name
     * @param mixed $val data to save
     * @param string $group variable group to save 
     * @example set('test', 1, 'core')
     * @example set('test', 1)
     * @example set('test', 1, 'user')
     * @example set('test', 1, 'display')
     */
    public function set($name, $val, $group = 'public')
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'set session variable',
                debug_backtrace(),
                '#374557'
            ));
        }

        $this->_checkKey($name);

        switch ($group) {
            case"public":
                $this->$name = $val;
                break;

            case"user":
                $this->_coreUser[$name] = $val;
                break;

            case"core":
                $this->_coreCore[$name] = $val;
                break;

            case"display":
                $this->_coreDisplay[$name] = $val;
                break;

            default:
                $this->$name = $val;
                break;
        }
    }

    /**
     * return data form session object
     * 
     * @param string $group data group to return
     * @return array|boolean
     * @example returns('public')
     * @example returns('core')
     * @example returns('display')
     * @example returns('user')
     */
    public function returns($group)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'return session variables from group',
                debug_backtrace(),
                '#374557'
            ));
        }

        switch ($group) {
            case"core":
                return $this->_coreCore;
                break;

            case"user":
                return $this->_coreUser;
                break;

            case"display":
                return $this->_coreDisplay;
                break;

            case"public":
                $arr = array();

                foreach ($this as $param => $val) {
                    if (
                           $param === '_coreCore' 
                        || $param === '_coreUser' 
                        || $param === '_coreDisplay'
                    ){
                        continue;
                    }
                    $arr[$param] = $val;
                }

                return $arr;
                break;

            default:
                break;
        }

        return FALSE;
    }

    /**
     * process SESSION again and save data in correct places
     */
    public function setSession()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'set session global array',
                debug_backtrace(),
                '#374557'
            ));
        }

        $_SESSION['public']     = $this->returns('public');
        $_SESSION['core']       = $this->returns('core');
        $_SESSION['user']       = $this->returns('user');
        $_SESSION['display']    = $this->returns('display');
    }

    /**
     * clear data in session, some specific information or all from given group
     * 
     * @param string $name name of info to remove, or group
     * @example clear() - clear all, without core
     * @example clear('core')
     * @example clear('user')
     * @example clear('some_variable')
     */
    public function clear($name = NULL)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'clear data in session',
                debug_backtrace(),
                '#374557'
            ));
        }

        if (!$name) {
            foreach ($this as $param => $val){

                $this->_checkKey($param);

                if ($param == '_coreCore') {
                    continue;
                }

                if ($param === '_coreUser' || $param === '_coreDisplay') {
                    $this->$param = array();
                    continue;
                }

                unset($this->$param);
            }
        } else {
            switch ($name) {
                case'core':
                    $this->_coreCore = array();
                    break;

                case'user':
                    $this->_coreUser = array();
                    break;

                case'display':
                    $this->_coreDisplay = array();
                    break;

                case'public':
                    foreach ($this as $param => $val) {
                        if(
                               $param === '_coreCore'
                            || $param === '_coreUser'
                            || $param === '_coreDisplay'
                        ){
                            continue;
                        }

                        $this->_checkKey($param);
                        unset($this->$param);
                    }
                    break;

                default:
                    $this->_checkKey($name);
                    unset($this->$name);
                    break;
            }
        }
    }
}

/**
 * process uploaded files
 */
class files
    extends globals_class
{
    /**
     * array of uploaded files errors
     * 
     * UPLOAD_ERR_OK
     * Value: 0; There is no error, the file uploaded with success.
     * UPLOAD_ERR_INI_SIZE
     * Value: 1; The uploaded file exceeds the upload_max_filesize directive in php.ini.
     * UPLOAD_ERR_FORM_SIZE
     * Value: 2; The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.
     * UPLOAD_ERR_PARTIAL
     * Value: 3; The uploaded file was only partially uploaded.
     * UPLOAD_ERR_NO_FILE
     * Value: 4; No file was uploaded.
     * UPLOAD_ERR_NO_TMP_DIR
     * Value: 6; Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.
     * UPLOAD_ERR_CANT_WRITE
     * Value: 7; Failed to write file to disk. Introduced in PHP 5.1.0.
     * UPLOAD_ERR_EXTENSION
     * Value: 8; File upload stopped by extension. Introduced in PHP 5.2.0.
     * 
     * @var array 
     */
    public $uploadErrors = array();

    /**
     * size of all uploaded files
     * @var integer
     */
    private $_uploadFullSize = 0;

    /**
     * process FILES array
     * @throws coreException core_error_10, core_error_11
     */
    public function run()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'start files class',
                debug_backtrace(),
                '#516E91'
            ));
        }

        if (!empty($_FILES)) {
            $counter = 0;

            foreach ($_FILES as $key => $file) {
                $this->_checkKey($key);

                $this->_maxParameters($counter, 'files');

                $fileMaxSize = core_class::options('file_max_size') * 1000;
                if ($file['size'] > $fileMaxSize) {
                    throw new coreException('core_error_10', $file['name']);
                }

                $this->_uploadFullSize += $file['size'];
                $maxSize                =  core_class::options('files_max_size') * 1000;

                if ($this->_uploadFullSize > $maxSize) {
                    throw new coreException('core_error_11', 'max: ' . $maxSize);
                }

                $path = pathinfo($file['name']);
                if (isset($path['extension'])) {
                    $extension = $path['extension'];
                } else {
                    $extension = NULL;
                }

                $this->$key = array(
                    'name'      => $file['name'],
                    'type'      => $file['type'],
                    'tmp_name'  => $file['tmp_name'],
                    'error'     => $file['error'],
                    'size'      => $file['size'],
                    'extension' => $extension,
                    'basename'  => $path['filename']
                );

                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $this->uploadErrors[$key] = $file['error'];
                }
            }
        }
    }

    /**
     * move uploaded files to correct place
     * can move single file, or group in given destination or destinations
     * 
     * @param string|array $destination
     * @param string|boolean $name form name witch came file if NULL read form name from destinations array
     * @example move(array('path1', 'path2'), 'form1') - put file to 2 directories
     * @example move(array('form1' => 'path', 'form2' => 'path2'))
     * @example move('some/path', 'form2')
     * @example move('some/path') - move all uploaded files to given path
     */
    public function move($destination, $name = NULL)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'move file to given destination',
                debug_backtrace(),
                '#516E91'
            ));
        }

        $this->_checkKey($name);

        if (is_array($destination)) {
            if ($name) {

                foreach ($destination as $path) {
                    $this->_createData($path, $this->$name['tmp_name']);
                }
            } else {

                foreach ($destination as $key => $path) {
                    $this->_createData($path, $this->$key['tmp_name']);
                }
            }
        } else {
            if ($name) {
                $this->_createData($destination, $this->$name['tmp_name']);
            } else {

                foreach ($this as $key => $val) {
                    if ($key === '_uploadFullSize' || $key === 'uploadErrors') {
                        continue;
                    }
                    $this->_createData($destination, $val['tmp_name']);
                }
            }
        }
    }

    /**
     * check if directory exist and create it if not and put file to directory
     * 
     * @param string $path
     * @param string $valueToPut
     * @return bool
     */
    protected function _createData($path, $valueToPut)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'check if exist and create directory',
                debug_backtrace(),
                '#516E91'
            ));
        }

        if (!self::exist($path)) {
            $bool = mkdir ($path);

            if (!$bool) {
                $this->uploadErrors['create_directory'][] = $path;
                return FALSE;
            }
        }

        return $this->_put($valueToPut, $path);
    }

    /**
     * get data from file, or fromm all files in object
     * 
     * @param string $name
     * @return mixed|array
     * @example read() - return array of files with their content
     * @example read('input_form_form')
     */
    public function read($name = NULL)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'return data from file',
                debug_backtrace(),
                '#516E91'
            ));
        }

        if ($name) {
            return $this->_single($name);
        } else {
            $data = array();

            foreach ($this as $key => $val) {
                if( $key === '_uploadFullSize' || $key === 'uploadErrors') {
                    continue;
                }

                $data[$key] = $this->_single($key);
            }

            return $data;
        }
    }

    /**
     * return size of all uploaded files in bytes
     * 
     * @return integer
     */
    public function uploadFullSize()
    {
        return $this->_uploadFullSize;
    }

    /**
     * return array with some file values
     * files name, their types, or errors
     * 
     * @param string $type data type to return
     * @return array
     * @example returns('name')
     * @example returns('type')
     * @example returns('tmp_name')
     * @example returns('size')
     * @example returns('extension')
     * @example returns('basename')
     * @example returns('error')
     * @example returns() - all data array
     * 
     * UPLOAD_ERR_OK
     * Value: 0; There is no error, the file uploaded with success.
     * UPLOAD_ERR_INI_SIZE
     * Value: 1; The uploaded file exceeds the upload_max_filesize directive in php.ini.
     * UPLOAD_ERR_FORM_SIZE
     * Value: 2; The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.
     * UPLOAD_ERR_PARTIAL
     * Value: 3; The uploaded file was only partially uploaded.
     * UPLOAD_ERR_NO_FILE
     * Value: 4; No file was uploaded.
     * UPLOAD_ERR_NO_TMP_DIR
     * Value: 6; Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.
     * UPLOAD_ERR_CANT_WRITE
     * Value: 7; Failed to write file to disk. Introduced in PHP 5.1.0.
     * UPLOAD_ERR_EXTENSION
     * Value: 8; File upload stopped by extension. Introduced in PHP 5.2.0.
     */
    public function returns($type = NULL)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'return all file parameters',
                debug_backtrace(),
                '#516E91'
            ));
        }

        $array = array();

        foreach ($this as $key => $val) {
            if ($key === '_uploadFullSize' || $key === 'uploadErrors') {
                continue;
            }

            switch($type){
                case"name":
                    $array[$key] = $val['name'];
                    break;

                case"type":
                    $array[$key] = $val['type'];
                    break;

                case"tmp_name":
                    $array[$key] = $val['tmp_name'];
                    break;

                case"size":
                    $array[$key] = $val['size'];
                    break;

                case"extension":
                    $array[$key] = $val['extension'];
                    break;

                case"basename":
                    $array[$key] = $val['basename'];
                    break;

                case"error":
                    if ($val['error'] !== UPLOAD_ERR_OK) {
                        $array[$key] = $val['error'];
                    }
                    break;

                default:
                    $array[$key] = $val;
                    break;
            }
        }

        return $array;
    }

    /**
     * check that file exists
     * 
     * @param string $path
     * @return boolean TRUE if exists, FALSE if not
     * @static
     */
    static function exist($path)
    {
        if (file_exists($path)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * return content of single file
     * 
     * @param string $file name of input from form
     * @return mixed
     * @throws coreException core_error_12
     */
    private function _single($file)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'return content of single file',
                debug_backtrace(),
                '#516E91'
            ));
        }

        $this->_checkKey($file);

        $name = starter_class::path('TMP') . 'tmp';
        $bool = move_uploaded_file($this->$file, $name);

        if (!$bool) {
            throw new coreException(
                'core_error_12',
                $this->$file . ' => ' . $name
            );
        }

        $data = file_get_contents($name);
        unlink($name);

        return $data;
    }

    /**
     * move file to given destination
     * 
     * @param string $filename name of file in tmp directory
     * @param string $destination
     * @return boolean
     * @throws coreException core_error_12
     * @example put('file_from_tmp', '/some_path/directory/file.name')
     */
    private function _put($filename, $destination)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'move file to destination',
                debug_backtrace(),
                '#516E91'
            ));
        }

        if (self::exist($destination . '/' . $filename)) {
            $this->uploadErrors['put_file'][] = $destination . '/' . $filename;
            return FALSE;
        }

        $bool = move_uploaded_file($filename, $destination);
        if (!$bool) {
            throw new coreException(
                'core_error_12', $filename . ' => ' . $destination
            );
        }

        return TRUE;
    }
}
