<?php
/**
 * klasa obslugujaca polaczenia i zapytania do bazy danych mysql
 * @author chajr <chajr@bluetree.pl>
 * @package db
 * @version 3.2
 * @copyright chajr/bluetree
 * @final
 * Display <a href="http://sam.zoy.org/wtfpl/COPYING">Do What The Fuck You Want To Public License</a>
 * @license http://sam.zoy.org/wtfpl/COPYING Do What The Fuck You Want To Public License
 */
final class mysql_class{
	/**
	 * informacja o bledzie jesli wystapil
	 * @var string 
	 */
	public $err = NULL;
	/**
	 * id ostatnio dodanego elelemntu
	 * @var integer
	 */
	public $id = NULL;
	/**
	 * ilosc zwruconych przez zapytanie wierszy
	 * @var integer 
	 */
	public $rows = NULL;
	/**
	 * nazwa wybranego polaczenia
	 * @var string 
	 */
	private $connection;
	/**
	 * przechowuje obiekt pobranych danych z bazy
	 * @var object
	 */
	private $result;
	/**
	 * ustawia domyslne polaczenie i wykonuje zapytanie
	 * opcjonalnie mozna zmienic kodowanie dla pojedynczego zapytania
	 * @param string $sql zapytanie do bazy danych
	 * @param string $connection opcjonalnie nazwa polaczenia z jakiego ma kozystac (0-domyslny np. przy zmianie kodowania)
	 * @param string $charset system kodowania znakow
	 * @example new mysql_class('SELECT * FROM tablica')
	 * @example new mysql_class('SELECT * FROM tablica', 'inne_polaczenie')
	 * @example new mysql_class('SELECT * FROM tablica', 0, 'LATIN1')
	 * @uses mysql_class::$connection
	 * @uses mysql_class::query()
	 * @uses mysql_class::set_names()
	 * @uses mysql_connection_class::$default_charset
	 */
	public function __construct($sql, $connection = 'default', $charset = NULL){
		if(!$connection){
			$this->connection = 'default';
		}else{
			$this->connection = $connection;
		}
		if($charset){
			$this->set_names($charset);
		}
		$this->query($sql);
		if($charset){
			$this->set_names(mysql_connection_class::$default_charset);
		}
	}
	/**
	 * zwraca dane, przetworzone do tablicy ($full = 1 - wszystkie pobrane dane) lub jako wynik fetch_assoc() (pojedynczy wiersz)
	 * @param boolean $full informacja czy ma zwracac dane do przetworzenia czy jako tablice
	 * @return array tablica danych 
	 * @uses mysql_class::$ilosc_wierszy
	 * @uses mysql_class::$connections
	 * @uses mysql_class::$connection
	 * @uses mysqli::fetch_assoc()
	 */
	public function result($full = FALSE){
		if($this->rows){
			if($full){
				$arr = array();
				while($array = $this->result->fetch_assoc()){
					if(!$array){
						return NULL;
					}
					$arr[] = $array;
				}
			}else{
				$arr = $this->result->fetch_assoc();
			}
			return $arr;
		}
	}
	/**
	 * zwraca obiekt pobranych danych (instancja mysqli result)
	 * @return object pobranych danych
	 * @uses mysql_class::$result
	 */
	public function returns($name = FALSE){
		return $this->result;
	}
	/**
	 * koduje tresci da zapytania (NUL (ASCII 0), \n, \r, \, ', ", and Control-Z)
	 * @param string $tresc treac do zakodowania
	 * @return string zakodowana tresc
	 */
	public final static function code($tresc){
		$tresc = mysqli_real_escape_string($tresc);
		return $tresc;
	}
	/**
	 * dodaje sekwencje ucieczki do zastrzezonych znakow (& ' " < >)
	 * @param string $tresc treac do zakodowania
	 * @return string zakodowana tresc
	 */
	public final static function entities($tresc){
		$tresc = @htmlspecialchars($tresc);
		return $tresc;
	}
	/**
	 * usuwa sekwence sterujace z zastzrezonych znakow (& ' " < >)
	 * @param string $tresc tresc do dekodowania
	 * @return string zdekodowana tresc
	 */
	public final static function decode($tresc){
		$tresc = @stripcslashes($tresc);
		return $tresc;
	}
	/**
	 * wykonuje zapytanie do bazy danych
	 * @param string $sql zapytanie do bazy
	 * @uses mysql_class::$rows
	 * @uses mysql_connection_class::$connections
	 * @uses mysql_class::$connection
	 * @uses mysql_class::$err
	 * @uses mysql_class::$result
	 * @uses mysql_class::$id
	 * @uses mysqli::$error
	 * @uses mysqli::$insert_id
	 * @uses mysqli::$num_rows
	 * @uses mysqli::query()
	 */
	private function query($sql){
		$bool = mysql_connection_class::$connections[$this->connection]->query($sql);
		if (!$bool){
			$this->err = mysql_connection_class::$connections[$this->connection]->error;
			return;
		}
		if(mysql_connection_class::$connections[$this->connection]->insert_id){
			$this->id = mysql_connection_class::$connections[$this->connection]->insert_id;
		}
		if(!is_bool($bool) && !is_integer($bool)){
			$this->rows = $bool->num_rows;
		}
		$this->result = $bool;
	}
	/**
	 * zmiana kodowania znakow dla zapytania
	 * @param string $charset system kodowania znakow
	 * @uses mysql_class::$connection
	 * @uses mysql_connection_class::$connections
	 * @uses mysqli::query()
	 */
	private function set_names($charset){
		mysql_connection_class::$connections[$this->connection]->query("SET NAMES '$charset'");
	}
}
/**
 * tworzy polaczenie z baza danych i przekazuje je do obiektu obslugo bazy danych
 * @author chajr <chajr@bluetree.pl>
 * @package db
 * @version 1.1.1
 * @copyright chajr/bluetree
 * @final
 * @todo definiowanie innych zestawow kodowych
 * Display <a href="http://sam.zoy.org/wtfpl/COPYING">Do What The Fuck You Want To Public License</a>
 * @license http://sam.zoy.org/wtfpl/COPYING Do What The Fuck You Want To Public License
 */
