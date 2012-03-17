<?PHP
/**
 * @author chajr <chajr@bluetree.pl>
 * @version 2.1.5
 * @access private
 * @copyright chajr/bluetree
 * @package core
 * @subpackage error
 */
 /**
 * funkcja przechwytujaca bufor i przekazujaca do metody error_class::fatal
 * @param string $bufor zbuforowana tresc
 * @return string zwraca kompletna tresc, lub blad do wyswietlenia
 * @uses error_class::fatal()
 */
function fatal($bufor){
	return error_class::fatal($bufor);
}
/**
 * funkcja obslugi bledow php error_class::error
 * @param integer $number numer bledu
 * @param string $string tresc bledu
 * @param string $file nazwa i scierzka pliku w ktorym wystapil blad
 * @param integer $line numer linii w ktorej wystapil blad
 * @uses error_class::error
 */
function error($number, $string, $file, $line){
	$error = new error_class();
	$error->add_error('critic', $number, $string, $line, $file, '');
	echo $error->render();
	exit;
}
/**
 * klasa obslugi bledow php
 * @package core
 * @subpackage error
 */
final class error_class{
	/**
	 * obiekt obslugi jezyka
	 * @var object 
	 */
	private $lang;
	/**
	 * obiekt klasy display
	 * @var object 
	 */
	private $display;
	/**
	 * tablica opcji frameworka
	 * @var array 
	 */
	private $options;
	/**
	 * tablica komunikatow
	 * @var array
	 */
	static $list = array(
		'critic' => array(),
		'warning' => array(),
		'info' => array(),
		'ok' => array(),
		'pointer' => array()
	);
	/**
	 * uruchomia klase obslugi bledu, jesli ze zmienna lang uruchamiana jest dla core, inaczej w przypadku bledow php
	 * @param type $lang jesli brak jezyka uruchamia dla bledow krytycznych, inaczej pozostale np throw
	 * @uses error_class::$options
	 * @uses error_class::$lang
	 * @uses error_class::$display
	 * @uses lang_class::$lang
	 * @uses starter_class::package()
	 * @uses option_class::load()
	 * @uses get::rewrite_get()
	 * @uses lang_class::chk_lang()
	 * @uses lang_class::__construct()
	 * @uses display_class::__construct()
	 * @uses core_class::options()
	 */
	public function __construct($lang = FALSE){
		if(!$lang){
			starter_class::package('CORE/lang_class,option_class,display_class,globals_class');
			$this->options = option_class::load();
			if($this->options['timezone']){
				@date_default_timezone_set($this->options['timezone']);
			}
			if($this->options['rewrite']){
				$uri = $_SERVER['REQUEST_URI'];
				$get = get::rewrite_get($this->options['test'], $uri);
				$lang = lang_class::chk_lang($get);//, $this->options
			}else{
				//$lang = lang_class::chk_lang($_GET);
				$lang =  $this->options['lang'];
			}
			if(!$lang){
				$lang = $this->options['lang'];
			}
			$this->lang = new lang_class($lang, $this->options);
			$this->display = new display_class('error', NULL, NULL, $this->lang->lang, NULL, NULL, $this->options);
		}else{
			$this->lang = $lang;
			$this->options = core_class::options();
		}
	}
	/**
	 * metoda obslugi bledu typu fatal
	 * pobiera dane i sprawdza istnienie komunikatu o bledzie, jesli jest przygotowywuje inf do wyswietlenia, oraz dane do loga
	 * @param string $bufor dane zwrocone przez frameworka
	 * @return string dane wejsciowe, lub informacja o bledzie
	 * @uses starter_class::load()
	 * @uses error_class::log()
	 * @uses error_class::other()
	 * @final
	 * @static
	 */
	static final function fatal($bufor){
		$bool = preg_match('#(error</b>:)(.+)(<br />)#', $bufor);
		if($bool){
			$tab = preg_split('#<(\/)?b>#', $bufor);
			$bufor = starter_class::load('elements/layouts/error.html', TRUE);
			$xml = starter_class::load('cfg/config.xml', TRUE);
			$poz = strpos($xml, '<option id="debug" value="');
			$len = strlen('<option id="debug" value="');
			$poz += $len;
			$debug = substr($xml, $poz, 1);
			$poz = strpos($xml, '<option id="timezone" value="');
			$len = strlen('<option id="timezone" value="');
			$poz += $len;
			$time = substr($xml, $poz);
			$poz = strpos($time, '"');
			$time = substr($time, 0, $poz);
			$bufor = str_replace(array(
				'{;lang;error_title;}', 
				'{;errors;error_code;}'
			), array(
				'CRITICAL PHP ERROR', 
				$tab[1]
			), $bufor);
			@date_default_timezone_set($time);
			$data = strftime('%H:%M:%S - %d-%m-%Y');
			$bufor = str_replace('{;data;}', $data, $bufor);
			if((bool)$debug){
				$bufor = str_replace(array(
					'{;errors;extend_message;}', 
					'{;errors;line;}',
					'{;errors;file;}'
				), array(
					'Message'.$tab[2], 
					'Line: '.$tab[5], 
					$tab[3]
				), $bufor);
			}else{
				$bufor = str_replace(array(
					'{;errors;message;}', 
					'{;errors;line;}',
					'{;errors;file;}'
				), '', $bufor);
			}
			$inf = array();
			$inf['Error'] = $tab[1];
			$inf['Message'] = $tab[2];
			$inf['Line'] = $tab[5];
			$inf[''] = $tab[3];
			$other = self::other();
			$inf = array_merge($inf, $other);
			$bool = self::log('fatal_error', $inf, $data);
			if(!$bool){
				$bufor = str_replace('{;log;}', 'log error', $bufor);
			}
			$bufor = preg_replace('#{;([\\w;-])+;}#', '', $bufor);
		}
		return $bufor;
	}
	/**
	 * umozliwia zgloszenie loga oraz zapisanie go do pliku, opcjonalnie we wskazane miejsce
	 * @example log('fatal', 'no i sie popsulo', '23:12 27.05.2010') - blad typu fatal z komunikatem o bledzie i wlasna data
	 * @example log('error', array('komunikat'=>'ajajajaj')) - jakis inny blad z tablica informacji
	 * @example log('inf', 'jakas wiadomosc', 0, 'modules/mod') - zapisuje loga z informacja do wskazanego folderu
	 * @example log('fatal', array('blad' => 'treesc bledu' 'inne' => array('info' => 'dodatkowe info', 'info2' => 'inne info')));
	 * @param string $typ nazwa dla przedrostka loga
	 * @param mixed $komunikaty tablica komunikatow, gdzie klucz stanowi nazwe elementu, wartosc, tresc do wstawienia, lub tresc komunikatu 
	 * @param time $data opcjonalnie data dla loga
	 * @param string $path opcjonalna scierzka gdzie ma zapisac loga
	 * @return boolean informacja o powodzeniu zapisania loga
	 * @uses starter_class::path()
	 * @final
	 * @static
	 */
	static final function log($typ, $komunikaty, $data = FALSE, $path = FALSE){
		if($data){
			$data = preg_replace('#[ :]#', '_', $data);
		}else{
			$data = strftime('%H_%M_%S_-_%d-%m-%Y');
		}
		if(!$path){
			$path = starter_class::path('log');
		}
		if(!file_exists($path)){
			@mkdir($path);
			@chmod($path, 0777);
		}
		$path .= "$typ-$data.log";
		if(is_array($komunikaty)){
			$komunikat = '';
			foreach($komunikaty as $key => $val){
				if(is_array($val)){
					foreach($val as $key2 => $val2){
						$komunikat .= "$key2: $val2\n";
					}
					$komunikat .= "\n\n-------------------------------------------------------------------------------------------------\n\n";
				}else{
					$komunikat .= "$key: $val\n";
				}
			}
		}else{
			$komunikat = $komunikaty;
		}
		$komunikat .= "\n\n\n";
		return error_log($komunikat, 3, $path);
	}
	/**
	 * zwraca dodatkowe informacje dla bledu (ip, przegladarke, URI, date)
	 * @return array tablica dodatkowych informacji
	 */
	static final function other(){
		$inf = array();
		$inf['ip'] = $_SERVER['REMOTE_ADDR'];
		$inf['browser'] = $_SERVER['HTTP_USER_AGENT'];
		$inf['URI'] = $_SERVER['REQUEST_URI'];
		$inf['data'] = strftime('%H:%M:%S - %d-%m-%Y');
		return $inf;
	}
	/**
	 * dodaje komunikat do odpowiedniej tablicy
	 * @example komunikat('warning', '', 'core_error_x', 1, 'CORE/core_class.php', 23 'jakas dodatkowa tresc', 'jakis_modul')
	 * @example komunikat('info', 1, 'jakis_kod', 1, 'CORE/core_class.php', '', '')
	 * @example komunikat('znacznik_bledu', '', 'jakis_kod', 1, 'CORE/core_class.php', 'jakies dodatkowe info', 'jakis_modul')
	 * @param string $type typ zglaszanego komunikatu, lub nazwa znacznika do ktorego ma kierowac blad
	 * @param integer $numer numer bledu
	 * @param string $error_code kod bledu, badz glowny komunikat
	 * @param string $file  pliku w ktorym wystapil blad
	 * @param integer $line numer linii bledu
	 * @param array $error_message dodatkowa informacje o bledzie
	 * @param string $mod nazwa modulu zglaszajacego blad lub false jesli blad zglasza core
	 * @uses error_class::$list
	 * @uses error_class::komunikat()
	 * @uses lang_class::$translation_blocks
	 * @return array zwraca tablice ze sformatowanym komunikatem o bledzie, jesli zostal dodany do listy, lub posty string
	 */
	public function add_error($type, $numer, $error_code, $line, $file, $error_message, $mod = FALSE){
		if(!$mod){
			$mod = 'core';
		}else{
			$bool1 = preg_match_all(lang_class::$translation_blocks[0], $error_message, $capture);
			foreach($capture[0] as $arr){
				$error_message = str_replace('{;lang;', '{;lang;'.$mod.';', $error_message);
			}
		}
		$error = '';
		switch($type){
			case'critic':
				$error = self::$list['critic'][$mod][] = $this->komunikat($numer, $error_code, $file, $line, $error_message, $mod);
				break;
			case'warning':
				$error = self::$list['warning'][$mod][] = $this->komunikat('', $error_code, $file, $line, $error_message, $mod);
				break;
			case'info':
				$error = self::$list['info'][$mod][] = $this->komunikat('', $error_code, '', '', $error_message, $mod);
				break;
			case'ok':
				$error = self::$list['ok'][$mod][] = $this->komunikat('', $error_code, '', '', $error_message, $mod);
				break;
			default:
				$error = self::$list['pointer'][$mod][$type] = $this->komunikat($numer, $error_code, $file, $line, $error_message, $mod);
				break;
		}
		return $error;
	}
	/**
	 * tworzy odpowiednio sformatowany komunikat
	 * @param integer $numer numer bledu
	 * @param string $error_code komunikat o bledzie
	 * @param string $file nazwa pliku w ktorym wystapil blad
	 * @param integer $linia numer linii bledu
	 * @param array $error_message dodatkowa informacja o bledzie
	 * @param string $mod nazwa modulu zglaszajacego blad lub false jesli blad zglasza core
	 * @uses error_class::$options
	 * @uses error_class::pack()
	 * @return array sformatowany komunikat o bledzie
     * @todo przerobic tulmaczenia tak aby obslugiwalo w pelni tulmaczenie komunikatow (problem dla metody log)
	 */
	private final function komunikat($numer, $error_code, $file, $linia, $error_message, $mod){
		$kom = array();
		$bool = $this->pack("modules/$mod/lang/".$mod."_error");
		if($bool){
			if(isset($bool[$error_code])){
				$error_code = @str_replace($error_code, $bool[$error_code], $error_code);
			}
		}else{
			$bool = $this->pack("cfg/lang/core_error");
			if($bool){
				if(isset($bool[$error_code])){
					$error_code = @str_replace($error_code, $bool[$error_code], $error_code);
				}
			}else{
				@trigger_error('No default error pack<br/>'.$mod.'<br/>'.$error_code);
			}
		}
		if(!$this->options['debug']){
			$file = '';
			$numer = '';
			$linia = '';
			$mod = '';
		}
		if($numer){
			$kom['number'] = $numer;
		}
		$kom['error_code'] = $error_code;
		$kom['extend_message'] = $error_message;
		if($linia){
			$kom['line'] = $linia;
		}
		if($file){
			$kom['file'] = $file;
		}
		if($mod){
			$kom['module'] = $mod;
		}
		return $kom;
	}
	/**
	 * sklada komunikat o bledzie, wysyla loga i zwraca go do wyswietlenia
	 * jesli type na true, rendering pochodzi z core, inaczej generuje bledy pochodzice z php
	 * @param boolean $type typ zwracanej zawartosci, jesli false, zwraca critic, jesli true, tablice bledow i informacji
	 * @return mixed pelen komunikat o bledzie, lub tablica bledow
	 * @uses error_class::$options
	 * @uses error_class::$list
	 * @uses error_class::$display
	 * @uses error_class::$lang
	 * @uses error_class::$options
	 * @uses error_class::other()
	 * @uses error_class::log()
	 * @uses display_class::loop()
	 * @uses display_class::generate()
	 * @uses display_class::render()
	 * @uses lang_class::translate()
	 * @uses lang_class::set_array()
	 */
	public final function render($type = FALSE){
		if($type){
			$temp_list = array('critic' => '', 'warning' => '', 'info' => '', 'ok' => '', 'pointer' => '');
			foreach(self::$list as $type => $modules){
				if(!empty($modules)){
					$temp = '';
					foreach($modules as $mod => $array){
						$bool = $this->lang->set_array();
						if($type == 'pointer'){
							if($mod != 'core'){
								$bool = $this->lang->set_array($mod, 1, NULL, 1);
								if(!$bool){
									@trigger_error('No default lang pack<br/><br/>'.$string);
								}
							}
							foreach($modules as $mod_name => $err_block){
								foreach($err_block as $block_name => $error_content){
									$this->display = new display_class('error_'.$type, NULL, NULL, $this->lang->lang, NULL, NULL, $this->options);
									$bool = $this->display->generate(array(
										'extend_message' => $error_content['extend_message'],
										'error_code' => $error_content['error_code']
									), '');
									$this->lang->translate($this->display, 1);
									$content = $this->display->render();
									unset($this->display);
									$temp[] = array('content' => $content, 'mod' => $mod, 'point' => $block_name);
									$op = $this->options['errors_log'];
									if((bool)$op{2}){
										self::log('pointer', $content);
									}
								}
							}
						}else{
							$this->display = new display_class('error_'.$type, NULL, NULL, $this->lang->lang, NULL, NULL, $this->options);
							$this->display->loop('errors', $array);
							if($mod != 'core'){
								$bool = $this->lang->set_array($mod, 1);
							}
							if(!$bool){
								@trigger_error('No default lang pack<br/><br/>'.$string);
							}
							$this->lang->translate($this->display);
							$temp .= $this->display->render();
						}
					}
					$temp_list[$type] = $temp;
				}
			}
			return $temp_list;
		}else{
			$other = self::other();
			$log = array_merge(self::$list['critic']['core'], $other);
			self::log('critic', $log, $other['data']);
			$this->display->loop('errors', self::$list['critic']['core']);
			$this->display->generate($other, '');
			$bool = $this->lang->set_array(0, 1);
			if(!$bool){
				@trigger_error('No default lang pack<br/><br/>'.$string);
			}
			$this->lang->translate($this->display);
			return $this->display->render();
		}
	}
	/**
	 * laduje tablice z kodami bledow
	 * @param string $pack nazwa pliku do zaladowania i scierzki do niego
	 * @return array tablica z kodami bledow i ich tresciami, lub false jesli blad
	 * @uses error_class::$lang
	 * @uses lang_class::$lang
	 * @uses lang_class::$default
	 * @uses starter_class::load()
	 * @todo odczyt z podobnego pliku jezykowego
	 */
	private function pack($pack){
		$bool = starter_class::load($pack.'_'.$this->lang->lang.'.php', 'content', TRUE);
       	if(!$bool){
			$bool = starter_class::load($pack.'_'.$this->lang->default.'.php', 'content', TRUE);
           	if(!$bool){
				return FALSE;
			}
		}
		return $bool;
	}
}
?>