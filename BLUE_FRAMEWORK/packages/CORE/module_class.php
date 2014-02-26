<?php
/**
 * helper class for modules, all modules cna extend that class
 * 
 * @category    BlueFramework
 * @package     BlueFramework Core
 * @subpackage  module
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     2.6.1
 */
abstract class module_class
{
    /**
     * module version number
     * @var integer
     */
    static $version;

    /**
     * module full name
     * @var string
     */
    static $name;

    /**
     * list of required libraries
     * @var array
     */
    public $requireLibraries = array();

    /**
     * list of required modules
     * @var array
     */
    public $requireModules = array();

    /**
     * contains loader_class object
     * @var loader_class
     */
    public $core;

    /**
     * liast of parameters witch module will start
     * @var array
     */
    public $params;

    /**
     * name of block witch module will be loaded, order of templates appends from order of run
     * @var string
     */
    public $block;

    /**
     * module name
     * @var string
     */
    public $moduleName;

    /**
     * contains get object
     * @var get
     */
    public $get;

    /**
     * contains post object
     * @var post
     */
    public $post;

    /**
     * contains cookie object
     * @var cookie
     */
    public $cookie;

    /**
     * contains session object
     * @var session
     */
    public $session;

    /**
     * contains files object
     * @var files
     */
    public $files;

    /**
     * list of run modules
     * @var array
     */
    public $modules;

    /**
     * contains information that in module was some error (error as code)
     * each error call add code in specific field
     * @var array 
     */
    public $error = array(
        'ok'        => NULL,
        'info'      => NULL,
        'warning'   => NULL,
        'critic'    => NULL
    );

    /**
     * information that browser is mobile browser
     * @var boolean 
     */
    public $mobileBrowser;

    /**
     * information that critic framework error will be showed
     * @var array
     */
    private $_unThrow;
    
    /**
     * method used to start module
     */
    abstract public function run();

    /**
     * method to start module when some exception was throw
     */
    abstract public function runErrorMode();

    /**
     * run helper object, and start module run method
     * 
     * @param loader_class $core
     * @param array $params
     * @param string $unThrow
     * @param string $module
     * @param boolean $error if TRUE start module in error mode
     */
    public function __construct(
        loader_class $core,
        $params,
        $module,
        $unThrow,
        $error = FALSE
    ){
        $this->core             = $core;
        $this->params           = $params;
        $this->moduleName       = $module;
        $this->get              = $this->core->get;
        $this->post             = $this->core->post;
        $this->cookie           = $this->core->cookie;
        $this->session          = $this->core->session;
        $this->files            = $this->core->files;
        $this->modules          = $this->core->mod;
        $this->_unThrow         = $unThrow;
        $this->mobileBrowser    = $this->core->mobileBrowser;

        $this->_checkRequiredLibraries();

        if ($error) {
            $this->runErrorMode();
        } else {
            $this->run();
        }
    }

    /**
     * replace markers with given content, or group of markers
     * 
     * @param mixed $marker marker name or array of mrker=>value
     * @param string|boolean $content
     * @param boolean $core if TRUE replace core markers
     * @return integer|boolean number of replaced markers, or NULL if marker not found
     * @example generate('marker', 'some content')
     * @example generate(array('marker1' => 'content', 'marker2' => 'some content'))
     * @example generate('marker', 'some content', TRUE)
     */
    public function generate($marker, $content = FALSE, $core = FALSE)
    {
        if ($core) {
            $mod = 'core';
        } else {
            $mod = $this->moduleName;
        }
        return $this->core->generate($marker, $mod, $content);
    }

    /**
     * process array and generate proper for loop content
     * 
     * @param string $marker
     * @param array $contentArray
     * @return integer number of replaced markers or NULL if not found
     */
    public function loop($marker, array $contentArray)
    {
        return $this->core->loop($marker, $contentArray, $this->moduleName);
    }

    /**
     * add complete meta tag
     * @param string $meta
     */
    public function addMetaTag($meta)
    {
        $this->core->addMetaTag($meta);
    }

    /**
     * add content to existing meta tag
     * @param string $type
     * @param string $meta
     */
    public function addToMetaTag($type, $meta)
    {
        $this->core->addToMetaTag($type, $meta);
    }

    /**
     * depends of parameter (TRUE|FALSE) return code of default or loaded language
     * 
     * @param boolean $type if TRUE return default language
     * @return string
     */
    public function lang($type = NULL)
    {
        return $this->core->lang($type);
    }

    /**
     * set information in session, default in public section
     * data from public and display can be display by markers ({;session_display;marker;})
     * 
     * @param string $name variable name
     * @param mixed $value mixed
     * @param string $type (user, public, display or core)
     */
    public function setSession($name, $value, $type = 'public')
    {
        $this->core->session->set($name, $value, $type);
    }

    /**
     * clear session
     * @param string|boolean $type (user, public, display, core)
     */
    public function clearSession($type = NULL)
    {
        $this->core->session->clear($type);
    }

