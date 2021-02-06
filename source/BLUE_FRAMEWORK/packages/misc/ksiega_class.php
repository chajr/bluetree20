<?PHP
class ksiega_gosci{
	
	//tablica zakazanych slow
	private $slownik = array("bcc:", "subject", "cc:", "sex", "http", "pussy", "ringtones", "fuck" ,"fukin", "nudes", "viagra", "phentermine", "tramadol", "url", "www.", "xanax", "cipcia", "cwel", "cycki", "franca", "gówno", "gównian", "ruchać", "wyruchan", "skurwi", "zajeban", "burdel", "kurw", "kurewk", "huj" ,"chuj", "pierdol", "popierdol", "jeban", "pojeban", "cipa", "cipy", "cipk", "pizda", "pieprzy", "piepszy", "popieprz", "kutas", "kutafon", "fiut", "zajebiscie", "zajebiście", "zajebist", "jebać", "dupczy", "jebac", "cwel", "pizdy", "pizda", "pizde");
	public $show = '';
	public $tresc = FALSE;
	public $tytul = FALSE;
	
	
	public function __construct ($parametr){
		if ($parametr === NULL){
		//wczytanie normalnej zawartosci (dodanie wpisu)
		$this->show = "formularz";
		}else{
			//jesli podano parametr wyswietla tresc
			if ($parametr ==='dodaj'){
				//sprawdzeie tytulu
				$tytul = $_POST['tytul'];
				$this->ar($tytul, 'tytul');
				$tytul = $this->strip_tags_content($tytul, '');
				
				$tresc = $_POST['tresc'];
				$this->ar($tresc, 'tresc');
				$tresc = $this->strip_tags_content($tresc, '');
				
				//przygotowanie tresci do formatu xml
				$tytul = htmlspecialchars ($tytul);
				$tresc = htmlspecialchars ($tresc);
				$this->tytul = $tytul;
				$this->tresc = $tresc;
				$this->show = "dodano";
			}else if ($parametr !== 'dodaj' && $parametr !== NULL){
				$dom = new DOMDocument();
				$dom->preserveWhiteSpace = FALSE;
				$result = $dom->load ("xml/ksiega.xml");
				
				$elementy = $dom -> documentElement;
				//poczatkowy element tablicy
				$id = 0;
				$tablica = array();
				foreach ($elementy -> childNodes as $child){
					$tablica[$id]['nazwa'] = $child -> getAttribute('nazwa');
					$tablica[$id]['tresc'] = $child -> getAttribute('tresc');
					$tablica[$id]['data'] = $child -> getAttribute('data');
					$id++;
				}
				//sprawdzanie porawnosci parametru i przygotowanie tresci
				if ($parametr != NULL){
								
					$test = @$tablica[$parametr]['nazwa'];
					if ($test){
						$tresc = '<br /><br /><div class="tytul">';
						$tresc .= '<div class="red">';
						$tresc .= $tablica[$parametr]['data'];
						$tresc .= '</div>';
						$tresc .= $tablica[$parametr]['nazwa'];
						$tresc .= '<br /></div>';
						$tresc .= '<br /><br /><div class="nowosc_tresc">';
						$tresc .= $tablica[$parametr]['tresc'];
						$tresc .= '</div>';
						$this->tresc = $tresc;
					}
				}				
			}
		}
		
		
	}
	
	
	//sprawdzanie tresci
	private function ar ($tresc, $typ){
		if ($typ == 'tytul'){
			$typ = 'podanego tytułu';
			$typ1 = 'podanym tytule';
		}
		if ($typ == 'tresc'){
			$typ = 'podanej treści';
			$typ1 = 'podanej treści';
		}
		if (trim($tresc) === ''){
			throw new Exception ("Brak $typ");
		}
		//przeszukiwanie tablicy
		foreach ($this->slownik as $element){
			$wynik = substr_count ($tresc, $element);
			if ($wynik){
				throw new Exception ("Nidozwolone znaki występują w $typ1");
			}
		}
		
	}
	
	
	
	//funkcja sprawdzajaca znaki i usuwajaca tagi html
	private function strip_tags_content($text, $tags = '', $invert = FALSE) {
		  preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
		  $tags = array_unique($tags[1]);
			
		  if(is_array($tags) && count($tags) > 0) {
			if($invert == FALSE) {
			  return preg_replace('@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
			}else {
			  return preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
			}
		  }elseif($invert == FALSE) {
			return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
		  }
		  return $text;
	}
	
	
}
?>