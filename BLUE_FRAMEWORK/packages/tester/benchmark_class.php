<?PHP
/**
 * @author chajr <chajr@bluetree.pl>
 * @version 1.4.1
 * @access private
 * @copyright chajr/bluetree
*/
/**
 * umozliwia testowanie aplikacji pod wzgledem wydajnosci i czasu przetwarzania
 * @package tester
 * @subpackage benchmark
 */
class benchmark_class{
	/**
	 * przechowuje dane aktualnej sesji
	 * @var array tablica danych
	 */
	static $session = array('benchmark');
	/**
	 * uruchamia benchmarka, zapisuje w sesji poczatkowy znacznik czasu
	 * @param boolean $on zapisuje w sesji informacje dla pozostalych metod czy benchamrk wlaczony czy nie
	 * @static
	 */
	public static function start($on = TRUE){
		if($on){
			self::$session['benchmark']['memmory_start'] = memory_get_usage();
			self::$session['benchmark']['benchmark_start'] = self::$session['benchmark']['benchmark_znacznik'] = microtime(TRUE);
			self::$session['benchmark']['benchmark_znaczniki'] = array();
			self::$session['benchmark']['on'] = TRUE;
		}else{
			self::$session['benchmark']['on'] = FALSE;
		}
	}
	/**
	 * wstawia znacznik, zapisuje w sesji czas dzialania do aktualnej pozycji
	 * @param string $nazwa nazwa znacznika
	 * @static
	 */
	public static function znacznik($nazwa){
		if((bool)self::$session['benchmark']['on']){
			$czas_znacznika = microtime(TRUE) - self::$session['benchmark']['benchmark_znacznik'];
			self::$session['benchmark']['benchmark_znacznik'] = microtime(TRUE);
			self::$session['benchmark']['benchmark_znaczniki'][] = array($nazwa, $czas_znacznika, memory_get_usage());
		}
	}
	/**
	 * zatrzymuje dzialanie benchmarka, zapisuje ostatni czas
	 * @static
	 */
	public static function stop(){
		if((bool)self::$session['benchmark']['on']){
			self::$session['benchmark']['benchmark_koniec'] = microtime(TRUE);
		}
	}
	/**
	 * przygotowywuje widok i wyswietla liste znacznikow, ich czasw i wartosci procentowych
	 * @static
	 */
	public static function display(){
		$disp = '';
		if((bool)self::$session['benchmark']['on']){
			$disp = '<div style="
			color: #FFFFFF;
			background-color: #3d3d3d;
			border-color: #FFFFFF;
			border-width: 1px;
			border-style: solid;
			margin-left: auto;
			margin-right: auto;
			width: 90%;
			text-align: center;
			margin-bottom:25px;
			margin-top:25px;
			">';
			$total = (self::$session['benchmark']['benchmark_koniec'] - self::$session['benchmark']['benchmark_start'])*1000;
			$czas = number_format($total, 5, '.', ' ');
			$ram = memory_get_usage()/1024;
           	$disp .= 'Całkowity czas aplikacji: '.$czas.' ms&nbsp;&nbsp;&nbsp;&nbsp;Całkowite zuzycie pamięci: '.number_format($ram, 3, ',', '').' kB<br /><br />';

			$disp .= 'Czasy znaczników:<br /><table style="width:100%">'."\n";
           	foreach(self::$session['benchmark']['benchmark_znaczniki'] as $znacznik){
               	$czas = number_format($znacznik[1]*1000, 5, '.', ' ');
				$procent = ($znacznik[1]/$total)*100000;
				$procent = number_format($procent, 5);
				$ram = ($znacznik[2] - self::$session['benchmark']['memmory_start'])/1024;
				$disp .= '<tr>
					<td style="width:40%">'.$znacznik[0].'</td>'."\n";
				$disp .= '<td style="width:20%">'.$czas.' ms</td>'."\n";
				$disp .= '<td style="width:20%">'.$procent.' %</td>'."\n";
				$disp .= '<td style="width:20%">'.number_format($ram, 3, ',', '').' kB</td>
					</tr>'."\n";
			}
			$disp .= '</table></div>';
		}
		return $disp;
	}
}
?>