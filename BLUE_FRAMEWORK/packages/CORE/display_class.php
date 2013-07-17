<?php
/**
 * display class
 * processing templates, replacing markers, loops, render of whole content
 * loading external templates, clean from unused markers, set errors, fix paths
 * display css and js content
 *
 * @category    BlueFramework
 * @package     BlueFramework Core
 * @subpackage  display
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     2.5.0
 * 
 * @todo change class to allows use it independent in module
 */
class display_class
{
    /**
     * contains array with rendered content from main layout and modules
     * at the ane join array to string and save it to this variable
     * @var array|string
     */
    public $DISPLAY = array('core' => '');
    
    /**
     * if their been some elements must be loaded to block
     * that variable contains array with blocks and their modules
     * @var array|boolean
     */
    public $block = NULL;
    
    /**
     * array of js and contains with them modules to load
     * @var array
     */
    private $_js = array();
    
    /**
     * array of css and contains with them modules to load
     * @var array
     */
    private $_css = array();
    
    /**
     * framework options
     * @var array
     */
    private $_options;
    
    /**
     * selected language code
     * @var string
     */
    private $_lang;
    
    /**
     * get object
     * @var get
     */
    private $_get;
    
    /**
     * session object
     * @var session
     */
    private $_session;
    
    /**
     * regular expression that corresponds to all display class markers
     * @var string
    */
    private $_contentMarkers = "{;[\\w=\\-|&();\\/,]+;}";
    
    /**
     * load main layout and related with it external templates
     * fix paths and convert path markers
     * 
     * @param string|boolean $layout main layout name (if css/js NULL)
     * @param get $get
     * @param session $session
     * @param string $lang language code
     * @param array $css
     * @param array $js
     * @param array $options
     */
    public function __construct(
        $layout,
        $get,
        $session,
        $lang,
        $css,
        $js,
        $options = NULL
    ){
        $this->_lang    = $lang;
        $this->_css     = $css;
        $this->_js      = $js;
        $this->_get     = $get;
        $this->_session = $session;

        if ($options) {
            $this->_options = $options;
        } else {
            $this->_options = core_class::options();
        }

        if ($this->_get) {
            $typ = $this->_get->pageType();
        } else {
            $typ = NULL;
        }

        switch ($typ) {
            case'css':
            case'js':
                $this->DISPLAY['core'] = '{;css_js;}';
                break;

            default:
                $this->layout($layout);
                   break;
        }

        $this->_external();
    }

    /**
     * allows to render in template css nad js content
     * 
     * @todo cache for js and css files
     */
    public function other()
    {
        $content = '';
        if ($this->_get->pageType() === 'css') {
            header('content-type: text/css');
        } elseif ($this->_get->pageType() === 'js') {
            header("content-type: text/javascript");
        }
        
        foreach ($this->_get as $mod => $param) {
            if (is_array($param)) {

                foreach ($param as $val) {
                    $content .= $this->_read($mod, $val, $this->_get->pageType());
                }
            } else {
                $content .= $this->_read($mod, $param, $this->_get->pageType());
            }
        }
        
        $this->generate('css_js', $content);
    }

    /**
     * allows to replace marker with content, or group of markers by array
     * 
     * @param string|array $marker marker name or array (marker => value)
     * @param string|boolean $content some string or NULL if marker array given
     * @param string|boolean $module name of module that wants to replace content (default core)
     * @return integer count of replaced markers
     * @example generate('marker', 'content')
     * @example generate('marker', 'content', 'module')
     * @example generate(array('marker' => 'content', 'marker2' => 'other content'), '')
     */
    public function generate($marker, $content, $module = 'core')
    {
        $int = 0;

        if (isset($this->DISPLAY[$module])) {
            if (!$content && is_array($marker)) {

                foreach ($marker as $element => $content) {
                    $this->DISPLAY[$module] = str_replace(
                        '{;'.$element.';}',
                        $content,
                        $this->DISPLAY[$module],
                        $int2
                    );
                    $int += $int2;
                }

            } else {
                $this->DISPLAY[$module] = str_replace(
                    '{;'.$marker.';}',
                    $content,
                    $this->DISPLAY[$module],
                    $int
                );
            }
        }
        return $int;
    }

