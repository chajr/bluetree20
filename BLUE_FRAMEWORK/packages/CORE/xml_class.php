<?PHP
/**
 * klasa do odczytu/zapisu opcji konfiguracyjnych
 * @author chajr <chajr@bluetree.pl>
 * @version 1.3
 * @access public
 * @copyright chajr/bluetree
 * @package core
 * @final
*/
final class xml_class extends DOMDocument{
	/**
	 * Root dokumentu
	 * @var xmlobject
	 */
	public $documentElement;
	/**
	 * nazwa wezla
	 * @var string
	 */
	public $nodeName;
	/**
	 * typ wezla
	 * ELEMENT_NODE					(1) element
	 * ATTRIBUTE_NODE				(2) atrybut
	 * TEXT_NODE					(3) wezel tekstowy (elementu lub atrybutu)
	 * CDATA_SECTION_NODE			(4) sekcja CDATA
	 * ENTITY_REFERENCE_NODE		(5) referencja do encji
	 * ENTITY_NODE					(6) encja
	 * PROCESSING_INSTRUCTION_NODE	(7) instrukcja sterujaca
	 * COMMENT_NODE					(8) komentaz
	 * DOCUMENT_NODE				(9) dokument (caly dokument xml, glowny element drzewa DOM)
	 * @var integer
	 */
	public $nodeType;
	/**
	 * wartosc wezla
	 * @var mixed
	 */
	public $nodeValue;
	/**
	 * wezel nadrzedny
	 * @var xmlobject
	 */
	public $parentNode;
	/**
	 * zbior wezlow podrzednych
	 * @var xmlobject
	 */
	public $childNodes;
	/**
	 * pierwszy wezel podrzedny
	 * @var xmlobject
	 */
	public $firstChild;
	/**
	 * ostatni wezel podrzedny
	 * @var xmlobject
	 */
	public $lastChild;
	/**
	 * zbior atrybutow
	 * @var xmlobject
	 */
	public $attributes;
	/**
	 * nastepny wezel w kolekcji
	 * @var xmlobject
	 */
	public $nextSibling;
	/**
	 * poprzedni wezel w kolekcji
	 * @var xmlobject
	 */
	public $previousSibling;
	/**
	 * przestrzen nazw biezacego wezla
	 */
	public $namespaceURI;
	/**
	 * obiekt dokumentu wezla referencyjnego
	 * @var xmlobject
	 */
	public $ownerDocument;
	/**
	 * liczba elementow w kolekcji
	 * @var integer
	 */
	public $length;
	/**
	 * DTD, jesli jest zwraca obiekt typu documentType
	 * @var xmlobject
	 */
	public $doctype;
	/**
	 * sposob implementacji zawartosci dokumentu, zgodny z mime-type dokumentu
	 */
	public $implementation;
	/**
	 * informacja o ewentualnym bledzie
	 * @var string
	 * @access public
    */
   public $err = NULL;
   /**
	 * ostatnie wolne id
	 * @var string
	 * @access public
    */
   public $id_list;
   /**
	* uruchamia konstruktor DOMDocument, opcjonalnie tworzac nowy xml
    * @param float $version wersja xml-a
    * @param string $encoding kodowanie xml-a
    * @uses DOMDocument::__construct()
    */
   public function __construct($version = '', $encoding = ''){
	   parent::__construct($version, $encoding);
   }
   /**
	* wczytuje plik xml, opcjonalnie sprawdza
    * @example wczytaj ('cfg/config.xml', 1)
	* @final
	* @param string $url scierzka do pliku
    * @param boolean $parse jesli true ma sprawdzic poprawnosc pliku pod wzgledem DTD
	* @return boolean informacja czy zaladowano plik czy nie
    * @uses DOMDocument::$preserveWhiteSpace
    * @uses xml_class::$err
    * @uses DOMDocument::load()
    * @uses DOMDocument::validate()
    */
   public final function wczytaj($url, $parse = FALSE){
	   $this->preserveWhiteSpace = FALSE;
		$bool = @file_exists($url);
		if(!$bool){
			$this->err = 'Plik nie istnieje';
			return FALSE;
		}
		$bool = $this->load($url);
		if(!$bool){
			$this->err = 'Błąd podczas ładowania pliku';
			return FALSE;
		}
		if($parse && !@$this->validate()){
			$this->err = 'Błąd parsowania pliku XML';
			return FALSE;
		}
		return TRUE;
   }
    /**
	* zapisuje plik xml, opcjonalnie zwraca go jako xml do wykozystania
	* @final
	* @example zapisz('sciezka/plik.xml'); zapis do pliku
	* @example zapisz(0, 1) zwraca jako tekst
	* @param string $url scierzka do pliku
    * @param boolean $as_text czy ma zwrucic tresc xml-a
	* @return mixed informacja czy zapisano plik czy nie, lub gotowy xml
    * @uses DOMDocument::$formatOutput
    * @uses xml_class::$err
    * @uses DOMDocument::save()
    * @uses DOMDocument::saveXML()
    */
   public final function zapisz($url, $as_text = FALSE){
		$this->formatOutput = TRUE;
		if($url){
			$bool = $this->save($url);
			if(!$bool){
				$this->err = 'Bład podczas zapisu do pliku';
				return FALSE;
			}
		}
		if($as_text){
			$data = $this->saveXML();
			return $data;
		}
		return TRUE;
   }
   /**
	* generuje wolny identyfikator numeryczny
	* @final
	* @param string $url scierzka do pliku
    * @param boolean $as_text czy ma zwrucic tresc xml-a
	* @return integer zwraca id, lub 0 jesli nie odanleziono wezlow
    * @uses DOMDocument::$documentElement
    * @uses xml_class::$id_list
    * @uses xml_class::szukaj()
    */
   public final function free_id(){
		$root = $this->documentElement;
		if(!$root->hasChildNodes()){
			return 0;
		}else{
			$tab = array();
			$tab = $this->szukaj($root->childNodes, $tab, 'id');
			$tab[] = 'tworzy_nowe_wolne_id';
			$id = array_keys($tab, 'tworzy_nowe_wolne_id');
			unset($tab);
			$this->id_list = $id;
			return $id[0];
		}
   }
    /**
	* przeszukuje wezel w poszukiwaniu elementow o podanym atrybucie
	* @param xml_object $tab xml do przeszukania
    * @param array $val tablica
	* @param string $name nazwa atrybutu do sprawdzenia
	* @return array zwraca tablice
    */
   private function szukaj($tab, $val, $name){
		foreach($tab as $child){
			if($child->nodeType == 1){
				if($child->hasChildNodes()){
					$val = $this->szukaj($child->childNodes, $val, $name);
				}
				$id = $child->getAttribute($name);
				if($id){
					$val[$id] = $id;
				}
			}
		}
		return $val;
   }
    /**
	* przeszukuje wezel w poszukiwaniu elementow o podanym atrybucie
	* @param string $id id do wyszukania
    * @uses xml_class::getElementById()
	* @return boolean zwraca inf czy podany element istnieje
    */
   public final function sprawdz_id($id){
		$id = $this->getElementById($id);
		if($id){
			return TRUE;
		}else{
			return FALSE;
		}
   }
    /**
	* krutszy zapis zwracania elementu po id
	* @param string $id id do wyszukania
	* @return xml_object zwraca obiekt xml o podanym id
	* @uses xml_class::getElementById()
    */
	public function get_id($id){
		$id = $this->getElementById($id);
		return $id;
	}
}
?>