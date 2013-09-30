<?php
/**
 * framework core
 * runs libraries and modules, allows to exchange data between them, return ready content to display
 * all modules and libraries are lunched inside this class
 *
 * @category    BlueFramework
 * @package     BlueFramework Core
 * @subpackage  core
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     2.6.0
 */
final class core_class
{
    /**
     * contains all framework options
     * @var array
    */
    private static $_options = array();

    /**
     * contains ready to display page content
     * @var string
    */
    public $render = '';

    /**
     * contains get object
     * @var get
    */
    private $_get;

    /**
     * contains post object
     * @var post
    */
    private $_post;

    /**
     * contains cookie object
     * @var cookie
    */
    private $_cookie;

    /**
     * contains session object
     * @var session
    */
    private $_session;

    /**
     * contains files object
     * @var files
    */
    private $_files;

    /**
     * contains tree_class object
     * @var tree_class
     */
    private $_tree;

    /**
     * contains display_class object
     * @var display_class
     */
    private $_display;

    /**
     * contains meta_class object
     * @var meta_class
     */
    private $_meta;

    /**
     * contains loader_class object
     * @var loader_class
     */
    private $_loader;

    /**
     * contains error_class object
     * @var error_class
     */
    private $_error;

    /**
     * contains lang_class object
     * @var lang_class
     */
    private $_lang;

    /**
     * start all framework with rendering page
    */
    public function __construct()
    {
        tracer_class::marker(array(
            'start framework core',
            debug_backtrace(),
            '#006C94'
        ));

        self::$_options = option_class::load();
        benchmark_class::setMarker('load options');

        $this->_checkTechBreak();
        $this->_setTimezone();

        $this->_runBaseObjects();

        if ($this->_get->pageType() === 'html') {
            benchmark_class::startGroup('html group');
            $this->_htmlPage();
            benchmark_class::endGroup('html group');
        } else {
            $this->_otherPage();
        }

        $this->_setLanguage();

        $this->render = $this->_display->render();
        benchmark_class::setMarker('rendering');

        $this->_setEmptyOtherRender();
        $this->_transformGlobalArrays();
    }

    /**
     * clear global arrays and set to them correct data
     */
    protected function _transformGlobalArrays()
    {
        tracer_class::marker(array(
            'transforming global arrays',
            debug_backtrace(),
            '#006C94'
        ));

        if ($this->_get->pageType() === 'html') {
            globals_class::destroy();
            benchmark_class::setMarker('destroy global arrays');

            $this->_session->setSession();
            benchmark_class::setMarker('set data in session');

            $this->_cookie->setCookies();
            benchmark_class::setMarker('set data in cookie');
        }
    }

    /**
     * if css/js page is empty set default data for them
     */
    protected function _setEmptyOtherRender()
    {
        if (empty($this->render)
            && (
                   $this->_get->pageType() === 'css' 
                || $this->_get->pageType() === 'js'
            )
        ) {
            if ($this->_get->pageType() === 'js') {
                $this->render = '(function(){}())';
            } elseif($this->_get->pageType() == 'css') {
                $this->render = 'html{}';
            }
        }
    }

    /**
     * sets translations and translate all markers
     */
    protected function _setLanguage()
    {
        tracer_class::marker(array(
            'setting language',
            debug_backtrace(),
            '#006C94'
        ));

        $this->_lang->setTranslationArray();
        benchmark_class::setMarker('set translation array');

        $this->_lang->translate($this->_display);
        benchmark_class::setMarker('start translation');
    }

    /**
     * check if framework has technical break
     */
    protected function _checkTechBreak()
    {
        if (self::$_options['techbreak']) {
            $break = starter_class::load('elements/layouts/techbreak.html', TRUE);

            if (!$break) {
                echo 'Technical BREAK';
            } else {
                echo $break;
            }
            exit;
        }
    }

    /**
     * set default timezone
     */
    protected function _setTimezone()
    {
        if (self::$_options['timezone']) {
            @date_default_timezone_set(self::$_options['timezone']);
        }
    }