    /**
     * process array and generate proper for loop content
     * 
     * @param string $marker
     * @param array $contentArray
     * @param string|boolean $module name of module that wants to replace content (default core)
     * @return integer count of replaced markers
     * @uses display_class::$DISPLAY
     * @example loop('marker', array(array(key => val), array(key2 => val2)), 'mod');
     * @example loop('marker', array(array(key => val), array(key2 => val2)));
     */
    public function loop($marker, array $contentArray, $module = NULL)
    {
        if (!$module) {
            $module = 'core';
        }
        $int = 0;

        if ($contentArray) {
            $start          = '{;start;'.$marker.';}';
            $end            = '{;end;'.$marker.';}';

            $position1      = strpos($this->DISPLAY[$module], $start);
            $position1      = $position1 + mb_strlen($start);
            $position2      = strpos($this->DISPLAY[$module], $end);
            $position2      = $position2 - $position1;

            if ($position2 < 0) {
                return NULL;
            }

            $template   = substr($this->DISPLAY[$module], $position1, $position2);
            $end        = '';
            $int        = 0;

            foreach ($contentArray as $row) {
                $tmp = $template;

                foreach($row as $key => $value){
                    $model  = '{;'.$marker.';'.$key.';}';
                    $tmp    = str_replace($model, $value, $tmp);
                }

                $end .= $tmp;
            }

            $this->DISPLAY[$module] = str_replace(
                $template,
                $end,
                $this->DISPLAY[$module],
                $int2
            );
            $int += $int2;

            unset($end);
            unset($template);
            unset($contentArray);
        }
        return $int;
    }

    /**
     * load to array information about css and js to load from modules
     * 
     * @param string $module
     * @param string $name css/js name
     * @param string $type css or js
     * @param string $external information that file comes from external source or framework
     * @param string $media for css (eg. print, mobile)
     */
    public function set($module, $name, $type, $external, $media)
    {
        if ($type === 'js') {
            if ($media) {
                $this->_js[$external][$module]['media'][$media][$name] = $name;
            } else {
                $this->_js[$external][$module][$name] = $name;
            }

        } elseif($type === 'css') {
            if ($media) {
                $this->_css[$external][$module]['media'][$media][$name] = $name;
            } else {
                $this->_css[$external][$module][$name] = $name;
            }
        }
    }

    /**
     * join contents included in modules groups in complete page, replace paths
     * fix urls, clean from markers and optionaly compress
     * 
     * @return string complete content to displa 
     */
    public function render()
    {
        $blocks = array();
        foreach ($this->DISPLAY as $key => $val) {

            if ($key === 'core') {
                continue;
            }

            if ($this->block){ 
                if (isset($this->block[$key])) {
                    if (!isset($blocks[$this->block[$key]])) {
                        $blocks[$this->block[$key]] = '';
                    }

                    $blocks[$this->block[$key]] .= $val;
                }
            }

            $this->DISPLAY['core'] = str_replace(
                '{;mod;'.$key.';}',
                $val,
                $this->DISPLAY['core']
            );
            unset($this->DISPLAY[$key]);
        }

        foreach($blocks as $block_name => $block_content){
            $this->DISPLAY['core'] = str_replace(
                '{;block;'.$block_name.';}',
                $block_content,
                $this->DISPLAY['core']
            );
        }

        $this->_link('css');
        $this->_link('js');
        $this->_session(1);
        $this->_session(0);

        $this->DISPLAY = $this->DISPLAY['core'];

        $this->_path();
        $this->clean();
        $this->_compress();

        if (!(bool)$this->_options['debug']) {
            ob_clean();
        }
        return $this->DISPLAY;
    }

    /**
     * allows load template to DISPLAY array
     * 
     * @param string $layout name of layout to load
     * @param string|boolean $mod module name (if FALSE load to core)
     * @example layout('layout_name')
     * @example layout('layout_name', 'mod')
     * @throws coreException core_error_2
     */
    public function layout($layout, $mod = FALSE)
    {
        if (!$mod) {
            $path = "elements/layouts/$layout.html";
            $mod  = 'core';
        } else {
            $path = "modules/$mod/layouts/$layout.html";
        }

        $this->DISPLAY[$mod] = starter_class::load($path, TRUE);

        if (!$this->DISPLAY[$mod]) {
            throw new coreException('core_error_2', $mod . ' - ' . $path);
        }
    }

