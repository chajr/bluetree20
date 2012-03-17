<?PHP
/**
 * @version 1.6
 * @access private
 * @copyright chajr/bluetree
 * @package core
 */
/**
 * klasa pomocnicza dla klas danych globalnych
 * przetwarzajnie danych z get, post, cookie i session na obiekty, umozliwia odwolanie do nieistniejacych zmiennych
 * @throws coreException_class
 * @abstract
 */
abstract class globals_class {
	/**
	 * uruchamianie przetwarzania danych
	 * @abstract
	 */
	abstract function run();
	/**
	 * pobiera opcje z frameworka i startuje odpowiednia klase
	 * @uses self::run()
	 */
   	public function __construct(){
		$this->run();
	}
	/**
	 * zwraca null gdy nastepuje odwolanie do nieistniejacej metody, lub wartosc dla przekazanego parametru
	 * @param string $name
	 */
	public function __get($name) {
        $this->$name = NULL;
    }
	/**
	 * ustawia zmienna w get/post jesli istnieje dokona zastapienia
	 * @param string $name nazwa zmiennej do ustawienia
	 * @param mixed $value wartosc dla zmiennej
	 */
	public function __set($name, $value) {
		$this->$name = $value;
    }
	/**
	 * ustawia zmienna w get jesli istnieje tworzy ze zmiennej tablice i dodaje elementy w kolejnosci
	 * @param string $name nazwa zmiennej do ustawienia
	 * @param mixed $value wartosc dla zmiennej
	 * @uses globals_class::__set()
	 */
	protected function add($name, $value){
		if(isset($this->$name)){
			if(is_array($this->$name)){
				$new = array($value => $value);
				$this->$name = array_merge($this->$name, $new);
			}else{
				$data = $this->$name;
				$this->$name = array($data => $data, $value => $value);
			}
		}else{
			$this->$name = $value;
		}
	}
	/**
	 * niszczy superglobalne tablice
	 * @static
	 */
	public static function destroy(){
		unset($_GET);
		unset($_POST);
		$_COOKIE = array();
		$_SESSION = array();
		unset($_FILES);
		unset($_REQUEST);
   	}
	/**
	 * sprawdza poprawnosc paramertow zgodnie z wyrazeniem regularnym
	 * @param string $uri ciag uri do sprawdzenia
	 * @throws coreException core_error_4
	 * @uses core_class::options()
	 */
	protected function reg_exp($uri){
		if((bool) core_class::options('rewrite')){
			$bool = preg_match(core_class::options('reg_exp_rewrite'), $uri);
		}else{
			$bool = preg_match(core_class::options('reg_exp_classic'), $uri);
		}
		if (!$bool){
			throw new coreException('core_error_4', $uri.' - rewrite: '.core_class::options('rewrite'));
		}
	}
	/**
	 * sprawdza maksymalna ilosc parametrow
	 * @param integer $licznik numer aktualnie przekazanego parametru
	 * @param string $type typ sprawdzania get/post/files
	 * @uses core_class::options() 
	 * @uses core_class:$options
	 * @uses globals_class::$core
	 * @throws coreException core_error_5, core_error_7, core_error_8
	 */
	protected function max($licznik, $type){
		switch($type){
			case"get":
				$name = 'max_get';
				$arr = $_GET;
				$err = 'core_error_5';
				break;
			case"post":
				$name = 'max_post';
				$arr = $_POST;
				$err = 'core_error_7';
				break;
			case"files":
				$name = 'files_max';
				$arr = $_FILES;
				$err = 'core_error_8';
				break;
		}
		$name = core_class::options($name);
		if ((bool)$name){
			if ($licznik > $name){
				$inf = count($arr).' -> '.$name;
				throw new coreException($err, $inf);
			}
		}
	}
	/**
	 * sprawdza dlugosc parametru
	 * @param string $parametr parametr+wartosc do sprawdzenia
	 * @throws coreException core_error_6
	 * @uses core_class::options()
	 */
	protected function max_len($parametr){
		if(core_class::options('get_len')){
			$len = mb_strlen($parametr);
			if ($len > core_class::options('get_len')){
				throw new coreException('core_error_6', $parametr);
			}
		}
	}
	/**
	 * zwraca tablice danych w postaci zserializowanej
	 * @return string zserializowana tablica danych
	 */
	public function __toString(){
		$post_array = array();
		foreach($this as $key => $val){
			if($key == 'core_path' || $key == 'core_page_type' || $key == 'core_pages' || $key == 'core_lang' || $key == 'core_core' || $key == 'core_user'
				|| $key == 'core_display' || $key == 'upload_errors' || $key == 'full_size'){
				
			}
			$post_array[$key] = $val;
		}
		return serialize($post_array);
	}
}
/**
 * obsluga tablicy get
 */
