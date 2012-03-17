<?PHP
/**
 * klasa przechowujaca rozne przydatne wzory matematyczne
 * @author chajr <chajr@bluetree.pl>
 * @version 1.0
 * @access public
 * @copyright chajr/bluetree
 * @package misc
*/
class math_class {
	/**
	 * oblicza procent jaki stanowi jedna liczba z drugiej liczby
	 * @param integer $ile liczba ktora stanowi procent calosci
	 * @param integer $calosc liczba z ktorej obliczamy procent
	 * @return integer wartosc w procentach 
	 */
	static function number_to_percent($ile, $calosc){
		return ($ile/$calosc)*100;
	}
	/**
	 * oblicza procent z liczby
	 * @param integer $ile liczba ktora stanowi procent
	 * @param integer $calosc liczba z ktorej obliczamy procent
	 * @return integer wartosc w procentach 
	 */
	static function percent($ile, $calosc){
		return ($ile/100)*$calosc;
	}
	/**
	 * oblicza szacowany czas zakonczenia wzgledem ilosci oraz maksymalnej wartosci
	 * @param integer $naklad maks wyswietlen
	 * @param integer $wyswietlen ile juz wyswietlono
	 * @param integer $start czas startu w formie timestamp
	 * @return integer szacowany czas zakonczenia w formie timestamp 
	 */
	static function end($naklad, $wyswietlen, $start){
		if(!$wyswietlen){
			return 0;
		}
		$teraz = time();
		$end = $naklad/($wyswietlen/($teraz - $start));
		$end += $teraz;
		return $end;
	}
}
?>