    /**
     * set data on session markers
     * 
     * @param boolean $type array type to set (TRUE = core, FALSE = public)
     */
    private function _session($type)
    {
        if ($this->_session) {
            if ($type) {
                $type  = 'core';
                $array = $this->_session->returns('display');
            } else {
                $type  = 'public';
                $array = $this->_session->returns('public');
            }

            if ($array) {
                foreach ($array as $key => $val) {
                    $this->generate('session_' . $type . ';' . $key . ';', $val);
                }
            }
        }
    }

    /**
     * create URLs to css/js from given on array files
     * 
     * @param string $type (css | js)
     * 
     * @todo check all css types
     */
    private function _link($type){
        $links      = '';
        $end        = '';
        $front      = '';

        switch ($type) {
            case'css':
                $front  = '<link href="';
                $end    = '" rel="stylesheet" type="text/css"/>';
                $arr    = $this->_css;
                break;

            case'js':
                $front  = '<script src="';
                $end    = '" type="text/javascript"></script>';
                $arr    = $this->_js;
                break;
        }

        if (!empty($arr['external'])) {
            foreach ($arr['external'] as $mod) {
                foreach ($mod as $val) {
                    if (is_array($val)) {

                        foreach ($val as $key => $media) {
                            foreach ($media as $file) {
                                $links .= "\t\t" . $front . $file
                                    . '" media="' . $key . $end."\n";
                            }
                        }

                    } else {
                        $links .= "\t\t" . $front . $val . $end . "\n";
                    }
                }
            }
            unset($arr['external']);
        }

        if (!empty($arr['internal'])) {
            $path       = '{;core;domain;}{;core;lang;}{;path;core_' . $type . '/';
            $endPath    = ';}';
            $media      = '';
            $internal   = '';
            $intMedia   = '';
            $key        = '';

            foreach ($arr['internal'] as $mod => $values) {
                if (isset($values['media'])) {

                    foreach ($values['media'] as $key => $elements) {
                        foreach ($elements as $file) {
                            $intMedia .= $mod . ',' . $file . '/';
                        }
                    }

                    $media .= "\t\t" . $front . $path . $intMedia
                        . $endPath . '" media="' . $key . $end."\n";
                    unset($values['media']);
                }

                foreach ($values as $file) {
                    $internal .= $mod . ',' . $file . '/';
                }
            }

            if ($internal) {
                $links .= "\t\t" . $front . $path . $internal . $endPath . $end . "\n";
            }
            if ($media) {
                $links .= $media;
            }
        }

        $this->generate('core;'.$type, $links);
    }

    /**
     * read content of css/js file
     * 
     * @param string $mod
     * @param string $param file name to read
     * @param string $type (css | js)
     * @return string zwraca zawartosc pliku, lub pusty string
     */
    private function _read($mod, $param, $type)
    {
        if ($mod === 'core') {
            $main = 'elements/' . $type . '/';
        } else {
            $main = 'modules/'  .$mod . '/elements/' . $type . '/';
        }

        $data = starter_class::load($main . $param . '.' . $type, TRUE);

        if ($data) {
            $content = $data . "\n";

            if ($type === 'js') {
                $content .= ';' . "\n";
            }

            return $content;
        }
        return '';
    }

    /**
     * load external templates to main template, 
     * or some external templates to module template
     *
     * @param string $module optionaly module name that want to load external template
     * @throws coreException core_error_3
     */
    private function _external($module = NULL)
    {
        $array = array();

        if (!$module) {
            $path   = 'elements/layouts/';
            $module = 'core';
        } else {
            $path = 'modules/' . $module . '/layout/';
        }

        preg_match_all('#{;external;([\\w-])+;}#', $this->DISPLAY[$module], $array);

        foreach ($array[0] as $element) {

            $name = str_replace(
                array(
                    '{;external;',
                    ';}'
                ),
                '',
                $element
            );

            $content = starter_class::load($path . $name . '.html', TRUE);
            if (!$content) {
                throw new coreException('core_error_3', $path . $name . '.html');
            }

            $this->DISPLAY[$module] = str_replace(
                $element,
                $content,
                $this->DISPLAY[$module]
            );
        }
    }

