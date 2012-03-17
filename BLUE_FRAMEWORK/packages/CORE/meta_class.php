<?PHP
/**
 * @author chajr <chajr@bluetree.pl>
 * @version 1.2
 * @access private
 * @copyright chajr/bluetree
 * @package core
 * @todo obsluga meta charset z html-a 5 <meta charset="utf-8"> i innych niestandardowych metatagow
*/
/**
 * klasa obslugi metatagow i tytulu strony
 */
class meta_class {
	 /**
	 * przechowuje informacje czy wlaczona
	 * @var boolean
	 * @access private
    */
	 private $on = TRUE;
	  /**
	 * tablica metatagow do zlozenia
	 * @var array
	 * @access private
    */
	 private $meta = array('title' => '');
	  /**
	 * zlozone i kompletne metatagi
	 * @var string
	 * @access private
    */
	 private $compleat_meta = '';
	 /**
	  * odczytuje xml-a, sprawdza czy wlaczona obsluga metatagow, ustawia tablice z odpowiednimi wartosciami
	  * @uses meta_class::$on
	  * @uses meta_class::read()
	  * @uses xml_class::wczytaj()
	  * @uses xml_class::get_id()
	  * @uses xml_class::__construct()
	  * @uses starter_class::path()
	  * @uses core_class::options()
	  * @param array $get tablica odpalonych stron
	  * @throws coreException core_error_20, core_error_18
	 */
	 public function __construct($get){
		 if(!(bool)core_class::options('meta')){
			 $this->on = FALSE;
		 }else{
			$xml = new xml_class();
			$bool = $xml->wczytaj(starter_class::path('cfg').'meta.xml', TRUE);
			if(!$bool){
				throw new coreException('core_error_20', starter_class::path('cfg').'meta.xml');
			}
			$index = $xml->get_id('index');
			if(!$index){
				throw new coreException('core_error_18');
			}
			$this->read($index);
			if(!empty($get)){
				foreach($get as $page){
					$element = $xml->get_id($page);
					if($element){
						$this->read($element);
					}
				}
			}
		}
	}
	 /**
	  * dodaje nowy znacznik meta do listy
	  * dodaje do tresci z metatagami kompletny metatag
	  * @example add('<meta content="bluetree.pl powered by blueFramework 2.0" name="author"/>')
	  * @uses meta_class::$on
	  * @uses meta_class::$compleat_meta
	  * @param string $mata kompletny metatag
	 */
	public function add($meta){
		if($this->on){
			$this->compleat_meta .= "\t\t$meta\n";
		}
	}
	 /**
	  * uzupelnia odpowiedni znacznik o tresc
	  * sprawdza czy metatag istnieje w pamieci i dopisuje do niego tresc
	  * @example insert('keywords', ', slowo 1, slowo 2') - dodaje slowa kluczowe do metakeywords
	  * @uses meta_class::$on
	  * @uses meta_class::$meta
	  * @param string $typ poszukiwany atrybut
	  * @param string $tresc dane do dopisania
	 */
	public function insert($typ, $tresc){
		if($this->on && isset($this->meta[$typ])){
			$this->meta[$typ] .= ' '.$tresc;
		}
	}
	 /**
	  * sklada kompletny metatag i zapisuje go w szablonie glownym
	  * @uses meta_class::$on
	  * @uses meta_class::$meta
	  * @uses meta_class::$compleat_meta
	  * @uses dispaly_class::generate()
	  * @param object $dispaly obiekt display
	 */
	public function render(display_class $display){
		if($this->on){
			$bufor = '';
			foreach($this->meta as $key => $val){
				if($key == 'title'){
					continue;
				}else{
					$bufor .= "\t\t".'<meta content="'.$val.'" name="'.$key.'"/>'."\n";
				}
			}
			$bufor .= "\t\t".'<title>'.$this->meta['title'].'</title>'."\n";
			$display->generate('core;meta', $bufor.$this->compleat_meta);
		}
	}
	/**
	 * przetwarza pobrany element z listy metatagow
	 * przetwarza najpierw element tytul, a nastepnie reszte metatagow
	 * @uses meta_class::$meta
	 * @uses xml_class::$firstChild
	 * @uses xml_class::getAttribute()
	 * @uses xml_class::getElementsByTagName()
	 * @param xml_object $element znaczniki xml do przetworzenia
	 */
	private function read($element){
		$update = $element->firstChild->getAttribute('update');
		if((bool)$update && isset($this->meta['title'])){
			$this->meta['title'] .= $element->firstChild->getAttribute('tytul');
		}else{
			$this->meta['title'] = $element->firstChild->getAttribute('tytul');
		}
		$meta = $element->getElementsByTagName('meta');
		foreach($meta as $element){
			if($element->getAttribute('name')){
				$key = $element->getAttribute('name');
			}elseif($element->getAttribute('http-equiv')){
				$key = $element->getAttribute('http-equiv');
			}
			$update = $element->getAttribute('update');
			if((bool)$update && isset($this->meta[$key])){
				$this->meta[$key] .= $element->getAttribute('content');
			}else{
				$this->meta[$key] = $element->getAttribute('content');
			}
		}
	}
}
?>