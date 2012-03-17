<?PHP
/**
 * @author chajr <chajr@bluetree.pl>
 * @version 2.1
 * @access private
 * @copyright chajr/bluetree
*/
/**
 * klasa pomocnicza dla modulow, wszystkie moga po niej dziedziczyc
 * @package core
 */
abstract class module_class {
	/**
	 * numer wersji modulu
	 * @var integer
	 */
	static $version;
	/**
	 * pelna nazwa modulu
	 * @var string
	 */
	static $name;
	/**
	 * tablica wymaganych bibliotek
	 * @var array
	 */
	public $require_libs = array();
	/**
	 * tablica wymaganych modulow
	 * @var array
	 */
	public $require_modules = array();
	/**
	 * obiekt loader
	 * @var object
	 */
	public $core;
	/**
	 * tablica parametrow z ktorymi ma zostac uruchomiony modul
	 * @var array
	 */
	public $params;
	/**
	 * nazwa elementu blokowego, do ktorego ma zostac zaladowany modul (kolejnosc layoutow zalezna od kolejnosci uruchomienia)
	 * @var string
	 */
	public $block;
	/**
	 * nazwa kodowa modulu
	 * @var string
	 */
	public $mod_name;
	/**
	 * obiekt get
	 * @var object
	 */
	public $get;
	/**
	 * obiekt post
	 * @var object
	 */
	public $post;
	/**
	 * obiekt cookie
	 * @var object
	 */
	public $cookie;
	/**
	 * obiekt sesji
	 * @var object
	 */
	public $session;
	/**
	 * obiekt wyslanych plikow
	 * @var object
	 */
	public $files;
    /**
     * lista uruchomionych modulow
     * @var array
     */
    public $mod;
	/**
	 * przechowuje inf czy w module wystapil blad w postaci tablicy kodow, 
	 * kazde zgloszenie dodaje kod zgloszenia w odpowiednie pole
	 * @var array 
	 */
	public $error = array(
		'ok' => NULL,
		'info' => NULL,
		'warning' => NULL,
		'critic' => NULL
	);
	/**
	 * informacja czy serwis jest odpalony z przegladarki mobilnej
	 * @var boolean 
	 * @access public
	 */
	public $mobile_browser;
	/**
	 * informacja czy ma zwracac krytyczny blad frameworka
	 * @var array
	 */
	protected $unthrow;
	/**
	 * glowna metoda modulu, powodujaca jego uruchomienie
	 */
	abstract public function run();
	/**
	 * metoda modulu uruchamiana w przypadku wystapienia bledu w module
	 */
	abstract public function error_mode();
	/**
	 * metoda odpowiadajaca za zainstalowanie modulu
	 */
	abstract public function install();
	/**
	 * metoda oddpowiadajaca za odinstalowanie modulu
	 */
	abstract public function uninstall();
	/**
	 * uruchamia obiekt pomocniczy, skraca wlasciwosci, udostepnia podpowiadanie,
	 * sprawdza biblioteki/moduly, uruchamia odpowiednia metode, pomaga przy podpowiadaniu
	 * @param object $core obiekt klasy loader
	 * @param array $params tablica parametrow dla modulu
	 * @param string $block nazwa bloku do ktorego ma zostac zaladowany modul (opcjonalnie)
	 * @param string $mod_name nazwa kodowa modulu
	 * @param boolean $error tryb uruchomienia modulu, jesli true uruchamia w trybie bledu
	 * @uses module_class::$core;
	 * @uses loader_class::$get
	 * @uses loader_class::$post
	 * @uses loader_class::$cookie
	 * @uses loader_class::$session
	 * @uses loader_class::$files
	 * @uses loader_class::$mod
	 * @uses module_class::$unthrow
	 * @uses module_class::$params
	 * @uses module_class::$mod_name
	 * @uses module_class::$get
	 * @uses module_class::$post
	 * @uses module_class::$cookie
	 * @uses module_class::$session
	 * @uses module_class::$files
	 * @uses module_class::$mod
	 * @uses module_class::chk_lib()
	 * @uses module_class::error_mode()
	 * @uses module_class::run()
	 */
	public function __construct(loader_class $core, $params, $mod_name, $unthrow, $error = FALSE){
		$this->core = $core;
		$this->params = $params;
		$this->mod_name = $mod_name;
		$this->get = $this->core->get;
		$this->post = $this->core->post;
		$this->cookie = $this->core->cookie;
		$this->session = $this->core->session;
		$this->files = $this->core->files;
        $this->mod = $this->core->mod;
		$this->unthrow = $unthrow;
		$this->mobile_browser = $this->core->mobile_browser;
		$this->chk_lib();
		if($error){
			$this->error_mode();
		}else{
			$this->run();
		}
	}
	/**
	 * zastepuje znacznik trescia, badz grupe znacznikow, przypisanymi do nich tresciami
	 * @example generate('znacznik', 'jakas tresc do zastapienia')
	 * @example generate(array('znacznik1' => 'tresc', 'znacznik2' => 'inna tresc'))
	 * @example generate('znacznik', 'jakas tresc', 1)
	 * @param mixed $znacznik nazwa znacznika, badz tablica znacznik=>tresc
	 * @param string $tresc tresc ktora ma zostac zastapiony znacznik
	 * @param boolean $core jesli ustawiony na true to dokonuje zastapienia znacznika w core
	 * @return integer zwraca ilosc zastapionych znacznikow, lub NULL jesli nie znaleziono elementow
	 * @uses module_class::$core
	 * @uses module_class::$mod_name
	 * @uses loader_class::generate()
	 */
	public function generate($znacznik, $tresc = FALSE, $core = FALSE){
		if($core){
			$mod = 'core';
		}else{
			$mod = $this->mod_name;
		}
		return $this->core->generate($znacznik, $mod, $tresc);
	}
	/**
	 * przetwaza w petli zbior znacznikow (tablica tablic z odpowiednimi znacznikami)
	 * @param string $znacznik nazwa znacznika statrowego
	 * @param array $arr tablica tresci do wygenerowania
	 * @return integer zwraca ilosc zastapionych znacznikow, lub NULL jesli nie znaleziono elementow
	 * @uses module_class::$core
	 * @uses module_class::$mod_name
	 * @uses loader_class::loop()
	 */
	public function loop($znacznik, $arr){
		return $this->core->loop($znacznik, $arr, $this->mod_name);
	}
	/**
	 * dodaje komplety zancznik meta do naglowka
	 * @param string $meta kompletny metatag
	 * @uses module_class::$core
	 * @uses loader_class::add_meta()
	 */
	public function add_meta($meta){
		$this->core->add_meta($meta);
	}
	/**
	 * dopisuje tresc do istniejacego juz znacznika meta
	 * @param string $typ nazwa znacznika
	 * @param string $meta tresc do dopisania
	 * @uses module_class::$core
	 * @uses loader_class::add_to_meta()
	 */
	public function add_to_meta($typ, $meta){
		$this->core->add_to_meta($typ, $meta);
	}
    /**
     * w zaleznosci od paraametru (true/false) zwraca kod jezyka domyslnego lub zaladowanego
     * @param boolean $type jesli true zwraca kod domyslnego jezyka, jesli brak lub false zwraca ustawiony kod
	 * @return string kod jezyka
     * @uses module_class::$core
     * @uses loader_class::lang()
     */
	public function lang($type = NULL){
		return $this->core->lang($type);
	}
	/**
	 * ustawia w sesji informacje, domyslnie w public
	 * @param type $name string nazwa klucza przechowujacego dane
	 * @param type $val mixed zawartosc przekazana do sesji
	 * @param $type string typ pobieranych danych (user lub public)
	 * @uses module_class::$core
	 * @uses session::set()
	 */
	public function set_session($name, $val, $type = 'public'){
		switch($type){
			case'user':
				$type = 'user';
				break;
			default:
				$type = 'public';
				break;
		}
		$this->core->session->set($name, $val, $type);
	}
	public function clear_session($type = NULL){
		$this->core->session->clear($type);
	}
	/**
	 * zwraca wartosc specjalnej zmiennej w sesji
	 * @param type $val nazwa zmiennej w tablicy
	 * @param type $type typ tablicy specjalnej z sesji
	 * @return type wartosc zmiennej
	 * @uses module_class::$core
	 * @uses session::returns()
	 */
	public function return_session($val, $type){
		$list = $this->core->session->returns($type);
		if(isset($list[$val])){
			return $list[$val];
		}
		return NULL;
	}
	/**
	 * wczytuje do tablicy layout dla danego modulu
	 * @param string $name nazwa layoutu do zaladowania, jesli brak wczytuje layout o nazwie modulu
	 * @uses module_class::$core
	 * @uses loader_class::layout()
	 * @uses module_class::$mod_name
	 */
	protected function layout($name = NULL){
		$this->core->layout($this->mod_name, $name);
	}
	/**
	 * dodaje do strony js lub css zalaczonego do modulu
	 * @example set('jquery', 'js', 'external')
	 * @example set('jakis_skrypt', 'js')
	 * @example set('base', 'css', 'internal', 'print')
	 * @param string $name nazwa pliku do zaladowania
	 * @param string $type typ pliku (css, lub js)
	 * @param string $external czy plik z zewnetrznego serwisu (external), domyslnie internal
	 * @param string $media typ media dla css-a (np. print)
	 * @uses module_class::$core
	 * @uses module_class::$mod_name
	 * @uses loader_class::set()
	 */
	public function set($name, $type, $external = 'internal', $media = ''){
		$this->core->set($this->mod_name, $name, $type, $external, $media);
	}
	/**
	 * zwraca tablice breadcrumbs
	 * @return type array tablica z nazwami i linkami dla scierzki internaktywnej
	 * @uses module_class::$core
	 * @uses loader_class::breadcrumbs()
	 */
	public function breadcrumbs(){
		return $this->core->breadcrumbs();
	}
	/**
	 * tworzy mape strony wraz ze scierzkami, jesli strona ma ustawiony parametr hidden=1 bedzie niewidoczna na liscie
	 * @example map()
	 * @example map('inna_mapa')
	 * @example map('', 1)
	 * @param string $xml nazwa xml-a do przetwozenia, defaultowo tree
	 * @param boolean $admin jesli na true, zwraca kompletna liste wraz z opcjami
	 * @return array tablica z nazwami oraz linkami do stron/podstron
	 * @uses module_class::$core
	 * @uses loader_class::map()
	 */
	public function map($xml = '', $admin = FALSE){
		if(!$xml){
			$xml = 'tree';
		}
		return $this->core->map($xml, $admin);
	}
	/**
	 * umozliwia dodanie komunikatow, bez zatrzymywania modulu
	 * @param string $type typ komunikatu (critic, warning, info, ok), lub znacznik bledu do ktorego ma zapisac blad
	 * @param string $code kod bledu
	 * @param strin $message dodatkowe informacje
	 * @uses module_class::$core
	 * @uses loader_class::add_error()
	 * @uses module_class::$error
	 * @uses module_class::$mod_name
	 */
	public function error($type, $code, $message){
		switch ($type){
			case 'ok':
				$this->error['ok'] = 1;
				break;
			case 'info':
				$this->error['info'] = 1;
				break;
			case 'critic':
				$this->error['critic'] = 1;
				break;
			case 'warning':
				$this->error['warning'] = 1;
				break;
		}
		$this->core->add_error($this->mod_name, $type, $code, $message);
	}
	/**
	 * odczytuje i zwraca liste opcji dla modulu, lub wartosci konkretnej opcji
	 * @param string opcjonalnie nazwa konkretnego parametru dal modulu
	 * @return mixed zwraca tablice opcji, badz wartosc konkretnej opcji modulu
	 * @uses module_class::$mod_name
	 * @uses option_class::show()
	 * @uses option_class::load()
	 */
	public function load_options($option = NULL){
		if($option){
			return option_class::show($option, $this->mod_name);
		}
		return option_class::load($this->mod_name);
	}
	 /**
     * informuje frameworka ze modul ma zostac przetulmaczony
     * @uses module_class::$core
	 * @uses module_class::$mod_name
     * @uses loader_class::translate()
     */
	protected function translate(){
		$this->core->translate($this->mod_name);
	}
	/**
     * dodaje dodatkowa tablice do tulmaczen (np z bazy danych)
	 * @param array $array tablica dodatkowych kodow tulmaczeniowych
     * @uses module_class::$core
     * @uses loader_class::set_translate()
     */
	protected function set_translate($array){
		$this->core->set_translate($array);
	}
	/**
     * pomija uruchomienie podanego w prametrze modulu
     * @param string $name nazwa modulu, bez przyrostka _class
     * @example dissemble('modul1');
     * @uses module_class::$core
     * @uses loader_class::$dissemble
     */
	protected function dissemble($name){
		$this->core->dissemble[] = $name;
	}
	/**
     * zatrzymuje dalsze uruchamianie modulow
     * @uses module_class::$core
     * @uses loader_class::$stop
     */
	protected function stop(){
		$this->core->stop = TRUE;
	}
	/**
     * sprawdza czy zostaly zaladowane,lub uruchomione wymagane przez modul biblioteki i moduly
     * @uses module_class::$require_libs
     * @uses module_class:require_modules
     * @uses module_class::$unthrow
     * @uses module_class::$mod_name
     * @uses loader_class::$lib
	 * @uses module_class::$mod
     * @throws coreException core_error_20
     */
	private function chk_lib(){
		if(!empty($this->require_libs) && !(bool)$this->unthrow){
			foreach($this->require_libs as $lib){
               	$bool = in_array($lib, $this->core->lib);
				if(!$bool){
					throw new coreException('core_error_20', $this->mod_name.' - '.$lib);
				}
			}
		}
        if(!empty($this->require_modules) && !(bool)$this->unthrow){
			foreach($this->require_modules as $mod){
               	$bool = key_exists($mod, $this->mod);
				if(!$bool){
					throw new coreException('core_error_20', '{;lang;modul;} '.$this->mod_name.' {;lang;wymaga;} '.$mod);
				}
			}
		}
	}
}
?>