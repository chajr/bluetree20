<?PHP
/**
 * obiekt do prostych manipulacji na datach
 * @author chajr <chajr@bluetree.pl>
 * @version 0.5
 * @access private
 * @copyright chajr/bluetree
*/
class simpleData_class {
	/**
	 * zwraca aktualny czas (data+czas) odpowiednio sformatowany
     * @static
	 * @param string $stamp opcjonalnie unixowy znacznik czasu wg ktorego ma zwrucic formatowanie
	 * @return string zwraca sformatowany ciag z data i czasem
    */
	static function full_time($stamp = NULL){
		$cfg = '%H:%M:%S - %d-%m-%Y';
		if($stamp){
			return strftime($cfg, $stamp);
		}else{
			return strftime($cfg);
		}
	}
	/**
	 * zwraca date w formacie dzien-miesiac-rok, lub dla bazy w formacie rok miesiac dzien
	 * @param boolean $a typ zwracanej daty, jesli true rok-miesiac-dzien, false dzien-miesiac-rok
	 * @return string sformatowana aktualna data
	 */
	public static function data($a = FALSE){
		if($a){
			return strftime("%Y-%m-%d");
		}else{
			return strftime("%d-%m-%Y");
		}
	}
	/**
	 * zwraca czas w formacie godina-minuta-sekunda
	 * @return type 
	 */
	public static function czas(){
		return strftime("%H:%M:%S");
	}
	//----------------------------------------------------------------------------------------------
	public static function miesiac_nazwa($nazwa){						//zwraca nazwe miesiaca
		switch ($nazwa){
			case "1":
				return "Stycze�";
				break;
			case "2":
				return "Luty";
				break;
			case "3":
				return "Marzec";
				break;
			case "4":
				return "Kwiecie�";
				break;
			case "5":
				return "Maj";
				break;
			case "6":
				return "Czerwiec";
				break;
			case "7":
				return "Lipiec";
				break;
			case "8":
				return "Sierpie�";
				break;
			case "9":
				return "Wrzesie�";
				break;
			case "10":
				return "Pa�dziernik";
				break;
			case "11":
				return "Listopad";
				break;
			case "12":
				return "Grudzie�";
				break;
			default:
				return FALSE;
				break;
		}
	}
	//----------------------------------------------------------------------------------------------
	public static function dzien_nazwa($dzien, $miesiac, $rok){		//zwraca nazwe dnia w miesiacu
		$czas = mktime(0, 0, 0, $miesiac, $dzien, $rok);
		$tab = getdate($czas);
		return $tab['wday'];
		//yday - numer dnia od 0 do ~265
	}
	//----------------------------------------------------------------------------------------------
	public static function dni($miesiac, $rok){					//zwraca tablice dni w miesiacu
		$rok = self::przestepny($rok);							//sprawdza czy przestepny
		switch($miesiac){
			case "1":
				return 31;
				break;
			case "2":
				if($rok){										//dla roku przestepnego
					return 29;
				}else{
					return 28;
				}
				break;
			case "3":
				return 31;
				break;
			case "4":
				return 30;
				break;
			case "5":
				return 31;
				break;
			case "6":
				return 30;
				break;
			case "7":
				return 31;
				break;
			case "8":
				return 31;
				break;
			case "9":
				return 30;
				break;
			case "10":
				return 31;
				break;
			case "11":
				return 30;
				break;
			case "12":
				return 31;
				break;
			default:
				return FALSE;
				break;
		}
	}
	//----------------------------------------------------------------------------------------------
	public static function miesiace($rok){						//zwraca tablice miesiecy wraz z dniami w miesiacu
		$tab = array();
		for($i = 1; $i <= 12; $i++){
			$tab[$i] = self::dni($i, $rok);						//zwraca liczbe dni w podanym miesiacu
		}
		return $tab;
	}
	//----------------------------------------------------------------------------------------------
	public static function valid($data){						//sprawdza poprawnosc daty
		//checkdate ( int $month , int $day , int $year )
	}
	//----------------------------------------------------------------------------------------------
	public static function przestepny($rok){					//sprawdza czy rok jest przestepny, jesli tak zwraca 1
		if($rok%4 == 0 && $rok%100 != 0 || $rok%400 == 0){
			return 1;
		}
	}
	//----------------------------------------------------------------------------------------------
	public static function dzien($bool = FALSE){				//aktualny dzien - jesli true zwraca nazwe dnia
		if($bool){
			$dzien = strftime("%d");
			$miesiac = strftime("%m");
			$rok = strftime("%Y");
			$nazwa = self::dzien_nazwa($dzien, $miesiac, $rok);
			return $nazwa;
		}else{
			return strftime("%d");
		}
	}
	//----------------------------------------------------------------------------------------------
	public static function miesiac($bool = FALSE){				//aktualny miesiac - jesli true zwraca nzawe miesiaca
		$miesiac = strftime("%m");
		if($bool){
			return self::miesiac_nazwa($miesiac);
		}else{
			return $miesiac;
		}
	}
	//----------------------------------------------------------------------------------------------
	public static function rok(){								//aktualny rok
		$rok = strftime("%Y");
	}
	//----------------------------------------------------------------------------------------------
	public static function godzina(){							//godzina
		$rok = strftime("%H");
	}
	//----------------------------------------------------------------------------------------------
	public static function minuta(){							//minuta
		$rok = strftime("%M");
	}
	//----------------------------------------------------------------------------------------------
	public static function sekunda(){							//sekunda
		$rok = strftime("%S");
	}
}

?>