class get extends globals_class{
	/**
	 * scierzka absolutna dla strony
	 * @var string
	 * @access private
	 */
	private $core_path;
	/**
	 * informacja o typie strony, default html. inne to css i js
	 * @var string
	 * @access private
	 */
	private $core_page_type = 'html';
	/**
	 * przechowuje tablice z lista stron/podstron
	 * @var array
	 * @access private
	 */
	private $core_pages = array();
	/**
	 * informacja o jezyku pobranym z get, lub null jesli brak podanego
	 * @var string
	 */
	private $core_lang = NULL;
	/**
	 * przetwarza tablice $_GET (wedlug mode rewrite lub classic) i zapisuje dane w odpowiednie miejsca
	 * zmienne z get jako wlasciwosci obiekty, strony jako tablica
	 * zwraca scierzke i typ strony, oraz scierzki do stron (samej strony dal mode rewrite, oraz konkretnej podstrony)
	 * @uses globals_class::max()
	 * @uses globals_class::max_len()
	 * @uses globals_class::add()
	 * @uses get::$core_lang
	 * @uses get::$core_pages
	 * @uses get::$core_path
	 * @uses globals_class::reg_exp()
	 * @uses lang_class::chk_lang()
	 * @uses get::subdomain()
	 * @uses get::real_path()
	 * @uses core_class::options()
	 * @uses get::type()
	 * @uses get::rewrite_get()
	 * @uses globals_class::add()
	 */
	public function run(){
		$this->subdomain();
		if(isset($_SERVER['REQUEST_URI'])){
			$uri = $_SERVER['REQUEST_URI'];
			$this->reg_exp($uri);
			if(core_class::options('rewrite')){
				$get = self::rewrite_get(core_class::options('test'), $uri);
				$l = 0;
				$this->core_lang = lang_class::chk_lang($get);
				foreach($get as $parametr){
					if($parametr == ''){
						continue;
					}
					$l++;
					$this->max($l, 'get');
					$this->max_len($parametr);
					$bool = preg_match('#[\\w]*'.core_class::options('zmienne_rewrite_sep').'[\\w]*#', $parametr);
					if($bool){
						$parametr = explode(core_class::options('zmienne_rewrite_sep'), $parametr);
						$this->add($parametr[0], $parametr[1]);
					}else{
						$this->core_pages[] = $parametr;
					}
				}
			}else{
				if(!empty($_GET)){
					$uri = str_replace(
						array(
							core_class::options('test'),
							'/',
							'?'
						), '', $uri
					);
					$get = explode('&', $uri);
					$tmp_arr = array();
					$param_array = array();
					$l = 0;
					$this->core_lang = lang_class::chk_lang($_GET);
					foreach($get as $element){
						$l++;
						$this->max($l, 'get');
						$tmp_arr = explode('=', $element);
						$this->max_len($tmp_arr[1]);
						$bool = preg_match('#^p[0-9]+$#', $tmp_arr[0]);
						if($bool){
							$this->core_pages[] = $tmp_arr[1];
						}else{
							$this->add($tmp_arr[0], $tmp_arr[1]);
						}
					}
				}
			}
		}
		$this->core_path = self::real_path(core_class::options('test'));
		$this->type();
   	}
	/**
	 * zwraca scierzke naprawcza dla elementow
	 * @param string $test jesli ustawuiono podaje nazwe testowa
	 * @return string zwraca scierzke
	 */
	static function real_path($test = ''){
		$path = '';
		$host = $_SERVER['HTTP_HOST'];
		if(isset($_SERVER['SCRIPT_URI'])){
			$protocol = explode('://', $_SERVER['SCRIPT_URI']);
		}else{
			$protocol[0] = 'http';
		}
		$path = $protocol[0].'://'.$host.'/';
		if($test){
			$path .= $test.'/';
		}
		return $path;
	}
	/**
	 * przetwarza ciag uri na tablice
	 * @param string $test nazwa folderu testowego (jesli ustawiony)
	 * @param string $uri ciag uri
	 * @return array tablica elementow z ktorych sklada sie get
	 */
	static function rewrite_get($test, $uri){
		if($test){
			$uri = str_replace('/'.$test.'/', '', $uri);
		}
		$get = explode('/', $uri);
		return $get;
	}
	/**
	 * zwraca ustawiony jezyk, lub null jesli brak
	 * @return string kod ustawionego jezyka, lub null jesli obsluga jezykow jest wylaczona
	 * @uses core_class::options()
	 * uses get::$core_lang
	 */
	public function get_lang(){
		if((bool)core_class::options('lang_support')){
			return $this->core_lang;
		}else{
			return NULL;
		}
	}
	/**
	 * zwraca aktualnie wybrana strone
	 * @return string nazwa aktualnej strony
	 * @uses uses get::$core_pages
	 */
   	public function get_current_page(){
		return end($this->core_pages);
   	}
	/**
	 * zwraca rodzica aktualnie wybranej strony, lub tej ktora przekazano w parametrze
	 * @example get_parrent() - zwraca rodzica aktualnie wybranej podstrony
	 * @example get_parrent('podstrona') - zwraca rodzica dla podstrony "podstrona"
	 * @param string $page opcjonalnie strona dla ktorej trzeba zwrucic rodzica
	 * @return string nazwa rodzica, lub FALSE jesli brak
	 * @uses get::$core_pages
	 */
	public function get_parrent($page = NULL){
		if($page){
			foreach($this->core_pages as $key => $val){
				if($val == $page){
					return $key -1;
				}
			}
			return FALSE;
		}else{
			end($this->core_pages);
			return prev($this->core_pages);
       	}
	}
	/**
	 * pobiera glowna strone
	 * @return string nazwa glownej strony, lub null jesli brak
	 * @uses get::$core_pages
	 */
	public function get_master(){
		if(isset($this->core_pages[0])){
			return $this->core_pages[0];
		}
		return NULL;
   	}
	/**
	 * zwraca pelna tablice ze stronami/podstronami oraz parametrami get, badz tylko strony/podstrony
	 * @param boolean $pages jesli na true zwraca jedunie strony/podstrony
	 * @return array lista stron/podstron i parametrow lub lista samych stron
	 * @uses get::$core_pages
	 */
	public function full_get($pages = FALSE){
		if(!$pages){
			$arr = array('params' => array(), 'pages' => $this->core_pages);
			foreach($this as $param => $val){
				if($param == 'core_path' || $param == 'core_page_type' || $param == 'core_pages' || $param == 'core_lang'){
					continue;
				}
				$arr['params'][$param] = $val;
			}
			return $arr;
		}else{
			return $this->core_pages;
		}
	}
	/**
	 * zwraca typ podstrony
	 * @return string typ podstrony
	 * @uses get::$core_page_type
	 */
	public function typ(){
		return $this->core_page_type;
   	}
	/**
	 * zwraca glowna scierzke dla strony, badz kompletna scierzke lacznie z podstronami
	 * @param boolean $type jesli false zwraca domene, jesli true kompletna scierzke
	 * @example path() - zwraca domene serwera
	 * @example path(1) - zwraca kompletna scierzke do podanej strony
	 * @return string scierzka naprawcza
	 * @uses get::$core_pages
	 * @uses get::$core_path
	 * @uses get::$core_lang
	 * @uses core_class::options()
	 */
	public function path($type = FALSE){
		if($type){
			if(core_class::options('rewrite')){
				$path = $this->core_path;
				if($this->core_lang){
					$path .= $this->core_lang.'/';
				}
				foreach($this->core_pages as $page){
					$path .= "$page/";
				}
				return $path;
			}else{
				$host = rtrim($this->core_path, '/');
				if(core_class::options('test')){
					$host = rtrim($host, core_class::options('test'));
				}
				$host = str_replace('//', '/', $host.$_SERVER['REQUEST_URI']);
				return $host;
			}
		}else{
			return $this->core_path;
		}
   	}
	/**
	 * sprawdza typ strony i ustawia wartosc zmiennej
	 * @access private
	 * @uses get::$core_pages
	 * @uses get::$core_page_type
	 */
	private function type(){
		if(!empty($this->core_pages)){
			switch($this->core_pages[0]){
				case"core_css":
					$this->core_page_type = 'css';
					break;
				case"core_js":
					$this->core_page_type = 'js';
					break;
				default:
					$this->core_page_type = 'html';
					break;
			}
		}else{
			$this->core_page_type = 'html';
		}
	}
	/**
	 * metoda odczytujaca nazwe strony do zaladowania z subdomeny
	 * @uses core_class::options()
	 * @uses get::$core_pages
	 */
	private function subdomain(){
		if((bool)core_class::options('subdomain')){
			$host = preg_replace(core_class::options('domain'), '', $_SERVER['HTTP_HOST']);
			$domains = explode('.', $host);
			if(empty($domains)){
				$start = 1;
				foreach($domains as $domain){
					if($start > core_class::options('subdomain')){
						break;
					}
					$this->core_pages[] = $domain;
					$start++;
				}
			}
		}
	}
}
/**
 * przetwarza liste post
 */
