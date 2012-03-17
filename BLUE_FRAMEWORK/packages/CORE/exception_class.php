<?PHP
/**
 * @author chajr <chajr@bluetree.pl>
 * @version 1.1
 * @access private
 * @copyright chajr/bluetree
 * @package core
 * @subpackage error
 */
 /**
 * glowna klasa dla wszystkich exceptionow
 */
abstract class exception_class extends Exception{
    /**
	 * przechowuje kod bledu
	 * @var string
	 * @access private
	 */
	protected $err_code;
	/**
	 * przechowuje dodatkowe informacje o bledzie, zglaszane razem z kodem
	 * @var string
	 * @access private
	 */
	protected $err_message;
	/**
	 * przechowuje scierzke i nazwe pliku w ktorym wystapil wyjatek
	 * @var string
	 * @access private
	 */
	protected $err_file;
	/**
	 * przechowuje numer linni w ktorej zgloszono wyjatek
	 * @var integer
	 * @access private
	 */
	protected $err_line;
	/**
	 * przechowuje kod bledu z klase exception
	 * @var integer
	 * @access private
	 */
	protected $in_code;
	/**
	 * opcjonalnie nazwa modulu zglaszajacego blad
	 * @var string 
	 */
	protected $mod_name = '';
	/**
	 * pobiera wiadomosci i wpisuje do odpowiednich zmiennych
	 * @uses exception_class::$err_code
	 * @uses exception_class::$err_message
	 * @uses exception_class::$err_file
	 * @uses exception_class::$err_line
	 * @uses exception_class::$in_code
	 * @uses exception_class::$mod_name
	 * @uses Exception::getFile()
	 * @uses Exception::getLine()
	 * @uses Exception::getCode()
	 * @param string $code kod bledu
	 * @param string $message opcjonlanie dodatkowe informacje o bledzie
	 * @param string $mod opcjonalnie nazwa modulu z ktorego pochodzi blad
    */
	public function __construct($code, $message = '', $mod = ''){
		$this->err_code = $code;
		$this->err_message = $message;
		$this->err_file = $this->getFile();
		$this->err_line = $this->getLine();
		$this->in_code = $this->getCode();
		if(!$mod){
			$mod = 'core';
		}
		$this->mod_name = $mod;
	}
	/**
	 * zwraca informacje o bledzie w postaci tekstowej
	 * @return string pelna informacja na temat bledu 
	 * @uses exception_class::show()
	 */
//	public function __toString() {
//		return $this->show();
//	}
	/**
	 * dodaje blad do listy bledow
	 * @param $error object instancja obiektu error_class
	 * @param $mod_name string opcjonalnie nazwa modulu zglaszajacego blad
	 * @abstract
	 */
	abstract public function show(error_class $error, $mod_name = '');
	/**
	 * zwraca tablice informacji o bledzie
	 * @return array tablica informacji o bledzie 
	 */
	public function returns(){
		return array(
			$this->err_code,
			$this->err_message,
			$this->err_file,
			$this->err_line,
			$this->in_code,
			$this->mod_name,
		);
	}
}
/**
 * klasa bledow frameworka, zatrzymuje dzialanie calego frameworka i wyswietla blad
 * dzialanie takie jak w przypadku fatal i critic
 */
class coreException extends exception_class{
	/**
	 * dodaje blad do listy bledow, sprawdzajac przy tym powodujacy go modul
	 * @param $error object instancja obiektu error_class
	 * @param $mod_name string opcjonalnie nazwa modulu zglaszajacego blad
	 * @uses exception_class::$mod_name
	 * @uses exception_class::$in_code
	 * @uses exception_class::$err_code
	 * @uses exception_class::$err_line
	 * @uses exception_class::$err_file
	 * @uses exception_class::$err_message
	 * @uses error_class::add_error()
	 * @uses error_class::log()
	 * @uses core_class::options()
	*/
	public function show(error_class $error, $mod_name = ''){
		if($mod_name){
			$this->mod_name = $mod_name;
		}
		$er = $error->add_error('critic', $this->in_code,  $this->err_code, $this->err_line, $this->err_file, $this->err_message, $this->mod_name);
		$bool = core_class::options('errors_log');
		if((bool)$bool{0}){
			error_class::log('critic_coreException', $er);
		}
	}
	/**
	 * uruchamia klase obslugi bledow, dodaje blad krytyczny frameworka i wyswietla go zatrzymujac dzialanie frameworka
	 * @uses exception_class::$mod_name
	 * @uses exception_class::$in_code
	 * @uses exception_class::$err_code
	 * @uses exception_class::$err_line
	 * @uses exception_class::$err_file
	 * @uses exception_class::$err_message
	 * @uses error_class::add_error()
	 * @uses error_class::render()
	 * @uses error_class::__construct()
	 * @uses error_class::log()
	 * @uses core_class::options()
	 */
	public function show_core(){
		$error = new error_class();
		$er = $error->add_error('critic', $this->in_code,  $this->err_code, $this->err_line, $this->err_file, $this->err_message, $this->mod_name);
		$bool = core_class::options('errors_log');
		if((bool)$bool{0}){
			error_class::log('critic_coreException', $er);
		}
		echo $error->render();
		exit;
	}
}
/**
 * klasa obslugujaca bledy modulu, zatrzymuje dany modul, umozliwia dzialanie pozostalych
 */
