<?php
/**
 * class responsible for translating content
 * @category    BlueFramework
 * @package     BlueFramework Core
 * @subpackage  language
 * @author      Michał Adamiak    <chajr@bluetree.pl>
 * @copyright   chajr/bluetree
 * @version     2.8.0
 */
class lang_class
{
    /**
     * array of language codes, accepted by framework
     * check with language that are switched on in framework option file
     * @var array
     */
    static $codes = array('pl-PL', 'en-GB', 'en-EN', 'en-US', 'de-DE', 'af-AF',
        'ar-SA', 'ar-EG', 'ar-DZ', 'ar-TN', 'ar-YE', 'ar-JO', 'ar-KW', 'ar-BH',
        'eu-EU', 'be-BE', 'zh-TW', 'zh-HK', 'hr-HR', 'da-DA', 'nl-BE', 'en-AU',
        'en-NZ', 'en-ZA', 'en-TT', 'fo-FO', 'fi-FI', 'fr-BE', 'fr-CH', 'gd-GD',
        'de-LI', 'de-AT', 'he-HE', 'hu-HU', 'id-ID', 'it-CH', 'ko-KO', 'lv-LV',
        'mk-MK', 'mt-MT', 'no-NO', 'pt-BR', 'rm-RM', 'ro-MO', 'ru-MO', 'sr-SR',
        'sk-SK', 'sb-SB', 'es-CR', 'es-DO', 'es-CO', 'es-AR', 'es-CL', 'es-PY',
        'es-SV', 'es-NI', 'sx-SX', 'sv-FI', 'ts-TS', 'tr-TR', 'ur-UR', 'vi-VI',
        'ji-JI', 'sq-SQ', 'ar-IQ', 'ar-LY', 'ar-MA', 'ar-OM', 'ar-SY', 'ar-LB',
        'ar-AE', 'ar-QA', 'bg-BG', 'ca-CA', 'zh-CN', 'zh-SG', 'cs-CS', 'nl-NL',
        'en-CA', 'en-IE', 'en-JM', 'en-BZ', 'et-ET', 'fa-FA', 'fr-FR', 'fr-CA',
        'fr-LU', 'ga-GA', 'de-CH', 'de-LU', 'el-EL', 'hi-HI', 'is-IS', 'it-IT',
        'ja-JA', 'ko-KO', 'lt-LT', 'ms-MS', 'no-NO', 'pt-PT', 'ro-RO', 'ru-RU',
        'sz-SZ', 'sr-SR', 'sl-SL', 'es-ES', 'es-GT', 'es-PA', 'es-VE', 'es-PE',
        'es-EC', 'es-UY', 'es-BO', 'es-HN', 'es-PR', 'sv-SV', 'th-TH', 'tn-TN',
        'uk-UK', 've-VE', 'xh-XH', 'zu-ZU'
    );

    /**
     * markers to search translations
     * @var array
     */
    static $translationMarkers = array(
        '#{;lang;([\\w-])+;}#', 
        '#{;lang;([\\w-])+;([\\w-])+;}#', 
        '#{;lang-(\\w){2}-(\\w){2};([\\w-])+;}#', 
        '#{;lang-(\\w){2}-(\\w){2};([\\w-])+;([\\w-])+;}#'
    );

    /**
     * language code, that is set in framework
     * @var string
     */
    public $lang;

    /**
     * default framework language
     * @var string
     */
    public $default;

    /**
     * information that language support is on
     * @var boolean
     */
    private $_languageSupport;

    /**
     * display object
     * @var object
     */
    private $_display;

    /**
     * contain translation array
     * @var array
     */
    private $_translations;

    /**
     * framework options
     * @var array
     */
    private $_options;

