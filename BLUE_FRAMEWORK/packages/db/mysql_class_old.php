<?PHP
//v2.1.2
class mysql_class extends mysqli{

	public $id;						//przechowuje id ostatnio dodanego elementu
	public $err = NULL;				//przechowuje zakodowany kod bledu z mysql
	public $ilosc_wierszy = 0;

	public final function __construct($config){
		if(isset($config) && !empty($config)){
			parent::__construct($config[0], $config[1], $config[2], $config[3]);	//uruchamia polaczenie
			if (mysqli_connect_error()){
				$this->err = mysqli_connect_error();								//koduje blad i umieszcze w zmiennej
				return FALSE;
			}
			$this->query("SET NAMES 'UTF8'");										//ustawia system na utf-8
		}
	}

	public final function zapytanie ($pyt, $sprawdzanie = FALSE){
		if($sprawdzanie){
			$bool = @preg_match("/DELETE|DROP/i", $pyt);		//sprawdza czy w zapytaniu nie ma slow drop i delete
			if($bool){
				$this->err = 'QUERY_SECURE_ERROR';				//kod bledu jesli pytanie bylo niebezpieczne
				return FALSE;
			}
		}
		$bool = $this->query($pyt);								//wykonuje pytanie
		if (!$bool){
			$this->err = $this->error;							//kodowanie bledu
			return FALSE;
		}
		if(!is_bool($bool) && !is_integer($bool)){
			$this->ilosc_wierszy = $bool->num_rows;				//zapis ilosci zwroconych wierszy
		}
		//$this->id = $this->db->insert_id;
		return $bool;
	}

	public final static function tablica ($dane, $full = FALSE){	//przetwarzanie pobranych danych do tablicy
		if (!isset($dane) || $dane == NULL){
			return FALSE;
		}
		if($full){													//jesli full, zwraca pelna tablice wszystkich wierszy z bazy
			$tab = array();
			while($tablica = $dane->fetch_assoc()){
				if(!$tablica){
					return NULL;								//zwraca null jesli nic nie przetworzylo
				}
				$tab[] = $tablica;								//zwraca pelna liste wszystkich wierszy z bazy
			}
		}else{
			$tab = $dane->fetch_assoc();						//tworzy tablice, lub zwraca null gddy nic nie przetworzylo
		}
		return $tab;
	}

	public final function komplet($pyt, $full = FALSE){			//kompletne przetwarzanie tablicy z pytania
		$wynik = $this->zapytanie($pyt);						//wykonuje zapytanie
		if($wynik){
			$dane = $this->tablica($wynik, $full);				//jesli ok, przetwaraz dane
			return $dane;										//zwraca dane
		}else{
			return FALSE;										//jesli pytanie nieprawidlowe zwraca FALSE
		}
	}

	public final function pytanie_sprawdzajace ($pyt){			//sprawdza czy podany wpis istnieje w bazie danych
		$wynik = $this->zapytanie($pyt);
		if(!$wynik){
			return FALSE;										//zwraca false jesli blad zapytania
		}
		return $this->ilosc_wierszy;							//zwraca ilosc przetworzonych wierszy
	}

	public final function koduj ($tresc){						//kodowanie zmiennych dla umieszczenia w zapytaniu
		$tresc = @$this->real_escape_string ($tresc);			//NUL (ASCII 0), \n, \r, \, ', ", and Control-Z
		return $tresc;
	}

	public final static function koduj_encje ($tresc){			//kodowanie zmiennych w encje
		$tresc = @htmlspecialchars($tresc);						//& ' " < >
		return $tresc;
	}

	public final static function dekoduj ($tresc){				//dekodowanie tresci do wyswietlenia
		$tresc = @stripcslashes($tresc);
		return $tresc;
	}

	/*public final function __destruct (){
		$this->db->close();
	}*/

}
?>