class modException extends exception_class{
	/**
	 * dodaje blad do listy bledow, sprawdzajac przy tym powodujacy go modul
	 * @param $error object instancja obiektu error_class
	 * @param $mod_name string opcjonalnie nazwa modulu zglaszajacego blad
	 * @uses exception_class::$mod_name
	 * @uses exception_class::$in_code
	 * @uses exception_class::$err_code
	 * @uses exception_class::$err_line
	 * @uses exception_class::$err_file
	 * @uses exception_class::$err_message
	 * @uses error_class::add_error()
	 * @uses error_class::log()
	 * @uses core_class::options()
	 */
	public function show(error_class $error, $mod_name = ''){
		if($mod_name){
			$this->mod_name = $mod_name;
		}
		$er = $error->add_error('critic', $this->in_code,  $this->err_code, $this->err_line, $this->err_file, $this->err_message, $this->mod_name);
		$bool = core_class::options('errors_log');
		if((bool)$bool{0}){
			error_class::log('critic_modException', $er);
		}
	}
}
/**
 * klasa obslugujaca bledy bibliotek, zatrzymuje dana biblioteke, umozliwia dzialanie pozostalych bibliotek i modulow
 */
class libException extends exception_class{
	/**
	 * dodaje blad do listy bledow, sprawdzajac przy tym powodujacy go modul
	 * @param $error object instancja obiektu error_class
	 * @param $mod_name string opcjonalnie nazwa modulu zglaszajacego blad
	 * @uses exception_class::$mod_name
	 * @uses exception_class::$in_code
	 * @uses exception_class::$err_code
	 * @uses exception_class::$err_line
	 * @uses exception_class::$err_file
	 * @uses exception_class::$err_message
	 * @uses error_class::add_error()
	 * @uses error_class::log()
	 * @uses core_class::options()
	 */
	public function show(error_class $error, $mod_name = ''){
		if($mod_name){
			$this->mod_name = $mod_name;
		}
		$er = $error->add_error('critic', $this->in_code,  $this->err_code, $this->err_line, $this->err_file, $this->err_message, $this->mod_name);
		$bool = core_class::options('errors_log');
		if((bool)$bool{0}){
			error_class::log('critic_libException', $er);
		}
	}
}
/**
 * klasa obslugujaca bledy typu warning
 */
class warningException extends exception_class{
	/**
	 * dodaje warninga do listy bledow
	 * @param $error object instancja obiektu error_class
	 * @param $mod_name string opcjonalnie nazwa modulu zglaszajacego blad
	 * @uses exception_class::$in_code
	 * @uses exception_class::$err_code
	 * @uses exception_class::$err_line
	 * @uses exception_class::$err_file
	 * @uses exception_class::$err_message
	 * @uses error_class::add_error()
	 * @uses error_class::log()
	 * @uses core_class::options()
	 */
	public function show(error_class $error, $mod_name = ''){
		$error->add_error('warning', $this->in_code,  $this->err_code, $this->err_line, $this->err_file, $this->err_message, $mod_name);
		$bool = core_class::options('errors_log');
		if((bool)$bool{1}){
			error_class::log('warning', $er);
		}
	}
}
/**
 * klasa obslugujaca komunikaty typu info
 */
class infoException extends exception_class{
	/**
	 * dodaje informacje do listy
	 * @param $error object instancja obiektu error_class
	 * @param $mod_name string opcjonalnie nazwa modulu zglaszajacego blad
	 * @uses exception_class::$in_code
	 * @uses exception_class::$err_code
	 * @uses exception_class::$err_line
	 * @uses exception_class::$err_file
	 * @uses exception_class::$err_message
	 * @uses error_class::add_error()
	 */
	public function show(error_class $error, $mod_name = ''){
		$error->add_error('info', $this->in_code,  $this->err_code, $this->err_line, $this->err_file, $this->err_message, $mod_name);
	}
}
/**
 * klasa obslugujaca komunikaty typu ok
 */
class okException extends exception_class{
	/**
	 * dodaje informacje do listy
	 * @param $error object instancja obiektu error_class
	 * @param $mod_name string opcjonalnie nazwa modulu zglaszajacego blad
	 * @uses exception_class::$in_code
	 * @uses exception_class::$err_code
	 * @uses exception_class::$err_line
	 * @uses exception_class::$err_file
	 * @uses exception_class::$err_message
	 * @uses error_class::add_error()
	 */
	public function show(error_class $error, $mod_name = ''){
		$error->add_error('ok', $this->in_code,  $this->err_code, $this->err_line, $this->err_file, $this->err_message, $mod_name);
	}
}
/**
 * klasa obslugujaca bledy pakietow
 */
class packageException extends exception_class{
	/**
	 * obluga bledu
	 * @param error_class $error instancja obiektu error_class
	 * @param type $mod_name nazwa modulu zglaszajacego blad
	 */
	public function show(error_class $error, $mod_name = ''){

	}
}
?>