    /**
     * start language support
     * 
     * @param string $languageCode language code from URL or NULL if URL dot have language code
     * @param array|boolean $options
     * @throws coreException core_error_17
     */
    public function __construct($languageCode, $options = NULL)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'start language class',
                debug_backtrace(),
                '#6802CF'
            ));
        }

        if ($options) {
            $this->_options = $options;
        } else {
            $this->_options = core_class::options();
        }

        if ((bool)$this->_options['lang_support']) {
            $this->_languageSupport = TRUE;
        } else {
            $this->_languageSupport = FALSE;
            $this->_setLanguageLocalization($languageCode);
            return;
        }

        if ($languageCode && !in_array($languageCode, $this->_options['lang_on'])) {
            throw new coreException('core_error_17', $languageCode);
        }

        if ((bool)$this->_options['one_lang']) {
            $this->_setLanguageLocalization();
            return;
        }

       $this->_setLanguageLocalization($languageCode);
    }

    /**
     * set language and default language with localization
     * 
     * @param null|string $languageCode
     */
    protected function _setLanguageLocalization($languageCode = NULL)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'set localization and language',
                debug_backtrace(),
                '#6802CF'
            ));
        }

        $this->_setLanguage($languageCode);
        $language = str_replace('-', '_', $this->lang);
        setlocale(LC_ALL, $language . '.UTF8');
    }

    /**
     * set array to translate
     * 
     * @param string|boolean $mod optionally name of module that want to translate
     * @param boolean $type if FALSE setArray started from core, else run by error_class
     * @param string|boolean $languageCode force to load array with given language
     * @param boolean $switch allows to load translations from module to core (for marker error)
     * @return boolean
     * @throws coreException core_error_19
     */
    public function setTranslationArray(
        $mod            = FALSE,
        $type           = FALSE,
        $languageCode   = NULL,
        $switch         = NULL
    ){
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'set translations',
                debug_backtrace(),
                '#6802CF'
            ));
        }

        if($this->_languageSupport){
            if (!$mod || $mod == 'core') {
                $mod  = 'core';
                $path = 'cfg/lang/';
            } else {
                $path = 'modules/' . $mod . '/lang/';
            }

            if ($languageCode) {
                $lang = $this->loadLanguage($path . $mod, $languageCode);
            } else {
                $lang = $this->loadLanguage($path . $mod);
            }

            if (!$lang) {
                if ($type) {
                    return FALSE;
                } else {

                    if ($languageCode) {
                        throw new coreException(
                            'core_error_19',
                            $path . $mod . '_' . $languageCode
                        );
                    }

                    throw new coreException(
                        'core_error_19',
                        $path . $mod . '_' . $this->lang
                    );
                }
            }

            if ($switch ) {
                $mod = 'core';
            }

            if ($languageCode) {
                if (!isset($this->_translations[$languageCode][$mod])) {
                    $this->_translations[$languageCode][$mod] = $lang;
                } else {
                    $this->_translations[$languageCode][$mod] = array_merge(
                        $this->_translations[$languageCode][$mod],
                        $lang
                    );
                }
                return TRUE;
            }

            if (!isset($this->_translations[$mod])) {
                $this->_translations[$mod] = $lang;
            } else {
                $this->_translations[$mod] = array_merge(
                    $this->_translations[$mod],
                    $lang
                );
            }
        }
        return TRUE;
    }

    /**
     * run translations of template and modules
     * 
     * @param display_class $display
     */
    public function translate(display_class $display)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'translate templates and modules',
                debug_backtrace(),
                '#6802CF'
            ));
        }

        if ($this->_languageSupport) {
            $this->_display = $display;

            if ($this->_languageSupport) {
                foreach ($this->_display->DISPLAY as $key => $mod) {
                    $expression1 = self::$translationMarkers[0];
                    $expression2 = self::$translationMarkers[1];
                    $expression3 = self::$translationMarkers[2];
                    $expression4 = self::$translationMarkers[3];

                    $bool1 = preg_match_all($expression1, $mod, $capture1);
                    $bool2 = preg_match_all($expression2, $mod, $capture2);
                    $bool3 = preg_match_all($expression3, $mod, $capture3);
                    $bool4 = preg_match_all($expression4, $mod, $capture4);

                    if ($bool1) {
                        $this->_markersReplace(1, $key, $capture1[0]);
                    }

                    if ($bool2) {
                        $this->_markersReplace(2, $key, $capture2[0]);
                    }

                    if ($bool3) {
                        $this->_markersReplace(3, $key, $capture3[0]);
                    }

                    if ($bool4) {
                        $this->_markersReplace(4, $key, $capture4[0]);
                    }
                }
            }
        }
    }

    /**
     * search in content for markers and replace them with translations 
     * 
     * @param integer $type marker type
     * @param string $name module name to translation
     * @param array $markers list of captured markers
     */
    private function _markersReplace($type, $name, $markers)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'replace markers with translations',
                debug_backtrace(),
                '#6802CF'
            ));
        }

        foreach ($markers as $marker) {
            $marker = str_replace(
                array('{;', ';}'),
                '',
                $marker
            );
            $key = explode(';', $marker);

            switch ($type) {
                case 1:
                    if (isset($this->_translations[$name][$key[1]])) {
                        $this->_display->generate(
                            $marker,
                            $this->_translations[$name][$key[1]],
                            $name
                        );
                    } else {
                        $this->_display->generate(
                            $marker,
                            '{' . $key[1] . '}',
                            $name
                        );
                    }
                    break;

                case 2:
                    if (isset($this->_translations[$key[1]][$key[2]])) {
                        $this->_display->generate(
                            $marker,
                            $this->_translations[$key[1]][$key[2]],
                            $name
                        );
                    } else {
                        $this->_display->generate(
                            $marker,
                            '{' . $key[2] . '}',
                            $name)
                        ;
                    }
                    break;

                case 3:
                    $languageCode = str_replace('lang-', '', $key[0]);
                    $this->setTranslationArray($name, FALSE, $languageCode);

                    if (isset($this->_translations[$languageCode][$name][$key[1]])) {
                        $this->_display->generate(
                            $marker,
                            $this->_translations[$languageCode][$name][$key[1]],
                            $name
                        );
                    } else {
                        $this->_display->generate(
                            $marker,
                            '{' . $key[1] . '}',
                            $name
                        );
                    }
                    break;

                case 4:
                    $languageCode = str_replace('lang-', '', $key[0]);
                    $this->setTranslationArray($key[1], FALSE, $languageCode);
                    
                    if (isset($this->_translations[$languageCode][$key[1]][$key[2]])) {
                        $this->_display->generate(
                            $marker,
                            $this->_translations[$languageCode][$key[1]][$key[2]],
                            $name
                        );
                    } else {
                        $this->_display->generate(
                            $marker,
                            '{' . $key[2] . '}',
                            $name
                        );
                    }
                    break;
            }
        }
    }

    /**
     * load language file and return array to translate
     * 
     * @param string $path
     * @param string $languageCode optionally code of different language to load
     * @return array
     */
    public function loadLanguage($path, $languageCode = NULL)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'load language',
                debug_backtrace(),
                '#6802CF'
            ));
        }

        if (!$languageCode) {
            $languageCode = $this->lang;
        }

        $bool = starter_class::load(
            $path . '_' . $languageCode . '.php',
            'content'
        );

        if (!$bool) {
            $bool = $this->_similar($path);
            if (!$bool) {
                $bool = starter_class::load(
                    $path . '_' . $this->default . '.php',
                    'content'
                );
            }
        }

        return $bool;
    }

    /**
     * set default and system language
     * 
     * @param string $current if given set that language as system
     */
    private function _setLanguage($current = NULL)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'sets language',
                debug_backtrace(),
                '#6802CF'
            ));
        }

        if ($current) { 
            $this->lang = $current;
        } else {

            if ((bool)$this->_options['detect_lang']) {
                $this->_detectLanguage();
            } else {
                $this->lang = $this->_options['lang'];
            }
        }

        $this->default = $this->_options['lang'];
    }

    /**
     * if main language is not loaded, then try to load similar language
     * 
     * @param string $path
     * @return array
     */
    private function _similar($path)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'search for similar language',
                debug_backtrace(),
                '#6802CF'
            ));
        }

        $similar    = explode('-', $this->lang);
        $search     = starter_class::path() . $path . '_' . $similar[0] . '*.php';
        $arr        = glob($search);

        if (!empty($arr)) {
            $code = explode('/', $arr[0]);
            $code = explode('-', end($code));
            $code = rtrim($code[1], '.php');

            return starter_class::load(
                $path . '_' . $similar[0] . '-' . $code . '.php',
                'content',
                0
            );
        } else {
            return FALSE;
        }
    }

    /**
     * automatically detect language from browser
     */
    private function _detectLanguage()
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'detect language',
                debug_backtrace(),
                '#6802CF'
            ));
        }

        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){ 
            $this->lang = $this->_options['lang'];
        }

        $lang = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $bool = preg_match('#^[a-z]{2}-[A-Z]{2}$#', $lang[0]);

        if (!$bool) {
            $this->lang = $lang[0] . '-xx';
        }

        $this->lang = $lang[0];
    }

    /**
     * return information that language is given as code, or NULL if not
     * if language code is not in GET and language support is on, redirect to correct language
     * get given as reference, to remove language code
     * 
     * @param array $get GET
     * @return string|boolean language code, or NULL
     */
    static function checkLanguage(&$get)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'check language code',
                debug_backtrace(),
                '#6802CF'
            ));
        }

        if (core_class::options('lang_support')) {

            $lang = self::_checkLanguageCode($get);

            if (in_array($lang, self::$codes)) {
                unset($get['core_lang']);
                unset($get[0]);

                return $lang;
            } else {
                self::_redirectWithLanguageCode($get);
            }
        }

        return NULL;
    }

    /**
     * check that first element isn't empty, or has language code and return it
     * or start redirecting to page with language code
     * 
     * @param array $get GET array
     * @return string|null
     */
    static protected function _checkLanguageCode($get)
    {
        if (isset($get['core_lang'])) {
            $languageCode = $get['core_lang'];
            return $languageCode;
        }

        if (isset($get[0])) {
            $languageCode = $get[0];
            return $languageCode;
        }

        self::_redirectWithLanguageCode($get);
        return NULL;
    }

    /**
     * redirect to page with language code, if language code was missing
     * 
     * @param array $get GET
     */
    static protected function _redirectWithLanguageCode($get)
    {
        if (class_exists('tracer_class')) {
            tracer_class::marker(array(
                'redirect to page with language code',
                debug_backtrace(),
                '#6802CF'
            ));
        }

        $url = display_class::explodeUrl(
            $get,
            core_class::options('var_rewrite_sep')
        );

        if ((bool)core_class::options('rewrite')) {
            $convertedUrl = display_class::convertToRewriteUrl(
                $url['params'],
                $url['pages']
            );
            $finalUrl = core_class::options('lang') . '/' . $convertedUrl;
        } else {
            $convertedUrl = display_class::convertToClassicUrl(
                $url['params'],
                $url['pages']
            );

            if ($convertedUrl) {
                $convertedUrl = '&' . $convertedUrl;
            }
            
            $finalUrl = '?core_lang=' . core_class::options('lang') . $convertedUrl;
        }

        if (core_class::options('test')) {
            header('Location: /' . core_class::options('test') . '/' . $finalUrl);
            exit;
        }

        header('Location: /' . $finalUrl);
        exit;
    }
}
