<?php
/**
 * example module number 1
 *
 * @category    BlueFramework
 * @package     modules
 * @subpackage  modul1
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     1.4.0
 */
class modul1 
    extends module_class
{
    static $version             = '1.4.0';
    static $name                = 'module number 1';
    public $requireLibraries    = array();
    public $requireModules      = array();
    public $moduleVariable;

    public function run()
    {
        log_class::setSessionModel($this->session);

        //set module translations
        $this->_translate();

        //load module layout
        $this->layout('layout1');

        //generate some simple content
        $this->generate('marker', '{;lang;content_to_replace;}');

        //generate some simple content in main template
        $this->generate('marker', '{;lang;content_to_replace_core;}', TRUE);

        //generate content to group of markers
        $content = array(
            'marker-a' => '{;lang;content;} a',
            'marker-b' => '{;lang;content;} b'
        );
        $this->generate($content);

        //run loaded libraries
        $valid = new valid_class();
        $this->generate('lib', $valid->ret());

        //add css and js
        $this->set(
            'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.js',
            'js',
            'external'
        );
        $this->set('script', 'js');
        $this->set('base', 'css', 'internal', 'print');
        $this->set('base2', 'css');

        //generate some content (normal, loop, optional and from session)
        $tab = array(
            array(
                'item1' => '{;lang;val;} a1',
                'item2' => '{;lang;val;} a2'
            ), 
            array(
                'item1' => '{;lang;val;} b1',
                'item2' => '{;lang;val;} b2'
            )
        );
        $this->loop('loop1', $tab);
        $this->loop('loop_empty', array());

        $arr = array(
            array(
                'aaa' => '{;lang;val;} aaa1',
                'bbb' => '{;lang;val;} bbb2',
                'oo' => 'ooo'
            ), 
            array('aaa' => '{;lang;val;} ccc1',
                  'bbb' => '{;lang;val;} ddd2',
                  'oo2' => 'op2'
            )
        );
        $this->loop('loop2', $arr);

        //data from session
        $this->generate(array(
            'public'    => $this->session->val, 
            'user'      => $this->getSessionVariable('val_user', 'user')
        ));

        if ($this->session->val) {
            $this->session->val += 1;
        } else {
            $this->session->val = 1;
        }
        
        if ($this->getSessionVariable('val_user', 'user')) {
            $userValue = $this->getSessionVariable('val_user', 'user') +1;
        } else {
            $userValue = 1;
        }

        $this->setSession('val_user', $userValue, 'user');
        $this->generate(array(
            'public2'   => $this->session->val, 
            'user2'     => $this->getSessionVariable('val_user', 'user')
        ));
        
        //clear session
        //$this->clearSession('val');                    //!!WORKS
        
        //generate breadcrumbs
        $bread = var_export($this->breadcrumbs(), TRUE);
        $this->generate('breadcrumbs', $bread);

        //set data in cookies
        $this->generate('cookie', $this->cookie->cookieVariable);
        if ($this->cookie->cookieVariable) {
            $this->cookie->cookieVariable += 1;
        } else {
            $this->cookie->cookieVariable = 1;
        }
        $this->generate('cookie2', $this->cookie->cookieVariable);

        //access to POST, GET and FILES variables
        $arr = array(
            'val1' => var_export($this->get->val1, TRUE),
            'val2' => var_export($this->get->val2, TRUE),
            'val3' => var_export($this->get->val3, TRUE)
        );
        $this->generate($arr);

        //access to get_class
        $getArray = array(
            'path'          => get::realPath(),
            'rget'          => var_export(get::convertGet('', 'val/val2/val3'), TRUE),
            'lang'          => $this->get->getLanguage(),
            'current_page'  => $this->get->getCurrentPage(),
            'parent'        => $this->get->getParentPage(),
            'master'        => $this->get->getMasterPage(),
            'full'          => var_export($this->get->fullGetList(TRUE), TRUE),
            'full2'         => var_export($this->get->fullGetList(), TRUE),
            'type'          => $this->get->pageType(),
            'path_domain'   => $this->get->path(),
            'path'          => $this->get->path(TRUE),
        );
        $this->generate($getArray);

        //generate URL paths
        $this->generate(
            'some_path',
            '{;core;domain;}{;core;lang;}{;path;/strona2;}'
        );

        //add some meta tag
        $this->addMetaTag('<meta name="some_meta" content="something"/>');
        $this->addToMetaTag('keywords', 'word 1, word 2');

        //add content to be readed by other module
        $this->moduleVariable = 'aaaaaaaaaaa';

        //create page map
        $map = '<pre>' . var_export($this->map(), TRUE) . '</pre>';
        $this->generate('site_map', $map);

        //loading module to block

        //read parameters for module
        $param = '<pre>' . var_export($this->params, TRUE) . '</pre>';
        $this->generate('param', $param);

        //read module configuration
        $list = '<pre>' . var_export($this->loadModuleOptions(), TRUE) . '</pre>';
        $this->generate('module_config', $list);

        //checking required modules
        //in mod 3                        //!!WORKS

        //skipping modules
        //$this->_disabled('mod2');        //!!WORKS

        //stop framework
        //$this->_stop();                //!!WORKS

        //some error from php
        //echo $adfdsf;                    //!!WORKS

        //set error by rendering and throw to specific marker

        //error that will stop framework
        //throw new coreException(
        //    'core_error_20',
        //    'some other information {;lang;string_to_translate;}'
        //);            //!!WORKS

        //error that stop module
        //throw new modException(
        //    'error_from_module',
        //    'some other information {;lang;string_to_translate;}'
        //);            //!!WORKS

        //show some critic error, that wont stop module
        $this->error(
            'critic',
            'error_from_module',
            '<b>{;lang;mod_translation;} - {;lang;string_to_translate_error;}</b>'
        );

        //warning
        //throw new warningException('warning_code', 'some other information');                              //!!WORKS
        $this->error('warning', 'warning_code', '{;lang;other_info;}');

        //info
        //throw new infoException('info_code', 'some other information');                                    //!!WORKS
        $this->error(
            'info',
            'info_code',
            '<b>{;lang;core_translation;} - {;lang;core;string_to_translate;}</b>'//{;lang;core_translation;}:
        );

        //ok
        //throw new okException('ok_code', 'some other information');                                         //!!WORKS
        $this->error('ok', 'ok_code', '{;lang;other_info;}');

        //error to given marker
        $this->error(
            'error_marker',
            'some_code',
            '{;lang;other_info;} - {;lang;string_to_translate_error;}'
        );
        $this->error('error_marker1', 'some_code', '{;lang;other_info;}');

        //normal exception
        try{
            throw new Exception('some exception');
        } catch (Exception $e) {
            $this->error('error_marker2', 'some_code', '{;lang;normal_exception;}');
        }

        //array of errors and information
        $errors = '<pre>' . var_export($this->error, TRUE).'</pre>';
        $this->generate('errors_list', $errors);

        //forced path conversion

        //additional array to translate
        $this->_setTranslationArray(array(
            'additional_translation' => 'fsdfsdfsdfd'
        ));

        $this->setSession(
            'session_display_test',
            $this->session->val . ' - display',
            'display'
        );

    }
    
    public function runErrorMode(){
        $this->generate('marker', '{;lang;modul1_error_mode;}');
    }

    public function install(){}
    public function uninstall(){}
}
