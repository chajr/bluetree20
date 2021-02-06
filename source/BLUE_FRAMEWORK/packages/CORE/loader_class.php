<?php
/**
 * load and and start libraries and modules
 * @category    BlueFramework
 * @package     BlueFramework Core
 * @subpackage  loader
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     2.6.1
 */
class loader_class
{
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
     * information that framework must stop processing modules
     * @var boolean
     */
    public $stop = FALSE;

    /**
     * list of modules that must be skipped
     * @var array
     */
    public $disabled = array();

    /**
     * list of loaded libraries
     * @var array
     */
    public $lib = array();

    /**
     * list of loaded modules
     * @var array
     */
    public $mod = array();

    /**
     * contains files object
     * @var files
     */
    public $files;

    /**
     * information that mobile browser was detected
     * @var boolean 
     */
    public $mobileBrowser = NULL;

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
     * contains lang_class object
     * @var lang_class
     */
    private $_language;

    /**
     * contains meta_class object
     * @var meta_class
     */
    private $_meta;

    /**
     * contains error_class object
     * @var error_class
     */
    private $_error;

    /**
     * regular expression to check class
     * @var string
     */
    private $_class = "/^<\\?php((\r)?(\n)?)*(final )?class ([\\d\\w-_])+( extends| implements([\\d\\w-_])+)?(.*)}((\r)?(\n)?)*\\?>((\r)?(\n)?)*$/is";

    /**
     * contains names of blocks for modules and modules names that must be loaded to specific block
     * @var array 
     */
    private $_blocks = array();