final class mysql_connection_class extends mysqli{
	/**
	 * informacja o bledzie polaczenia
	 * @var string 
	 */
	public $err;
	/**
	 * przechowuje tablice polaczen (default domyslne)
	 * @var array 
	 */
	static $connections = array();
	/**
	 * domyslne kodowanie dla zapytan
	 * @var string 
	 */
	static $default_charset = 'UTF8';
	/**
	 * tworzy instancje obiektu mysqli i dokonuje polaczenie z baza danch
	 * NAZWA default JEST UZYWANA DLA DOMYSLNEGO POLACZENIA Z BAZA DANYCH!!!!
	 * @param array $config tablica parametrow (host, username, pass, dbName, connName)
	 * @param string $charset nazwa kodowa dla zestawu znakow (domyslnie UTF8)
	 * @return boolean jesli wystapil blad w placzeniu zwraca FALSE i informacje o bledzie w wlasciwosci $err
	 * @example new mysql_connection_class(array('localhost', 'user', 'qw4@#$', 'baza', 'nowe_polaczenie'))
	 * @example new mysql_connection_class(array('localhost', 'user', 'qw4@#$', 'baza'))
	 * @example new mysql_connection_class(array('localhost', 'user', 'qw4@#$', 'baza'), 'LATIN1')
	 * @uses mysql_connection_class::$err
	 * @uses mysql_connection_class::$connections
	 * @uses mysql_connection_class::$default_charset
	 * @uses mysqli::__construct()
	 * @uses mysqli::query()
	 */
	public final function __construct($config, $charset = 'UTF8'){
		self::$default_charset = $charset;
		if(isset($config) && !empty($config)){
			parent::__construct($config[0], $config[1], $config[2], $config[3]);
			if(mysqli_connect_error()){
				$this->err = mysqli_connect_error();
				return FALSE;
			}
			$this->query("SET NAMES '$charset'");
		}
		if(!isset($config[4]) || !$config[4]){
			$config[4] = 'default';
		}
		self::$connections[$config[4]] = $this;
	}
	/**
	 * niszczy wszystkie polaczenia
	 * @uses mysql_connection_class::$connections
	 */
	public final function __destruct() {
		self::$connections = array();
	}
	/**
	 * niszczy wszystkie polaczenia, lub wybrane polaczenia
	 * @param mixed $conn_array tablica polaczen do zniszczenia, lub nazwa polaczenia
	 * @example destruct()
	 * @example destruct('default')
	 * @example destruct(array('polaczenie1', 'polaczenie2'))
	 * @uses mysql_connection_class::$connections
	 */
	static function destruct($conn_array = NULL){
		if($conn_array){
			if(is_array($conn_array)){
				foreach($conn_array as $connection){
					unset(self::$connections[$connection]);
				}
			}else{
				unset(self::$connections[$conn_array]);
			}
		}else{
			self::$connections = array();
		}
	}
}
?>