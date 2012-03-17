<?PHP
/**
 * klasa odpowiedzialana za wyswietlanie
 * przetwarzanie szablonow, zastepowanie znacznikow, petle, renderowanie pelnej strony, ladowanie dodatkowych szablonow
 * oczyszczanie kodu ze znacznikow, uzupelnianie o bledy, naprawa scierzek, wyswietlanie css i js
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 2.4.4
 * @copyright chajr/bluetree
 * 
 * @todo przerobic display class tak aby mozna bylo stosowac jako biblioteke niezaleznie
 * 
 */
class display_class {
	/**
	 * przechowuje tablice z tresciami pochodzacymi z glownego layoutu oraz modulow
	 * na koncu sklada tablice i do wlasciwosci zapisuje kompletna tresc
	 * @var array
	 */
	public $DISPLAY = array('core' => '');
	/**
	 * jesli pojawily sie elementy majace zostac zaladowane do blokow, to wlasciwosc dostaje tablice z blokami i ich modulami
	 * @var array tablica z blokami i ich modulami
	 */
	public $block = NULL;
	/**
	 * tablica js i powiazanych z nimi modulow do zaladowania
	 * @var array
	 */
	private $js = array();
	/**
	 * tablica css i powiazanych z nimi modulow do zaladowania
	 * @var array
	 */
	private $css = array();
	/**
	 * opcje frameworka
	 * @var array
	 */
	private $options;
	/**
	 * kod wybranego jezyka
	 * @var string
	 */
	private $lang;
	/**
	 * obiekt get
	 * @var object
	 */
	private $get;
	/**
	 * obiekt sesji
	 * @var object
	 */
	private $session;
	/**
	 * wyrazenie regularne odpowiadajace wszystkim znacznikom
	 * @var string
	 * @access private
    */
	private $tagi_tresci = "{;[\\w=\-|&();\/,]+;}";
	/**
	 * laduje glowny layout oraz powiazane z nim pliki zewnetrzne
	 * naprawia sciarzki, oraz konwertuje znaczniki sciarzek wg ustawionych opcji
	 * @param string $layout nazwa glownego layoutu do zaladowania, NULL jesli renderuje css/js
	 * @param object $get obiekt get
	 * @param object $session obiekt sesji
	 * @param string $lang kod jezyka jaki zostal ustawiony we frameworku
	 * @param array $css tablica css-ow do zaladowania
	 * @param array $js tablica js-ow do zaladowania
	 * @param array $options opcjonalnie tablica opcji
	 * @uses display_class::$options
	 * @uses display_class::$get
	 * @uses display_class::$session
	 * @uses display_class::$DISPLAY
	 * @uses display_class::$lang
	 * @uses dispaly_class::external()
	 * @uses starter_class::load()
	 * @uses display_class::layout()
	 * @uses get::typ()
	 * @uses core_class::options()
	 */
	public function __construct($layout, $get, $session, $lang, $css, $js, $options = NULL){
		$this->lang = $lang;
		$this->css = $css;
		$this->js = $js;
		$this->get = $get;
		$this->session = $session;
		if($options){
			$this->options = $options;
		}else{
			$this->options = core_class::options();
		}
		if($this->get){
			$typ = $this->get->typ();
		}else{
			$typ = NULL;
		}
		switch($typ){
			case'css':
			case'js':
				$this->DISPLAY['core'] = '{;css_js;}';
				break;
			default:
				$this->layout($layout);
               	break;
		}
		$this->external();
	}
	/**
	 * umozliwia zaladowanie i wyrenderowanie w szablonie zawartosci css i js
	 * @uses display_class::$get
	 * @uses get::typ()
	 * @uses display_class::read()
	 * @uses display_class::generate()
	 * @todo caschowanie js i css, jesli istnieje plik gdzie link = md5(link), to pobiera go, lub twozy
	 */
	public function other(){
		$content = '';
		if($this->get->typ() == 'css'){
			header('content-type: text/css');
		}elseif($this->get->typ() == 'js'){
			header("content-type: text/javascript");
		}
		foreach($this->get as $mod => $param){
			if(is_array($param)){
				foreach($param as $val){
					$content .= $this->read($mod, $val, $this->get->typ());
				}
			}else{
				$content .= $this->read($mod, $param, $this->get->typ());
			}
		}
		$this->generate('css_js', $content);
	}
	/**
	 * umozliwia zastapienie znacznika trescia w danym module, lub grupy znacznikow tablica (gdzie klucz tablicy == znacznik)
	 * @param mixed $znacznik nazwa znacznika do zastapienia, lub tablica znacznik => wartosc
	 * @param string $tresc tresc do zastapienia znacznika, lub pusty kiedy przekazywany array
	 * @param string $modul nazwa modulu zglaszajacego generowanie (core domyslnie)
	 * @return integer zwraca ilosc zastapionych znacznikow, lub NULL jesli nie znaleziono elementow
	 * @example generate('znacznik', 'jakas tres do wyswietlenia')
	 * @example generate('znacznik', 'jakas tres do wyswietlenia', 'modul')
	 * @example generate(array('znacznik1' => 'tresc', 'znacznik2' => 'inna tresc'), '')
	 * @uses display_class::$DISPLAY
	 */
	public function generate($znacznik, $tresc, $modul = 'core'){
		if(isset($this->DISPLAY[$modul])){
			$int = 0;
			if(!$tresc && is_array($znacznik)){
				foreach($znacznik as $element => $tresc){
					$this->DISPLAY[$modul] = str_replace('{;'.$element.';}', $tresc, $this->DISPLAY[$modul], $int2);
					$int += $int2;
				}
			}else{
				$this->DISPLAY[$modul] = str_replace('{;'.$znacznik.';}', $tresc, $this->DISPLAY[$modul], $int);
			}
			return $int;
		}
		return NULL;
	}
	/**
	 * przetwarza tablice i generuje na jej podstawie odpowiednia tresc
	 * @param string $znacznik znacznik do zastapienia
	 * @param array $tablica dane do zapisania
	 * @param string $modul opcjonalnie modul ktory zglasza tresc, inaczej zastepuje w szablonie glownym
	 * @return integer zwraca ilosc zastapionych znacznikow, lub NULL jesli nie znaleziono elementow
	 * @uses display_class::$DISPLAY
	 * @example loop('jakis_znacznik', array(array(key => val), array(key2 => val2)), 'mod');
	 * @example loop('jakis_znacznik', array(array(key => val), array(key2 => val2)));
	 */
	public function loop($znacznik, $tablica, $modul = NULL){
		if(!$modul){
			$modul = 'core';
		}
		$int = NULL;
		if($tablica){
			$start = '{;start;'.$znacznik.';}';
			$end = '{;end;'.$znacznik.';}';
			$poz1 = strpos($this->DISPLAY[$modul], $start);
			$poz1 = $poz1 + mb_strlen($start);
			$poz2 = strpos($this->DISPLAY[$modul], $end);
			$poz2 = $poz2 - $poz1;
			if($poz2 < 0){
				return;
			}
			$szablon = substr($this->DISPLAY[$modul], $poz1, $poz2);
			$end = '';
			$tmp = '';
			$int = 0;
			foreach($tablica as $wiersz){
				$tmp = $szablon;
				foreach($wiersz as $klucz => $wartosc){
					$wzor = '{;'.$znacznik.';'.$klucz.';}';
					$tmp = str_replace($wzor, $wartosc, $tmp);
				}
				$end .= $tmp;
			}
			$this->DISPLAY[$modul] = str_replace($szablon, $end, $this->DISPLAY[$modul], $int2);
			$int += $int2;
			unset($end);
			unset($szablon);
			unset($tablica);
		}
		return $int;
	}
	/**
	 * laduje do tablicy informacje o css-ach o js-ach do zaladowania z modulow
	 * @param string $mod nazwa modulu
	 * @param string $name nazwa pliku css/js
	 * @param string $type typ pliku (css lub js)
	 * @param string $external czy plik pochodzi z zewnetrznego zrodla, czy z frameworka
	 * @param string $media typ plik css (np. print, mobile)
	 * @uses display_class::$js
	 * @uses dispaly_class::$css
	 */
	public function set($mod, $name, $type, $external, $media){
		if($type == 'js'){
			if($media){
				$this->js[$external][$mod]['media'][$media][$name] = $name;
			}else{
				$this->js[$external][$mod][$name] = $name;
			}
		}elseif($type == 'css'){
			if($media){
				$this->css[$external][$mod]['media'][$media][$name] = $name;
			}else{
				$this->css[$external][$mod][$name] = $name;
			}
		}
	}
	/**
	 * scala tresci zawarte w grupach modulow w kompletna strone, zastepuje sciezki,
	 * naprawia linki, czysci i kompresuje, jesli debug wylaczony usuwa wszystko co zostalo wyswietlone
	 * @return string kompletna zawartosc strony do wyswietlenia
	 * @uses display_class::$DISPLAY
	 * @uses display_class::$block
	 * @uses display_class::$options
	 * @uses display_class::path()
	 * @uses display_class::clean()
	 * @uses display_class::compress()
	 * @uses display_class::link()
	 */
	public function render(){
		$blocks = array();
		foreach($this->DISPLAY as $key => $val){
			if($key == 'core'){
				continue;
			}
			if($this->block){
				if(isset($this->block[$key])){
					if(!isset($blocks[$this->block[$key]])){
						$blocks[$this->block[$key]] = '';
					}
					$blocks[$this->block[$key]] .= $val;
				}
			}
			$this->DISPLAY['core'] = str_replace('{;mod;'.$key.';}', $val, $this->DISPLAY['core']);
			unset($this->DISPLAY[$key]);
		}
		foreach($blocks as $block_name => $block_content){
			$this->DISPLAY['core'] = str_replace('{;block;'.$block_name.';}', $block_content, $this->DISPLAY['core']);
		}
		$this->link('css');
		$this->link('js');
		$this->session(1);
		$this->session(0);
		$this->DISPLAY = $this->DISPLAY['core'];
		$this->path();
		$this->clean();
		$this->compress();
		if(!(bool)$this->options['debug']){
			ob_clean();
		}
		return $this->DISPLAY;
	}
	/**
	 * umozliwia zaladowanie layoutu do tablicy DISPLAY (glowny, badz layouty dla modulow)
	 * @param string $layout nazwa layoutu do zaladowania
	 * @param string $mod nazwa moulu dla ktorego ladowany layout (jesli FALSE ladowany layout dla core)
	 * @uses display_class::$DISPLAY
	 * @uses starter_class::load()
	 * @example layout('nazwa_layoutu')
	 * @example layout('nazwa_layoutu', 'mod')
	 * @throws coreException core_error_2
	 */
	public function layout($layout, $mod = FALSE){
		if(!$mod){
			$path = "elements/layouts/$layout.html";
			$mod = 'core';
		}else{
			$path = "modules/$mod/layouts/$layout.html";
		}
		$this->DISPLAY[$mod] = starter_class::load($path, TRUE);
		if(!$this->DISPLAY[$mod]){
			throw new coreException('core_error_2', $mod.' - '.$path);
		}
	}
	/**
	 * uzupelnia znaczniki session o informacje pochodzace z sesji
	 * @param boolean $type typ tablicy do przetworzenia (core, lub public. core inf pochodzace z frameworka)
	 * @uses display_class::$session
	 * @uses display_class::generate()
	 * @uses session::returns()
	 */
	private function session($type){
		if($this->session){
			if($type){
				$type = 'core';
				$array = $this->session->returns('display');
			}else{
				$type = 'public';
				$array = $this->session->returns('public');
			}
			if($array){
				foreach($array as $key => $val){
					$this->generate('session_'.$type.';'.$key.';', $val);
				}
			}
		}
	}
	/**
	 * twozy linki do css/ja z podanych plikow w listach
	 * @param string $type typ linku do utwozenia (css lub js)
	 * @uses display_class::$css
	 * @uses display_class::$js
	 * @uses display_class::generate()
	 * @todo sprawdzic jakie sa jeszcze typy w css
	 */
	private function link($type){
		$links = '';
		$internal = '';
		switch($type){
			case'css':
				$front = '<link href="';
				$end = '" rel="stylesheet" type="text/css"/>';
				$arr = $this->css;
				break;
			case'js':
				$front = '<script src="';
				$end = '" type="text/javascript"></script>';
				$arr = $this->js;
				break;
		}
		if(!empty($arr['external'])){
			foreach($arr['external'] as $mod){
               	foreach($mod as $val){
					if(is_array($val)){
						foreach($val as $key => $media){
							foreach($media as $file){
								$links .= "\t\t".$front.$file.'" media="'.$key.$end."\n";
							}
                       	}
					}else{
						$links .= "\t\t".$front.$val.$end."\n";
					}
				}
           	}
			unset($arr['external']);
		}
		if(!empty($arr['internal'])){
           	$path = '{;core;domain;}{;core;lang;}{;path;core_'.$type.'/';
			$endpath = ';}';
			$media = '';
			$internal = '';
			$int_media = '';
           	foreach($arr['internal'] as $mod => $values){
				if(isset($values['media'])){
					foreach($values['media'] as $key => $elements){
						foreach($elements as $file){
							$int_media .= $mod.','.$file.'/';
						}
					}
					$media .= "\t\t".$front.$path.$int_media.$endpath.'" media="'.$key.$end."\n";
					unset($values['media']);
				}
				foreach($values as $file){
					$internal .= $mod.','.$file.'/';
				}
			}
			if($internal){
				$links .= "\t\t".$front.$path.$internal.$endpath.$end."\n";
			}
			if($media){
				$links .= $media;
			}
		}
		$this->generate('core;'.$type, $links);
	}
	/**
	 * odczyt zawartosci pliku css/js
	 * @param string $mod nazwa modulu zglaszajacego zadanie do css/js
	 * @param string $param nazwa pliku do odczytania
	 * @param string $type typ pliku (css lub js)
	 * @return string zwraca zawartosc pliku, lub pusty string
	 * @uses starter_class::load()
	 */
	private function read($mod, $param, $type){
		if($mod == 'core'){
			$main = 'elements/'.$type.'/';
		}else{
			$main = 'modules/'.$mod.'/elements/'.$type.'/';
		}
		$data = starter_class::load($main.$param.'.'.$type, TRUE);
		if($data){
			$content = $data."\n";
			return $content;
		}
		return '';
	}
	/**
	 * laduje dodatkowe szablony do glownego layoutu, badz dodatkowe elementy dla layoutu danego modulu
	 * @param string $modul opcjonalnie nazwa modulu ktory zglasza ladowanie dodatkowych szablonow
	 * @uses display_class::$DISPLAY
	 * @uses starter_class::load()
	 */
	private function external($modul = NULL){
		$tab = array();
		if(!$modul){
			$path = 'elements/layouts/';
			$modul = 'core';
		}else{
			$path = 'modules/'.$modul.'/layout/';
		}
		preg_match_all('#{;external;([\\w-])+;}#', $this->DISPLAY[$modul], $tab);
		foreach ($tab[0] as $element){
			$nazwa = str_replace(
				array(
					'{;external;', 
					';}'
				), '', $element
			);
			$tresc = starter_class::load($path.$nazwa.'.html', TRUE);
			if(!$tresc){
				echo 'core_error_3 '.$path.$nazwa.'.html';
			}
			$this->DISPLAY[$modul] = str_replace($element, $tresc, $this->DISPLAY[$modul]);
		}
	}
	/**
	 * sprawdzanie scierzek czy zgloszone przy bledzie, czy przy normalnej stronie
	 * @uses display_class::$get
	 * @uses get::path()
	 * @uses get::real_path()
	 * @return array tablica scierzek naprawczych
	 */
	private function chk_path(){
		$path = array();
		if($this->get){
			$path[0] = $this->get->path();
			$path[1] = $this->get->path(1);
		}else{
			$path[0] = $path[1] = get::real_path($this->options['test']);
		}
		return $path;
	}
	/**
	 * zastepuje znaczniki sciezek odpowiednimi danymi
	 * @example {;core;domain;} - zwraca protokol, domene i folder testowy
	 * @example {;core;lang;} - zwraca kod jezyka jesli obsluga jezykow wlaczona
	 * @example {;path;jakas sciezka;} - zwraca skonwertowana sciezke (bez jezyka i domeny)
	 * @example {;full;jakas sciezka;} - zwraca pelna sciezke wraz z domena i jezykiem
	 * @example {;rel;jakas sciezka;} - zwraca aktualna sciezke i dopisuje do niej podana sciezke
	 * @uses display_class::$DISPLAY
	 * @uses display_class::$options
	 * @uses display_class::$lang
	 * @uses display_class::$get
	 * @uses display_class::separator()
	 * @uses display_class::convert()
	 * @uses display_class::chk_path()
	 * @uses get::path()
	 */
	private function path(){
		$path = $this->chk_path();
		$this->DISPLAY = preg_replace('#{;core;domain;}#', $path[0], $this->DISPLAY);
		if(!$this->options['rewrite']){
			$lang = '?core_lang='.$this->lang.$this->separator();
		}else{
			$lang = $this->lang.'/';
		}
		$this->DISPLAY = preg_replace('#{;core;lang;}#', $lang, $this->DISPLAY);
		$this->DISPLAY = preg_replace('#{;core;mainpath;}#', $path[1], $this->DISPLAY);
		preg_match_all('#{;path;[\\w-/'.$this->options['zmienne_rewrite_sep'].']+;}#', $this->DISPLAY, $tab);
		$this->convert($tab, 'path');
		preg_match_all('#{;full;[\\w-/'.$this->options['zmienne_rewrite_sep'].']+;}#', $this->DISPLAY, $tab);
		$this->convert($tab, 'full');
		preg_match_all('#{;rel;[\\w-/'.$this->options['zmienne_rewrite_sep'].']+;}#', $this->DISPLAY, $tab);
		$this->convert($tab, 'rel');
	}
	/**
	 * konwertuje tablice znacznikow scierzek w poprawne zciezki URI
	 * @param array $tab tablica znacznikow
	 * @param string $type typ sciezki do przekonwertowania
	 * @uses display_class::$get
	 * @uses display_class::$lang
	 * @uses display_class::$options
	 * @uses display_class::$DISPLAY
	 * @uses display_class::convert_rewrite()
	 * @uses display_class::convert_classic()
	 * @uses display_class::chk_path()
	 * @uses display_class::separator()
	 * @uses get::path()
	 */
	private function convert($tab, $type){
		if($tab){
			$path = $this->chk_path();
			switch($type){
				case'path':
					$update = '';
					break;
				case'full':
					$update = $path[0];
					if($this->lang){
						if($this->options['rewrite']){
							$update .= $this->lang.'/';
						}else{
							$update .= '?core_lang='.$this->lang.$this->separator();
						}
					}else{
						if(!$this->options['rewrite']){
							$update .= '?';
						}
					}
					break;
				case'rel':
					$update = $path[1];
					if(!$this->options['rewrite']){
						$update .= '?';
					}
					break;
			}
			foreach($tab[0] as $link){
				$path = str_replace(
					array(
						'{;'.$type.';',
						';}'
					), '', $link);
				$path = explode('/', $path);
				$pages = array();
				$params = array();
				foreach($path as $value){
					if(preg_match('#['.$this->options['zmienne_rewrite_sep'].']{1}#', $value)){
						$params[] = $value;
					}elseif($value){
						$pages[] = $value;
					}
				}
				if((bool)$this->options['rewrite']){
					$final = self::convert_rewrite($params, $pages);
				}else{
					$final = self::convert_classic($params, $pages, $this->separator());
				}
				if($update){
					$final = $update.$final;
				}
				$this->DISPLAY = str_replace($link, $final, $this->DISPLAY);
			}
		}
	}
	/**
	 * tworzy separator z ampersand dla js lub xhtml strict
	 * @return string skonwertowany separator
	 * @uses display_class::js
	 */
	private function separator(){
		if(!empty($this->js)){
			$separator = '&';
		}else{
			$separator = '&amp;';
		}
		return $separator;
	}
	/**
	 * oczyszcza layout z niewykozystanych znacznikow
	 * @uses display_class::$DISPLAY
	 * @uses display_class::clean_chk()
	 * @uses display_class::$tagi_tresci
	 */
	private function clean(){
		$this->clean_chk('opt');
		$this->clean_chk('petla');
		$this->DISPLAY = preg_replace('#'.$this->tagi_tresci.'#', '', $this->DISPLAY);
	}
	/**
	 * oczyszcza layout z petli w ktorych znajduja sie nieobsluzone znaczniki,
	 * badz niewykozystane znaczniki opcjonalne
	 * @param string $typ typ do sprawdzenia
	 * @uses display_class::$tagi_tresc
	 * @uses display_class::$DISPLAY
	 */
	private function clean_chk($typ){
		switch($typ){
			case'petla':
				$reg1 = '#{;(start|end);([\\w-])+;}#';
				$reg2 = '#{;([\\w-])+;([\\w-])+;}#';
				$reg3 = '{;start;';
				$reg4 = '{;end;';
				break;
			case'opt':
				$reg1 = '#{;op;([\\w-])+;}#';
				$reg2 = $this->tagi_tresci;
				$reg3 = '{;op;';
				$reg4 = '{;op_end;';
				break;
			default:
				return;
				break;
		}
		$bool = preg_match_all($reg1, $this->DISPLAY, $tab);
       	if(!empty($tab) && !empty($tab[0])){
			foreach($tab[0] as $znacznik){
               	$start = strpos($this->DISPLAY, $znacznik);
				$znacznik_end = str_replace($reg3, $reg4, $znacznik);
				$end = strpos($this->DISPLAY, $znacznik_end);
				if(!$start || !$end){
					continue;
				}
              	$start_content = $start + mb_strlen($znacznik);
				$content_len = $end - $start_content;
				$string = substr($this->DISPLAY, $start_content, $content_len);
               	$len = ($end += mb_strlen($znacznik_end)) - $start;
				$string_del = substr($this->DISPLAY, $start, $len);
               	$bool = preg_match($reg2, $string);
				if($bool){
                   	$this->DISPLAY = str_replace($string_del, '', $this->DISPLAY);
				}else{
					$this->DISPLAY = str_replace($string_del, $string, $this->DISPLAY);
				}
           	}
		}
	}
	/**
	 * kompresuje zawartosc strony zgodnie z poziomem ustawionej kompresji
	 * @uses display_class::$options
	 * @uses display_class::$DISPLAY
	 */
	private function compress(){
		if((bool)$this->options['compress']){
			header('Content-encoding: gzip');
			$this->DISPLAY = gzcompress($this->DISPLAY, $this->options['compress']);
		}
	}
	/**
	 * konwertuje znacznik na standardowa scierzke
	 * @param array $params tablica parametrow
	 * @param array $pages tablica stron
	 * @return string skonwertowany na sciezke znacznik
	 * @static
	 */
	static function convert_classic($params, $pages, $separator = '&'){
		$licznik = 0;
		$final = '';
		foreach($pages as $page){
			$final .= 'p'.$licznik.'='.$page.$separator;
			$licznik++;
		}
		foreach ($params as $param){
			$param = str_replace(',', '=', $param);
			$final .= $param.$separator;
		}
		$final = rtrim($final, '&amp;');
		$final = rtrim($final, '&');
		return $final;
	}
	/**
	 * konwertuje znacznik wedlug mode_rewrite
	 * @param array $params tablica parametrow
	 * @param array $pages tablica stron
	 * @return string skonwertowany na sciezke znacznik
	 * @static
	 */
	static function convert_rewrite($params, $pages){
		$final = '';
		foreach($pages as $page){
			$final .= $page.'/';
		}
		foreach ($params as $param){
			$final .= $param.'/';
		}
		$final = rtrim($final, ',');
		return $final;
	}
	/**
	 * przetwarza przekazana tablice get i wydziala z niej scierzki i parametry
	 * @param array $path tablica elementow get do sprawdzenia
	 * @param string $separator znak odzielajacy nazwe zmiennej od jej wartosci
	 * @return array tablica stron i parametrow
	 */
	static function explode_url($path, $separator){
		$pages = array();
		$params = array();
		foreach($path as $value){
			if(preg_match('#['.$separator.']{1}#', $value)){
				$params[] = $value;
			}elseif($value){
				$pages[] = $value;
			}
		}
		return array('pages' => $pages, 'params' => $params);
	}
}
?>