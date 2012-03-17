<?PHP
/**
 * obiekt do prostych manipulacji na datach
 * @author chajr <chajr@bluetree.pl>
 * @version 1.2
 * @access private
 * @copyright chajr/bluetree
 * @package time
 * @todo numer tygodnia
 * @todo time conversion zmiana sekund na dni itp (np sekundy/60/60 - godziny)
 * Display <a href="http://sam.zoy.org/wtfpl/COPYING">Do What The Fuck You Want To Public License</a>
 * @license http://sam.zoy.org/wtfpl/COPYING Do What The Fuck You Want To Public License
*/
class simpleData_class {
	/**
	 * zwraca aktualny czas (data+czas) odpowiednio sformatowany
     * @static
	 * @param integer $stamp opcjonalnie unixowy znacznik czasu wg ktorego ma zwrucic formatowanie
	 * @return string zwraca sformatowany ciag z data i czasem
    */
	public static function full_time($stamp = NULL){
		$cfg = '%H:%M:%S - %d-%m-%Y';
		if(!$stamp){
			$stamp = time();
		}
		return strftime($cfg, $stamp);
	}
	/**
	 * zwraca date w formacie dzien-miesiac-rok, lub dla bazy w formacie rok miesiac dzien
	 * @param integer $stamp opcjonalnie unixowy znacznik czasu
	 * @param boolean $a typ zwracanej daty, jesli true rok-miesiac-dzien, false dzien-miesiac-rok
	 * @return string sformatowana aktualna data
	 * @example data(0, 1)
	 * @example data()
	 * @example data(2354365346)
	 */
	public static function data($stamp = NULL, $a = FALSE){
		if(!$stamp){
			$stamp = time();
		}
		if($a){
			return strftime('%Y-%m-%d', $stamp);
		}else{
			return strftime('%d-%m-%Y', $stamp);
		}
	}
	/**
	 * zwraca czas w formacie godina-minuta-sekunda
	 * @param integer $stamp opcjonalnie unixowy znacznik czasu
	 * @return string sformatowana data  
	 */
	public static function czas($stamp = NULL){
		if(!$stamp){
			$stamp = time();
		}
		return strftime('%H:%M:%S', $stamp);
	}
	/**
	 * zwraca nazwe miesiaca w postaci tekstowej
	 * @param integer $stamp unixowy znacznik czasu
	 * @param string $stamp jesli numer przekazany jako string (lub wiekszy od 12) jest to numer miesiaca
	 * @param boolean $short jesli na true zwraca nazwe miesiaca w postaci skroconej, inaczej pelna nazwa
	 * @return string nazwa miesiaca
	 * @example miesiac_nazwa(); - nazwa aktualnego miesiaca
	 * @example miesiac_nazwa(254543534); - nazwa jakiegos miesiaca
	 * @example miesiac_nazwa('8'); - zwroci sierpien
	 * @example miesiac_nazwa('13'); - zwroci styczen (> 12 traktowane jako znacznik czasu)
	 * przykladowo miesiac trwa 2764800s (60*60*24*32)
	 */
	public static function miesiac_nazwa($stamp = NULL, $short = NULL){
		if(!$stamp){
			$stamp = time();
		}
		if($short){
			$m = '%b';
		}else{
			$m = '%B';
		}
		if(is_string($stamp)){
			switch ($stamp){//JAKO STRING
				case '1':
					return strftime($m, 1);
					break;
				case '2':
					return strftime($m, 2764800);
					break;
				case '3':
					return strftime($m, 5529600);
					break;
				case '4':
					return strftime($m, 8294400);
					break;
				case '5':
					return strftime($m, 11059200);
					break;
				case '6':
					return strftime($m, 13824000);
					break;
				case '7':
					return strftime($m, 16588800);
					break;
				case '8':
					return strftime($m, 19353600);
					break;
				case '9':
					return strftime($m, 22118400);
					break;
				case '10':
					return strftime($m, 24883200);
					break;
				case '11':
					return strftime($m, 27648000);
					break;
				case '12':
					return strftime($m, 30412800);
					break;
				default:
					return strftime($m, $stamp);
					break;
			}
		}else{
			return strftime($m, $stamp);
		}
	}
	/**
	 * zwraca nazwe dnia w tygodniu
	 * @param mixed $stamp unixowy znacznik czasu, lub tablica ($dzien, $miesiac, $rok) lub jesli jako string zwraca nazwe dnia o podanym numerze
	 * @param boolean $short jesli na true zwraca wersje skrucona nazwy
	 * @return mixed nazwa dnia (poniedzialek, wtorek itp), lub jego numer w tygodniu (1 poniedzialek ...)
	 * @example dzien_nazwa(23424234);
	 * @example dzien_nazwa(array(12, 12, 1983))
	 * @example dzien_nazwa('0'); - niedziela
	 * @example dzien_nazwa('0', 1); - niedziela wersja skrucona
	 * przykladowo dzien trwa 90000s (60*60*22)
	 */
	public static function dzien_nazwa($stamp = NULL, $short = NULL){
		if(!is_string($stamp)){
			if(!$stamp){
				$stamp = time();
			}elseif(is_array ($stamp)){
				$stamp = mktime(0, 0, 0, $stamp[1], $stamp[0], $stamp[2]);
			}
			$tab = getdate($stamp);
		}else{
			$tab['wday'] = $stamp;
		}
		if($short){
			$short = '%a';
		}else{
			$short = '%A';
		}
		switch($tab['wday']){
			case '0':
				return strftime($short, 1312077709);//niedziala
				break;
			case '1':
				return strftime($short, 1312167709);
				break;
			case '2':
				return strftime($short, 1312257709);
				break;
			case '3':
				return strftime($short, 1312347709);
				break;
			case '4':
				return strftime($short, 1312437709);
				break;
			case '5':
				return strftime($short, 1312527709);//1312527709 (9:00)
				break;
			case '6':
				return strftime($short, 1312617709);
				break;
		}
	}
	/**
	 * zwraca numer dnia w roku (od 1 do 365)
	 * numery miesiacy lub dni bez zera na poczatku
	 * @param mixed $data unixowy znacznik czasu, lub tablica ($dzien, $miesiac, $rok)
	 * @return integer numer dnia w roku 
	 * @example dzien_numer(23424234);
	 * @example dzien_numer(array(12, 12, 1983))
	 */
	public static function dzien_numer($data = NULL){
		if(!$data){
			$data = time();
		}elseif(is_array ($data)){
			$data = mktime(0, 0, 0, $data[1], $data[0], $data[2]);
		}
		$tab = getdate($data);
		return $tab['yday']+1;
	}
	/**
	 * zwraca liczbe dni w miesiacu
	 * @param mixed $stamp unixowy znacznik czasu, lub tablica (miesiac, rok), jesli brak aktualny miesiac
	 * @return integer zwraca liczbe dni w miesiacu
	 * @example dni()
	 * @example dni(34234234)
	 * @example dni(array(12, 1983))
	 * @uses simpleData_class::miesiac()
	 * @uses simpleData_class::rok()
	 * @uses simpleData_class::przestepny()
	 */
	public static function dni($stamp = NULL){
		if(is_array ($stamp)){
			$miesiac = $stamp[0];
			$rok = $stamp[1];
		}else{
			if(!$stamp){
				$stamp = time();
			}
			$miesiac = self::miesiac($stamp);
			$rok = self::rok($stamp);
		}
		$rok = self::przestepny($rok);
		switch($miesiac){
			case '1':
				return 31;
				break;
			case '2':
				if($rok){
					return 29;
				}else{
					return 28;
				}
				break;
			case '3':
				return 31;
				break;
			case '4':
				return 30;
				break;
			case '5':
				return 31;
				break;
			case '6':
				return 30;
				break;
			case '7':
				return 31;
				break;
			case '8':
				return 31;
				break;
			case '9':
				return 30;
				break;
			case '10':
				return 31;
				break;
			case '11':
				return 30;
				break;
			case '12':
				return 31;
				break;
			default:
				return FALSE;
				break;
		}
	}
	/**
	 * zwraca tablice miesiecy wraz z dniami w miesiacu
	 * @param mixed $stamp unixowy znacznik czasu, jesli brak, aktualny czas, jesli jako string to uznaje to jako rok
	 * @example miesiace(23423423423)
	 * @example miesiace('2011')
	 * @return array tablica miesiecy w roku wraz z liczba dni
	 * @uses simpleData_class::dni()
	 * @uses simpleData_class::rok()
	 */
	public static function miesiace($stamp = NULL){
		if(is_string($stamp)){
			$stamp = mktime(0, 0, 0, '24', '9', $stamp);
		}
		if(!$stamp){
			$stamp = time();
		}
		$tab = array();
		$rok = self::rok($stamp);
		for($i = 1; $i <= 12; $i++){
			$tab[$i] = self::dni(array($i, $rok));
		}
		return $tab;
	}
	/**
	 * sprawdza poprawnosc daty
	 * @param integer $stamp unixowy znacznik czasu, lub tablica daty, jesli brak aktualny czas
	 * @return boolenan TRUE jesli data poprawna, inaczej FALSE
	 * @example valid()
	 * @example valid(34234234)
	 * @example valid(array(12, 12, 1983)) dzien miesiac rok
	 * @uses simpleData_class::miesiac()
	 * @uses simpleData_class::rok()
	 * @uses simpleData_class::dzien()
	 */
	public static function valid($stamp = NULL){
		if(is_array($stamp)){
			$miesiac = $stamp[1];
			$rok = $stamp[2];
			$dzien = $stamp[0];
		}else{
			if(!$stamp){
				$stamp = time();
			}
			$miesiac = self::miesiac($stamp);
			$rok = self::rok($stamp);
			$dzien = self::dzien($stamp);
		}
		return checkdate($miesiac, $dzien, $rok );
	}
	/**
	 * sprawdza czy rok jest przestepny, jesli tak zwraca 1
	 * @param integer $rok rok ktory ma sprawdzic, jesli brak pobiera z aktualnego czasu
	 * @return boolean jesli TRUE rok przestepny, jesli FALSE zwykly
	 */
	public static function przestepny($rok = NULL){
		if(!$rok){
			$rok = self::rok();
		}
		if($rok%4 == 0 && $rok%100 != 0 || $rok%400 == 0){
			return TRUE;
		}
		return FALSE;
	}
	/**
	 * zwraca aktualny, lub podany ze znacznika dzien w miesiacu, badz jego nazwe
	 * @param integer $stamp unixowy znacznik czasu
	 * @param boolean $name czy ma zwrucic w postaci nazwy
	 * @param boolean $short czy ma zwrucic w postaci skruconej
	 * @return mixed numer dnia, nazwa
	 * @example dzien()
	 * @example dzien(252453, 0, 1)
	 * @example dzien(0, 1)
	 * @example dzien(2423424, 1, 1) wersja skrocona
	 * @uses simpleData_class::dzien_nazwa() 
	 */
	public static function dzien($stamp = NULL, $name = FALSE, $short = FALSE){
		if(!$stamp){
			$stamp = time();
		}
		if($name){
			if($short){
				return self::dzien_nazwa($stamp, 1);
			}
			return self::dzien_nazwa($stamp);
		}else{
			return strftime('%d', $stamp);
		}
	}
	/**
	 * aktualny miesiac, lub podany z timestamp
	 * @param integer $stamp unixowy znacznik czasu
	 * @param boolean $bool czy ma zwrucic nazwe miesiaca
	 * @param boolena $short  czy ma zwrucic w postaci skruconej
	 * @return mixed zwraca numer miesiac, lub jego nazwe
	 * @example miesiac(3424234, 1)
	 * @example miesiac()
	 * @example miesiac(234234234, 1, 1) wersja skrocona
	 * @uses simpleData_class::miesiac_nazwa()
	 */
	public static function miesiac($stamp = NULL, $bool = FALSE, $short = FALSE){
		if(!$stamp){
			$stamp = time();
		}
		if($bool){
			if($short){
				return self::miesiac_nazwa($stamp, 1);
			}
			return self::miesiac_nazwa($stamp);
		}else{
			return strftime('%m', $stamp);
		}
	}
	/**
	 * aktualny rok, lub podany z timestamp
	 * @param integer $stamp opcjonalnie znacznik czasu
	 * @return integer rok
	 */
	public static function rok($stamp = NULL){
		if(!$stamp){
			$stamp = time();
		}
		return strftime('%Y', $stamp);
	}
	/**
	 * zwraca aktualna godzine, lub z podanego timestampa
	 * @param integer $stamp opcjonalnie znacznik czasu
	 * @return integer godzina
	 */
	public static function godzina($stamp = NULL){
		if(!$stamp){
			$stamp = time();
		}
		return strftime('%H', $stamp);
	}
	/**
	 * zwraca aktualna minute, lub z podanego timestampa
	 * @param integer $stamp opcjonalnie znacznik czasu
	 * @return integer minuta
	 */
	public static function minuta($stamp = NULL){
		if(!$stamp){
			$stamp = time();
		}
		return strftime('%M', $stamp);
	}
	/**
	 * zwraca aktualna sekunda, lub z podanego timestampa
	 * @param integer $stamp opcjonalnie znacznik czasu
	 * @return integer sekunda
	 */
	public static function sekunda($stamp = NULL){
		if(!$stamp){
			$stamp = time();
		}
		return strftime('%S', $stamp);
	}
	/**
	 * zwraca numer tygodnia w roku zgodnie z ISO8601:1998
	 * @param integer $stamp opcjonalnie znacznik czasu
	 * @return integer numer tygodnia 
	 */
	public static function tydzien($stamp = NULL){
		if(!$stamp){
			$stamp = time();
		}
		return strftime('%W', $stamp);
	}
	/**
	 * konwertuje w razie potrzeby ciag znakow zwracanych jako iso na UTF-8
	 * domyslnie z ISO-8859-2 na UTF-8
	 * @param string $string ciag znakow do konwersji
	 * @param string $from typ zestawu z ktorego ma dokonac konwersji
	 * @param string $to zestaw znakow do ktorego ma dokonac konwersji
	 * @return string odpowiednio skonwertowany ciag znakow
	 */
	public static function convert($string, $from = 'ISO-8859-2', $to = 'UTF-8'){
		return iconv($from, $to, $string);
	}
}
?>