    /**
     * check that path is given on error or normal page
     * 
     * @return array of fix paths
     */
    private function _checkPath()
    {
        $path = array();

        if ($this->_get) {
            $path[0] = $this->_get->path();
            $path[1] = $this->_get->path(TRUE);
         }else {
            $path[0] = $path[1] = get::realPath($this->_options['test']);
        }

        return $path;
    }

    /**
     * replace paths marker with data
     * 
     * @example {;core;domain;} - set protocol, domain and test folder
     * @example {;core;lang;} - set language code if language support is enabled
     * @example {;path;jakas sciezka;} - set converted path, without domain and language code
     * @example {;full;jakas sciezka;} - set full path with domain and language code
     * @example {;rel;jakas sciezka;} - set current path and write to it given path
     */
    private function _path()
    {
        $path = $this->_checkPath();
        $this->DISPLAY = preg_replace(
            '#{;core;domain;}#',
            $path[0],
            $this->DISPLAY
        );

        if (!$this->_options['rewrite']) {
            $lang = '?core_lang=' . $this->_lang . $this->_separator();
        } else {
            $lang = $this->_lang . '/';
        }

        $this->DISPLAY = preg_replace('#{;core;lang;}#', $lang, $this->DISPLAY);
        $this->DISPLAY = preg_replace('#{;core;mainpath;}#', $path[1], $this->DISPLAY);

        preg_match_all(
            '#{;path;[\\w-/' . $this->_options['zmienne_rewrite_sep'] . ']+;}#',
            $this->DISPLAY,
            $array
        );
        $this->_convert($array, 'path');

        preg_match_all(
            '#{;full;[\\w-/' . $this->_options['zmienne_rewrite_sep'] . ']+;}#',
            $this->DISPLAY,
            $array
        );
        $this->_convert($array, 'full');

        preg_match_all(
            '#{;rel;[\\w-/' . $this->_options['zmienne_rewrite_sep'] . ']+;}#',
            $this->DISPLAY,
            $array);
        $this->_convert($array, 'rel');
    }

    /**
     * convert array of path markers into correct URLs
     * 
     * @param array $array array of markers
     * @param string $type type to convert (path|full|rel)
     */
    private function _convert(array $array, $type)
    {
        $update = '';

        if ($array) {
            $path = $this->_checkPath();

            switch ($type) {
                case'path':
                    $update = '';
                    break;

                case'full':
                    $update = $path[0];

                    if ($this->_lang) {
                        if ($this->_options['rewrite']) {
                            $update .= $this->_lang . '/';
                        } else {
                            $update .= '?core_lang=' . $this->_lang . $this->_separator();
                        }
                    } else {
                        if (!$this->_options['rewrite']) {
                            $update .= '?';
                        }
                    }
                    break;

                case'rel':
                    $update = $path[1];

                    if (!$this->_options['rewrite']) {
                        $update .= '?';
                    }
                    break;
            }

            foreach ($array[0] as $link) {
                $path = str_replace(
                    array(
                        '{;'.$type.';',
                        ';}'
                    ),
                    '',
                    $link
                );
                $path   = explode('/', $path);
                $pages  = array();
                $params = array();

                foreach ($path as $value) {
                    $bool = preg_match(
                        '#[' . $this->_options['zmienne_rewrite_sep'] . ']{1}#',
                        $value
                    );

                    if ($bool) {
                        $params[] = $value;
                    } elseif ($value) {
                        $pages[] = $value;
                    }
                }

                if ((bool)$this->_options['rewrite']) {
                    $final = self::convertToRewriteUrl($params, $pages);
                } else {
                    $final = self::convertToClassicUrl(
                        $params,
                        $pages,
                        $this->_separator()
                    );
                }

                if ($update) {
                    $final = $update . $final;
                }

                $this->DISPLAY = str_replace($link, $final, $this->DISPLAY);
            }
        }
    }