    /**
     * run loader object, and set given objects as variables
     * 
     * @param tree_class $tree obiekt drzewa strony
     * @param display_class $display obiekt wyswietlania tresci
     * @param lang_class $lang obiekt obslugi jezyka
     * @param meta_class $meta obiekt obslugi metatagow
     * @param get $get obiekt get
     * @param post $post obiekt post
     * @param cookie $cookie obiekt cookie
     * @param session $session obiekt sesji
     * @param files $files obiekt zaladowanych plikow
     * @param error_class $error obiekt obslugi bledow
     */
    public function __construct(
        tree_class      $tree,
        display_class   $display,
        lang_class      $lang,
        meta_class      $meta,
        get             $get,
        post            $post,
        cookie          $cookie,
        session         $session,
        files           $files, 
        error_class     $error
    ) {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'start loader class',
                debug_backtrace(),
                '#008E85'
            ));
        }

        $this->_tree        = $tree;
        $this->_display     = $display;
        $this->_language    = $lang;
        $this->_meta        = $meta;
        $this->get          = $get;
        $this->post         = $post;
        $this->cookie       = $cookie;
        $this->session      = $session;
        $this->files        = $files;
        $this->_error       = $error;

        $this->_detectMobileBrowser();
        $this->_load('lib');
        $this->_load('mod');
    }

    /**
     * return list of blocks and their modules
     * @return array
     */
    public function getBlocks()
    {
        return $this->_blocks;
    }

    /**
     * replace given marker by given content in given module
     * 
     * @param string $marker
     * @param string $module
     * @param mixed $content
     * @return integer|boolean number of replaced markers, or NULL
     * @example generate('marker', 'module', 'some content')
     * @example generate(array('marker1' => 'content', 'marker2' => 'content'), 'module')
     */
    public function generate($marker, $module, $content = FALSE)
    {
        $bool = $this->_display->generate($marker, $content, $module);
        return $bool;
    }

    /**
     * process array and generate proper for loop content
     * 
     * @param string $marker
     * @param array $contentArray
     * @param string $module
     * @return integer|boolean number of replaced markers, or NULL
     */
    public function loop($marker, array $contentArray, $module)
    {
        $bool = $this->_display->loop($marker, $contentArray, $module);
        return $bool;
    }

    /**
     * add css or js file to load on module page
     * 
     * @param string $module
     * @param string $fileName
     * @param string $type (css, or js)
     * @param string $external (internal or external)
     * @param string $media (eg. print)
     * @example set('module', 'jquery', 'js', 'external')
     * @example set('module', 'some_script', 'js')
     * @example set('module', 'base', 'css', 'internal', 'print')
     */
    public function set(
        $module,
        $fileName,
        $type,
        $external   = 'internal',
        $media      = ''
    ){
        $this->_display->set($module, $fileName, $type, $external, $media);
    }

    /**
     * add complete meta tag to header
     * 
     * @param string $meta
     */
    public function addMetaTag($meta)
    {
        $this->_meta->add($meta);
    }

    /**
     * add content to existing meta tag
     * 
     * @param string $type meta tag type
     * @param string $meta content to add
     */
    public function addToMetaTag($type, $meta)
    {
        $this->_meta->insert($type, $meta);
    }

    /**
     * depends of given type (TRUE|FALSE) return default or loaded language code
     * 
     * @param boolean $type TRUE - default language, FALSE - loaded
     * @return string
     */
    public function lang($type = FALSE)
    {
        if ($type) {
            return $this->_language->default;
        } else {
            return $this->_language->lang;
        }
    }

    /**
     * load to array template for given module
     * 
     * @param string $module
     * @param string $name layout name, if NULL load layout on the same name as module
     */
    public function layout($module, $name = NULL)
    {
        if (!$name) {
            $name = $module;
        }

        $this->_display->layout($name, $module);
    }

    /**
     * inform framework that module want to be translated
     * 
     * @param string $module
     */
    public function translate($module)
    {
        $this->_language->setTranslationArray($module);
    }

    /**
     * return breadcrumbs array
     * contains names and url
     * 
     * @return array
     */
    public function breadcrumbs()
    {
        return $this->_tree->breadcrumbs;
    }

    /**
     * create site map with paths
     * if page has set parameter hidden=1, wont be added to list
     * 
     * @param string $xml name of xml file to make map
     * @param boolean $admin if set on TRUE will return complete list with all options
     * @return array
     */
    public function map($xml = 'tree', $admin = FALSE)
    {
        return $this->_tree->map($xml, $admin);
    }

    /**
     * process xml pages tree and generate sitemap for google
     *
     * @return string full page map in xml
     */
    public function siteMap()
    {
        return $this->_tree->siteMap();
    }

    /**
     * add information to statement list
     * 
     * @param string $module
     * @param string $type statement type (critic, warning, info, ok) or error marker
     * @param string $code
     * @param string $message some other info
     */
    public function addError($module, $type, $code, $message)
    {
        $this->_error->addError($type, '', $code, '', '', $message, $module);
    }

    /**
     * @param $array
     */
    public function setTranslationArray($array){
        
    }

    /**
     * load modules and libraries
     * 
     * @param string $type (lib/mod)
     * @throws coreException core_error_20
     */
    private function _load($type)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'load library or module',
                debug_backtrace(),
                '#008E85'
            ));
        }

        foreach ($this->_tree->$type as $name => $val) {
            if ($type === 'lib') {
                $libs = starter_class::package($name);

                if (!$libs) {
                    throw new coreException('core_error_20', $name);
                }
                $this->lib = array_merge($this->lib, $libs);

            } elseif ($type === 'mod') {
                if ($this->stop) {
                    break;
                }

                if (in_array($name, $this->_tree->$type)) {
                    continue;
                }

                if ($val['exec']) {
                    $exe = $val['exec'];
                } else {
                    $exe = $name;
                }

                $this->_setBlock($val['block'], $name);
                $path = 'modules/' . $name . '/' . $exe . '.php';
                $bool = starter_class::load($path);

                if (!$bool) {
                    throw new coreException('core_error_20', $name . ' - ' . $exe);
                }

                $this->_validate($path, $exe, $name);
                $this->_run($exe, $name, $val['param']);
            }
        }
    }

    /**
     * assign to module block to witch module will be loaded
     * 
     * @param string $block
     * @param string $module
     */
    private function _setBlock($block, $module)
    {
        $this->_blocks[$module] = $block;
    }

    /**
     * run given module and catch all errors that module throw
     * 
     * @param string $execute name of class to start module (normally name is the same as module)
     * @param string $module
     * @param array $params some other parameters for module
     * @example run ('mod_class', 'mod_class', array(), 'left_column')
     * @example run ('mod_some_class', 'mod_class', array(0 => 'asdas', 1 => 'some val'), '')
     * @throws coreException core_error_22
     */
    private function _run($execute, $module, $params)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'run module',
                debug_backtrace(),
                '#008E85'
            ));
        }

        $unThrow = core_class::options('unthrow');
        try {
            $bool = class_exists($execute);
            if (!$bool) {
                throw new coreException('core_error_22', $execute . ' - ' . $module);
            }

            if (in_array($module, $this->disabled)) {
                return;
            }

            $this->mod[$module] = new $execute($this, $params, $module, $unThrow);

        } catch (coreException $errorCore) {
            $errorCore->show($this->_error);
            $this->stop = TRUE;

        } catch (modException $errorMode) {
            if (!(bool)$unThrow) {
                $errorMode->show($this->_error, $module);

                try {
                    $this->mod[$module] = new $execute(
                        $this,
                        $params,
                        $module,
                        $unThrow,
                        TRUE
                    );

                } catch (coreException $errorCore) {
                    $errorCore->show($this->_error, $module);
                    $this->stop = TRUE;

                } catch (modException $errorMode) {
                    $errorMode->show($this->_error, $module);
                }
            }

        } catch (warningException $warning){
            $warning->show($this->_error, $module);

        } catch (infoException $info) {
            $info->show($this->_error, $module);

        } catch (okException $ok) {
            $ok->show($this->_error, $module);

        } catch (Exception $error) {

            try {
                throw new coreException(
                    $error->getCode(),
                    $error->getMessage(),
                    $module
                );

            } catch (coreException $errorCore) {
                $errorCore->show($this->_error, $module);
                $this->stop = TRUE;
            }
        }
    }

    /**
     * check that loaded file is an class (if on ion configuration)
     * 
     * @param string $path 
     * @param string $execute name of class to start module
     * @param string $module
     * @throws coreException core_error_20, core_error_21
     */
    private function _validate($path, $execute, $module)
    {
        if (core_class::options('core_procedural_mod_check')) {
            $info = $module . ' - ' . $execute;

            $content = starter_class::load($path, TRUE);
            if (!$content) {
                throw new coreException('core_error_20', $info);
            }

            $bool = preg_match($this->_class, $content);
            if(!$bool){
                throw new coreException('core_error_21', $info);
            }
        }
    }

    /**
     * detect that framework is lunched by mobile browser
     */
    private function _detectMobileBrowser()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match(
                '/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|'
                . 'fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|'
                . 'midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|'
                . 'pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows '
                . '(ce|phone)|xda|xiino/i',
                $userAgent
            )
           || preg_match(
                '/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|'
                . 'oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|'
                . 'go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|'
                . 'rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|'
                . 'cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|'
                . 'devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|'
                . 'k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|'
                . 'gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|'
                . 'hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|'
                . 'tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|'
                . 'inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|'
                . 'klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|'
                . 'e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|'
                . '21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|'
                . 'do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|'
                . 'n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|'
                . 'nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|'
                . 'pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|'
                . 'prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|'
                . 'r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|]'
                . 'sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|'
                . 'sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|'
                . 'h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|'
                . 'tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|'
                . 'up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|'
                . 'vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|'
                . 'webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|'
                . 'zeto|zte\-/i', 
                substr($userAgent,0,4)
           )
        ){
            $this->mobileBrowser = TRUE;
        } else {
            $this->mobileBrowser = FALSE;
        }
    }
}
