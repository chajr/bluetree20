<?php
/**
 * process page tree structure, return modules, libraries, css, js and template list
 *
 * @category    BlueFramework
 * @package     BlueFramework Core
 * @subpackage  tree
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     2.7.2
 */
class tree_class
{
    /**
     * contains xml_class object with sitemap
     * @var xml_class 
     */
    private $_siteMap;

    /**
     * list of GET parameters with page names
     * @var array
     */
    private $_get;

    /**
     * main page name
     * @var string
     */
    private $_masterPageName;

    /**
     * DOM element with page to be processed
     * @var DOMElement
     */
    private $_mainPage;

    /**
     * xml_class object with tree structure
     * @var xml_class
     */
    private $_treeStructure;

    /**
     * tree pointer, with number of get element to process
     * @var integer
     */
    private $_pointer = 1;

    /**
     * current language
     * @var string
     */
    private $_language;

    /**
     * list of modules to load
     * @example [module_name] = array('param' => array(parameters array),
     *                                'exec'  => 'execute file',
     *                                'block' => 'block for modules')
     * @var array
     */
    public $mod = array();

    /**
     * list of libraries to load
     * @example [package_name + libraries] = array(parameters array)
     * @var array
     */
    public $lib = array();

    /**
     * list of css files, external and internal
     * @example array('external' => array(external css),
     *                'internal' => array(internal css))
     * @var array
     */
    public $css = array(
        'external' => array(),
        'internal' => array(
            'core' => array()
        )
    );

    /**
     * list of js files, external and internal
     * @example array('external' => array(external js),
     *                'internal' => array(internal js))
     * @var array
     */
    public $js = array(
        'external' => array(),
        'internal' => array(
            'core' => array()
        )
    );

    /**
     * array with path to current opened page
     * @example array([0] => array(list of page names),
     *                [1] => array(page id-s list))
     * @var array
     */
    public $breadcrumbs = array();

    /**
     * name of main layout to load
     * @var string
     * @access public
     */
    public $layout;

    /**
     * list of menu id-s to witch must be linked page
     * @var array
     */
    public $menu = array();

    /**
     * contains path to page for breadcrumbs
     * 
     * @var string
     */
    protected $_breadcrumbsPath = '';

