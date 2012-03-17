<?PHP
/**
 * @author chajr <chajr@bluetree.pl>
 * @version 2.1
 * @access private
 * @copyright chajr/bluetree
*/
/**
 * wczytuje i uruchamia biblioteki oraz moduly
 * @package core
 */
class loader_class {
	/**
	 * przechowuje obiekt get
	 * @var object
	 * @access public
	 */
	public $get;
	/**
	 * przechowuje obiekt post
	 * @var object
	 * @access public
	 */
	public $post;
	/**
	 * przechowuje obiekt cookie
	 * @var object
	 * @access public
	 */
	public $cookie;
	/**
	 * przechowuje obiekt sesji
	 * @var object
	 * @access public
	 */
	public $session;
	/**
	 * informacja czy ma zatrzymac przetwazanie modulow
	 * @var boolean
	 * @access public
	 */
	public $stop = FALSE;
	/**
	 * tablica modulow ktore maja byc nieuruchomione
	 * @var array
	 * @access public
	 */
	public $dissemble = array();
	/**
	 * tablica zaladowanych bibliotek
	 * @var array
	 * @access public
	 */
	public $lib = array();
	/**
	 * tablica uruchomionych modulow
	 * @var array
	 * @access public
	 */
	public $mod = array();
	/**
	 * przechowuje obiekt wyslanych plikow
	 * @var object
	 * @access public
	 */
	public $files;
	/**
	 * informacja czy serwis jest odpalony z przegladarki mobilnej
	 * @var boolean 
	 * @access public
	 */
	public $mobile_browser = NULL;
	/**
	 * obiekt drzewa strony
	 * @var object
	 * @access private
	 */
	private $tree;
	/**
	 * przechowuje obiekt display
	 * @var object
	 * @access private
	 */
	private $display;
	/**
	 * przechowuje obiekt obslugi jezyka
	 * @var object
	 */
	private $lang;
	/**
	 * przechowuje obiekt obslugi metatagow
	 * @var object
	 * @access private
	 */
	private $meta;
	/**
	 * obiekt bledow
	 * @var object
	 * @access private
	 */
	private $error;
	/**
	 * wyrazenie regularne dopasowujace klase
	 * @var string
	 * @access private
	 */
	private $klasa = "/^<\\?PHP((\r)?(\n)?)*(final )?class ([\\d\\w-_])+( extends| implements([\\d\\w-_])+)?(.*)}((\r)?(\n)?)*\\?>((\r)?(\n)?)*$/is";
	/**
	 * przechowuje nazwy blokow dla modulow i nazwy modolow ktore maja zostac zaladowane do wskazanego bloku
	 * @var type 
	 */
	private $block = array();
	/**
	 * uruchamia caly obiekt i przepisuje odpowiednie obiekty do wykozystania
	 * @param object $tree obiekt drzewa strony
	 * @param object $display obiekt wyswietlania tresci
	 * @param object $lang obiekt obslugi jezyka
	 * @param object $meta obiekt obslugi metatagow
	 * @param object $get obiekt get
	 * @param object $post obiekt post
	 * @param object $cookie obiekt cookie
	 * @param object $session obiekt sesji
	 * @param object $files obiekt zaladowanych plikow
	 * @param object $error obiekt obslugi bledow
	 * @uses loader_class::$tree
	 * @uses loader_class::$display
	 * @uses loader_class::$lang
	 * @uses loader_class::$meta
	 * @uses loader_class::$get
	 * @uses loader_class::$post
	 * @uses loader_class::$cookie
	 * @uses loader_class::$session
	 * @uses loader_class::$files
	 * @uses loader_class::$error
	 * @uses loader_class::load()
	 */
    public function __construct($tree, $display, $lang, $meta, $get, $post, $cookie, $session, $files, error_class $error) {
		$this->tree = $tree;
		$this->display = $display;
		$this->lang = $lang;
		$this->meta = $meta;
		$this->get = $get;
		$this->post = $post;
		$this->cookie = $cookie;
		$this->session = $session;
		$this->files = $files;
		$this->error = $error;
		$this->detect_mobile();
		$this->load('lib');
		$this->load('mod');
	}
	/**
	 * zwraca tablice blokow i ich modulow
	 * @return array tablica nazw blokow i powiazanych z nimi modulow 
	 */
	public function return_block(){
		return $this->block;
	}
	/**
	 * generowanie tresci do znacznika w odpowiednim module
	 * @example generate('nazwa_modulu', 'znacznik', 'jakas tresc do zastapienia')
	 * @example generate('nazwa_modulu', array('znacznik1' => 'tresc', 'znacznik2' => 'inna tresc'))
	 * @param string $modul nazwa modulu do ktorego ma zostac wpisana tresc
	 * @param string $znacznik nazwa znacznika do zastapienia
	 * @param mixed $tresc jakas tresc, badz tablica tresci i zancznikow
	 * @return integer zwraca ilosc zastapionych znacznikow, lub NULL jesli nie znaleziono elementow
	 * @uses loader_class::$dispaly
	 * @uses display_class::generate()
	 */
	public function generate($znacznik, $modul, $tresc = FALSE){
		$bool = $this->display->generate($znacznik, $tresc, $modul);
		return $bool;
	}
	/**
	 * przetwaza w petli zbior znacznikow
	 * @param string $modul nazwa modulu
	 * @param string $znacznik nazwa znacznika statrowego
	 * @param array $arr tablica tresci do wygenerowania
	 * @return integer zwraca ilosc zastapionych znacznikow, lub NULL jesli nie znaleziono elementow
	 * @uses loader_class::$display
	 * @uses display_class::loop()
	 */
	public function loop($znacznik, $arr, $modul){
		$bool = $this->display->loop($znacznik, $arr, $modul);
		return $bool;
	}
	/**
	 * dodaje do strony js lub css zalaczonego do modulu
	 * @example set('nazwa_modulu', 'jquery', 'js', 'external')
	 * @example set('nazwa_modulu', 'jakis_skrypt', 'js')
	 * @example set('nazwa_modulu', 'base', 'css', 'internal', 'print')
	 * @param string $mod nazwa modulu
	 * @param string $name nazwa pliku do zaladowania
	 * @param string $type typ pliku (css, lub js)
	 * @param string $external czy plik z zewnetrznego serwisu (external), domyslnie internal
	 * @param string $media  typ media dla css-a (np. print)
	 * @uses loader_class::$display
	 * @uses display_class::set()
	 */
	public function set($mod, $name, $type, $external = 'internal', $media = ''){
		$this->display->set($mod, $name, $type, $external, $media);
	}
	/**
	 * dodaje komplety zancznik meta do naglowka
	 * @param string $meta kompletny metatag
	 * @uses loader_class::$meta
	 * @uses meta_class::add()
	 */
	public function add_meta($meta){
		$this->meta->add($meta);
	}
	/**
	 * dopisuje tresc do istniejacego juz znacznika meta
	 * @param string $typ nazwa znacznika
	 * @param string $meta tresc do dopisania
	 * @uses loader_class::$meta
	 * @uses meta_class::insert()
	 */
	public function add_to_meta($typ, $meta){
		$this->meta->insert($typ, $meta);
	}
	/**
	 * w zaleznosci od parametru (true/false) zwraca kod jezyka domyslnego lub zaladowanego
	 * @param boolean $type jesli true zwraca kod domyslnego jezyka, jesli brak lub false zwraca ustawiony kod
	 * @return string kod jezyka
	 * @uses loader_class::$lang
	 * @uses lang_class::$default
	 * @uses lang_class::$lang
	 */
	public function lang($type = FALSE){
		if($type){
			return $this->lang->default;
		}else{
			return $this->lang->lang;
		}
	}
	/**
	 * wczytuje do tablicy layout dla danego modulu
	 * @param string $mod nazwa modulu
	 * @param string $name nazwa layoutu do zaladowania, jesli brak wczytuje layout o nazwie modulu
	 * @uses loader_class::$display
	 * @uses display_class::layout()
	 */
	public function layout($mod, $name = NULL){
		if(!$name){
			$name = $mod;
		}
		$this->display->layout($name, $mod);
	}
	/**
	 * informuje fraeworka ze modul ma zostac przetulmaczony
	 * @param string $name  nazwa modulu do tulmaczenia
	 * @uses loader_class::$lang
	 * @uses lang_class::set_array()
	 */
	public function translate($name){
		$this->lang->set_array($name);
	}
	/**
	 * zwraca tablice breadcrumbs
	 * @return type array tablica z nazwami i linkami dla scierzki internaktywnej
	 * @uses loader_class::$tree
	 * @uses tree_class::$breadcrumbs
	 */
	public function breadcrumbs(){
		return $this->tree->breadcrumbs;
	}
	/**
	 * tworzy mape strony wraz ze scierzkami, jesli strona ma ustawiony parametr hidden=1 bedzie niewidoczna na liscie
	 * @param string $xml nazwa xml-a do przetwozenia, defaultowo tree
	 * @param boolean $admin jesli na true, zwraca kompletna liste wraz z opcjami
	 * @return array tablica z nazwami oraz linkami do stron/podstron
	 * @uses loader_class::$tree
	 * @uses tree_class::map()
	 */
	public function map ($xml = 'tree', $admin = FALSE){
		return $this->tree->map($xml, $admin);
	}
	/**
	 * dodaje informacje do tablicy komunikatow
	 * @param string $mod nazwa modulu zglaszajacego
	 * @param string $type typ komunikatu (critic, warning, info, ok) lub znacznik bledu
	 * @param string $code kod bledu
	 * @param strin $message dodatkowe informacje
	 * @uses loader_class::$error
	 * @uses error_class::add_error()
	 */
	public function add_error($mod, $type, $code, $message){
		$this->error->add_error($type, '', $code, '', '', $message, $mod);
	}
	
	
	
	
	
