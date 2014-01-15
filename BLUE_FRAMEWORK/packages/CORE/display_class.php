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
 * @version     2.11.5
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
    protected $_js = array();
    
    /**
     * array of css and contains with them modules to load
     * @var array
     */
    protected $_css = array();
    
    /**
     * framework options
     * @var array
     */
    protected $_options;
    
    /**
     * selected language code
     * @var string
     */
    protected $_lang;
    
    /**
     * get object
     * @var get
     */
    protected $_get;
    
    /**
     * session object
     * @var session
     */
    protected $_session;
    
    /**
     * regular expression that corresponds to all display class markers
     * @var string
    */
    protected $_contentMarkers = "#{;[\\w=\\-|&();\\/,]+;}#";

    /**
     * default class options
     * @var array
     */
    protected $_defaultOptions = array(
        'template'      => '',
        'independent'   => FALSE,
        'get'           => NULL,
        'session'       => NULL,
        'language'      => NULL,
        'css'           => NULL,
        'js'            => NULL,
        'options'       => array(),
        'clean'         => TRUE
    );

    /**
     * load templates and create layout to use in core, or individually
     * 
     * string|boolean $layout main layout name (if css/js NULL)
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'start display class',
                debug_backtrace(),
                '#006400'
            ));
        }

        $this->_defaultOptions = array_merge($this->_defaultOptions, $options);

        if ($this->_defaultOptions['options']) {
            $this->_options = $this->_defaultOptions['options'];
        } else {
            $this->_options = core_class::options();
        }

        $this->_lang    = $this->_defaultOptions['language'];
        $this->_css     = $this->_defaultOptions['css'];
        $this->_js      = $this->_defaultOptions['js'];
        $this->_get     = $this->_defaultOptions['get'];
        $this->_session = $this->_defaultOptions['session'];

        if ($this->_defaultOptions['independent']) {
            $this->layout($this->_defaultOptions['template']);
        } else {
            $this->_constructMainLayout();
        }

        $this->_external('core');
    }

    /**
     * load main layout and related with it external templates
     * fix paths and convert path markers
     */
    protected function _constructMainLayout()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'create main layout for display',
                debug_backtrace(),
                '#006400'
            ));
        }

        if ($this->_get) {
            $typ = $this->_get->pageType();
        } else {
            $typ = NULL;
        }

        switch ($typ) {
            case'css': case'js':
            $this->DISPLAY['core'] = '{;css_js;}';
            break;

            default:
                $this->layout($this->_defaultOptions['template']);
                break;
        }
    }

    /**
     * allows to render in template css nad js content
     */
    public function other()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'render css or js content in template',
                debug_backtrace(),
                '#006400'
            ));
        }

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
        $int        = 0;
        $content    = $this->_checkContent($content);

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
                $string             = $this->DISPLAY[$module];
                $convertedString    = str_replace(
                    '{;'.$marker.';}',
                    $content,
                    $string,
                    $int
                );
                $this->DISPLAY[$module] = $convertedString;
            }
        }
        return $int;
    }

    /**
     * check if content is array, and return serialized array and info to log
     * 
     * @param string|array $content
     * @return string
     */
    protected function _checkContent($content)
    {
        if (is_array($content)) {
            $exportContent = var_export($content, TRUE);
            error_class::log('inf', "detected content as array \n\n $exportContent");

            return serialize($content);
        }

        return $content;
    }

    /**
     * process array and generate proper for loop content
     * 
     * @param string $marker
     * @param array $contentArray
     * @param string|boolean $module name of module that wants to replace content (default core)
     * @return integer count of replaced markers
     * @example loop('marker', array(array(key => val), array(key2 => val2)), 'mod');
     * @example loop('marker', array(array(key => val), array(key2 => val2)));
     */
    public function loop($marker, array $contentArray, $module = NULL)
    {
        if (!$module) {
            $module = 'core';
        }
        $int                = 0;
        $startMarker        = '{;start;' . $marker . ';}';
        $endMarker          = '{;end;' . $marker . ';}';
        $messageStartMarker = '{;start_empty;' . $marker . ';}';
        $messageEndMarker   = '{;end_empty;' . $marker . ';}';
        $end                = '';
        $template           = $this->_getGroupMarkerContent(
            $module,
            $startMarker,
            $endMarker
        );

        if (NULL === $template) {
            return NULL;
        }

        if (empty($contentArray)) {
            $this->DISPLAY[$module] = str_replace(
                $template,
                '',
                $this->DISPLAY[$module]
            );

            $this->generate($messageStartMarker, '', $module);
            $this->generate($messageEndMarker, '', $module);
        } else {
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

            $emptyMessage = $this->_getGroupMarker(
                $module,
                $messageStartMarker,
                $messageEndMarker
            );

            $this->DISPLAY[$module] = str_replace(
                $emptyMessage,
                '',
                $this->DISPLAY[$module]
            );
        }

        return $int;
    }

    /**
     * get whole content without markers of marker group
     * 
     * @param string $module
     * @param string $startMarker
     * @param string $endMarker
     * @return null|string
     */
    protected function _getGroupMarkerContent($module, $startMarker, $endMarker)
    {
        $position1      = strpos($this->DISPLAY[$module], $startMarker);
        $position1      = $position1 + mb_strlen($startMarker);
        $position2      = strpos($this->DISPLAY[$module], $endMarker);
        $position2      = $position2 - $position1;

        if ($position2 < 0 || !$position1) {
            return NULL;
        }

        return substr($this->DISPLAY[$module], $position1, $position2);
    }

    /**
     * get whole content with markers of marker group (eg. loop marker and content)
     * 
     * @param string $module
     * @param string $startMarker
     * @param string $endMarker
     * @return null|string
     */
    protected function _getGroupMarker($module, $startMarker, $endMarker)
    {
        $position1      = strpos($this->DISPLAY[$module], $startMarker);
        $position2      = strpos($this->DISPLAY[$module], $endMarker);
        $position2      = $position2 + mb_strlen($endMarker);
        $position2      = $position2 - $position1;

        if ($position2 < 0 || !$position1) {
            return NULL;
        }

        return substr($this->DISPLAY[$module], $position1, $position2);
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
     * fix urls, clean from markers and optionally compress
     * 
     * @return string complete content to display
     */
    public function render()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'render content of display class',
                debug_backtrace(),
                '#006400'
            ));
        }

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
                '{;mod;' . $key . ';}',
                $val,
                $this->DISPLAY['core']
            );
            unset($this->DISPLAY[$key]);
        }

        foreach($blocks as $blockName => $blockContent){
            $this->DISPLAY['core'] = str_replace(
                '{;block;' . $blockName . ';}',
                $blockContent,
                $this->DISPLAY['core']
            );
        }

        $this->_link('css');
        $this->_link('js');
        $this->_session();

        $this->DISPLAY = $this->DISPLAY['core'];

        $this->_path();
        $this->_clean();
        $this->_compress();

        if (!(bool)$this->_options['debug']) {
            ob_clean();
        }
        return $this->DISPLAY;
    }

    /**
     * allows load template to DISPLAY array
     * 
     * @param string $template name of layout to load
     * @param string|boolean $module module name (default core)
     * @example layout('layout_name')
     * @example layout('layout_name', 'module')
     * @throws coreException core_error_2
     */
    public function layout($template, $module = 'core')
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'load layout to display class',
                debug_backtrace(),
                '#006400'
            ));
        }

        $path                   = $this->_checkTemplatePath($template, $module);
        $this->DISPLAY[$module] = starter_class::load($path, TRUE);
        $this->_external($module);

        if (!$this->DISPLAY[$module]) {
            throw new coreException('core_error_2', $module . ' - ' . $path);
        }
    }

    /**
     * return correct path for required templates
     * 
     * @param string $template
     * @param string $module
     * @return string
     */
    protected function _checkTemplatePath($template, $module)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'create path for required templates',
                debug_backtrace(),
                '#006400'
            ));
        }

        if ($this->_defaultOptions['independent']) {
            return $template;
        }

        if ($module === 'core') {
            $path = "elements/layouts/$template.html";
        } else {
            $path = "modules/$module/layouts/$template.html";
        }

        return $path;
    }

    /**
     * set data on session markers
     * only from public and display
     */
    protected function _session()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'set data on session markers',
                debug_backtrace(),
                '#006400'
            ));
        }

        if ($this->_session && $this->_session->returns('display')) {
            foreach ($this->_session->returns('display') as $key => $val) {
                $this->generate('session_display;' . $key, $val);
            }
        }
    }

    /**
     * create URLs to css/js from given on array files
     *
     * @param string $type (css | js)
     */
    protected function _link($type)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'create urls to js/css files',
                debug_backtrace(),
                '#006400'
            ));
        }

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
                                    . '" media="' . $key . $end . "\n";
                            }
                        }

                    } else {
                        $links .= "\t\t" . $front . $val . $end . "\n";
                    }
                }
            }
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
                        . $endPath . '" media="' . $key . $end . "\n";
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

        $this->generate('core;' . $type, $links);
    }

    /**
     * read content of css/js file
     * 
     * @param string $mod
     * @param string $param file name to read
     * @param string $type (css | js)
     * @return string return file content or empty string
     */
    protected function _read($mod, $param, $type)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'read contnt of js/css files',
                debug_backtrace(),
                '#006400'
            ));
        }

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
     * @param string $module module name that want to load external template
     * @throws coreException core_error_3
     */
    protected function _external($module)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'load external template',
                debug_backtrace(),
                '#006400'
            ));
        }

        $array = array();
        preg_match_all(
            '#{;external;([\\w\/-])+;}#',
            $this->DISPLAY[$module],
            $array)
        ;

        foreach ($array[0] as $element) {

            $name = str_replace(
                array('{;external;', ';}'),
                '',
                $element
            );

            $finalPath = $this->_checkTemplatePath($name, $module);
            $content   = starter_class::load($finalPath, TRUE);

            if (!$content) {
                throw new coreException('core_error_3', $finalPath);
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
    protected function _checkPath()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'check that path is given on error or normal page',
                debug_backtrace(),
                '#006400'
            ));
        }

        $path = array();

        if ($this->_get) {
            $path[0] = $this->_get->path();
            $path[1] = $this->_get->path(TRUE);
        } else {
            $path[0] = $path[1] = get::realPath($this->_options['test']);
        }

        return $path;
    }

    /**
     * replace paths marker with data
     * 
     * @example {;core;domain;} - set protocol, domain and test folder
     * @example {;core;lang;} - set language code if language support is enabled
     * @example {;path;some path;} - set converted path, without domain and language code
     * @example {;full;some path;} - set full path with domain and language code
     * @example {;rel;some path;} - set current path and write to it given path
     */
    protected function _path()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'replace path markers',
                debug_backtrace(),
                '#006400'
            ));
        }

        $path = $this->_checkPath();

        $this->DISPLAY = preg_replace(
            '#{;core;domain;}#',
            $path[0],
            $this->DISPLAY
        );

        $this->DISPLAY = preg_replace(
            '#{;core;lang;}#',
            $this->_getLanguageCode(),
            $this->DISPLAY
        );

        $this->DISPLAY = preg_replace(
            '#{;core;mainpath;}#',
            $path[1],
            $this->DISPLAY
        );

        preg_match_all(
            '#{;path;[\\w-/\.' . $this->_options['var_rewrite_sep'] . ']+;}#',
            $this->DISPLAY,
            $array
        );
        $this->_convert($array, 'path');

        preg_match_all(
            '#{;full;[\\w-/\.' . $this->_options['var_rewrite_sep'] . ']+;}#',
            $this->DISPLAY,
            $array
        );
        $this->_convert($array, 'full');

        preg_match_all(
            '#{;rel;[\\w-/\.' . $this->_options['var_rewrite_sep'] . ']+;}#',
            $this->DISPLAY,
            $array
        );
        $this->_convert($array, 'rel');
    }

    /**
     * return language code for url depends of system url type
     * 
     * @return string
     */
    protected function _getLanguageCode()
    {
        if (!$this->_options['rewrite']) {
            $lang = '?core_lang=' . $this->_lang . $this->_separator();
        } else {
            $lang = $this->_lang . '/';
        }

        return $lang;
    }

    /**
     * convert array of path markers into correct URLs
     * 
     * @param array $array array of markers
     * @param string $type type to convert (path|full|rel)
     */
    protected function _convert(array $array, $type)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'convert path markers to url',
                debug_backtrace(),
                '#006400'
            ));
        }

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
                        '#[' . $this->_options['var_rewrite_sep'] . ']{1}#',
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
    protected function _separator()
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
    protected function _clean()
    {
        if ($this->_defaultOptions['clean'] === FALSE) {
            return;
        }

        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'remove unused markers',
                debug_backtrace(),
                '#006400'
            ));
        }

        $this->_cleanMarkers('loop');
        $this->_cleanMarkers('optional');

        $this->DISPLAY = preg_replace(
            $this->_contentMarkers,
            '',
            $this->DISPLAY
        );
    }

    /**
     * clean template from unused markers on loops and optional values
     * 
     * @param string $type type to check
     */
    protected function _cleanMarkers($type)
    {
        switch ($type) {
            case'loop':
                $reg1 = '#{;(start|end);([\\w-])+;}#';
                $reg2 = FALSE;
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
                $end            += mb_strlen($endMarker);
                $len            = $end - $start;
                $stringToRemove = substr($this->DISPLAY, $start, $len);

                if ($reg2) {
                    $bool = preg_match($reg2, $string);
                    if ($bool) {
                        $this->DISPLAY = str_replace($stringToRemove, '', $this->DISPLAY);
                    } else {
                        $this->DISPLAY = str_replace($stringToRemove, $string, $this->DISPLAY);
                    }
                } else {
                    $this->DISPLAY = preg_replace($reg1, '', $this->DISPLAY);
                }
            }
        }
    }

    /**
     * compress content with given compress level
     */
    protected function _compress()
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
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'conversion to classic url',
                debug_backtrace(),
                '#006400'
            ));
        }

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
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'conversion to mode rewrite url',
                debug_backtrace(),
                '#006400'
            ));
        }

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
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'return url parameters',
                debug_backtrace(),
                '#006400'
            ));
        }

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
