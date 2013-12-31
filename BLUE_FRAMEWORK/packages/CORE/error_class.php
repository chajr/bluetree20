<?php
/**
 * @category    BlueFramework
 * @package     BlueFramework Core
 * @subpackage  error
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     2.4.6
 */

/**
 * function to catch $buffer and give stream to error_class::fatal method
 * 
 * @param string $buffer
 * @return string content or error
 */
function fatal($buffer)
{
    return error_class::fatal($buffer);
}

/**
 * function to handle php errors
 * 
 * @param integer $number error number
 * @param string $string error content
 * @param string $file
 * @param integer $line
 */
function error($number, $string, $file, $line)
{
    $error = new error_class();
    $error->addError('critic', $number, $string, $line, $file, '');
    echo $error->render();
    exit;
}

/**
 * error handling class
 */
final class error_class
{
    /**
     * contains lang_class
     * @var lang_class 
     */
    private $_lang;

    /**
     * contains display_class
     * @var display_class 
     */
    private $_display;

    /**
     * framework options
     * @var array 
     */
    private $_options;

    /**
     * array of messages
     * @var array
     */
    static $list = [
        'critic'    => [],
        'warning'   => [],
        'info'      => [],
        'ok'        => [],
        'pointer'   => [],
    ];

    /**
     * start error handling class
     * if $lang variable was given, start class for core usage
     * other start to handle php errors
     * 
     * @param string|boolean $lang
     */
    public function __construct($lang = FALSE)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker([
                'create error object to handle errors',
                debug_backtrace(),
                '#ff0000'
            ]);
        }

        if ($lang) {
            $this->_lang     = $lang;
            $this->_options  = core_class::options();
        } else {
            starter_class::package(
                'CORE/lang_class,option_class,display_class,globals_class'
            );
            $this->_options = option_class::load();

            if ($this->_options['timezone']) {
                @date_default_timezone_set($this->_options['timezone']);
            }

            if ($this->_options['rewrite']) {
                $uri  = $_SERVER['REQUEST_URI'];
                $get  = get::convertGet($this->_options['test'], $uri);
                $lang = lang_class::checkLanguage($get);
            } else {
                $lang =  $this->_options['lang'];
            }

            if (!$lang) {
                $lang = $this->_options['lang'];
            }

            $this->_lang     = new lang_class($lang, $this->_options);
            $this->_display  = new display_class([
                'template' => 'error',
                'language' => $this->_lang->lang,
                'options'  => $this->_options,
            ]);
        }
    }

    /**
     * handling fatal error
     * check if in buffer was some php error occurred
     * if was prepare framework error to show and data for log file 
     * 
     * @param string $buffer captured page content
     * @return string
     */
    static final function fatal($buffer)
    {
        $bool = preg_match('#(error</b>:)(.+)(<br />)#', $buffer);

        if ($bool) {
            $array      = preg_split('#<(\/)?b>#', $buffer);
            $buffer     = starter_class::load('elements/layouts/error.html', TRUE);
            $xml        = starter_class::load('cfg/config.xml', TRUE);
            $position   = strpos($xml, '<option id="debug" value="');
            $length     = strlen('<option id="debug" value="');
            $position   += $length;
            $debug      = substr($xml, $position, 1);
            $position   = strpos($xml, '<option id="timezone" value="');
            $length     = strlen('<option id="timezone" value="');
            $position   += $length;
            $time       = substr($xml, $position);
            $position   = strpos($time, '"');
            $time       = substr($time, 0, $position);

            $buffer = str_replace(
                [
                    '{;lang;error_title;}',
                    '{;errors;error_code;}',
                ],
                [
                    'CRITICAL PHP ERROR',
                    $array[1],
                ],
                $buffer
            );

            @date_default_timezone_set($time);
            $date   = strftime('%H:%M:%S - %d-%m-%Y');
            $buffer = str_replace('{;date;}', $date, $buffer);

            if ((bool)$debug) {
                $buffer = str_replace(
                    [
                        '{;errors;extend_message;}',
                        '{;errors;line;}',
                        '{;errors;file;}',
                    ],
                    [
                        'Message'.$array[2],
                        'Line: '.$array[5],
                        $array[3],
                    ],
                    $buffer
                );
            } else {
                $buffer = str_replace(
                    [
                        '{;errors;message;}',
                        '{;errors;line;}',
                        '{;errors;file;}',
                    ],
                    '',
                    $buffer
                );
            }

            $inf            = [];
            $inf['Error']   = $array[1];
            $inf['Message'] = $array[2];
            $inf['Line']    = $array[5];
            $inf['']        = $array[3];
            $other          = self::other();
            $inf            = array_merge($inf, $other);
            $bool           = self::log('fatal_error', $inf, $date);

            if (!$bool) {
                $buffer = str_replace('{;log;}', 'log error', $buffer);
            }

            $buffer = preg_replace('#{;([\\w;-])+;}#', '', $buffer);

            if ((bool)$debug) {
                if (class_exists('tracer_class')) {
                    $buffer .= tracer_class::display();
                }
            }
        }

        return $buffer;
    }

    /**
     * save log information in specified destination
     *
     * @param string $type log prefix
     * @param array|string $data array of information where key ins element name, val is value to display, or simply string with information
     * @param string|boolean $time optionally log creation time
     * @param string|boolean $path optionally destination path
     * @return boolean if log was saved properly
     * @example log('fatal', 'some error info', '23:12 27.05.2010')
     * @example log('error', array('info'=>'ajajajaj'))
     * @example log('inf', 'some message', 0, 'modules/mod')
     * @example log('fatal', array('error' => 'content' 'other' => array('info' => 'some info', 'info2' => 'another info')));
     */
    static final function log($type, $data, $time = FALSE, $path = FALSE)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker([
                'create log file',
                debug_backtrace(),
                '#900000'
            ]);
        }

        if ($time) {
            $time = preg_replace('#[ :]#', '_', $time);
        } else {
            $time = strftime('%H_%M_%S_-_%d-%m-%Y');
        }

        if (!$path) {
            $path = starter_class::path('log');
        }

        if (!file_exists($path)) {
            @mkdir($path);
            @chmod($path, 0777);
        }

        $path .= "$type-$time.log";

        if (is_array($data)) {
            $information = '';

            foreach ($data as $key => $val) {
                if (is_array($val)) {

                    foreach ($val as $key2 => $val2) {
                        $information .= "$key2: $val2\n";
                    }

                    $information .= "\n\n-------------------------------------------------------------------------------------------------\n\n";
                } else {
                    $information .= "$key: $val\n";
                }
            }
        } else {
            $information = $data;
        }

        $information .= "\n\n\n";
        return error_log($information, 3, $path);
    }

    /**
     * return some info for error (browser type, URI, date)
     * 
     * @return array
     */
    static final function other()
    {
        $inf            = [];
        $inf['ip']      = $_SERVER['REMOTE_ADDR'];
        $inf['browser'] = $_SERVER['HTTP_USER_AGENT'];
        $inf['URI']     = $_SERVER['REQUEST_URI'];
        $inf['date']    = strftime('%H:%M:%S - %d-%m-%Y');

        return $inf;
    }

    /**
     * add statement to correct array
     * 
     * @param string $type statement type or marker name to show error
     * @param integer $number error number
     * @param string $errorCode error code or error description
     * @param string $file file with error
     * @param integer $line error line number
     * @param array $errorMessage some other error info
     * @param string|boolean $mod name of module that want to sent error, if false error come from core
     * @return array array with formatted information or simple string
     * @example addError('warning', '', 'core_error_x', 1, 'CORE/core_class.php', 23 'some description', 'module_name')
     * @example addError('info', 1, 'some_code', 1, 'CORE/core_class.php', '', '')
     * @example addError('error_marker', '', 'some_code', 1, 'CORE/core_class.php', 'some description', 'module_name')
     */
    public function addError(
        $type,
        $number,
        $errorCode,
        $line,
        $file,
        $errorMessage,
        $mod = 'core'
    ){
        if (class_exists('tracer_class')) {
            tracer_class::marker([
                'add some error',
                debug_backtrace(),
                '#900000'
            ]);
        }

        preg_match_all(
            lang_class::$translationMarkers[0],
            $errorMessage,
            $capture
        );

        foreach ($capture[0] as $marker) {
            $newMarker = str_replace(
                '{;lang;',
                '{;lang;' . $mod . ';',
                $marker
            );

            $errorMessage = str_replace(
                $marker,
                $newMarker,
                $errorMessage
            );
        }

        switch ($type) {
            case'critic':
                $error = self::$list['critic'][$mod][] = $this->_statement(
                    $number,
                    $errorCode,
                    $file,
                    $line,
                    $errorMessage,
                    $mod
                );
                break;

            case'warning':
                $error = self::$list['warning'][$mod][] = $this->_statement(
                    '',
                    $errorCode,
                    $file,
                    $line,
                    $errorMessage,
                    $mod
                );
                break;

            case'info':
                $error = self::$list['info'][$mod][] = $this->_statement(
                    '',
                    $errorCode,
                    '',
                    '',
                    $errorMessage,
                    $mod
                );
                break;

            case'ok':
                $error = self::$list['ok'][$mod][] = $this->_statement(
                    '',
                    $errorCode,
                    '',
                    '',
                    $errorMessage,
                    $mod
                );
                break;

            default:
                $error = self::$list['pointer'][$mod][$type] = $this->_statement(
                    $number,
                    $errorCode,
                    $file,
                    $line,
                    $errorMessage,
                    $mod
                );
                break;
        }

        return $error;
    }

    /**
     * create formatted statement
     * 
     * @param integer $number error number
     * @param string $errorCode error info
     * @param string $file file with error
     * @param integer $line numer linii bledu
     * @param array $errorMessage some other error info
     * @param string $mod name of module that want to sent error, if false error come from core
     * @return array
     */
    private final function _statement(
        $number,
        $errorCode,
        $file,
        $line,
        $errorMessage,
        $mod
    ){
        if (class_exists('tracer_class')) {
            tracer_class::marker([
                'create formatted statement',
                debug_backtrace(),
                '#900000'
            ]);
        }

        $kom  = [];
        $bool = $this->_pack("modules/$mod/lang/" . $mod . '_error');

        if ($bool) {

            if (isset($bool[$errorCode])) {
                $errorCode = @str_replace(
                    $errorCode,
                    $bool[$errorCode],
                    $errorCode
                );
            }
        } else {
            $bool = $this->_pack("cfg/lang/core_error");

            if ($bool) {

                if (isset($bool[$errorCode])) {
                    $errorCode = @str_replace(
                        $errorCode,
                        $bool[$errorCode],
                        $errorCode
                    );
                }

            } else {
                @trigger_error(
                    'No default error pack<br/>' . $mod . '<br/>' . $errorCode
                );
            }
        }

        if (!$this->_options['debug']) {
            $file   = '';
            $number = '';
            $line   = '';
            $mod    = '';
        }

        if ($number) {
            $kom['number'] = $number;
        }

        $kom['error_code']      = $errorCode;
        $kom['extend_message']  = $errorMessage;

        if ($line) {
            $kom['line'] = $line;
        }

        if ($file) {
            $kom['file'] = $file;
        }

        if ($mod) {
            $kom['module'] = $mod;
        }

        return $kom;
    }

    /**
     * 
     * render error statement, sent log and return it content to display
     * if type set on true, render comes from core, in false render php errors
     * 
     * @param boolean $type if false -> critic, true -> array of errors
     * @return string|array error statement or array of errors
     */
    public final function render($type = FALSE)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker([
                'render error',
                debug_backtrace(),
                '#900000'
            ]);
        }

        if ($type) {
            $tempList = [
                'critic'    => '',
                'warning'   => '',
                'info'      => '',
                'ok'        => '',
                'pointer'   => '',
            ];

            foreach (self::$list as $type => $modules) {
                if (!empty($modules)) {
                    $temp = '';

                    foreach ($modules as $mod => $array) {
                        $bool = $this->_lang->setTranslationArray();

                        if ($type === 'pointer') {
                            if ($mod !== 'core') {
                                $bool = $this->_lang->setTranslationArray(
                                    $mod,
                                    TRUE,
                                    NULL,
                                    TRUE
                                );

                                if (!$bool) {
                                    @trigger_error(
                                        'No default lang pack<br/><br/>' . $mod
                                    );
                                }
                            }

                            foreach ($modules as $errorBlock) {
                                foreach ($errorBlock as $blockName => $errorContent) {

                                    $this->_display = new display_class([
                                        'template' => 'error_' . $type,
                                        'language' => $this->_lang->lang,
                                        'options'  => $this->_options,
                                    ]);

                                    $this->_display->generate(
                                        [
                                            'extend_message' => $errorContent['extend_message'],
                                            'error_code'     => $errorContent['error_code']
                                        ],
                                        ''
                                    );

                                    $this->_lang->translate($this->_display, TRUE);
                                    $content = $this->_display->render();
                                    unset($this->_display);

                                    $temp[] = [
                                        'content' => $content,
                                        'mod' => $mod,
                                        'point' => $blockName
                                    ];
                                    $op     = $this->_options['errors_log'];

                                    if((bool)$op{2}){
                                        self::log('pointer', $content);
                                    }
                                }
                            }
                            
                        } else {
                            $this->_display = new display_class([
                                'template' => 'error_' . $type,
                                'language' => $this->_lang->lang,
                                'options'  => $this->_options,
                            ]);

                            $this->_display->loop('errors', $array);

                            if ($mod !== 'core') {
                                $bool = $this->_lang->setTranslationArray(
                                    $mod,
                                    TRUE
                                );
                            }

                            if (!$bool) {
                                @trigger_error(
                                    'No default lang pack<br/><br/>' . $mod
                                );
                            }

                            $this->_lang->translate($this->_display);
                            $temp .= $this->_display->render();
                        }
                    }

                    $tempList[$type] = $temp;
                }
            }

            return $tempList;

        } else {
            $other  = self::other();
            $log    = array_merge(self::$list['critic']['core'], $other);

            self::log('critic', $log, $other['date']);

            $this->_display->loop('errors', self::$list['critic']['core']);
            $this->_display->generate($other, '');

            $bool = $this->_lang->setTranslationArray(FALSE, TRUE);

            if (!$bool) {
                @trigger_error('No default lang pack<br/><br/>');
            }

            $this->_lang->translate($this->_display);
            $content = $this->_display->render();

            if ($this->_options['debug']) {
                if (class_exists('tracer_class')) {
                    $content .= tracer_class::display();
                }
            }

            return $content;
        }
    }

    /**
     * load array with error codes
     * 
     * @param string $pack name of file to load and path to it
     * @return array|boolean with errors code and some description
     */
    private function _pack($pack)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker([
                'load array with error codes',
                debug_backtrace(),
                '#900000'
            ]);
        }

        $translationArray = $this->_lang->loadLanguage($pack);

        if (!$translationArray) {
            return FALSE;
        }

        return $translationArray;
    }
}