    /**
     * return ampersand as char or entity for xhtml or js
     * 
     * @return string (& | &amp;)
     */
    private function _separator()
    {
        if (!empty($this->_js)) {
            $separator = '&';
        } else {
            $separator = '&amp;';
        }

        return $separator;
    }

    /**
     * run clean methods to remove unused markers
     */
    private function clean()
    {
        $this->_cleanMarkers('optional');
        $this->_cleanMarkers('loop');
        
        $this->DISPLAY = preg_replace(
            '#' . $this->_contentMarkers . '#',
            '',
            $this->DISPLAY
        );
    }

    /**
     * clean template from unused markers on loops and optional values
     * 
     * @param string $typ typ do sprawdzenia
     */
    private function _cleanMarkers($typ)
    {
        switch($typ){
            case'loop':
                $reg1 = '#{;(start|end);([\\w-])+;}#';
                $reg2 = '#{;([\\w-])+;([\\w-])+;}#';
                $reg3 = '{;start;';
                $reg4 = '{;end;';
                break;

            case'optional':
                $reg1 = '#{;op;([\\w-])+;}#';
                $reg2 = $this->_contentMarkers;
                $reg3 = '{;op;';
                $reg4 = '{;op_end;';
                break;

            default:
                return;
                break;
        }

        preg_match_all($reg1, $this->DISPLAY, $array);
        if (!empty($array) && !empty($array[0])) {
            foreach ($array[0] as $marker) {

                $start      = strpos($this->DISPLAY, $marker);
                $endMarker  = str_replace($reg3, $reg4, $marker);
                $end        = strpos($this->DISPLAY, $endMarker);

                if (!$start || !$end) {
                    continue;
                }

                $startContent   = $start + mb_strlen($marker);
                $contentLength  = $end - $startContent;
                $string         = substr($this->DISPLAY, $startContent, $contentLength);
                $len            = ($end += mb_strlen($endMarker)) - $start;
                $stringToRemove = substr($this->DISPLAY, $start, $len);
                $bool           = preg_match($reg2, $string);

                if ($bool) {
                    $this->DISPLAY = str_replace($stringToRemove, '', $this->DISPLAY);
                } else {
                    $this->DISPLAY = str_replace($stringToRemove, $string, $this->DISPLAY);
                }
            }
        }
    }

    /**
     * compress content with given compress level
     */
    private function _compress()
    {
        if ((bool)$this->_options['compress']) {
            header('Content-encoding: gzip');
            $this->DISPLAY = gzcompress($this->DISPLAY, $this->_options['compress']);
        }
    }

    /**
     * convert data to classic URL path
     * 
     * @param array $params array of parameters
     * @param array $pages array of pages
     * @param string $separator
     * @return string
     */
    static function convertToClassicUrl(array $params, array $pages, $separator = '&')
    {
        $counter    = 0;
        $final      = '';

        foreach ($pages as $page) {
            $final .= 'p' . $counter . '=' . $page . $separator;
            $counter++;
        }

        foreach ($params as $param) {
            $param  = str_replace(',', '=', $param);
            $final .= $param . $separator;
        }

        $final = rtrim($final, '&amp;');
        $final = rtrim($final, '&');

        return $final;
    }

    /**
     * convert data to mode rewrite URL
     * 
     * @param array $params array of parameters
     * @param array $pages array of pages
     * @return string
     */
    static function convertToRewriteUrl(array $params, array $pages)
    {
        $final = '';

        foreach ($pages as $page) {
            $final .= $page . '/';
        }

        foreach ($params as $param) {
            $final .= $param . '/';
        }

        $final = rtrim($final, ',');

        return $final;
    }

    /**
     * process given get array and separate from them paths and parameters
     * 
     * @param array $path array of get elements to check
     * @param string $separator char that will separate value from name
     * @return array
     */
    static function explodeUrl($path, $separator)
    {
        $pages  = array();
        $params = array();

        foreach ($path as $value) {
            if (preg_match('#[' . $separator . ']{1}#', $value)) {
                $params[] = $value;
            } elseif ($value) {
                $pages[] = $value;
            }
        }

        return array('pages' => $pages, 'params' => $params);
    }
}