class post extends globals_class{
	/**
	 * przetwarza liste $_POST i ewentualnie wybiera poziom zabezpieczenia danych
	 * @uses globals_class::max()
	 * @uses core_class::options()
	 * @uses globals_class::__set()
	 */
	public function run(){
		if (!empty($_POST)){
			$l = 0;
			foreach ($_POST as $klucz => $parametr){
				$l++;
				$this->max($l, 'post');
				if((bool)core_class::options('post_secure') && !is_array($parametr)){
					if(core_class::options('post_secure') == 2){
						$parametr = htmlspecialchars($parametr, ENT_NOQUOTES);
					}
					$parametr = addslashes($parametr);
				}
				$this->$klucz = $parametr;
			}
		}
	}
}
/**
 * przetwarza liste cookie
 */
class cookie extends globals_class{
	/**
	 * przetwarza $_COOKIE
	 */
	public function run(){
		if(!empty($_COOKIE)){
			foreach ($_COOKIE as $klucz => $parametr){
				$this->$klucz = $parametr;
			}
		}else{
			$_COOKIE = array();
		}
	}
	/**
	 * ustawia pliki cookie bedace w obiekcie, z domyslna wartoscia czasu zycia
	 * oraz regeneruje identyfikator sesji
	 * @uses core_class::options()
	 */
	public function set_cookies(){
		foreach($this as $key => $val){
			if($key == 'PHPSESSID'){
				session_regenerate_id();
				$val = session_id();
			}
			setcookie($key, $val, time() + core_class::options('cookielifetime'));
		}
	}
}
/**
 * pretwarza sesje i umozliwia jej zmiany
 */