    /**
     * start common objects for html and css/js page
     */
    protected function _runBaseObjects()
    {
        tracer_class::marker(array(
            'start base objects',
            debug_backtrace(),
            '#006C94'
        ));

        $this->_get = new get();
        benchmark_class::setMarker('runs get object');

        $this->_post = new post();
        benchmark_class::setMarker('runs post object');

        $this->_lang = new lang_class($this->_get->getLanguage());
        benchmark_class::setMarker('runs language object');

        $this->_error = new error_class($this->_lang);
        benchmark_class::setMarker('runs error object');
    }

    /**
     * runs correct for html page libraries and starts libraries/modules
     */
    protected function _htmlPage()
    {
        tracer_class::marker(array(
            'start create of html page',
            debug_backtrace(),
            '#006C94'
        ));

        $this->_session = new session();
        benchmark_class::setMarker('runs session object');

        $this->_cookie = new cookie();
        benchmark_class::setMarker('runs cookie object');

        $this->_files = new files();
        benchmark_class::setMarker('runs files object');

        globals_class::destroy();
        benchmark_class::setMarker('destroy global arrays');

        $this->_tree = new tree_class(
            $this->_get->fullGetList(),
            $this->_lang->lang
        );
        benchmark_class::setMarker('runs tree object');

        $this->_display = new display_class(array(
            'template'  => $this->_tree->layout,
            'get'       => $this->_get,
            'session'   => $this->_session,
            'language'  => $this->_lang->lang,
            'css'       => $this->_tree->css,
            'js'        => $this->_tree->js,
        ));
        benchmark_class::setMarker('runs display object');

        $this->_meta = new meta_class($this->_get->fullGetList());
        benchmark_class::setMarker('runs meta object');

        benchmark_class::startGroup('loader group');
        $this->_loader = new loader_class(
            $this->_tree,
            $this->_display,
            $this->_lang,
            $this->_meta,
            $this->_get,
            $this->_post,
            $this->_cookie,
            $this->_session,
            $this->_files,
            $this->_error
        );
        benchmark_class::setMarker('loading modules, libraries and start them');
        benchmark_class::endGroup('loader group');

        $this->_display->block = $this->_loader->getBlocks();
        benchmark_class::setMarker('set blocks');

        $this->_meta->render($this->_display);
        benchmark_class::setMarker('meta data rendering');

        $this->_checkErrors();
    }

    /**
     * check if there was some errors or other communications
     * if was display them
     */
    protected function _checkErrors()
    {
        tracer_class::marker(array(
            'check that errors exists',
            debug_backtrace(),
            '#006C94'
        ));

        $errorList = $this->_error->render(1);

        if ($errorList['pointer']) {
            foreach($errorList['pointer'] as $error){
                $this->_display->generate(
                    $error['point'],
                    $error['content'],
                    $error['mod']
                );
            }
        }

        if ($errorList['critic']) {
            $this->_display->generate('core_error', $errorList['critic']);
        }

        if ($errorList['warning']) {
            $this->_display->generate('core_warning', $errorList['warning']);
        }

        if ($errorList['info']) {
            $this->_display->generate('core_info', $errorList['info']);
        }

        if ($errorList['ok']) {
            $this->_display->generate('core_ok', $errorList['ok']);
        }
    }

    /**
     * runs correct for css or js page libraries and render content
     */
    protected function _otherPage()
    {
        tracer_class::marker(array(
            'start create other page type',
            debug_backtrace(),
            '#006C94'
        ));

        $this->_display = new display_class(array(
            'get'       => $this->_get,
            'language'  => $this->_lang->lang,
        ));
        benchmark_class::setMarker('runs display object for css/js');

        $this->_display->other();
        benchmark_class::setMarker('rendering css/js');

        benchmark_class::turnOffBenchmark();
        tracer_class::turnOffTracer();
    }

    /**
     * return framework options
     * given option or all options
     * 
     * @param string|bool $name
     * @return mixed
     */
    public static function options($name = FALSE)
    {
        tracer_class::marker(array(
            'return framework options',
            debug_backtrace(),
            '#006C94'
        ));

        if ($name) {
            if (!isset(self::$_options[$name])) {
                return NULL;
            }

            return self::$_options[$name];
        }

        return self::$_options; 
    }
}
