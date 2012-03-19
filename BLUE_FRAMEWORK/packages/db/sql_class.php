<?PHP
/**
 * klasa obslugujaca polaczenia i zapytania do wszystkich baz danych z pakietu db
 * @author chajr <chajr@bluetree.pl>
 * @package db
 * @version 0.1
 * @copyright chajr/bluetree
 * @final
 * Display <a href="http://sam.zoy.org/wtfpl/COPYING">Do What The Fuck You Want To Public License</a>
 * @license http://sam.zoy.org/wtfpl/COPYING Do What The Fuck You Want To Public License
 */
final class sql_class {
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
	static $connections = array();
	static $default_charset = 'UTF8';
	public function __construct(){
		
	}
	public function full_result(){
		
	}
	public function result(){
		
	}
	public function returns(){
		
	}
	static function set_connection(){
		
	}
}
?>