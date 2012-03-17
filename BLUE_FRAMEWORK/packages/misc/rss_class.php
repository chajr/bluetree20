<?PHP
class rss extends DOMDocument{
	
	private $chanel = NULL;
	
	//konstruktor, powoluje dom xml
	public function __construct($copy, $pubdate, $managingEditor, $webMaster, $ttl){
		//wywolanie konstruktora dom
		parent::__construct('1.0', 'UTF-8');
		//foramtowanie pliku wynikowego
		$this->formatOutput = TRUE;
		//tworzenie roota
		$root = $this->createElement('rss');
		$root = $this->appendChild($root);
		$root->setAttribute('version', '2.0');
		//tworzenie elementu kanal
		$chanel = $this->createElement('channel');
		$this->chanel = $root->appendChild($chanel);
		
		//tworzy podelementy z glownymi danymi rss
		$prawa = $this->createElement('copyright', $copy);
		$data = $this->createElement('pubDate', $pubdate);
		$mail_strona = $this->createElement('managingEditor', $managingEditor);
		$mail_admin = $this->createElement('webMaster', $webMaster);
		$czas_zycia = $this->createElement('ttl', $ttl);
		//zaladowanie elementow
		//$this->chanel->appendChild($prawa);
		$this->chanel->appendChild($data);
		$this->chanel->appendChild($mail_strona);
		$this->chanel->appendChild($mail_admin);
		$this->chanel->appendChild($czas_zycia);
	}
	
	//tworzenie glownego elementu z tresci
	public function main($tytul, $link, $opis){
		//konwersja znakow
		$tytul = $this->konwersja($tytul);
		$opis = $this->konwersja($opis);
		//tworzenie nadrzednego elementu z tresciami
		$tytul = $this->createElement('title', $tytul);
		$link = $this->createElement('link', $link);
		$opis = $this->createElement('description', $opis);
		//zaladowanie elementow
		$this->chanel->appendChild($tytul);
		$this->chanel->appendChild($link);
		$this->chanel->appendChild($opis);
	}
	
	//tworzenie pod elementow
	public function dodaj($tytul, $link, $opis){
		//konwersja znakow
		$tytul = $this->konwersja($tytul);
		$opis = $this->konwersja($opis);
		//tworzenie elementu podrzednego
		$item = $this->createElement('item');
		//tworzenie elementu z tresciami
		$tytul = $this->createElement('title', $tytul);
		$link = $this->createElement('link', $link);
		$opis = $this->createElement('description', $opis);
		//zaladowanie elementow
		$item->appendChild($tytul);
		$item->appendChild($link);
		$item->appendChild($opis);
		
		$this->chanel->appendChild($item);
	}
	
	//konwersja znakow
	private function konwersja($tresc){
		$tresc = htmlspecialchars($tresc);
		return $tresc;
	}
	
	//do zwracania wartosci sluzy funkcja z klasy nadrzednej
	//saveXML();
	
}
?>