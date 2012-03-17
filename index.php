<?PHP
/**
 * @author chajr <chajr@bluetree.pl>
 * @version 2.1.1
 * @access private
 * @copyright chajr/bluetree
*/
starter_class::load('packages/tester/benchmark_class.php');
benchmark_class::start();
$bool = starter_class::load('packages/CORE/error_class.php');
if(!$bool){
	die ('missing error_class :(');
}
ob_start('fatal');
if(!isset($_SESSION)){
	session_start();
}
benchmark_class::znacznik('start obslugi bledow');
starter_class::run();
benchmark_class::znacznik('koniec');
ob_end_flush();
benchmark_class::stop();
echo benchmark_class::display();
/**
 * startowanie frameworka
 * @fianl
 * @package start
 */
final class starter_class{
	/**
	 * uruchamia frameworka i wyświetla tresc / bledy
	 * laduje klase obslugi bledow, start buforowania, ustawia obsluge bledow, uruchamia jadro frameworka, wyswietla tresc, lub bledy
	 * @final
	 * @uses starter_class::pack()
	 * @uses starter_class::load()
	 * @uses core_class::$render
	 * @throws coreException core_error_0, core_error_1
    */
	static final function run(){
		error_reporting (E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_STRICT | E_ALL);
		set_error_handler ("error");
		try{
			$bool = starter_class::package('CORE');
			benchmark_class::znacznik('zaladowanie pakietu core');
			if(!$bool){
				throw new coreException('core_error_0');
			}
			$core = new core_class();
			if($core->render !== NULL && !empty($core->render) && $core->render){
				echo $core->render;
				unset($core->render);
			}else{
				throw new coreException ('core_error_1');
			}
       	}catch (coreException $error_core){
			$error_core->show_core();
       	}
	}
	/**
	 * umozliwia ladowanie plikow z frameworka, zwraca inf o zaladowaniu, zawartosc pliku badz wskazana zmienna
	 * @example load('cfg/config')
	 * @example load('elements/layouts/index.html', TRUE) - odczyt zawartosci
	 * @example load('cfg/lang/core_pl.php', 'content') - odczytuje podana zmienna
	 * @example load('cfg/lang/packages/CORE/core_class.php', 0, 'require') - wczytuje plik tak aby byla mozliwosc jego ponownego wczytania
	 * @param string $path scierzka do pliku
	 * @param mixed $read jesli TRUE plik ma zostac odczytany, jesli string zwraca zmienna podaja jako string
	 * @param string $type typ zaladowania pliku (default - require_once)
	 * @return mixed zwraca zawartosc pliku, wskazana zmienna badz inf czy plik zostal zaladowany
	 * @uses starter_class::path()
	 * @final
	 * @static
	 */
	static final function load($path, $read = FALSE, $type = ''){
		$path2 = $path;
		$path = self::path().$path;
		if(!file_exists($path)){
           	return FALSE;
		}
		if($read){
			if($read === TRUE){
				return file_get_contents($path);
           	}else{
				include ($path);
				if(isset($$read)){
					return $$read;
				}else{
					return FALSE;
				}
			}
		}
		switch($type){
			case'require':
				require($path);
				break;
			case'include':
				include ($path);
				break;
			case'include_once':
				include_once ($path);
				break;
			default:
				require_once ($path);
				break;
       	}
		return TRUE;
	}
	/**
	 * umozliwia ladowanie pakietow (calosc plikow jesli sama nazwa pakietu, lub okreslone pliki po przecinku)
	 * laduje tylko pliki z dopiskiem _class, przy podawaniu nazw tylko czlon poczatkowy bez _class
	 * @example pack('CORE') - caly pakiet
	 * @example pack('CORE/core_class') - pojedyncza biblioteka
	 * @example pack('CORE/core_class,error_class') - kilka bibliotek
	 * @param string $pack nazwa pakietu do zaladowania, lubo okreslone pliki
	 * @return mixed zwraca liste zaladoawnych plikow, badz false w przypadku bledu 
	 * @uses starter_class::load()
	 * @final
	 * @static
	 */
	static final function package($pack){
		$preg = preg_match('#^[\w-]+\/([\w-]+[,]?)+$#', $pack);
		if($preg){
			$pack = explode('/', $pack);
			$list = explode(',', $pack[1]);
			$pack = $pack[0];
		}
		$uchwyt = opendir(self::path('packages').$pack);
		if($uchwyt){
			$tab = array();
			while($plik = readdir($uchwyt)){
				$typ = preg_match('#^[\\w-]+(_(class|interface)\.php){1}$#', $plik);
				if($typ){
					$short = str_replace('_class.php', '', $plik);
					$short = str_replace('_interface.php', '', $short);
					if($preg){
						$bool = in_array($short, $list);
                       	if(!$bool){
							continue;
						}
                   	}
					$bool = self::load('packages/'.$pack.'/'.$plik);
					$tab[] = $short;
					if(!$bool){
						closedir($uchwyt);
						return FALSE;
					}
				}
			}
			closedir($uchwyt);
		}
		return $tab;
	}
	/**
	 * zwraca scierzke glowna
	 * zwraca dirname(__FILE__).'/BLUE_FRAMEWORK/ lub + scierzka podana w parametrze + / lub scierzke glowna
	 * @example path() - zwraca scierzke do katalogu frameworka
	 * @example path('elements/layouts'); - zwraca podana scierzke w katalogu frameworka
	 * @example path(1) - zwraca glowna scierzke dla pliku index.php
	 * @param mixed $pack jesli string tworzy scierzke wedlug wskazania, jesli brak (0) glowny katalog frameworka, lub gdy 1 glowna scierzke dla index.php
	 * @return string zwraca kompletna scierzke
	 * @final
	 * @static
	 */
	static final function path($pack = 0){
		if($pack === 1){
			return dirname(__FILE__);
		}elseif($pack){
			$pack .= '/';
		}else{
			$pack = '';
		}
		return dirname(__FILE__).'/BLUE_FRAMEWORK/'.$pack;
	}
}
?>