    /**
     * return value of variable in session
     * 
     * @param string $variable variable name
     * @param string $type session array type
     * @return mixed|boolean
     */
    public function getSessionVariable($variable, $type)
    {
        $list = $this->core->session->returns($type);
        if (isset($list[$variable])) {
            return $list[$variable];
        }

        return NULL;
    }

    /**
     * load to array template for current module
     * if name is not given, then load template as same name that module
     * 
     * @param string $name
     */
    protected function layout($name = NULL)
    {
        $this->core->layout($this->moduleName, $name);
    }

    /**
     * add to page css or js for module
     * can add internal or external file
     * 
     * @param string $name file name to load
     * @param string $type file type (css|js)
     * @param string $external if file from external service give external value
     * @param string $media media type for css (eg. print)
     * @example set('jquery', 'js', 'external')
     * @example set('some_script', 'js')
     * @example set('base', 'css', 'internal', 'print')
     */
    public function set($name, $type, $external = 'internal', $media = '')
    {
        $this->core->set($this->moduleName, $name, $type, $external, $media);
    }

    /**
     * return breadcrumbs list with links
     * 
     * @return array
     */
    public function breadcrumbs()
    {
        return $this->core->breadcrumbs();
    }

    /**
     * return site map with paths
     * if site has parameter hidden=1 then will be unavailable on list
     * 
     * @param string $xml xml name to create map, default is tree
     * @param boolean $full if set on TRUE return complete list with all options
     * @return array
     * @example map()
     * @example map('other_map')
     * @example map('', 1)
     */
    public function map($xml = '', $full = FALSE)
    {
        if (!$xml) {
            $xml = 'tree';
        }
        return $this->core->map($xml, $full);
    }

    /**
     * process xml pages tree and generate sitemap for google
     *
     * @return string full page map in xml
     */
    public function siteMap()
    {
        return $this->core->siteMap();
    }

    /**
     * allows add some messages (ok, info, critic error, warning)
     * without stopping module
     * 
     * @param string $type (critic|warning|info|ok) or error marker to write message
     * @param string $code error code
     * @param string $message some additional information's
     */
    public function error($type, $code, $message)
    {
        switch ($type) {
            case 'ok':
                $this->error['ok'] = 1;
                break;

            case 'info':
                $this->error['info'] = 1;
                break;

            case 'critic':
                $this->error['critic'] = 1;
                break;

            case 'warning':
                $this->error['warning'] = 1;
                break;
        }

        $this->core->addError($this->moduleName, $type, $code, $message);
    }

    /**
     * read and return options for module, or some specific option value
     * 
     * @param string $option optionally option name
     * @return array|mixed
     */
    public function loadModuleOptions($option = NULL)
    {
        if ($option) {
            return option_class::show($option, $this->moduleName);
        }

        return option_class::load($this->moduleName);
    }

    /**
     * lunched when module try to start method that dos not exists
     * 
     * @param string $method
     * @param array $args
     * @return null
     */
    public function __call($method, $args)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'call to undefined method',
                debug_backtrace(),
                '#FA6E6E'
            ));
        }

        return NULL;
    }

    /**
     * return full module directory
     * 
     * @return string
     */
    public function getModuleDir()
    {
        return starter_class::path('modules/' . $this->moduleName);
    }

    /**
     * inform framework that module want to be translated
     */
    protected function _translate()
    {
        $this->core->translate($this->moduleName);
    }

    /**
     * add another translation array, can replace some translations
     * not implemented yet
     * 
     * @param array $array
     */
    protected function _setTranslationArray($array)
    {
        $this->core->setTranslationArray($array);
    }

    /**
     * skip lunching given module
     * 
     * @param string $name module name without _class suffix
     * @example dissemble('module1');
     */
    protected function _disabled($name)
    {
        $this->core->disabled[] = $name;
    }

    /**
     * stop lunching all modules on list
     */
    protected function _stop()
    {
        $this->core->stop = TRUE;
    }

    /**
     * check that all required by current module, modules and libraries was loaded
     * 
     * @throws coreException core_error_20
     */
    private function _checkRequiredLibraries()
    {
        if (!empty($this->requireLibraries) && !(bool)$this->_unThrow) {
            foreach ($this->requireLibraries as $lib) {

                $bool = in_array($lib, $this->core->lib);

                if (!$bool) {
                    throw new coreException(
                        'core_error_20',
                        $this->moduleName . ' - ' . $lib
                    );
                }
            }
        }

        if (!empty($this->requireModules) && !(bool)$this->_unThrow) {
            foreach ($this->requireModules as $mod) {

                $bool = array_key_exists($mod, $this->modules);

                if (!$bool) {
                    throw new coreException(
                        'core_error_20',
                        '{;lang;module;} ' . $this->moduleName 
                         . ' {;lang;require;} ' . $mod
                    );
                }
            }
        }
    }
}