    /**
     * start processing of pages tree
     * load list of libraries, modules, css, js and layouts for current page
     * 
     * @param string $language current language
     * @param array $get list of pages
     * @throws coreException core_error_16
     */
    public function __construct(array $get, $language)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'start tree class',
                debug_backtrace(),
                '#BF5702'
            ));
        }

        $this->_language        = $language;
        $this->_get             = $get;
        $this->_treeStructure   = new xml_class();

        $this->_load();
        $this->_setActivePage();

        if (!$this->layout) {
            throw new coreException('core_error_16', $this->layout);
        }
    }

    /**
     * create page map with paths
     * if page has parameter hidden=1 will be unavailable on list
     * if $full parameter is TRUE will return full list with all options
     * 
     * @param string $xml xml tree file to process, default is tree
     * @param boolean $full if TRUE, return complete list with all options
     * @return array
     * @throws coreException core_error_13
     */
    public function map($xml = 'tree', $full = FALSE)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'build map',
                debug_backtrace(),
                '#BF5702'
            ));
        }

        $bool = $this->_treeStructure->loadXmlFile(
            starter_class::path('cfg') . $xml . '.xml',
            TRUE
        );

        if (!$bool) {
            throw new coreException(
                'core_error_13',
                $this->_treeStructure->err . ' ' . $xml
            );
        }

        $map = $this->_subMap(
            $this->_treeStructure->documentElement->childNodes,
            '',
            $full
        );
        return $map;
    }

    /**
     * process xml pages tree and generate sitemap for google
     * @return string full page map in xml
     */
    public function siteMap()
    {
        $mainPage       = $this->_treeStructure->documentElement->childNodes;
        $this->_siteMap = new xml_class('1.0', 'UTF-8');
        $root           = $this->_siteMap->createElement('urlset');

        $root->setAttribute(
            'xmlns',
            'http://www.sitemaps.org/schemas/sitemap/0.9'
        );
        $this->_siteMap->appendChild($root);
        $this->_siteSubMap($mainPage);

        return $this->_siteMap->saveXmlFile(FALSE, TRUE);
    }

    /**
     * set last page in breadcrumbs array to active
     */
    protected function _setActivePage()
    {
        $breadcrumbsLastIndex = count($this->breadcrumbs) -1;
        $this->breadcrumbs[$breadcrumbsLastIndex]['active'] = 'active';
        unset($this->breadcrumbs[$breadcrumbsLastIndex]['path']);
    }

    /**
     * internal recurrent function return subpages tree and search inside another
     * process structure for sitemap
     * 
     * @param DOMNodeList $nodes collection of xml nodes to process
     * @param string $path parent path for elements
     */
    private function _siteSubMap(DOMNodeList $nodes, $path = '')
    {
        foreach ($nodes as $child) {
            if ($child->nodeType == 8) {
                continue;
            }

            $name = $child->nodeName;
            if (   $name === 'lib'
                || $name === 'mod'
                || $name === 'css'
                || $name === 'js'
            ){
                continue;
            }

            $id = $child->getAttribute('id');
            if ($child->childNodes 
                && $child->firstChild
                && (   $child->firstChild->nodeName === 'sub' 
                    || $child->firstChild->nodeName === 'page'
                )
            ) {
                $this->_siteSubMap($child->childNodes, "$path/$id");
            }

            $url        = $this->_siteMap->createElement('url');
            $changeFreq = $child->getAttribute('changefreq');
            if (!$changeFreq) {
                $changeFreq = 'never';
            }

            $changeFreq = $this->_siteMap->createElement('changefreq', $changeFreq);
            $priority   = $child->getAttribute('priority');
            if (!$priority) {
                $priority = '0.1';
            }

            $folder     = get::realPath(core_class::options('test'));
            $lang       = $this->_language;
            $priority   = $this->_siteMap->createElement('priority', $priority);
            $loc        = $this->_siteMap->createElement(
                'loc',
                "$folder$lang$path/$id"
            );

            $url->appendChild($loc);
            $url->appendChild($changeFreq);
            $url->appendChild($priority);
            $this->_siteMap->documentElement->appendChild($url);
        }
    }

    /**
     * internal recurrent function return tree of subpages and searching inside another
     * 
     * @param DOMNodeList $nodes collection of xml nodes to process
     * @param string $path parent path for elements
     * @param boolean $admin if TRUE, return complete list with options
     * @return array
     */
    private function _subMap(DOMNodeList $nodes, $path, $admin)
    {
        $map = array();
 
        foreach ($nodes as $child) {
            if($child->nodeType == 8){
                continue;
            }

            $name    = $child->nodeName;
            $options = $child->getAttribute('options');
            if (    $name === 'lib'
                || $name === 'mod'
                || $name === 'css'
                || $name === 'js'
            ){
                continue;
            }

            if (   (!$admin && !(bool)$options{0})
                || (!$admin && !(bool)$options{1}) 
                || (!$admin && !$this->_checkDate($child))
            ){
                continue;
            }

            $id = $child->getAttribute('id');
            if (    $child->childNodes
                && (   $child->firstChild->nodeName === 'sub' 
                    || $child->firstChild->nodeName === 'page'
                )
            ) {
                $map[$id]['sub'] = $this->_subMap(
                    $child->childNodes,
                    "$path/$id",
                    $admin
                );
            }

            $map[$id]['name'] = $child->getAttribute('name');
            $map[$id]['path'] = "{;path;$path/$id;}";
            if ($admin) {
                $map[$id]['options'] = array(
                    $options,
                    $child->getAttribute('startDate'),
                    $child->getAttribute('endDate'),
                );
            }
        }
        return $map;
    }

    /**
     * create navigation path
     */
    private function _breadcrumbs()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'create breadcrumbs',
                debug_backtrace(),
                '#BF5702'
            ));
        }

        $options = $this->_mainPage->getAttribute('options');

        if ((bool)$options{3}) {
            $this->_breadcrumbsPath .= $this->_mainPage->getAttribute('id') . '/';
            $list = [
                'id'        => $this->_mainPage->getAttribute('id'),
                'name'      => $this->_mainPage->getAttribute('name'),
                'path'      => $this->_breadcrumbsPath,
            ];
            $this->breadcrumbs[] = $list;
        }
    }

    /**
     * add main page (index) to breadcrumbs list, to return Main Page
     */
    protected function _setBreadcrumbsMainPage()
    {
        $basePage = $this->_treeStructure->getId('index');
        $list     = [
            'id'        => 'index',
            'name'      => $basePage->getAttribute('name'),
            'path'      => '/',
        ];
        $this->breadcrumbs[] = $list;
    }

    /**
     * check that given page exists and if not must show page with error404 index
     * 
     * @throws coreException core_error_14
     */
    private function _chk404()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'check that page exists',
                debug_backtrace(),
                '#BF5702'
            ));
        }

        if (!$this->_mainPage && (bool)core_class::options('error404')) {
            $path = 'error404';
            if (!(bool)core_class::options('rewrite')) {
                $path = "index.php?core_lang=$this->_language&p0=$path";
            }

            if (core_class::options('test')) {
                $path = core_class::options('test') . "/$path";
            }

            header("Location: /$path");
            exit;

        } elseif (!$this->_mainPage && !(bool)core_class::options('error404')) {
            $this->_mainPage = $this->_treeStructure->getId('index');
        }

        if (!$this->_mainPage) {
            throw new coreException('core_error_14');
        }
    }

    /**
     * check that given page will load other xml tree file 
     * if will, clear all loaded elements and load new tree, keeping all navigation paths
     */
    private function _external()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'check for external tree',
                debug_backtrace(),
                '#BF5702'
            ));
        }

        if ((bool)$this->_mainPage->getAttribute('external')) {
            $this->_clear();
            $this->_load($this->_mainPage->getAttribute('external'));
        }
    }

    /**
     * recurrent function checking subpages (if exists)
     * and check that given in GET exists in tree
     * if exist depends on inheritance, add or remove modules, libraries, css, js
     */
    private function _tree()
    {
        if (   $this->_mainPage->firstChild->nodeName === 'sub'
            && isset($this->_get[$this->_pointer])
        ){
            $children = $this->_mainPage->childNodes;

            foreach ($children as $child) {
                if( $child->nodeName === 'sub'
                    && $child->getAttribute('id') === $this->_get[$this->_pointer]
                ){
                    $this->_mainPage    = $child;
                    $options            = $child->getAttribute('options');

                    $this->_on();
                    $this->_checkDate();
                    $this->_redirect();
                    $this->_breadcrumbs();

                    if (!(bool)$options{4}) {
                        $this->_clear();
                    }

                    $this->_set();
                    $this->_menu();
                    $this->_pointer++;
                    $this->_tree();

                    break;
                } else {
                    $this->_mainPage = NULL;
                }
            }
            $this->_chk404();
        }
    }

    /**
     * check that current page is on
     * 
     * @throws coreException core_error_15
     */
    private function _on()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'check that page is on or off',
                debug_backtrace(),
                '#BF5702'
            ));
        }

        $options = $this->_mainPage->getAttribute('options');

        if (!(bool)$options{0}) {
            throw new coreException('core_error_15');
        }
    }

    /**
     * set on list modules, libraries, css and js
     * 
     * @param boolean $root if TRUE load tree root element, else process page/subpage
     */
    private function _set($root = FALSE)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'set list of modules/libs/css/js',
                debug_backtrace(),
                '#BF5702'
            ));
        }

        if (!$root) {
            $this->layout = $this->_mainPage->getAttribute('layout');
        }

        foreach ($this->_mainPage->childNodes as $nod) {
            switch($nod->nodeName){

                case'lib':
                    if ((bool)$nod->getAttribute('on')) {
                        $this->lib[$nod->nodeValue] = $nod->nodeValue;
                    }
                    break;

                case'mod':
                    if ((bool)$nod->getAttribute('on')) {
                        $this->mod[$nod->nodeValue]['param'] = $this->_parameters(
                            $nod->getAttribute('param')
                        );

                        $this->mod[$nod->nodeValue]['exec'] = $nod->getAttribute(
                            'exec'
                        );

                        $this->mod[$nod->nodeValue]['block'] = $nod->getAttribute(
                            'block'
                        );
                    }
                    break;

                case'css':
                    $type = $this->_isExternal($nod);
                    if ($nod->getAttribute('media')) {
                        $media = $nod->getAttribute('media');
                        $value = $nod->nodeValue;

                        $this->css[$type]['core']['media'][$media][$value]
                            = $nod->nodeValue;
                    } else {
                        $this->css[$type]['core'][$nod->nodeValue] = $nod->nodeValue;
                    }
                    break;

                case'js':
                    $type = $this->_isExternal($nod);
                    $this->js[$type]['core'][$nod->nodeValue] = $nod->nodeValue;
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * clean lists when inheritance is off, or external tree was loaded
     */
    private function _clear()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'clear list for inheritance switched off',
                debug_backtrace(),
                '#BF5702'
            ));
        }

        $this->layout = '';
        $this->lib      = array();
        $this->mod      = array();

        $this->css = array(
            'external' => array(),
            'internal' => array()
        );
        $this->js = array(
            'external' => array(),
            'internal' => array()
        );
    }

    /**
     * load tree xml file, load content to memory and start processing 
     * 
     * @param string $xml xml file name, default is tree
     * @throws coreException core_error_13
     */
    private function _load($xml = 'tree')
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'load tree xml file',
                debug_backtrace(),
                '#BF5702'
            ));
        }

        $bool = $this->_treeStructure->loadXmlFile(
            starter_class::path('cfg') . $xml . '.xml',
            TRUE
        );

        if (!$bool) {
            throw new coreException(
                'core_error_13 ',
                $this->_treeStructure->err . ' ' . $xml
            );
        }

        if (empty($this->_get)) {
            $this->_masterPageName = 'index';
        } else {
            if ($xml !== 'tree') {
                $param = $this->_get[1];
            } else {
                $param = $this->_get[0];
            }

            $this->_masterPageName = $param;
        }

        $this->_mainPage = $this->_treeStructure->documentElement;
        $this->_on();
        $this->_set(TRUE);
        $this->_mainPage = $this->_treeStructure->getId($this->_masterPageName);
        $this->_chk404();
        $this->_on();
        $this->_redirect();
        $this->_external();
        $this->_setBreadcrumbsMainPage();
        $this->_breadcrumbs();
        $this->_set();
        $this->_menu();
        $this->_tree();
    }

    /**
     * check that css or js is an internal or external
     * 
     * @param DOMElement $nod
     * @return string (internal|external)
     */
    private function _isExternal(DOMElement $nod)
    {
        if ($nod->getAttribute('external')) {
            return 'external';
        } else {
            return 'internal';
        }
    }

    /**
     * process parameters given by libraries/modules
     * return parameters converted to array
     * 
     * @param string $param
     * @return array|boolean
     */
    private function _parameters($param)
    {
        $option = core_class::options('param_sep');

        if ($option) {
            $sep = $option;
        } else {
            $sep = '::';
        }

        $config = explode($sep, $param);
        if (empty($config)) {
            return NULL;
        }

        return $config;
    }

    /**
     * set link between pages/subpages and menus
     */
    private function _menu()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'set link between pages and subpages',
                debug_backtrace(),
                '#BF5702'
            ));
        }

        $options = $this->_mainPage->getAttribute('options');

        if ((bool)$options{2}) {
            $menu = $this->_mainPage->getElementsByTagName('menu');

            if ($menu) {
                foreach ($menu as $element) {
                    $this->menu[] = $element->nodeValue;
                }
            } else {
                $this->menu[] = 'main';
            }
        }
    }

    /**
     * if page/subpage has redirect, redirect from current page to given
     */
    private function _redirect()
    {
        $location = $this->_mainPage->getAttribute('redirect');

        if ($location) {
            header('Location: ' . $location);
            exit;
        }
    }

    /**
     * check date of start showing and end showing current page, and show error
     * 
     * @param DOMElement|boolean $node node to check, if FALSE check main page
     * @return boolean TRUE if page is available, otherwise FALSE
     * @throws coreException core_error_23, core_error_24
     */
    private function _checkDate($node = FALSE)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'check page show/expire time',
                debug_backtrace(),
                '#BF5702'
            ));
        }

        if (!$node) {
            $node = $this->_mainPage;
        }

        $time = $node->getAttribute('startDate');
        if ($time && $time > time()) {
            if (!$node) {
                $date = strftime('%c', $time);
                throw new coreException('core_error_23', $date);
            }
            return FALSE;
        }

        $time = $node->getAttribute('endDate');
        if ($time && $time < time()) {
            if (!$node) {
                $date = strftime('%c', $time);
                throw new coreException('core_error_24', $date);
            }
            return FALSE;
        }

        return TRUE;
    }
}