class session extends globals_class{
	/**
	 * przechowuje dane o uzytkowniku, np login, uprawnienia, czas do wylogowania
	 * @var array
	 * @private
	 */
	private $core_user = array();
	/**
	 * przechowuje dane dla frameworka, np ustawiony jezyk
	 * @var array
	 * @private
	 */
	private $core_core = array();
	/**
	 * przechowuje wspoldzielone dane do wyswietlania
	 * @var array
	 * @private
	 */
	private $core_display = array();
	/**
	 * przetwarza liste $_SESSION i zachowuje w 4 postaciach public, core, user, display
	 * @uses session::$core_user
	 * @uses session::$core_core
	 * @uses session::$core_dispaly
	 */
	public function run(){
		if(!empty($_SESSION)){
			foreach($_SESSION as $key => $val){
				if(!$val){
					$val = array(); //??????? jakis problem przy przetwarzaniu SESSION['display']
				}
				switch($key){
					case"public":
						foreach($val as $key2 => $val2){
							$this->$key2 = $val2;
						}
						break;
					case"user":
						foreach($val as $key2 => $val2){
							$this->core_user[$key2] = $val2;
						}
						break;
					case"core":
						foreach($val as $key2 => $val2){
							$this->core_core[$key2] = $val2;
						}
						break;
					case"display":
						foreach($val as $key2 => $val2){
							$this->core_display[$key2] = $val2;
						}
						break;
					default:
						$this->$key = $val;
						break;
               	}
			}
		}else{
			$_SESSION['user'] = array();
			$_SESSION['core'] = array();
			$_SESSION['public'] = array();
			$_SESSION['display'] = array();
		}
    }
	/**
	 * ustawia zmienne w obiekcie session
	 * @example set('test', 1, 'core') - ustawia zmienna test o wartosci 1 w tablicy core
	 * @example set('test', 1) - zwraca publiczne dane z sesji
	 * @example set('test', 1, 'user')
	 * @example set('test', 1, 'display')
	 * @param string $type typ zamiennej do zapisania, jesli brak to zwraca public
	 * @param string $name nazwa dla zmiennej
	 * @param mixed $val dane do zapisania
	 * @uses session::$core_user
	 * @uses session::$core_core
	 * @uses session::$core_dispaly
	 */
	public function set($name, $val, $type = 'public'){
		switch($type){
			case"public":
				$this->$name = $val;
				break;
			case"user":
				$this->core_user[$name] = $val;
				break;
			case"core":
				$this->core_core[$name] = $val;
				break;
			case"display":
				$this->core_display[$name] = $val;
				break;
			default:
				$this->$name = $val;
				break;
		}
   	}
	/**
	 * zwraca dane z obiektu session
	 * @example returns('public')
	 * @example returns('core')
	 * @example returns('display')
	 * @example returns('user')
	 * @param string $type typ danych do zwrocenia
	 * @return array tablica zawierajaca dane z odpowiedniej sekcji
	 * @uses session::$core_user
	 * @uses session::$core_core
	 * @uses session::$core_dispaly
	 */
	public function returns($type){
		switch($type){
			case"core":
				return $this->core_core;
				break;
			case"user":
				return $this->core_user;
				break;
			case"display":
				return $this->core_display;
				break;
			case"public":
				$arr = array();
				foreach($this as $param => $val){
					if($param == 'core_core' || $param == 'core_user' || $param == 'core_display'){
						continue;
					}
					$arr[$param] = $val;
				}
				return $arr;
				break;
			default:
				break;
       	}
	}
	/**
	 * przetwarza ponownie sesje i zapisuje dane w odpowiednie miejsca na tablicach
	 * zapisuje sformatowane dane w sesji
	 * @uses session::returns()
	 */
	public function set_session(){
		$_SESSION['public'] = $this->returns('public');
		$_SESSION['core'] = $this->returns('core');
		$_SESSION['user'] = $this->returns('user');
		$_SESSION['display'] = $this->returns('display');
	}
	/**
	 * czysci dane w sesji, badz konkretna informacje, badz grupe informacji (core, user, display, public)
	 * @example clear() czysci wszystko z wyjatkiem core
	 * @example clear('core') czysci tablice core
	 * @example clear('user') czysci tablice uzytkownika
	 * @example clear('jakas_zmienna') czysci jakas zmienna
	 * @param string $type nazwa informacji do usuniecia z public, lub nazwa grupy informacji do usuniecia
	 * @uses session::$core_core
	 * @uses session::$core_user
	 * @uses session::$core_display
	 */
	public function clear($type = NULL){
		if(!$type){
			foreach($this as $param => $val){
				if($param == 'core_core'){
					continue;
				}
				if($param == 'core_user' || $param == 'core_display'){
					$this->$param = array();
					continue;
				}
				unset($this->$param);
			}
		}else{
			switch($type){
				case'core':
					$this->core_core = array();
					break;
				case'user':
					$this->core_user = array();
					break;
				case'display':
					$this->core_display = array();
					break;
				case'public':
					foreach($this as $param => $val){
						if($param == 'core_core' || $param == 'core_user' || $param == 'core_display'){
							continue;
						}
						unset($this->$param);
					}
					break;
				default:
					unset($this->$type);
					break;
			}
		}
	}
}
/**
 * obsluga zaladowanych do frameworka plikow
 */
