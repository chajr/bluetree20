<?PHP
/**
 * @author chajr <chajr@bluetree.pl>
 * @version 0.7
 * @access public
 * @copyright chajr/bluetree
 * @package valid
 * @todo sprawdanie czy zawiera znaki html
*/
/**
 * zbior prostych metod statycznych odpowiedzialnych za walidacje danych
 */
class simpleValid_class {
	/**
	 * tablica wyrazen regularnych
	 * @var array
	 */
	static $reg_array = array(
		'string' =>				'#^[\p{L}]*$#u',
		'letters' =>			'#^[\p{L} _ ,.-]*$#u',
		'letters_extend' =>		'#^[\p{L}_ ,\\.;:-]*$#u',
		'fullchars' =>			'#^[\p{L}\\d_ ,\\.;:/!@#$%^&*()+=|\\\{}\\]\\[<>?`~\'"-]*$#u',
		'integer' =>			'#^[\\d]*$#',
		'multinum' =>			'#^[\\d /-]*$#',
		'num_chars' =>			'#^[\p{L}\\d\\.,_-]*$#u',
		'num_char_extends' =>	'#^[\p{L}\\d_ ,\\.;:-]*$#u',
		'numeric' =>			'#^(-)?[\\d]*$#',
		'float' =>				'#^(-)?[\\d]*((,|\\.)?[\\d]*)?$#',
		'mail' =>				'#^[\\w_\.-]*[\\w_]@[\\w_\.-]*\.[\\w_-]{2,3}$#e',
		'url' =>				'#^(http://)?[\\w\\._-]+(/)?$#',
		'url_extend' =>			'#^((http|https|ftp|ftps)://)?[\\w\\._-]+(/)?$#',
		'url_full' =>			'#^((http|https|ftp|ftps)://)?[\\w\\._/-]+(\\?[\\w&%=+-]*)?$#',
		'price' =>				'#^[\\d]*((,|\\.)[\\d]{0,2})?$#',
		'postcode' =>			'#^[\\d]{2}-[\\d]{3}$#',
		'phone' =>				'#^((\\+)[\\d]{2})?( ?\\( ?[\\d]+ ?\\) ?)?[\\d -]*$#',
		'date2' =>				'#^[\\d]{2}-[\\d]{2}-[\\d]{4}$#',
		'date' =>				'#^[\\d]{4}-[\\d]{2}-[\\d]{2}$#',
		'month' =>				'#^[\\d]{4}-[\\d]{2}$#',
		'datetime' =>			'#^[\\d]{4}-[\\d]{2}-[\\d]{2} [\\d]{2}:[\\d]{2}$#',
		'jdate' =>				'#^[\\d]{2}/[\\d]{2}/[\\d]{4}$#',							//czas podany z jquery datetimepickera
		'jdatetime' =>			'#^[\\d]{2}/[\\d]{2}/[\\d]{4} [\\d]{2}:[\\d]{2}$#',			//czas podany z jquery datetimepickera
		'time' =>				'#^[\\d]{2}:[\\d]{2}(:[\\d]{2})?$#',
		'hex_color' =>			'/^#[\\da-f]{6}$/i',
		'hex' =>				'/^#[\\da-f]+$/i',
		'hex2' =>				'#^0x[\\da-f]+$#i',
		'octal' =>				'#^0[0-7]+$#',
		'binary' =>				'#^b[0-1]+$#i',
		'week' =>				'#^[\\d]{4}-[\\d]{2}$#'
	);
	/**
	 * informacja z walidacji pesla 0 -kobieta, 1 -mezczyzna
	 * @var integer 
	 */
	static $pesel_sex = NULL;
	/**
	 * metoda umozliwia walidacje wedlug wybranego wzorca
	 * 'string' =>				'#^[\p{L}]*$#u',
		'letters' =>			'#^[\p{L} _ ,.-]*$#u',
		'letters_extend' =>		'#^[\p{L}_ ,\\.;:-]*$#u',
		'fullchars' =>			'#^[\p{L}\\d_ ,\\.;:/!@#$%^&*()+=|\\\{}\\]\\[<>?`~\'"-]*$#u',
		'integer' =>			'#^[\\d]*$#',
		'multinum' =>			'#^[\\d /-]*$#',
		'num_chars' =>			'#^[\p{L}\\d\\.,_-]*$#u',
		'num_char_extends' =>	'#^[\p{L}\\d_ ,\\.;:-]*$#u',
		'numeric' =>			'#^(-)?[\\d]*$#',
		'float' =>				'#^(-)?[\\d]*((,|\\.)?[\\d]*)?$#',
		'mail' =>				'#^[\\w_\.-]*[\\w_]@[\\w_\.-]*\.[\\w_-]{2,3}$#e',
		'url' =>				'#^(http://)?[\\w\\._-]+(/)?$#',
		'url_extend' =>			'#^((http|https|ftp|ftps)://)?[\\w\\._-]+(/)?$#',
		'url_full' =>			'#^((http|https|ftp|ftps)://)?[\\w\\._/-]+(\\?[\\w&%=+-]*)?$#',
		'price' =>				'#^[\\d]*((,|\\.)[\\d]{0,2})?$#',
		'postcode' =>			'#^[\\d]{2}-[\\d]{3}$#',
		'phone' =>				'#^((\\+)[\\d]{2})?( ?\\( ?[\\d]+ ?\\) ?)?[\\d -]*$#',
		'date2' =>				'#^[\\d]{2}-[\\d]{2}-[\\d]{4}$#',
		'date' =>				'#^[\\d]{4}-[\\d]{2}-[\\d]{2}$#',
		'month' =>				'#^[\\d]{4}-[\\d]{2}$#',
		'datetime' =>			'#^[\\d]{4}-[\\d]{2}-[\\d]{2} [\\d]{2}:[\\d]{2}$#',
		'jdate' =>				'#^[\\d]{2}/[\\d]{2}/[\\d]{4}$#',							//czas podany z jquery datetimepickera
		'jdatetime' =>			'#^[\\d]{2}/[\\d]{2}/[\\d]{4} [\\d]{2}:[\\d]{2}$#',			//czas podany z jquery datetimepickera
		'time' =>				'#^[\\d]{2}:[\\d]{2}(:[\\d]{2})?$#',
		'hex_color' =>			'/^#[\\da-f]{6}$/i',
		'hex' =>				'/^#[\\da-f]+$/i',
		'hex2' =>				'#^0x[\\da-f]+$#i',
		'octal' =>				'#^0[0-7]+$#',
		'binary' =>				'#^[0-1]+$#',
	 * @param mixed $val wartosc do sprawdzenia
	 * @param string $type typ walidacji
	 * @return boolean jesli poprawna zwraca TRUE, inaczej FALSE, jesli brak walidacji na liscie zwraca NULL
	 * @uses simpleValid_class::$reg_array
	 */
	static function valid($val, $type){
		if(!isset(self::$reg_array[$type])){
			return NULL;
		}
		$bool = preg_match(self::$reg_array[$type], $val);
		if(!$bool){
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * sprawdza poprawnosc adresu e-mail
	 * @param string $adress adres e-mail
	 * @return boolean jesli poprawny zwraca TRUE, inaczej FALSE
	 * @uses simpleValid_class::$reg_array
	 */
	static function mail($adress){
		if (!preg_match (self::$reg_array['mail'], $adress)){
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * sprawdzanie cen
	 * @param integer $val wartosc do sprawdzenia
	 * @return boolean informacja o poprawnosci danych
	 * @uses simpleValid_class::$reg_array
	 */
	static function price($val){
		$bool = preg_match(self::$reg_array['price'], $val);
		if(!$bool){
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * sprawdza poprawnosc kodu pocztowego
	 * @param string $val kod pocztowy
	 * @return boolean informacja o poprawnosci danych
	 * @uses simpleValid_class::$reg_array
	 */
	static function postcode($val){
		$bool = preg_match(self::$reg_array['postcode'], $val);
		if(!$bool){
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * sprawdza poprawnosc NIP-u
	 * @param string $val numer nip
	 * @return boolean informacja o poprawnosci danych
	 */
	static function nip($pNip){
        if(!empty($pNip)) {
            $weights = array(6, 5, 7, 2, 3, 4, 5, 6, 7);
            $nip = preg_replace('#[\\s-]#', '', $pNip);
            if (strlen($nip) == 10 && is_numeric($nip)) {    
                $sum = 0;
                for($i = 0; $i < 9; $i++){
                    $sum += $nip[$i] * $weights[$i];
				}
                return ($sum % 11) == $nip[9];
            }
        }
        return FALSE;
	}
	/**
	 * sprawdza dlugosc ciagu, mozliwe okreslenie max lub min lub obu na raz
	 * @param mixed $val ciag znakow do sprawdzenia
	 * @param integer $min minimalna dlugosc ciagu, jesli NULL nie sprawdza
	 * @param integer $max maksymalna dlugosc ciagu, jesli NULL nie sprawdza
	 * @return boolean informacja o poprawnosci danych
	 * @example char_lenght('asdasdasd', $min = NULL, $max = 23)
	 * @example char_lenght('asdasdasd', $min = 3, $max = 23)
	 * @example char_lenght('asdasdasd', $min = 3)
	 * @uses simpleValid_class::range()
	 */
	static function char_lenght($val, $min = NULL, $max = NULL){
		$dlugosc = mb_strlen($val);
		$bool = self::range($dlugosc, $min, $max);
		return $bool;
	}
	/**
	 * sprawdza zakres wartosci numerycznej, mozliwe okreslenie max lub min lub obu na raz
	 * umozliwia sprawdzanie liczb dziesietnych, hex, osemkowych i binarnych
	 * @param integer $val wartosc do sprawdzenia
	 * @param integer $min minimalna wartosc ciagu, jesli NULL nie sprawdza
	 * @param integer $max maksymalna wartosc ciagu, jesli NULL nie sprawdza
	 * @example char_lenght(23423, $min = NULL, $max = 23)
	 * @example char_lenght(23423, $min = 3, $max = 23)
	 * @example char_lenght(23423, $min = 3)
	 * @example char_lenght(0xd3a743f2ab, $min = 3)
	 * @example char_lenght('#aaffff', $min = 3)
	 * @return boolena informacja o poprawnosci danych
	 */
	static function range($val, $min = NULL, $max = NULL){
		if(preg_match(self::$reg_array['hex'], $min) || preg_match(self::$reg_array['hex2'], $min)){
			$val = hexdec($val);
			$min = hexdec($min);
		}
		if(preg_match(self::$reg_array['hex'], $max) || preg_match(self::$reg_array['hex2'], $max)){
			$val = hexdec($val);
			$max = hexdec($max);
		}
		if(preg_match(self::$reg_array['octal'], $min)){
			$val = octdec($val);
			$min = octdec($min);
		}
		if(preg_match(self::$reg_array['octal'], $max)){
			$val = octdec($val);
			$max = octdec($max);
		}
		if(preg_match(self::$reg_array['binary'], $min)){
			$val = bindec($val);
			$min = bindec($min);
		}
		if(preg_match(self::$reg_array['binary'], $max)){
			$val = bindec($val);
			$max = bindec($max);
		}
		if($min != NULL && $min > $val){
			return FALSE;
		}
		if($max != NULL && $max < $val){
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * spawdza czy liczba jest mniejsza od 0
	 * jesli mniejsza zwraca TRUE
	 * @param integer $val liczba
	 * @return boolean informacja o poprawnosci danych 
	 */
	static function under_0($val){
		if($val < 0){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	/**
	 * sprawdza poprawnosc nueru pesel, dodatkowo zwraca info o plci
	 * 0 -kobieta, 1 -mezczyzna
	 * @param mixed $str numer pesel
	 * @return boolean informacja o poprawnosci danych 
	 * @uses simpleValid_class::$pesel_sex
	 */
	static function pesel($str){
		$str = preg_replace('#[\\s-]#', '', $str);
		if(!preg_match('#^[0-9]{11}$#',$str)){
			return FALSE;
		}
		if (($pesel[9] % 2) == 0){ 
			self::$pesel_sex = '0';
		}else{ 
			self::$pesel_sex = '1';
		}
		$arrSteps = array(1, 3, 7, 9, 1, 3, 7, 9, 1, 3); 
		$intSum = 0;
		for ($i = 0; $i < 10; $i++){
			$intSum += $arrSteps[$i] * $str[$i];
		}
		$int = 10 - $intSum % 10;
		$intControlNr = ($int == 10)?0:$int;
		if ($intControlNr == $str[10]){
			return TRUE;
		}
		return FALSE;
	}
	/**
	 * sprawdzanie numeru regon
	 * @param mixed $str numer regon
	 * @return boolean informacja o poprawnosci danych 
	 */
	static function regon($str){
		$str = preg_replace('#[\\s-]#', '', $str);
		if (strlen($str) != 9){
			return FALSE;
		}
		$arrSteps = array(8, 9, 2, 3, 4, 5, 6, 7);
		$intSum=0;
		for ($i = 0; $i < 8; $i++){
			$intSum += $arrSteps[$i] * $str[$i];
		}
		$int = $intSum % 11;
		$intControlNr=($int == 10)?0:$int;
		if ($intControlNr == $str[8]){
			return TRUE;
		}
		return FALSE;
	}
	/**
	 * walidacja numeru konta wedlug standardu NRB
	 * @param mixed $p_iNRB numer konta
	 * @return boolean informacja o poprawnosci danych 
	 */
	static function nrb($p_iNRB){
		$iNRB = preg_replace('#[\\s-]#', '', $p_iNRB);
		if(strlen($iNRB) != 26){
			return FALSE;
		}
		$aWagiCyfr = array(1, 10, 3, 30, 9, 90, 27, 76, 81, 34, 49, 5, 50, 15, 53, 45, 62, 38, 89, 17, 73, 51, 25, 56, 75, 71, 31, 19, 93, 57);
		$iNRB = $iNRB.'2521';
		$iNRB = substr($iNRB, 2).substr($iNRB, 0, 2); 
		$iSumaCyfr = 0;
		for($i = 0; $i < 30; $i++){
			$iSumaCyfr += $iNRB[29-$i] * $aWagiCyfr[$i];
		}
		// Sprawdzenie czy modulo z sumy wag poszczegolnych cyfr jest rowne 1
		if($iSumaCyfr % 97 == 1){
			return TRUE;
		}
		return FALSE;
	}
	/**
	 * metoda sprawdza poprawnosc numer konta zgodnie ze standardem IBAN
	 * @param mixed $numer nuemr konta iban
	 * @return boolean informacja o poprawnosci danych 
	 * @author Bartłomiej Zastawnik, "Rzast"
	 */
	static function iban($numer) {
		//(c) Bartłomiej Zastawnik, "Rzast".
		$puste = array(' ', '-', '_', '.', ',','/', '|');//znaki do usuniącia
		$temp = strtoupper(str_replace($puste, '', $numer));//Zostają cyferki + duże litery
		if (($temp{0}<='9')&&($temp{1}<='9')){//Jeżeli na początku są cyfry, to dopisujemy PL, inne kraje muszć być jawnie wprowadzone
			$temp ='PL'.$temp;
		}
		$temp=substr($temp,4).substr($temp, 0, 4);//przesuwanie cyfr kontrolnych na koniec
		$znaki=array(
			'0'=>'0','1'=>'1','2'=>'2','3'=>'3','4'=>'4',
			'5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9',
			'A'=>'10','B'=>'11','C'=>'12','D'=>'13','E'=>'14','F'=>'15',
			'G'=>'16','H'=>'17','I'=>'18','J'=>'19','K'=>'20',
			'L'=>'21','M'=>'22','N'=>'23','O'=>'24','P'=>'25',
			'Q'=>'26','R'=>'27','S'=>'28','T'=>'29','U'=>'30',
			'V'=>'31','W'=>'32','X'=>'33','Y'=>'34','Z'=>'35'
		);//Tablica zamienników, potrzebnych do wyliczenia sumy kontrolnej
		$ilosc=strlen($temp);//długość numeru
		$ciag='';
		for ($i=0;$i<$ilosc;$i++){
			$ciag.=$znaki[$temp{$i}];
		}
		$mod = 0;
		$ilosc=strlen($ciag);//nowa długość numeru
		for($i=0;$i<$ilosc;$i=$i+6) {
			//oblicznie modulo, $ciag jest zbyt wielkć liczbę na format integer, wiąc dzielć go na kawaśki
			$mod = (int)($mod.substr($ciag, $i, 6)) % 97; 
		}
		$out=($mod==1)?TRUE:FALSE;
		return $out;
	}
	/**
	 * sprawdza adres url
	 * @param string $url adres url
	 * @param integer jesli 1 sprawdza tez typy https, ftp i ftps, jesli 2 sprawdza razem z GET
	 * @return boolean informacja o poprawnosci danych 
	 * @uses simpleValid_class::$reg_array
	 */
	static function url($url, $type){
		switch($type){
			case 1:
				$type = self::$reg_array['url_extend'];
				break;
			case 2:
				$type = self::$reg_array['url_full'];
				break;
			default:
				$type = self::$reg_array['url'];
				break;
		}
		$bool = preg_match($type, $url);
		if(!$bool){
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * sprawdza poprawnosc nueru telefonu
	 * np +48 ( 052 ) 131 231-2312
	 * @param mixed $phone numer telefonu
	 * @return type boolean informacja o poprawnosci danych 
	 * @uses simpleValid_class::$reg_array
	 */
	static function phone($phone){
		if (!preg_match (self::$reg_array['phone'], $phone)){
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * sprawdza czy podana liczba posiada odpowiedni krok
	 * @param float $step krok do sprawdzenia
	 * @param float $default domyslna wartosc (0)
	 * @param float $val wartosc do sprawdzenia
	 * @return boolean informacja o poprawnosci danych 
	 * @example step(5, 15, 5) TRUE
	 * @example step(5, 12) FALSE
	 * @uses simpleValid_class::valid()
	 */
	static function step($step, $val, $default = 0){
		if(!self::valid($step, 'float') || !self::valid($default, 'float') || !self::valid($val, 'float')){
			return FALSE;
		}
		$check = (abs($val)-abs($default))%$step;
		if($check){
			return FALSE;
		}
		return TRUE;
	}
	static function check_date($data){
		
	}
}
?>