	public function set_translate($array){
		
	}
	/**
	 * laduje moduly oraz biblioteki
	 * @param string $type typ elementu do zaladowania (lib/mod)
	 * @uses loader_class::$tree
	 * @uses tree_class::$mod
	 * @uses tree_class::$lib
	 * @uses starter_class::package()
	 * @uses loader_class::$lib
	 * @uses starter_class::load()
	 * @uses loader_class::validate()
	 * @uses loader_class::run()
	 * @uses loader_class::set_block()
	 * @throws coreException core_error_20
	 */
	private function load($type){
		foreach($this->tree->$type as $name => $val){
			if($type == 'lib'){
				$libs = array();
				$libs = starter_class::package($name);
				if(!$libs){
					throw new coreException('core_error_20', $name);
				}
				$this->lib = array_merge($this->lib, $libs);
			}elseif($type == 'mod'){
				if($this->stop){
					break;
				}
				if(in_array($name, $this->tree->$type)){
					continue;
				}
				if($val['exec']){
					$exe = $val['exec'];
				}else{
					$exe = $name;
				}
				$this->set_block($val['block'], $name);
				$path = 'modules/'.$name.'/'.$exe.'.php';
				$bool = starter_class::load($path);
				if(!$bool){
					throw new coreException('core_error_20', $name.' - '.$exe);
				}
				$this->validate($path, $exe, $name);
				$this->run($exe, $name, $val['param']);
			}
		}
	}
	/**
	 * przypisuje do modulu blok do jakiego ma zostac zaladowany
	 * @param string $block nazwa bloku
	 * @param string $mod_name nazwa modulu
	 * @uses loader_class::$block
	 */
	private function set_block($block, $mod_name){
		if($block){
			$this->block[$mod_name] = $block;
		}
	}
	/**
	 * uruchamia wybrany modul, oraz przechwytuje zgloszone przez niego bledy
	 * @example run ('mod_class', 'mod_class', array(), 'left_column')
	 * @example run ('mod_innyplik_class', 'mod_class', array(0 => 'asdas', 1 => 'inna wartosc'), '')
	 * @param string $exe nazwa klasy ktora ma zostac uruchomiona (moze byc nazwa modulo, bac innym plikiem z modulu)
	 * @param string $mod_name nazwa modulu
	 * @param array $params tablica dodatkowych parametrow dla modulu
	 * @uses loader_class::$mod
	 * @uses loader_class::$dissemble
	 * @uses loader_class::$stop
	 * @uses loader_class::$options
	 * @uses loader_class::$error
	 * @uses core_class::options()
	 * @uses modException_class::show()
	 * @uses warningException_class::show()
	 * @uses infoException_class::show()
	 * @uses okException_class::show()
	 * @uses coreException::show()
	 * @uses Exception::getCode()
	 * @uses Exception::getMessage()
	 * @throws coreException
	 */
	private function run($exe, $mod_name, $params){
		$unthrow = core_class::options('unthrow');
		try{
			$bool = class_exists ($exe);
			if(!$bool){
				throw new coreException('core_error_22', $exe.' - '.$mod_name);
			}
			if(in_array($mod_name, $this->dissemble)){
				return;
			}
			$this->mod[$mod_name] = new $exe($this, $params, $mod_name, $unthrow);
		}catch (coreException $error_core){
			$error_core->show($this->error);
			$this->stop = TRUE;
		}catch (modException $error_mod){
			if(!(bool)$unthrow){
				$error_mod->show($this->error, $mod_name);
				try{
					$this->mod[$mod_name] = new $exe($this, $params, $mod_name, $unthrow, TRUE);
				}catch (coreException $error_core){
					$error_core->show($this->error, $mod_name);
					$this->stop = TRUE;
				}catch (modException $error_mod){
					$error_mod->show($this->error, $mod_name);
				}
			}
		}catch (warningException $warning){
			$warning->show($this->error, $mod_name);
		}catch (infoException $info){
			$info->show($this->error, $mod_name);
		}catch (okException $ok){
			$ok->show($this->error, $mod_name);
		}catch (Exception $e){
			try{
				throw new coreException($e->getCode(), $e->getMessage(), $mod_name);
			}catch (coreException $error_core){
				$error_core->show($this->error, $mod_name);
				//$this->stop = TRUE;???
			}
		}
	}
	/**
	 * sprawdza zaladowany plik czy jest obiektem (jesli wybrano opcje w konfiguracji)
	 * @param string $path scierzka do biblioteki/modulu
	 * @param string $exe nazwa pliku startowego
	 * @param string $mod_name nazwa modulu
	 * @uses loader_class::$options
	 * @uses loader_class::$klasa
	 * @uses starter_class::load()
	 * @uses core_class::options()
	 * @throws coreException core_error_20, core_error_21
	 */
	private function validate($path, $exe, $mod_name){
		if(core_class::options('core_procedural_mod_check')){
			$tresc = starter_class::load($path, TRUE);
			if(!$tresc){
				throw new coreException('core_error_20', $mod_name.' - '.$exe);
			}
			$bool = preg_match($this->klasa, $tresc);
			if(!$bool){
				throw new coreException_class('core_error_21', $mod_name.' - '.$exe);
			}
		}
	}
	/**
	 * wykrywa czy serwis jest odpalony z przegladarki mobilnej
	 * @uses core_class::$mobile_browser
	 */
	private function detect_mobile(){
//		$useragent = $_SERVER['HTTP_USER_AGENT'];
//		if(preg_match('/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)
//				|| preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i', 
//					substr($useragent,0,4))
//		){
//			$this->mobile_browser = TRUE;
//		}else{
//			$this->mobile_browser = FALSE;
//		}
	}
}
?>