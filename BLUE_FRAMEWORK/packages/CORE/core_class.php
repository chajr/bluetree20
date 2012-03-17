<?PHP
/**
 * jadro frameworka
 * uruchamia biblioteki, umozliwia miedzy nimi wymiane danych, zwraca gotowa tresc
 * wszystkie biblioteki frameworka sa czescia tej biblioteki i wniej sa uruchamiane
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 2.2.1
 * @copyright chajr/bluetree
 * @final
 */
final class core_class{
	/**
	 * przechowuje wszystkie opcje frameworka do wykozystania
	 * @var array
	 * @access private
	 * @static
    */
	private static $options = array();
	/**
	 * na koncu zapisuje w niej gotowa tresc do zwrotu
	 * @var string
	 * @access public
    */
	public $render = '';
	/**
	 * przechowuje obiekt GET
	 * @var object
	 * @access private
    */
	private $get;
	/**
	 * przechowuje obiekt POST
	 * @var object
	 * @access private
    */
	private $post;
	/**
	 * przechowuje obiekt COOKIE
	 * @var object
	 * @access private
    */
	private $cookie;
	/**
	 * przechowuje obiekt SESSION
	 * @var object
	 * @access private
    */
	private $session;
	/**
	 * przechowuje obiekt FILES
	 * @var object
	 * @access private
    */
	private $files;
	/**
	 * obiekt drzewa strony
	 * @var object
	 */
	private $tree;
	/**
	 * obiekt display
	 * @var object
	 */
	private $display;
	/**
	 * obiekt obslugi metatagow i tytulow strony
	 * @var object
	 */
	private $meta;
	/**
	 * obiekt loadera
	 * @var object
	 */
	private $loader;
	/**
	 * obiekt obslugi bledow
	 * @var object
	 */
	private $error;
	/**
	 * uruchamia biblioteki frameworka
	 * @uses option_class::load()
	 * @uses core_class::$options
	 * @uses core_class::$get
	 * @uses core_class::$post
	 * @uses core_class::$cookie
	 * @uses core_class::$session
	 * @uses core_class::$files
	 * @uses core_class::$display
	 * @uses core_class::$meta
	 * @uses core_class::$render
	 * @uses core_class::$lang
	 * @uses core_class::$error
	 * @uses core_class::$loader
	 * @uses globals_class::destroy();
	 * @uses meta_class::render()
	 * @uses get::typ()
	 * @uses get::full_get()
	 * @uses get::destroy()
	 * @uses session::run()
	 * @uses session::set_session()
	 * @uses tree_class::$layout
	 * @uses display_class::other()
	 * @uses display_class::render()
	 * @uses display_class::generate()
	 * @uses error_class::render()
	 * @uses cookie::set_cookie()
    */
	public function __construct(){
		self::$options = option_class::load();
		benchmark_class::znacznik('zaladowanie opcji');
		if(self::$options['techbreak']){
			$break = starter_class::load('elements/layouts/techbreak.html', TRUE);
			if(!$break){
				echo 'Technical BREAK';
			}else{
				echo $break;
			}
			exit;
		}
		if(self::$options['timezone']){
			@date_default_timezone_set(self::$options['timezone']);
		}
		$this->get = new get();
		benchmark_class::znacznik('powolanie obiektu obslugi get');
		$this->post = new post();
		benchmark_class::znacznik('powolanie obiektu obslugi post');
		$this->lang = new lang_class($this->get->get_lang());
		benchmark_class::znacznik('powolanie obiektu obslugi jezyka');
		$this->error = new error_class($this->lang);
		benchmark_class::znacznik('powolanie obiektu obslugi bledow');
		if($this->get->typ() == 'html'){
			$this->session = new session();
			benchmark_class::znacznik('powolanie obiektu obslugi session');
			$this->cookie = new cookie();
			benchmark_class::znacznik('powolanie obiektu obslugi cookie');
			$this->files = new files();
			benchmark_class::znacznik('powolanie obiektu obslugi uploudu plikow');
			globals_class::destroy();
			benchmark_class::znacznik('niszczenie tablic globalnych');
			$this->tree = new tree_class($this->get->full_get(1), $this->lang->lang);
			benchmark_class::znacznik('powolanie obiektu obslugi drzewa strony');
			$this->display = new display_class($this->tree->layout, $this->get, $this->session,
					$this->lang->lang, $this->tree->css, $this->tree->js);
			benchmark_class::znacznik('powolanie obiektu display');
			$this->meta = new meta_class($this->get->full_get(1));
			benchmark_class::znacznik('powolanie obiektu obslugi znacznikow meta');
			$this->loader = new loader_class($this->tree, $this->display, $this->lang, $this->meta, 
					$this->get, $this->post, $this->cookie, $this->session, $this->files, $this->error);
			$this->display->block = $this->loader->return_block();
			benchmark_class::znacznik('ladowanie modulow i bibliotek, oraz ich uruchomienie');
			$this->meta->render($this->display);
			benchmark_class::znacznik('rendering danych meta');
			$error_arr = $this->error->render(1);
			if($error_arr['pointer']){
				foreach($error_arr['pointer'] as $point_error){
					$this->display->generate($point_error['point'], $point_error['content'], $point_error['mod']);
				}
			}
			if($error_arr['critic']){
				$this->display->generate('core_error', $error_arr['critic']);
			}
			if($error_arr['warning']){
				$this->display->generate('core_warning', $error_arr['warning']);
			}
			if($error_arr['info']){
				$this->display->generate('core_info', $error_arr['info']);
			}
			if($error_arr['ok']){
				$this->display->generate('core_ok', $error_arr['ok']);
			}
		}else{
			$this->display = new display_class(NULL, $this->get, NULL, $this->lang->lang, NULL, NULL);
			benchmark_class::znacznik('powolanie obiektu display');
			$this->display->other();
			benchmark_class::znacznik('wygenerowanie css/js');
			benchmark_class::$session['benchmark']['on'] = FALSE;
		}
		$this->lang->set_array();
		benchmark_class::znacznik('ustawanie tablicy jezyka');
		$this->lang->translate($this->display);
		benchmark_class::znacznik('tulmaczenie');
		$this->render = $this->display->render();
		benchmark_class::znacznik('rendering');
		if(empty($this->render) && ($this->get->typ() == 'css' || $this->get->typ() == 'js')){
			if($this->get->typ() == 'js'){
				$this->render = '(function(){}())';
			}elseif($this->get->typ() == 'css'){
				$this->render = 'html{}';
			}
		}
		if($this->get->typ() == 'html'){
			globals_class::destroy();
			benchmark_class::znacznik('niszczenie tablic globalnych');
			$this->session->set_session();
			benchmark_class::znacznik('ustawianie danych w sesji');
			$this->cookie->set_cookies();
			benchmark_class::znacznik('ustawianie danych cookie');
		}
   	}
	/**
	 * zwraca opcje frameworka, podana opcje, lub cala liste
	 * @param string $name nazwa opcji do pobrania, lub brak jesli ma pobrac cala liste
	 * @return mixed zwraca wartosc opcji (jesli barka zwraca NULL), lub tablice opcji (jesli brak pusta tablice)
	 * @uses core_class::$options
	 */
	public static function options($name = FALSE){
		if($name){
			if(!isset(self::$options[$name])){
				return NULL;
			}
			return self::$options[$name];
		}
		return self::$options; 
	}
}
?>