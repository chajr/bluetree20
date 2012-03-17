<?php
/**
 * @author chajr <chajr@bluetree.pl>
 * @version 2.3.5
 * @access private
 * @copyright chajr/bluetree
 * @package core
*/
/**
 * klasa odpowiedzialna za dokonywanie tulmaczen w renderowanej tresci, lub bledach
 * @package core
 */
class lang_class{
	/**
	 * tablica kodow jezykowych akceptowanych przez frameworka
	 * porownywane z lista wlaczonych jezykow znajdujaca sie w pliku config.xml
	 * @var array
	 */
	static $codes = array('pl-PL', 'en-GB', 'en-EN', 'en-US', 'de-DE', 'af-AF', 'ar-SA', 'ar-EG', 'ar-DZ', 'ar-TN', 'ar-YE', 'ar-JO', 'ar-KW', 'ar-BH',
		'eu-EU', 'be-BE', 'zh-TW', 'zh-HK', 'hr-HR', 'da-DA', 'nl-BE', 'en-AU', 'en-NZ', 'en-ZA', 'en-TT', 'fo-FO', 'fi-FI', 'fr-BE', 'fr-CH', 'gd-GD',
		'de-LI', 'de-AT', 'he-HE', 'hu-HU', 'id-ID', 'it-CH', 'ko-KO', 'lv-LV', 'mk-MK', 'mt-MT', 'no-NO', 'pt-BR', 'rm-RM', 'ro-MO', 'ru-MO', 'sr-SR',
		'sk-SK', 'sb-SB', 'es-CR', 'es-DO', 'es-CO', 'es-AR', 'es-CL', 'es-PY', 'es-SV', 'es-NI', 'sx-SX', 'sv-FI', 'ts-TS', 'tr-TR', 'ur-UR', 'vi-VI',
		'ji-JI', 'sq-SQ', 'ar-IQ', 'ar-LY', 'ar-MA', 'ar-OM', 'ar-SY', 'ar-LB', 'ar-AE', 'ar-QA', 'bg-BG', 'ca-CA', 'zh-CN', 'zh-SG', 'cs-CS', 'nl-NL',
		'en-CA', 'en-IE', 'en-JM', 'en-BZ', 'et-ET', 'fa-FA', 'fr-FR', 'fr-CA', 'fr-LU', 'ga-GA', 'de-CH', 'de-LU', 'el-EL', 'hi-HI', 'is-IS', 'it-IT',
		'ja-JA', 'ko-KO', 'lt-LT', 'ms-MS', 'no-NO', 'pt-PT', 'ro-RO', 'ru-RU', 'sz-SZ', 'sr-SR', 'sl-SL', 'es-ES', 'es-GT', 'es-PA', 'es-VE', 'es-PE',
		'es-EC', 'es-UY', 'es-BO', 'es-HN', 'es-PR', 'sv-SV', 'th-TH', 'tn-TN', 'uk-UK', 've-VE', 'xh-XH', 'zu-ZU'
	);
	/**
	 * bloki do wyszukiwania w tulmaczeniach
	 * @var array
	 */
	static $translation_blocks = array(
		'#{;lang;([\\w-])+;}#', 
		'#{;lang;([\\w-])+;([\\w-])+;}#', 
		'#{;lang-(\\w){2}-(\\w){2};([\\w-])+;}#', 
		'#{;lang-(\\w){2}-(\\w){2};([\\w-])+;([\\w-])+;}#'
	);
	/**
	 * kod jezyka jaki zostal ustawiony we framework
	 * @var string
	 */
	public $lang;
	/**
	 * domyslny jezyk dla frameworka
	 * @var string
	 */
	public $default;
	/**
	 * informacja dla metod czy obsluga jezykow jest wlaczona
	 * @var boolean
	 */
	private $lang_support;
	/**
	 * obiekt display
	 * @var object
	 */
	private $display;
	/**
	 * przechowuje tablice tulmaczen
	 * @var array
	 */
	private $translations;
	/**
	 * tablica opcji frameworka
	 * @var array
	 */
	private $options;
	/**
	 * uruchamia obsluge jezyka
	 * @param mixed $lang wybrany jezyk z ciagu url lub NULL jesli nie bylo w url-u
	 * @return boolean zwraca NULL, return uruchamiany gdy wybrano opcje bez jezyka, albo tylko z jednym jezykiem
	 * @uses core_class::$options()
	 * @uses lang_class::$lang_support
	 * @uses lang_class::set_lang()
	 * @throws core_exception core_error_17
	 */
	public function __construct($lang, $options = NULL){
		if($options){
			$this->options = $options;
		}else{
			$this->options = core_class::options();
		}
		if((bool)$this->options['lang_support']){
			$this->lang_support = TRUE;
		}else{
			$this->lang_support = FALSE;
			setlocale(LC_ALL, str_replace('-', '_', $this->options['lang']).'.UTF8');
			return;
		}
		if((bool)$this->options['one_lang']){
			$this->set_lang();
			return;
		}
		if($lang && !in_array($lang, $this->options['lang_on'])){
			throw coreException('core_error_17', $lang);
		}
		setlocale(LC_ALL, str_replace('-', '_', $lang).'.UTF8');
		$this->set_lang($lang);
	}
	/**
	 * ustawia tablice do tulmaczen
	 * @param string $mod opcjonalnie nazwa modulu chcacego dokonac swojego tulmaczenia
	 * @param boolean $type jesli false set array uruchomione z poziomu jadra, inaczej przez obsluge bledow wstrzymujacych dzialanie frameworka
	 * @param string $lang_code wymusza zaladowanie tablicy do tulmaczenia z konkretnym jezykiem
	 * @param boolean $switch umozliwia zaladowanie tablicy tulmaczen z modulu do tablicy core (dla zglaszania bledu do znacznika)
	 * @uses lang_class::$lang_support
	 * @uses lang_class::$lang
	 * @uses lang_class::$translations
	 * @uses lang_class::load_lang()
	 * @throws coreException core_error_19
	 */
	public function set_array($mod = FALSE, $type = FALSE, $lang_code = NULL, $switch = NULL){
		if($this->lang_support){
			if(!$mod || $mod == 'core'){
				$mod = 'core';
				$path = 'cfg/lang/';
			}else{
				$path = 'modules/'.$mod.'/lang/';
			}
			if($lang_code){
				$lang = $this->load_lang($path.$mod, $lang_code);
			}else{
				$lang = $this->load_lang($path.$mod);
			}
			if(!$lang){
				if($type){
					return FALSE;
				}else{
					if($lang_code){
						throw new coreException('core_error_19', $path.$mod.'_'.$lang_code);
					}
					throw new coreException('core_error_19', $path.$mod.'_'.$this->lang);
				}
			}
			if($switch){
				$mod = 'core';
			}
			if($lang_code){
				if(!isset($this->translations[$lang_code][$mod])){
					$this->translations[$lang_code][$mod] = $lang;
				}else{
					$this->translations[$lang_code][$mod] = array_merge($this->translations[$lang_code][$mod], $lang);
				}
				return TRUE;
			}
			if(!isset($this->translations[$mod])){
				$this->translations[$mod] = $lang;
			}else{
				$this->translations[$mod] = array_merge($this->translations[$mod], $lang);
			}
		}
		return TRUE;
	}
	/**
	 * uruchamia tulmaczenie szablonu i uruchomionych modulow
	 * @param object $display obiekt klasy display
	 * @uses lang_class::$display
	 * @uses lang_class::$lang_support
	 * @uses lang_class::$translation_blocks
	 * @uses display_class::$DISPLAY
	 * @uses lang_class::lang_replace()
	 */
	public function translate(display_class $display){
		if($this->lang_support){
			$this->display = $display;
			if($this->lang_support){
				foreach($this->display->DISPLAY as $key => $mod){
					$bool1 = preg_match_all(self::$translation_blocks[0], $mod, $capture1);
					$bool2 = preg_match_all(self::$translation_blocks[1], $mod, $capture2);
					$bool3 = preg_match_all(self::$translation_blocks[2], $mod, $capture3);
					$bool4 = preg_match_all(self::$translation_blocks[3], $mod, $capture4);
					if($bool1){
						$this->lang_replace(1, $key, $capture1[0]);
					}
					if($bool2){
						$this->lang_replace(2, $key, $capture2[0]);
					}
					if($bool3){
						$this->lang_replace(3, $key, $capture3[0]);
					}
					if($bool4){
						$this->lang_replace(4, $key, $capture4[0]);
					}
				}
			}
		}
	}
	/**
	 * wyszukuje w tresci znaczniki i zastepuje je odpowiednimi tulmaczeniami
	 * @param string $name nazwa modulu do przetulmaczenia
	 * @param array $arr tablica wychwyconych znacznikow
	 * @uses lang_class::$translations
	 * @uses lang_class::$display
	 * @uses display_class::generate()
	 * @uses lang_class::set_array()
	 */
	private function lang_replace($type, $name, $arr){
		foreach($arr as $znacznik){
			$znacznik = str_replace(
				array('{;', ';}'), 
				'', $znacznik
			);
			$key = explode(';', $znacznik);
			switch($type){
				case 1:
					if(isset($this->translations[$name][$key[1]])){
						$this->display->generate($znacznik, $this->translations[$name][$key[1]], $name);
					}else{
						$this->display->generate($znacznik, '{'.$key[1].'}', $name);
					}
					break;
				case 2:
					if(isset($this->translations[$key[1]][$key[2]])){
						$this->display->generate($znacznik, $this->translations[$key[1]][$key[2]], $name);
					}else{
						$this->display->generate($znacznik, '{'.$key[2].'}', $name);
					}
					break;
				case 3:
					$lang_code = str_replace('lang-', '', $key[0]);
					$this->set_array($name, 0, $lang_code);
					if(isset($this->translations[$lang_code][$name][$key[1]])){
						$this->display->generate($znacznik, $this->translations[$lang_code][$name][$key[1]], $name);
					}else{
						$this->display->generate($znacznik, '{'.$key[1].'}', $name);
					}
					break;
				case 4:
					$lang_code = str_replace('lang-', '', $key[0]);
					$this->set_array($key[1], 0, $lang_code);
					if(isset($this->translations[$lang_code][$key[1]][$key[2]])){
						$this->display->generate($znacznik, $this->translations[$lang_code][$key[1]][$key[2]], $name);
					}else{
						$this->display->generate($znacznik, '{'.$key[2].'}', $name);
					}
					break;
			}
		}
	}
	/**
	 * wczytuje plik jezykowy i zwraca tablice tlumaczen
	 * @param string $path scierzka do wczytania pliku
	 * @param string $lang_code opcjonalny kod innego jezyka do zaladowania
	 * @return array zwraca tablice tlumaczen
	 * @uses lang_class::$lang
	 * @uses lang_class::$default
	 * @uses lang_class::similar()
	 * @uses starter_class::load()
	 */
	private function load_lang($path, $lang_code = NULL){
		if(!$lang_code){
			$lang_code = $this->lang;
		}
		$bool = starter_class::load($path.'_'.$lang_code.'.php', 'content', 0);
		if(!$bool){
			$bool = $this->similar($path);
			if(!$bool){
				$bool = starter_class::load($path.'_'.$this->default.'.php', 'content', 0);
			}
		}
		return $bool;
	}
	/**
	 * ustawia jezyk systemowy i domyslny
	 * @param string $current jesli podano kod jezyka ustawia go jako glowny
	 * @uses lang_class::$lang
	 * @uses lang_class::$options
	 * @uses lang_class::$default
	 * @uses lang_class::detect()
	 */
	private function set_lang($current = NULL){
		if($current){
			$this->lang = $current;
		}else{
			if((bool)$this->options['detect_lang']){
				$this->detect();
			}else{
				$this->lang = $this->options['lang'];
			}
		}
		$this->default = $this->options['lang'];
	}
	/**
	 * jesli nie zaladowalo jezyka glownego, probuje zaladowac inny podobny jezyk
	 * @param string $path scierzka do lokalizacji plikow jezykowych
	 * @return array tablica jezykowa, lub FALSE jesli nie zaladowano
	 * @uses lang_class::$lang
	 * @uses starter_class::path()
	 * @uses starter_class::load()
	 */
	private function similar($path){
		$similar = explode('-', $this->lang);
		$search = starter_class::path().$path.'_'.$similar[0].'*.php';
		$arr = glob($search);
		if(!empty($arr)){
			$code = explode('/', $arr[0]);
			$code = explode('-', end($code));
			$code = rtrim($code[1], '.php');
			return starter_class::load($path.'_'.$similar[0].'-'.$code.'.php', 'content', 0);
		}else{
			return FALSE;
		}
	}
	/**
	 * automatyczna detekcja jezyka ustawionego w przegladarce
	 * @uses lang_class::$options
	 * @uses lang_class::$lang
	 */
	private function detect(){
		if(!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
			$this->lang = $this->options['lang'];
		}
		$lang = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$bool = preg_match('#^[a-z]{2}-[A-Z]{2}$#', $lang[0]);
		if(!$bool){
			$this->lang = $lang[0].'-xx';
		}
		$this->lang = $lang[0];
	}
	/**
	 * zwraca informacje czy przekazano jezyk jako kod jezyka, lub null jesli brak
	 * jesli brak kodu jezykowego i obsluga jezykow wlaczona tworzy przekierowanie na odpowiedni jezyk
	 * @param array $get tablica elementow get pobierana poprzez referencje
	 * @return mixed kod jezyka lub null jesli brak na liscie
	 * @uses lang_class::$codes
	 * @uses core_class::options()
	 * @todo pobiewranie parametrow z url jesli byl brak jezyka
	 * @todo ustawianie jezyka jesli wlaczony klasyczny url
	 */
	static function chk_lang(&$get){
		if(isset($get['core_lang'])){
			$lang = $get['core_lang'];
		}else{
			$lang = $get[0];
		}
		if(core_class::options('lang_support')){
			if(in_array($lang, self::$codes)){
				unset($get[0]);
				unset($get['core_lang']);
				return $lang;
			}else{
				$link = '';
				$url = display_class::explode_url($get, core_class::options('zmienne_rewrite_sep'));
				if((bool)core_class::options('rewrite')){
					$final = display_class::convert_rewrite($url['params'], $url['pages']);
					$final = core_class::options('lang').'/'.$final;
				}else{
					$final = display_class::convert_classic($url['params'], $url['pages']);
					$final = '?core_lang='.core_class::options('lang').'&'.$final;
				}
				if(core_class::options('test')){
					header('Location: /'.core_class::options('test').'/'.$final);
					exit;
				}
				header('Location: /'.$final);
				exit;
			}
		}else{
			return NULL;
		}
	}
}
?>