class files extends globals_class{
	/**
	 * tablica bledow z uploadowanych plikow
	 * @var array 
	 */
	public $upload_errors = array();
	/**
	 * rozmiar wszystkich wyslanych plikow
	 * @var integer
	 * @access private
	 */
	private $full_size = 0;
	/**
	 * przetwarza liste $_FILES
	 * @uses core_class::options()
	 * @uses files::$full_size
	 * @uses globals_class::max()
	 * @uses globals_class::__set()
	 * @throws coreException core_error_10, core_error_11
	 */
    public function run() {
		if(!empty($_FILES)){
			$l = 0;
			foreach($_FILES as $key => $file){
				$this->max($l, 'files');
				if($file['size'] > core_class::options('file_max_size')){
					throw new coreException('core_error_10', $file['name']);
				}
				$this->full_size += $file['size'];
				$max_size =  core_class::options('files_max_size');
				if($this->full_size > $max_size){
					throw new coreException('core_error_11', 'max: '.$max_size);
				}
				$path = pathinfo($file['name']);
				$this->$key = array(
					'name' => $file['name'],
					'type' => $file['type'],
					'tmp_name' => $file['tmp_name'],
					'error' => $file['error'],
					'size' => $file['size'],
					'extension' => $path['extension'],
					'basename' => $path['filename']
				);
           	}
		}
    }
	/**
	 * przenosci zaladowane pliki w odpowiednie miejsca
	 * moze przenosci pojedynczy, badz grupe we wskazane miejsce, badz w miejsca
	 * @example move(array('scierzka', 'scierzka2'), 'form1') - przenosi pliki z form1 do dwoch katalogow
	 * @example move(array('form1' => 'scierzka', 'form2' => 'scierzka2')) - przenosi pliki z form1 do scierzka a z form2 do scierzka 2
	 * @example move('jakas/scierzka', 'form2') - przenosi plik z form2 do wybrnej scierzki
	 * @example move('jakas/scierzka') - przenosi wszystkie wyslane pliki do wskazanej scierzki
	 * @param mixed $destination scierzka do ktorej ma przeniesc zawartosc, badz tablica scierzek
	 * @param string $name nazwa formularza z ktorego ma zostac przeniesiony plik (lub inputa?)
	 * @uses files::put()
	 * @uses globals_class::__set()
	 * @todo sprawdzanie czy scierzka istnieje i ewentualne jej uworzenie ?
	 * @todo co kiedy plik juz istnieje, zastepowanie, lub zgloszenie bledu, lub wstepne sprawdzenie
	 */
	public function move($destination, $name = NULL){
		if(is_array($destination)){
			if($name){
				foreach($destination as $path){
					$this->put($this->$name['tmp_name'], $path);
				}
			}else{
				foreach($destination as $key => $path){
					$this->put($this->$key['tmp_name'], $path);
				}
			}
		}else{
			if($name){
				$this->put($this->$name['tmp_name'], $destination);
			}else{
				foreach($this as $key => $val){
					if($key == 'full_size' || $key == 'upload_errors'){
						continue;
					}
					$this->put($val['tmp_name'], $destination);
				}
			}
		}
	}
	/**
	 * pobiera dane z pliku, badz wszystkich plikow w obiekcie
	 * @example read() - zwraca tablice zawartosci plikow
	 * @example read('pole_z_formularza') - zwraca zawartosc pliku dla danego pola
	 * @param string $name nazwa pola z formularza dla ktorego ma zostac zwrucona tresc pliku
	 * @return mixed zawartosc pliku, badz tablica zawartosci wszystkich plikow
	 * @uses files::single()
	 */
	public function read($name = NULL){
		if($name){
			return $this->single($name);
		}else{
			$data = array();
			foreach($this as $key => $val){
				if($key == 'full_size' || $key == 'upload_errors'){
					continue;
				}
				$data[$key] = $this->single($key);
			}
			return $data;
		}
	}
	/**
	 * zwraca rozmiar wszystkich wyslanych plikow
	 * @return integer laczny rozmiar plikow w bajtach
	 * @uses files::$full_size
	 */
	public function full_size(){
		return $this->full_size;
	}
	/**
	 * zwraca tablice z pewnymi wartosciami, np nazwy plikow, albo ich typy, lub bledy
	 * @example returns('name')
	 * @example returns('type')
	 * @example returns('tmp_name')
	 * @example returns('size')
	 * @example returns('extension')
	 * @example returns('basename')
	 * @example returns('error')
	 * @param string $type typ danych do zwrucenia
	 * @return array tablica zawierajaca dane
	 * @uses files::$upload_errors
	 * UPLOAD_ERR_OK
	 * Value: 0; There is no error, the file uploaded with success.
	 * UPLOAD_ERR_INI_SIZE
	 * Value: 1; The uploaded file exceeds the upload_max_filesize directive in php.ini.
	 * UPLOAD_ERR_FORM_SIZE
	 * Value: 2; The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.
	 * UPLOAD_ERR_PARTIAL
	 * Value: 3; The uploaded file was only partially uploaded.
	 * UPLOAD_ERR_NO_FILE
	 * Value: 4; No file was uploaded.
	 * UPLOAD_ERR_NO_TMP_DIR
	 * Value: 6; Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.
	 * UPLOAD_ERR_CANT_WRITE
	 * Value: 7; Failed to write file to disk. Introduced in PHP 5.1.0.
	 * UPLOAD_ERR_EXTENSION
	 * Value: 8; File upload stopped by extension. Introduced in PHP 5.2.0.
	 * @todo obsluga bledow uploadowanych plikow
	 */
	public function returns($type){
		$arr = array();
		foreach($this as $key => $val){
			if($key == 'full_size' || $key == 'upload_errors'){
				continue;
			}
			switch($type){
				case"name":
					$arr[$key] = $val['name'];
					break;
				case"type":
					$arr[$key] = $val['type'];
					break;
				case"tmp_name":
					$arr[$key] = $val['tmp_name'];
					break;
				case"size":
					$arr[$key] = $val['size'];
					break;
				case"extension":
					$arr[$key] = $val['extension'];
					break;
				case"basename":
					$arr[$key] = $val['basename'];
					break;
				case"error":
					if($val['error'] != UPLOAD_ERR_OK){
						$arr[$key] = $val['error'];
					}else{
						$this->upload_errors;
					}
					break;
				default:
					break;
			}
		}
		return $arr;
	}
	/**
	 * sprawdza czy plik istnieje
	 * @param string $path scierzka do sprawdzenia z nazwa pliku
	 * @return boolean TRUE jesli istnieje, FALSE jesli nie istnieje
	 * @static
	 */
	static function exist($path){
		if(file_exists($path)){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	/**
	 * zwraca zawartosc pojedynczego pliku
	 * @param string $file nazwa pola file z formularza
	 * @return mixed zawartosc pliku
	 * @uses starter_class::path()
	 * @throws coreException core_error_12
	 */
	private function single($file){
		$name = starter_class::path('TMP').'tmp';
		$bool = move_uploaded_file($this->$file, $name);
		if(!$bool){
			throw new coreException('core_error_12', $this->$file.' => '.$name);
		}
		$data = file_get_contents($name);
		unlink($name);
		return $data;
	}
	/**
	 * przenosi plik we wskazane miejsce
	 * @example put('plik_z_tmp', '/jakas_scierzka/folder/nazwa.pliku')
	 * @param string $filename nazwa pliku w tmp
	 * @param string $destination docelowa scierzka i nazwa pliku
	 * @throws coreException core_error_12
	 */
	private function put($filename, $destination){
		$bool = move_uploaded_file($filename, $destination);
		if(!$bool){
			throw new coreException('core_error_12', $filename.' => '.$destination);
		}
	}
}
?>