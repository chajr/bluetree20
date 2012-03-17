<?PHP
/**
 * przetwarza drzewo strony, zwraca listye modulow, bibliotek, css, js i szablon glowny do zaladowania
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 2.2.2
 * @copyright chajr/bluetree
 * @todo dorobic w pliku drzewa atrybut lang, wskazujacy ze dana strona powiazana jest tylko i wylacznie z danymi jezykami
 */
class tree_class {
	/**
	 * obiekt xml w ktorym tworzona jest mapa strony
	 * @var object 
	 * @access private
	 */
	private $sitemap;
	/**
	 * lista parametrow get z nazwami stron
	 * @var array
	 * @access private
	 */
	private $get;
	/**
	 * naswa strony glownej
	 * @var string
	 * @access private
	 */
	private $master;
	/**
	 * obiekt xml ze strona do przetwarzania
	 * @var xmlobject
	 * @access private
	 */
	private $main;
	/**
	 * glowny obiekt xml
	 * @var xmlobject
	 * @access private
	 */
	private $xml;
	/**
	 * wskaznik dla drzewa ktory element z get ma byc aktualnie przetwazany
	 * @var integer
	 * @access private
	 */
	private $pointer = 1;
	/**
	 * ustawiony jezyk
	 * @var string
	 */
	private $lang;
	/**
	 * tablica z modulami do zaladowania
	 * @example [nazwa_modulu] = array('param' => array(tablica parametrow),
	 *									'exec' => 'plik wykonawczy',
	 *									'block' => 'blok do ktorego beda ladowane moduly')
	 * @var array
	 * @access public
	 */
	public $mod = array();
	/**
	 * tablica z bibliotekami do zaladowania
	 * @example [nazwa pakietu+biblioteki] = array(tablica parametrow)
	 * @var array
	 * @access public
	 */
	public $lib = array();
	/**
	 * tablica css-ow do zaladowania, wewnetrznych oraz zewnetrznych (http://)
	 * @example array('external' => array(css-y zewnetrzne), 'interanl' => array(css-y wewnetrzne))
	 * @var array
	 * @access public
	 */
	public $css = array(
		'external' => array(),
		'internal' => array(
			'core' => array()
		)
	);
	/**
	 * tablica js-ow do zaladowania, wewnetrznych oraz zewnetrznych (http://)
	 * @example array('external' => array(js-y zewnetrzne), 'interanl' => array(js-y wewnetrzne))
	 * @var array
	 * @access public
	 */
	public $js = array(
		'external' => array(),
		'internal' => array(
			'core' => array()
		)
	);
	/**
	 * tablica zwracajaca scierzke do obecnie otwartej podstrony
	 * @example array([0] => array(lista nazw dla stron), [1] => array(lista id))
	 * @var array
	 * @access public
	 */
	public $breadcrumbs = array();
	/**
	 * nazwa layoutu ktory ma zostac zaladowany
	 * @var string
	 * @access public
	 */
	public $layout;
	/**
	 * lista identyfikatorow menu do ktorych ma zostac dowiazana strona
	 * @var array
	 * @access public
	 */
	public $menu = array();
	/**
	 * uruchamia przetwarzanie drzewa strony
	 * laduje liste bibliotek, modulow, css-ow, js-ow, layout do uruchomienia dla wybraniej strony
	 * @param string $lang wlaczony jezyk
	 * @param array $get tablica stron
	 * @uses tree_class::$layout
	 * @uses tree_class::$get
	 * @uses tree_class::$xml
	 * @uses tree_class::load()
	 * @uses xml_class::__construct()
	 * @throws coreException core_error_16
	 */
	public function __construct($get, $lang){
		$this->lang = $lang;
		$this->get = $get;
		$this->xml = new xml_class();
		$this->load();
		if(!$this->layout){
			throw new coreException('core_error_16', $this->layout);
		}
	}
	/**
	 * tworzy mape strony wraz ze scierzkami, jesli strona ma ustawiony parametr hidden=1 bedzie niewidoczna na liscie
	 * @param string $xml nazwa xml-a do przetwozenia, defaultowo tree
	 * @param boolean $admin jesli na true, zwraca kompletna liste wraz z opcjami
	 * @return array tablica z nazwami oraz linkami do stron/podstron
	 * @uses tree_class::$xml
	 * @uses tree_class::submap()
	 * @uses xml_class::$documentElement
	 * @uses xml_class::$childNodes
	 * @uses xml_class::wczytaj()
	 * @uses xml_class::$err
	 * @uses starter_class::path()
	 * @throws coreException core_error_13
	 */
	public function map($xml = 'tree', $admin = FALSE){
		$mapa = array();
		$bool = $this->xml->wczytaj(starter_class::path('cfg').$xml.'.xml', TRUE);
		if(!$bool){
			throw new coreException('core_error_13', $this->xml->err.' '.$xml);
		}
		$mapa = $this->submap($this->xml->documentElement->childNodes, '', $admin);
		return $mapa;
	}
	/**
	 * przetwarza drzewo xml strony i generuje na jego podstawie mape strony dla google
	 * @return xml kompletna mapa strony
	 * @uses tree_class::$xml
	 * @uses tree_class::$sitemap
	 * @uses xml_class::$documentElement
	 * @uses xml_class::$childNodes
	 * @uses xml_class::__construct()
	 * @uses xml_class::createElement()
	 * @uses xml_Class::setAttribute
	 * @uses xml_class::appendChild()
	 * @uses xml_class::zapisz()
	 * @uses tree_class::site_submap()
	 */
	public function sitemap(){
		$main_page = $this->xml->documentElement->childNodes;
		$this->sitemap = new xml_class(1.0, 'UTF-8');
		$root = $this->sitemap->createElement('urlset');
		$root->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
		$this->sitemap->appendChild($root);
		$this->site_submap($main_page);
		return $this->sitemap->zapisz(0, 1);
	}
	/**
	 * wewnetrzna funkcja rekurencyjna zwracajaca drzewo podstron i poszukujaca wewnatrz siebie kolejnych
	 * przetwarza strukture dla sitemap
	 * @param xmlobject $nodes olekcja wezlow xml-a do przetwozenia
	 * @param string $path scierzka nadrzedna dla elementow
	 * @uses xml_class::$nodeType
	 * @uses xml_class::$nodeName
	 * @uses xml_class::$childNodes
	 * @uses xml_class::$documentElement
	 * @uses xml_class::$firstChild
	 * @uses tree_class::$sitemap
	 * @uses tree_class::$lang
	 * @uses xml_class::getAttribute()
	 * @uses xml_class::createElement()
	 * @uses xml_class::appendChild()
	 * @uses get::real_path()
	 * @uses core_class::options()
	 * @uses tree_class::site_submap()
	 */
	private function site_submap($nodes, $path = ''){
		foreach($nodes as $child){
			if($child->nodeType == 8){
				continue;
			}
			$name = $child->nodeName;
			if($name == 'lib' || $name == 'mod' || $name == 'css' || $name == 'js'){
				continue;
			}
			$id = $child->getAttribute('id');
			if($child->childNodes && ($child->firstChild->nodeName == 'sub' || $child->firstChild->nodeName == 'page')){
				$this->site_submap($child->childNodes, "$path/$id");
			}
			$url = $this->sitemap->createElement('url');
			$changefreq = $child->getAttribute('changefreq');
			if(!$changefreq){
				$changefreq = 'never';
			}
			$changefreq = $this->sitemap->createElement('changefreq', $changefreq);
			$priority = $child->getAttribute('priority');
			if(!$priority){
				$priority = '0.1';
			}
			$folder = get::real_path(core_class::options('test'));
			$lang = $this->lang;
			$priority = $this->sitemap->createElement('priority', $priority);
			$loc = $this->sitemap->createElement('loc', "$folder$lang$path/$id");
			$url->appendChild($loc);
			$url->appendChild($changefreq);
			$url->appendChild($priority);
			$this->sitemap->documentElement->appendChild($url);
		}
	}
	/**
	 * wewnetrzna funkcja rekurencyjna zwracajaca drzewo podstron i poszukujaca wewnatrz siebie kolejnych
	 * @param xmlobject $nodes kolekcja wezlow xml-a do przetwozenia
	 * @param string $path scierzka nadrzedna dla elementow
	 * @param boolean $admin jesli na rue, zwraca kompletna liste wraz z opcjami
	 * @return array tablica podstron
	 * @uses tree_class::submap()
	 * @uses tree_class::date()
	 * @uses xml_class::$nodeType
	 * @uses xml_class::$nodeName
	 * @uses xml_class::$childNodes
	 * @uses xml_class::$firstChild
	 * @uses xml_class::getAttribute()
	 */
	private function submap($nodes, $path, $admin){
		$mapa = array();
		foreach($nodes as $child){
			if($child->nodeType == 8){
				continue;
			}
			$name = $child->nodeName;
			$options = $child->getAttribute('options');
			if($name == 'lib' || $name == 'mod' || $name == 'css' || $name == 'js'){
				continue;
			}
			if((!$admin && !(bool)$options{0}) || 
				(!$admin && !(bool)$options{1}) || (!$admin && !$this->date($child))){
				continue;
			}
			$id = $child->getAttribute('id');
			if($child->childNodes && ($child->firstChild->nodeName == 'sub' || $child->firstChild->nodeName == 'page')){
				$mapa[$id]['sub'] = $this->submap($child->childNodes, "$path/$id", $admin);
			}
			$mapa[$id]['name'] = $child->getAttribute('name');
			$mapa[$id]['path'] = "{;path;$path/$id;}";
			if($admin){
				$mapa[$id]['options'] = array($options, $child->getAttribute('startDate'), $child->getAttribute('endDate'));
			}
		}
		return $mapa;
	}
	/**
	 * tworzy scierzke nawigacji
	 * @uses tree_class::$breadcrumbs
	 * @uses tree_class::$main
	 * @uses xml_class::getAttribute()
	 */
	private function breadcrumbs(){
		$options = $this->main->getAttribute('options');
		if((bool)$options{3}){
			$list = array(
				'id' => $this->main->getAttribute('id'),
				'name' => $this->main->getAttribute('name')
			);
			$this->breadcrumbs[] = $list;
		}
	}
	/**
	 * sprawdza czy podana strona istnieje, oraz czy ma zamiast niej zaladowac index czy error404
	 * @uses tree_class::$main
	 * @uses tree_class::$options
	 * @uses tree_class::$xml
	 * @uses xml_class::get_id()
	 * @uses core_class::options()
	 * @uses tree_class::$lang
	 * @throws coreException core_error_14
	 */
	private function chk404(){
		if(!$this->main && (bool)core_class::options('error404')){
			$path = 'error404';
			if(!(bool)core_class::options('rewrite')){
				$path = "index.php?core_lang=$this->lang&p0=$path";
			}
			if(core_class::options('test')){
				$path = core_class::options('test')."/$path";
			}
			header("Location: /$path");
			exit;
		}elseif(!$this->main && !(bool)core_class::options('error404')){
			$this->main = $this->xml->get_id('index');
		}
		if(!$this->main){
			throw new coreException('core_error_14');
		}
	}
	/**
	 * sprawcza czy dana strona laduje jakies zewnetrzne drzewo strony
	 * jesli tak czycsci wszystkie zaladowane elementy i laduje nowe drzewo strony, zachowujac scierzke nawigacji
	 * @uses tree_class::$main
	 * @uses tree_class::clear()
	 * @uses tree_class::load()
	 * @uses xml_class::getAttribute()
	 */
	private function external(){
		if((bool)$this->main->getAttribute('external')){
			$this->clear();
			$this->load($this->main->getAttribute('external'));
		}
	}
	/**
	 * funkcja rekurencyjna przechodzaca przez podstrony (jesli istnieja) i sprawdzajaca czy podana w get podstrona znajduje sie na drzewie
	 * jesli tak zaleznie od dziedziczenia dodaje, badz usuwa i dodaje moduly, biblioteki css-y i js-y do uruchomienia
	 * @uses tree_class::$main
	 * @uses tree_class::$get
	 * @uses tree_class::$pointer
	 * @uses tree_class::on()
	 * @uses tree_class::breadcrumbs()
	 * @uses tree_class::clear()
	 * @uses tree_class::set()
	 * @uses tree_class::tree()
	 * @uses tree_class::chk404()
	 * @uses tree_class::redirect()
	 * @uses tree_class::date()
	 * @uses xml_class::$firstChild
	 * @uses xml_class::$nodeName
	 * @uses xml_class::$childNodes
	 * @uses xml_class::getAttribute()
	 */
	private function tree(){
		if($this->main->firstChild->nodeName == 'sub' && isset($this->get[$this->pointer])){
			$childs = $this->main->childNodes;
			foreach($childs as $child){
				if($child->nodeName == 'sub' && $child->getAttribute('id') == $this->get[$this->pointer]){
					$this->main = $child;
					$this->on();
					$this->date();
					$this->redirect();
					$this->breadcrumbs();
					$options = $child->getAttribute('options');
					if(!(bool)$options{4}){
						$this->clear();
					}
					$this->set();
					$this->menu();
					$this->pointer++;
					$this->tree();
					break;
				}else{
					unset($this->main);
				}
			}
			$this->chk404();
		}
	}
	/**
	 * sprawdza czy dana strona jest wlaczona
	 * @uses tree_class::$main
	 * @uses xml_class::getAttribute()
	 * @throws coreException core_error_15
	 */
	private function on(){
		$options = $this->main->getAttribute('options');
		if(!(bool)$options{0}){
			throw new coreException('core_error_15');
		}
	}
	/**
	 * ustawia na listach moduly, biblioteki css-y i js-y
	 * @param boolean $root czy elementem do przetwazania ma byc normalna strona/podstrona czy root drzewa xml
	 * @uses tree_class::$css
	 * @uses tree_class::$js
	 * @uses tree_class::$main
	 * @uses tree_class::$layout
	 * @uses tree_class::$lib
	 * @uses tree_class::$mod
	 * @uses tree_class::param()
	 * @uses xml_class::$childNodes
	 * @uses xml_class::$nodeName
	 * @uses xml_class::$nodeValue
	 * @uses xml_class::getAttribute()
	 * @uses tree_class::ext()
	 */
	private function set($root = FALSE){
		if(!$root){
			$this->layout = $this->main->getAttribute('layout');
		}
		foreach($this->main->childNodes as $nod){
			switch($nod->nodeName){
				case'lib':
					if((bool)$nod->getAttribute('on')){
						$this->lib[$nod->nodeValue] = $nod->nodeValue;
					}
					break;
				case'mod':
					if((bool)$nod->getAttribute('on')){
						$this->mod[$nod->nodeValue]['param'] = $this->param($nod->getAttribute('param'));
						$this->mod[$nod->nodeValue]['exec'] = $nod->getAttribute('exec');
						$this->mod[$nod->nodeValue]['block'] = $nod->getAttribute('block');
					}
					break;
				case'css':
					$type = $this->ext($nod);
					if($nod->getAttribute('media')){
						$this->css[$type]['core']['media'][$nod->getAttribute('media')][$nod->nodeValue] = $nod->nodeValue;
					}else{
						$this->css[$type]['core'][$nod->nodeValue] = $nod->nodeValue;
					}
					break;
				case'js':
					$type = $this->ext($nod);
					$this->js[$type]['core'][$nod->nodeValue] = $nod->nodeValue;
					break;
				default:
					break;
			}
		}
	}
	/**
	 * jesli wystapilo wylaczenie dziedziczenia, badz zaladowanie zewnetrznego drzewa oczyszcza tablice
	 * @uses tree_class::$layout
	 * @uses tree_class::$css
	 * @uses tree_class::$js
	 * @uses tree_class::$lib
	 * @uses tree_class::$mod
	 */
	private function clear(){
		$this->layout = '';
		$this->css = array('external' => array(), 'internal' => array());
		$this->js = array('external' => array(), 'internal' => array());
		$this->lib = array();
		$this->mod = array();
	}
	/**
	 * wczytuje plik drzewa, laduje do pamieci i uruchamia przetwazanie
	 * @param string $xml nazwa xml-a do zaladowania, defaultowo tree
	 * @uses tree_class::$xml
	 * @uses tree_class::$get
	 * @uses tree_class::$master
	 * @uses tree_class::$main
	 * @uses tree_class::on()
	 * @uses tree_class::chk404()
	 * @uses tree_class::external()
	 * @uses tree_class::breadcrumbs()
	 * @uses tree_class::set()
	 * @uses tree_class::tree()
	 * @uses tree_class::redirect()
	 * @uses tree_class::menu()
	 * @uses xml_class::$documentElement
	 * @uses xml_class::$err
	 * @uses xml_class::get_id()
	 * @uses xml_class::wczytaj()
	 * @uses starter_class::path()
	 * @throws coreException core_error_13
	 */
	private function load($xml = 'tree'){
		$bool = $this->xml->wczytaj(starter_class::path('cfg').$xml.'.xml', TRUE);
		if(!$bool){
			throw new coreException('core_error_13 ', $this->xml->err.' '.$xml);
		}
		if(empty($this->get)){
			$this->master = 'index';
		}else{
			if($xml != 'tree'){
				$param = $this->get[1];
			}else{
				$param = $this->get[0];
			}
			$this->master = $param;
		}
		$this->main = $this->xml->documentElement;
		$this->on();
		$this->set(1);
		$this->main = $this->xml->get_id($this->master);
		$this->chk404();
		$this->on();
		$this->redirect();
		$this->external();
		$this->breadcrumbs();
		$this->set();
		$this->menu();
		$this->tree();
	}
	/**
	 * sprawdza typ css-a/js-a (zewnetrzny lub wewnetrzny)
	 * @param xmlobject $nod obiekt xml do sprawdzenia
	 * @return string typ css-a/js-a (internal lub external)
	 * @uses xml_class::getAttribute()
	 */
	private function ext($nod){
		if($nod->getAttribute('external')){
			return 'external';
		}else{
			return 'internal';
		}
	}
	/**
	 * przetwarza parametrzy zgloszone przez lib/mod
	 * zwraca parametry przerobione na tablice
	 * @param string $param parametry do przetworzenia
	 * @return array tablica parametrow do przekazania modulowi/bibliotece
	 * @uses core_class::cptions()
	 */
	private function param($param){
		$option = core_class::options('param_sep');
		if($option){
			$sep = $option;
		}else{
			$sep = '::';
		}
		$config = explode($sep, $param);
		if(empty($config)){
			return NULL;
		}
		return $config;
	}
	/**
	 * ustala powiazania stron/podstron do konkretnych menusow
	 * @uses tree_class::$main
	 * @uses tree_class::$menu
	 * @uses xml_class::$nodeValue
	 * @uses xml_class::getAttribute()
	 * @uses xml_class::getElementsByTagName()
	 */
	private function menu(){
		$options = $this->main->getAttribute('options');
		if((bool)$options{2}){
			$menu = $this->main->getElementsByTagName('menu');
			if($menu){
				foreach($menu as $element){
					$this->menu[] = $element->nodeValue;
				}
			}else{
				$this->menu[] = 'main';
			}
		}
	}
	/**
	 * jesli strona/podstrona ma ustawione przekierowanie, laduje inna strona ustawiona jako atrybut
	 * @uses tree_class::$main
	 * @uses xml_class::getAttribute()
	 */
	private function redirect(){
		$location = $this->main->getAttribute('redirect');
		if($location){
			header('Location: '.$location);
			exit;
		}
	}
	/**
	 * sprawdza date rozpoczecia i zakonczenia wyswietlania strony i zglasza odpowiedni blad
	 * @param boolean $node wezel strony do sprawdzenia, jesli brak, sprawdza glowny przetwazany
	 * @return boolean zwraca true jesli strona widoczna, false jesli niewidoczna, gdy podany zostal wezel
	 * @uses tree_class::$main
	 * @uses xml_class::getAttribute()
	 * @throws coreException core_error_23, core_error_24
	 */
	private function date($node = FALSE){
		if(!$node){
			$node = $this->main;
		}
		$time = $node->getAttribute('startDate');
		if($time && $time > time()){
			if(!$node){
				$date = strftime('%c', $time);
				throw new coreException('core_error_23', $date);
			}
			return FALSE;
		}
		$time = $node->getAttribute('endDate');
		if($time && $time < time()){
			if(!$node){
				$date = strftime('%c', $time);
				throw new coreException('core_error_24', $date);
			}
			return FALSE;
		}
		return TRUE;
	}
}
?>