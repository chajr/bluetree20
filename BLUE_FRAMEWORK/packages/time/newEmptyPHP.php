<?php
				$czas_start = 1329560189;
				$current_time = time();
				$current_time = $czas_start - $current_time;
				$ddd = simpleData_class::dzien_numer($current_time) -1;
				if($ddd > 0){
					$current_time = $current_time - ($ddd*24*60*60);
				}
				$hhh = floor($current_time/60/60);
				$current_time = $current_time - ($hhh*60*60);
				//$hhh = simpleData_class::godzina($czas_start);
				$mmm = floor($current_time/60);
				$current_time = $current_time - ($mmm*60);
				$sss = floor($current_time - ($mmm/60));
				echo '<pre>';
				var_dump($ddd, $hhh, $mmm, $sss);
				echo '</pre>';
///**
// * obiekt do manipulacji na dacie, tworzy obiekt z daty
// * @author chajr <chajr@bluetree.pl>
// * @version 1.1 alpha
// * @access public
// * @copyright chajr/bluetree
// * @package time
// * @todo pokrywanie się przedzialow czasowych
// * Display <a href="http://sam.zoy.org/wtfpl/COPYING">Do What The Fuck You Want To Public License</a>
// * @license http://sam.zoy.org/wtfpl/COPYING Do What The Fuck You Want To Public License
//*/
//class data_class{
//	/**
//	 * przechowuje unixowy znacznik czasu
//	 * @var integer
//	 */
//	private $unix_timestamp = 0;
//	/**
//	 * zwraca informacje o bledzie
//	 * @var string
//	 */
//	public $err;
//	/**
//	 * informacja czy ma uzywac metody convert z simpleData_class do naprawiania tekstow (domyslnie FALSE)
//	 * @var boolean 
//	 */
//	public $use_conversion = FALSE;
//	/**
//	 * tablica typow do konwersji domyslnie z ISO-8859-2 do UTF-8
//	 * @var array
//	 */
//	public $conversion_array = array('from' => 'ISO-8859-2', 'to' => 'UTF-8');
//	protected $time_diffrence = 0;
//	/**
//	 * tworzy obiekt z podanym znacznikiem czasu, lub aktualnym czasem wywolania
//	 * @param mixed $data unixowy znacznik czasu, lub tablica (godzina, minuta, sekunda, dzien, miesiac, rok)
//	 * @example __construct(23424242);
//	 * @example __construct(array(15, 0, 0, 24, 9, 2011));
//	 * @uses data_class::$unix_timestamp
//	 * @uses data_class::$err
//	 * @uses simpleData_class::valid()
//	 */
//	public function __construct($data = FALSE){
//		if($data && is_numeric($data)){
//			$this->unix_timestamp = $data;
//		}elseif(is_array($data)){
//			if(!simpleData_class::valid(
//					array($data[4], $data[3], $data[5])
//			)){
//				$this->err = 'INVALID_TIME_FORMAT';
//				return;
//			}
//			$this->unix_timestamp = mktime($data[0], $data[1], $data[2], $data[4], $data[3], $data[5]);
//		}else{
//			$this->unix_timestamp = time();
//		}
//	}
//	/**
//	 * zwraca aktualny czas (data+czas) odpowiednio sformatowany
//	 * @return string zwraca sformatowany ciag z data i czasem
//	 * @uses simpleData_class::full_time()
//	 * @uses data_class::$unix_timestamp
//    */
//	public function full_time() {
//		return simpleData_class::full_time($this->unix_timestamp);
//	}
//	/**
//	 * zwraca date w formacie dzien-miesiac-rok, lub dla bazy w formacie rok miesiac dzien
//	 * @param boolean $type typ zwracanej daty, jesli true rok-miesiac-dzien, false dzien-miesiac-rok
//	 * @return string sformatowana aktualna data
//	 * @example data(1)
//	 * @example data()
//	 * @uses simpleData_class::data()
//	 * @uses data_class::$unix_timestamp
//	 */
//	public function data($type = FALSE) {
//		return simpleData_class::data($this->unix_timestamp, $type);
//	}
//	/**
//	 * zwraca czas w formacie godina-minuta-sekunda
//	 * @return string sformatowana data  
//	 * @uses simpleData_class::czas()
//	 * @uses data_class::$unix_timestamp
//	 */
//	public function czas() {
//		return simpleData_class::czas($this->unix_timestamp);
//	}
//	/**
//	 * zwraca nazwe miesiaca zgodna z aktualnie ustawiona lokalizacja
//	 * @param boolena $short jesli na TRUE zwracxa nazwe w postaci skroconej
//	 * @return string nazwa miesiaca
//	 * @uses simpleData_class::miesiac_nazwa()
//	 * @uses data_class::$unix_timestamp
//	 * @uses data_class::$use_conversion
//	 * @uses data_class::$conversion_array
//	 */
//	public function miesiac_nazwa($short = NULL){
//		$miesiac = simpleData_class::miesiac_nazwa($this->unix_timestamp, $short);
//		if($this->use_conversion){
//			$miesiac = simpleData_class::convert($miesiac, $this->conversion_array['from'], $this->conversion_array['to']);
//		}
//		return $miesiac;
//	}
//	/**
//	 * zwraca nazwe dnia w tygodniu, zgodnie z aktualnie ustawiona lokalizacja
//	 * @param boolena $short jesli na TRUE zwraca nazwe w postaci skroconej
//	 * @return string nazwa dnia w miesiacu 
//	 * @uses simpleData_class::dzien_nazwa()
//	 * @uses data_class::$unix_timestamp
//	 * @uses data_class::$unix_timestamp
//	 * @uses data_class::$conversion_array
//	 */
//	public function dzien_nazwa($short = NULL){
//		$dzien = simpleData_class::dzien_nazwa($this->unix_timestamp, $short);
//		if($this->use_conversion){
//			$dzien = simpleData_class::convert($dzien, $this->conversion_array['from'], $this->conversion_array['to']);
//		}
//		return $dzien;
//	}
//	/**
//	 * zwraca aktualny dzien, badz jego nazwe
//	 * @param boolean $name czy ma zwrocic nawe dnia
//	 * @param boolena $short czy ma zwrocic w postaci skroconej
//	 * @return mixed numer dnia badz jego nazwa
//	 * @example dzien()
//	 * @example dzien(1) -nazwa
//	 * @example dzien(1, 1) -nazwa skrocona
//	 * @uses simpleData_class::dzien()
//	 * @uses data_class::$unix_timestamp
//	 * @uses data_class::$unix_timestamp
//	 * @uses data_class::$conversion_array
//	 */
//	public function dzien($name = NULL, $short = NULL){
//		$dzien = simpleData_class::dzien($this->unix_timestamp, $name, $short);
//		if($this->use_conversion){
//			$dzien = simpleData_class::convert($dzien, $this->conversion_array['from'], $this->conversion_array['to']);
//		}
//		return $dzien;
//	}
//	/**
//	 * zwraca numer dnia w roku
//	 * @return integer numer dnia w roku
//	 * @uses simpleData_class::dzien_numer()
//	 * @uses data_class::$unix_timestamp
//	 */
//	public function dzien_numer(){
//		return simpleData_class::dzien_numer($this->unix_timestamp);
//	}
//	/**
//	 * zwraca liczbe dni w miesiacu
//	 * @return integer liczba dni w miesiacu
//	 * @uses simpleData_class::dni()
//	 * @uses data_class::$unix_timestamp
//	 */
//	public function dni(){
//		return simpleData_class::dni($this->unix_timestamp);
//	}
//	/**
//	 * zwraca tablice miesiacy w roku wraz z liczba dni
//	 * @return array tablica miesiecy w roku wraz z liczba dni
//	 * @uses simpleData_class::miesiace()
//	 * @uses data_class::$unix_timestamp
//	 */
//	public function miesiace(){
//		return simpleData_class::miesiace($this->unix_timestamp);
//	}
//	/**
//	 * sprawdza czy podany rok jest przestepny
//	 * @return boolena jesli przestapny zwraca TRUE, inaczej FALSE 
//	 * @uses simpleData_class::przestepny()
//	 * @uses data_class::$unix_timestamp
//	 */
//	public function przestepny(){
//		return simpleData_class::przestepny($this->unix_timestamp);
//	}
//	/**
//	 * zwraca aktualny numer miesiac badz jego nazwe
//	 * @param boolean $bool czy ma zwrucic nazwe miesiaca
//	 * @param boolena $short czy ma zwrucic w postaci skruconej
//	 * @return mixed zwraca numer, badz nazwe miesiaca 
//	 * @example miesiac()
//	 * @example miesiac(1)
//	 * @example miesiac(1, 1)
//	 * @uses simpleData_class::miesiac()
//	 * @uses data_class::$unix_timestamp
//	 * @uses data_class::$conversion_array
//	 * @uses data_class::$use_conversion
//	 */
//	public function miesiac($bool = FALSE, $short = FALSE){
//		$miesiac = simpleData_class::miesiac($this->unix_timestamp, $name, $short);
//		if($this->use_conversion){
//			$miesiac = simpleData_class::convert($miesiac, $this->conversion_array['from'], $this->conversion_array['to']);
//		}
//		return $miesiac;
//	}
//	/**
//	 * zwraca podany rok
//	 * @return integer rok
//	 * @uses simpleData_class::rok()
//	 * @uses data_class::$unix_timestamp 
//	 */
//	public function rok(){
//		return simpleData_class::rok($this->unix_timestamp);
//	}
//	/**
//	 * zwraca aktualna godzine
//	 * @return integer godzina
//	 * @uses simpleData_class::godzina()
//	 * @uses data_class::$unix_timestamp 
//	 */
//	public function godzina(){
//		return simpleData_class::godzina($this->unix_timestamp);
//	}
//	/**
//	 * zwraca liczbe minut
//	 * @return integer minuty
//	 * @uses simpleData_class::minuta()
//	 * @uses data_class::$unix_timestamp 
//	 */
//	public function minuta(){
//		return simpleData_class::minuta($this->unix_timestamp);
//	}
//	/**
//	 * zwraca liczbe sekund
//	 * @return integer sekundy
//	 * @uses simpleData_class::sekunda()
//	 * @uses data_class::$unix_timestamp 
//	 */
//	public function sekunda(){
//		return simpleData_class::sekunda($this->unix_timestamp);
//	}
//	/**
//	 * zwraca numer tygodnia w roku zgodnie z ISO8601:1998
//	 * @return integer numer tygodnia 
//	 * @uses simpleData_class::tydzien()
//	 * @uses data_class::$unix_timestamp
//	 */
//	public function tydzien(){
//		return simpleData_class::tydzien($this->unix_timestamp);
//	}
//	/**
//	 * sprawdza czy dwie daty sa identyczne
//	 * @param data_class $data obiekt data class do porownania
//	 * @return boolena jesli TRUE sa identyczne inaczej FALSE
//	 * @uses data_class::$unix_timestamp
//	 */
//	public function same(data_class $data){
//		if($data == $this->unix_timestamp){
//			return TRUE;
//		}else{
//			return FALSE;
//		}
//	}
//	/**
//	 * zwraca tablice roznic miedzy datami, badz wartosc konkretnej roznicy
//	 * jesli data w obiekcie jest starsza zwraca w postaci liczby dodatniej, jesli mniejsza zwraca w postaci liczby ujemnej
//	 * jesli jakas wartosc jest rowna zwraca 0
//	 * @param data_class $data obiekt data_class
//	 * @param string $diff_type typ roznicy jaka ma zwrocic, domyslnie lista wszystkich (seconds, minutes, houres, days, weeks, months, years)
//	 * @param boolean czy ma zwracac porownanie absolutne (FALSE) czy w zaleznosci od innych zwroconych parametrow
//	 * @return mixed zwraca roznice, lub tablice roznic
//	 * @example diff(#data_object, 0, 1)
//	 * @example diff(#data_object)
//	 * @example diff(#data_object, 'days', 1)
//	 * @example diff(#data_object, 'weeks', 0)
//	 * @example diff(#data_object, 'houres')
//	 * @uses data_class::$unix_timestamp
//	 * @uses data_class::seconds_diff()
//	 * @uses data_class::years_diff()
//	 * @uses data_class::months_diff()
//	 * @uses data_class::weeks_diff()
//	 * @uses data_class::days_diff()
//	 * @uses data_class::houres_diff()
//	 * @uses data_class::minutes_diff()
//	 */
//	public function diff(data_class $data, $diff_type = NULL, $relative = NULL){
//		switch($diff_type){
//			case'seconds':
//				return $this->seconds_diff($data, $relative);
//				break;
//			case'years':
//				return $this->years_diff($data);
//				break;
//			case'months':
//				return $this->months_diff($data, $relative);
//				break;
//			case'weeks':
//				return $this->weeks_diff($data, $relative);
//				break;
//			case'days':
//				return $this->days_diff($data, $relative);
//				break;
//			case'houres':
//				return $this->houres_diff($data, $relative);
//				break;
//			case'minutes':
//				return $this->minutes_diff($data, $relative);
//				break;
//			default:
//				$diff_list = array(
//					'seconds' => $this->seconds_diff($data, $relative),
//					'years' => $this->years_diff($data),
//					'months' => $this->months_diff($data, $relative),
//					'weeks' => $this->weeks_diff($data, $relative),
//					'days' => $this->days_diff($data, $relative),
//					'houres' => $this->houres_diff($data, $relative),
//					'minutes' => $this->minutes_diff($data, $relative)
//				);
//				return $diff_list;
//				break;
//		}
//	}
//	/**
//	 * zwraca obiekt jako unixowy znacznik czasu w postaci tekstu
//	 * @return string
//	 * @uses data_class::$unix_timestamp
//	 */
//	public function __toString() {
//		return (string)$this->unix_timestamp;
//	}
//	/**
//	 * zwraca czas sformatowany zgodnie z opcjami funkcji strftime()
//	 * %a - skrótowa nazwa dnia tygodnia zgodnie z lokalizacją<br/>
//	 	%A - pełna nazwa dnia tygodnia zgodnie z lokalizacją<br/>
//		%b - skrótowa nazwa miesiąca zgodnie z lokalizacją<br/>
//		%B - pełna nazwa miesiąca zgodnie z lokalizacją<br/>
//		%c - preferowana reprezentacja daty i czasu zgodnie z lokalizacją<br/>
//		%C - numer wieku (rok podzielony przez 100 i skrócony do liczby całkowitej, przedział od 00 do 99)<br/>
//		%d - dzień miesiąca jako liczba dziesiętna (przedział od 01 do 31)<br/>
//		%D - to samo co %m/%d/%y<br/>
//		%e - dzień miesiąca jako liczba dziesiętna, przy czym pojedyncza cyfra poprzedzona jest spacją (przedział od " 1" do "31")<br/>
//		%g - tak jak %G, ale bez uwzględnienia wieku<br/>
//		%G - rok w zapisie czterocyfrowym, powiązany z numerem tygodnia wg ISO. Symbol ten ma ten sam format i wartość jak %Y, z tym wyjątkiem, że jeśli numer tygodnia wg ISO należy do poprzedniego lub następnego roku, to poprzedni lub następny rok jest zwracany przez ten symbol.<br/>
//		%h - tak jak %b<br/>
//		%H - godzina jako liczba dziesiętna w systemie 24-godzinnym (przedział od 00 do 23)<br/>
//		%I - godzina jako liczba dziesiętna w systemie 12-godzinnym (przedział od 01 do 12)<br/>
//		%j - dzień roku jako liczba dziesiętna (przedział od 001 do 366)<br/>
//		%m - miesiąc jako liczba dziesiętna (przedział od 01 do 12)<br/>
//		%M - minuty jako liczba dziesiętna<br/>
//		%n - znak nowej linii<br/>
//		%p - albo "am" lub "pm" zgodnie z podanym czasem, albo łańcuchy znaków odpowiadające lokalizacji<br/>
//		%r - czas w notacji a.m. lub p.m.<br/>
//		%R - czas w notacji 24-godzinnej<br/>
//		%S - sekundy jako liczba dziesiętna<br/>
//		%t - znak tabulacji<br/>
//		%T - aktualny czas, odpowiednik %H:%M:%S<br/>
//		%u - numer dnia tygodnia jako liczba dziesiętna [1,7], gdzie 1 oznacza poniedziałek<br/>
//		%U - numer tygodnia aktualnego roku jako liczba dziesiętna, począwszy od pierwszej niedzieli jako pierwszego dnia pierwszego tygodnia<br/>
//		%V - numer tygodnia aktualnego roku wg ISO 8601:1988 jako liczba dziesiętna, przedział od 01 do 53, gdzie tydzień 1 jest pierwszym tygodniem, którym ma co najmniej 4 dni w aktualnym roku, przy czym pierwszym dniem tygodnia jest poniedziałek. (Przy użyciu %G lub %g otrzymuje się rok, który odpowiada numerowi tygodnia dla podanego znacznika czasu).<br/>
//		%W - numer tygodnia aktualnego roku jako liczba dziesiętna, począwszy od pierwszego poniedziałku, jako pierwszego dnia pierwszego tygodnia<br/>
//		%w - dzień tygodnia jako liczba dziesiętna, począwszy od niedzieli - numer 0<br/>
//		%x - preferowana reprezentacja daty, zgodnie z lokalizacją, bez czasu<br/>
//		%X - preferowana reprezentacja czasu, zgodnie z lokalizacją, bez daty<br/>
//		%y - rok jako liczba dziesiętna, bez uwzględnienia wieku (przedział od 00 do 99)<br/>
//		%Y - rok jako liczba dziesiętna, z wiekiem włącznie<br/>
//		%Z - strefa czasowa, nazwa lub skrót<br/>
//		%% - znak "%"
//	 * @param string $format typ formatowania zgodny z strftime
//	 * @return mixed sformatowany czas
//	 * @example other_formats("%A - nazwa tygodnia zgodna z lokalizacją ")
//	 * @uses ata_class::$unix_timestamp
//	 * @uses ata_class::$use_conversion
//	 * @uses ata_class::$conversion_array
//	 */
//	public function other_formats($format){
//		$formated = strftime ($format, $this->unix_timestamp);
//		if($this->use_conversion){
//			$formated = simpleData_class::convert($formated, $this->conversion_array['from'], $this->conversion_array['to']);
//		}
//		return $formated;
//	}
//	/**
//	 * sprawdza poprawnosc daty
//	 * @return boolena informacja o poprawnosci daty
//	 * @uses data_class::$unix_timestamp 
//	 * @uses data_class::valid()
//	 */
//	protected function valid(){
//		return simpleData_class::valid($this->unix_timestamp);
//	}
//	/**
//	 * zwraca roznice miedzy sekundami
//	 * @param data_class $data obiekt data_class
//	 * @param boolena $relative czy roznica absolutna, czy zalezna od ustawionego czasu
//	 * @return integer roznica sekund
//	 * @uses data_class::sekunda()
//	 * @uses data_class::$unix_timestamp
//	 */
//	protected function seconds_diff(data_class $data, $relative){
//		if($relative){
//			echo '<pre>';
//			var_dump($this->sekunda(), $data->sekunda());
//			echo '</pre>';
//			return $this->sekunda() - $data->sekunda();
//		}
//		$data = (int)"$data";
//		return $this->unix_timestamp - $data;
//	}
//	/**
//	 * zwraca roznice miedzy minutami
//	 * @param data_class $data obiekt data_class
//	 * @param boolena $relative czy roznica absolutna, czy zalezna od ustawionego czasu
//	 * @return integer roznica minut 
//	 * @uses data_class::minuta()
//	 * @uses data_class::calculate_diff()
//	 */
//	protected function minutes_diff(data_class $data, $relative){
//		if($relative){
//			return $this->minuta() - $data->minuta();
//		}
//		$diff = $this->calculate_diff($data);
//		return floor($diff/60);
//	}
//	/**
//	 * zwraca roznice miedzy godzinami
//	 * @param data_class $data obiekt data_class
//	 * @param boolena $relative czy roznica absolutna, czy zalezna od ustawionego czasu
//	 * @return integer roznica godzin 
//	 * @uses data_class::godzina()
//	 * @uses data_class::calculate_diff()
//	 */
//	protected function houres_diff(data_class $data, $relative){
//		if($relative){
//			return $this->godzina() - $data->godzina();
//		}
//		$diff = $this->calculate_diff($data);
//		return floor($diff/60/60);
//	}
//	/**
//	 * zwraca roznice miedzy tygodniami
//	 * @param data_class $data obiekt data_class
//	 * @param boolena $relative czy roznica absolutna, czy zalezna od ustawionego czasu
//	 * @return integer roznica tygodni 
//	 * @uses data_class::tydzien()
//	 * @uses data_class::calculate_diff()
//	 */
//	protected function weeks_diff(data_class $data, $relative){
//		if($relative){
//			return $this->tydzien() - $data->tydzien();
//		}
//		$diff = $this->calculate_diff($data);
//		return floor($diff/7/24/60/60);
//	}
//	/**
//	 * zwraca roznice miedzy dniami
//	 * @param data_class $data obiekt data_class
//	 * @param boolena $relative czy roznica absolutna, czy zalezna od ustawionego czasu
//	 * @return integer roznica dni 
//	 * @uses data_class::dzien()
//	 * @uses data_class::calculate_diff()
//	 */
//	protected function days_diff(data_class $data, $relative){
//		if($relative){
//			return $this->dzien() - $data->dzien();
//		}
//		$diff = $this->calculate_diff($data);
//		return floor($diff/24/60/60);
//	}
//	/**
//	 * zwraca roznice miedzy miesiacami
//	 * @param data_class $data obiekt data_class
//	 * @param boolena $relative czy roznica absolutna, czy zalezna od ustawionego czasu
//	 * @return integer roznica miesiecy 
//	 * @uses data_class::miesiac()
//	 * @uses data_class::calculate_diff()
//	 */
//	protected function months_diff(data_class $data, $relative){
//		if($relative){
//			return $this->miesiac() - $data->miesiac();
//		}
//		$diff = $this->calculate_diff($data);
//		return floor($diff/12/24/60/60);
//	}
//	/**
//	 * zwraca roznice miedzy latami
//	 * @param data_class $data obiekt data_class
//	 * @return integer roznica lat 
//	 * @uses data_class::rok()
//	 */
//	protected function years_diff(data_class $data, $relative){
//		$diff = $this->rok() - $data->rok();
//		if($relative){
//			if($diff > 0){
//				$this->time_diffrence;
//			}else{
//				$this->time_diffrence = ;
//			}
//		}
//		return $this->rok() - $data->rok();
//	}
//	/**
//	 * zwraca roznice sekund dla podanych czasow
//	 * @param data_class $data obiekt data_class
//	 * @return integer bezwzgledna roznica sekund
//	 */
//	private function calculate_diff(data_class $data){
//		$data = (int)"$data";
//		return $this->unix_timestamp - $data;
//	}
//}
?>