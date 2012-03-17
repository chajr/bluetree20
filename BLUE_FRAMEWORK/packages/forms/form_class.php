<?php
/**
 * tworzy formularz, oraz obsluguje wszelkie operacje na nim
 * @author chajr <chajr@bluetree.pl>
 * @package form
 * @version 0.7
 * @copyright chajr/bluetree
 */
class form_class {
	/**
	 * przechowuje komunikaty o bledach z klasy
	 * @var string 
	 */
	public $error = NULL;
	/**
	 * tablica zbednych atrybutow
	 * @var array
	 */
	public $attributes = array(
		'valid_dependency', 'other_val', 'valid_type', 'js', 'minlength', 'check_val', 'check_field', 'escape', 'entities', 'dynamic'
	);
	/**
	 * lista bledow inputow
	 * @var array
	 */
	public $error_list = array();
	/**
	 * obiekt xml ze strona do przetwarzania
	 * @var xmlobject
	 * @access protected
	 */
	protected $main;
	/**
	 * przechowuje obiekt klasy xml
	 * @var object 
	 * @access protected
	 */
	protected $xml;
	/**
	 * czy ma walidowac formularz, TUE tak, FALSE nie
	 * @var boolean 
	 * @access protected
	 */
	protected $valid = TRUE;
	/**
	 * informacja o wystapieniu bledow w formularzu, jesli FALSE brak, TRUE jest blad
	 * @var boolean 
	 * @access protected
	 */
	protected $input_error = FALSE;
	/**
	 * przechowuje zmodyfikowany formularz do wyswietlenia
	 * @var object 
	 * @access protected
	 */
	protected $display;
	/**
	 * przechowuje aktualnie przetwarzany element formularza
	 * @var xml_object 
	 * @access protected
	 */
	protected $current_input;
	/**
	 * przechowuje tablice dodatkowych definicji inputow
	 * @var array 
	 */
	protected $list_definition;
	/**
	 * przechowuje liste wszystkich inputow
	 * @var array 
	 */
	protected $all_inputs;
	/**
	 * przechowuje numer indexu radio w tablicy list definition
	 * @var integer 
	 */
	protected $radio_index_number = 0;
	/**
	 * tworzy obiekt formularza, sprawdza czy istnieje, wczytuje definicje formularza i pobiera glowny wezel
	 * @param string $mod nazwa modulu z formularzem
	 * @param string $form nazwa definicji formularza
	 * @uses form_class::$error
	 * @uses form_class::$main
	 * @uses form_class::$valid
	 * @uses xml_class::$documentElement
	 * @uses xml_class::$err
	 * @uses form_class::get_scripts()
	 * @uses starter_class::path()
	 * @uses xml_class::__construct()
	 * @uses xml_class::wczytaj()
	 * @uses xml_class::getAttribute()
	 */
	public function __construct($mod, $form, $list_definition = NULL){
		$this->list_definition = $list_definition;
		$definition = starter_class::path('modules/'.$mod.'/layouts').$form.'.xml';
		if(!file_exists($definition)){
			$this->error = 'definition_none_exist';
			return FALSE;
		}
		$this->xml = new xml_class();
		$bool = $this->xml->wczytaj($definition, 0);
		if(!$bool){
			$this->error = $xml->err;
			return FALSE;
		}
		$this->main = $this->xml->documentElement;
		$valid = $this->main->getAttribute('novalidate');
		if($valid == 'novalidate'){
			$this->valid = FALSE;
		}
	}
	/**
	 * wyswietla glowny formularz (do dodawania, oraz edycji wpisu, wraz z ewentualnymi bledami)
	 * @return string zwraca pelny formularz do wyswietlenia
	 * @uses form_class::$display
	 * @uses form_class::$xml
	 * @uses form_class::$current_input
	 * @uses form_class::$all_inputs
	 * @uses form_class::update_attr()
	 * @uses form_class::check_list()
	 * @uses form_class::transform_text()
	 * @uses form_class::transform_number()
	 * @uses form_class::transform_hidden()
	 * @uses form_class::transform_color()
	 * @uses form_class::transform_date()
	 * @uses xml_class::getElementsByTagName()
	 * @uses xml_class::getAttribute()
	 * @uses xml_class::zapisz()
	 * 
	 * @todo wyswietlenie pol dynamicznych wraz z wartosciami w przypadku bledu
	 * 
	 */
	public function display_form(){
		$this->display = clone $this->xml;
		$this->all_inputs = $this->display->getElementsByTagName('input');
		foreach($this->all_inputs as $index => $this->current_input){
			$this->update_attr($index);
			$type = $this->current_input->getAttribute('type');
			switch($type){
				case'text': case'password': case'search': case'tel': case'url':
					$this->transform_text();
					break;
				case'number': case'hidden': case'range':
					$this->transform_number();
					break;
				case'email':
					$this->transform_email();
					break;
				case'color': case'date': case'datetime': case'datetime-local': case'time': case'week':
					$this->transform_color();
					break;
				case'checkbox': case'radio':
					$this->transform_checkbox();
					break;
				default:
					break;
			}
		}
		//$this->check_list();
		$form = $this->display->zapisz(0, 1);
		$form = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $form);
		$form = preg_replace('#<!DOCTYPE root SYSTEM "[\\w/_-]+\.dtd">#ui', '', $form);
		return $form;
	}
	/**
	 * walidacja inputow z formularza
	 * w pierwszej kolejnosci uaktualnia inputy, potem sprawdza zaleznosc, wymaganie, wzorzec, dlugosc typ i pole, 
	 * dalej wedlug typu inputa. typ wedlug inputa jesli pattern i valid_type bylo puste
	 * @param object $list obiekt danych post 
	 * @return boolean jesli true walidacja przeszla pomyslnie, lub nie jest wymagana, jesli false wystapily bledy
	 * @uses form_class::$valid
	 * @uses form_class::$current_input
	 * @uses form_class::$input_error
	 * @uses form_class::$main
	 * @uses form_class::$xml
	 * @uses form_class::$all_inputs
	 * @uses form_class::update_attr()
	 * @uses form_class::base_validation()
	 * @uses form_class::update_error_node()
	 * @uses form_class::add_class()
	 * @uses xml_class::getElementsByTagName()
	 * @uses xml_class::getAttribute()
	 * @todo mozliwa konwersja jezykowa kodow
	 */
	public function valid(post $list){
		if(!$this->valid){
			return TRUE;
		}
		$this->all_inputs = $this->xml->getElementsByTagName('input');
		foreach($this->all_inputs as $index => $this->current_input){
			$name = $this->current_input->getAttribute('name');
			if(!$name){
				continue;
			}
			$this->update_attr($index);
			$valid = $this->current_input->getAttribute('formnovalidate');
			if($valid == 'off'){
				continue;
			}
			if($this->chk_dynamic()){
				$name = preg_replace('#[\[\]]#', '', $name);
			}else{
				$val = trim($list->$name);
				$this->base_validation($val, $list);
				$this->switch_valid($list, $val, $name);
			}
//			if(preg_match('#^[\p{L}\\d_-]+\[\]$#iu', $name)){
//				$name = preg_replace('#[\[\]]#', '', $name);
//				if(!in_array($name, $this->dont_check)){
//					foreach($list->$name as $value){
//						$this->base_validation($value, $list);
//					}
//					$this->dont_check[] = $name;
//					$val = $list->$name;
//				}
//			}else{
				
//			}
			$this->update_error_node($name, $list);
		}
		if($this->input_error){
			$this->add_class($this->main, 'form_error');
			return FALSE;
		}
		return TRUE;
	}
	/**
	 * podstawowa walidacja pola, taka sama dla wszystkich pol formularza
	 * jesli zostanie okreslony require i valid dependency to po wypelnieniu nadrzednego pola, pole sprawdzane stanie sie wymagane
	 * @param mixed $val wartosc do sprawdzenia
	 * @param object $list obiekt danych post do sprawdzenia
	 * @return void
	 * @uses form_class::valid_dependency()
	 * @uses form_class::valid_require()
	 * @uses form_class::valid_pattern()
	 * @uses form_class::valid_length()
	 * @uses form_class::valid_type()
	 * @uses form_class::valid_field()
	 */
	protected function base_validation($val, post $list){
		
		//sprawdzanie atrybutu disabled, jesli disabled nie przetwarza pola
		
		$bool = $this->valid_dependency($list);
		if($bool){
			return;
		}
		$bool = $this->valid_require($val);
		if($bool){
			return;
		}
		$this->valid_pattern($val);
		$this->valid_length($val);
		$this->valid_type($val);
		$this->valid_field();
	}
	/**
	 * przelacza walidacje dla konkretnych typow inputow
	 * @param object $list obiekt danych post 
	 * @param mixed $val dane do sprawdzenia
	 * @param string $name nazwa danych z post
	 * @uses form_class::$current_input
	 * @uses form_class::valid_text()
	 * @uses form_class::valid_number()
	 * @uses form_class::valid_email()
	 * @uses form_class::valid_color()
	 * @uses form_class::valid_date()
	 * @uses form_class::valid_datetime()
	 * @uses form_class::valid_month()
	 * @uses form_class::valid_tel()
	 * @uses form_class::valid_time()
	 * @uses form_class::valid_url()
	 * @uses form_class::valid_week()
	 * @uses form_class::valid_checkbox()
	 * @uses xml_class::getAttribute()
	 */
	private function switch_valid(post $list, $val, $name){
		if(isset($list->$name) && $val){
			switch($this->current_input->getAttribute('type')){
				case'text': case 'hiden': case'password': case'search':
					$this->valid_text($val);
					break;
				case'number': case'range':
					$this->valid_number($val);
					break;
				case'email':
					$this->valid_email($val);
					break;
				case'color':
					$this->valid_color($val);
					break;
				case'date':
					$this->valid_date($val);
					break;
				case'datetime': case'datetime-local':
					$this->valid_datetime($val);
					break;
				case'month':
					$this->valid_month($val);
					break;
				case'tel':
					$this->valid_tel($val);
					break;
				case'time':
					$this->valid_time($val);
					break;
				case'url':
					$this->valid_url($val);
					break;
				case'week':
					$this->valid_week($val);
					break;
				case'checkbox': case'radio':
					$this->valid_checkbox($val);
					break;
				default:
					break;
			}
		}
	}
	/**
	 * walidacja inputa typu text
	 * @param mixed $val wartosc do sprawdzenia
	 */
	private function valid_text($val){
		//input typu text ma pelna liste znakow i nie nie weryfikuje ich
	}
	/**
	 * walidacja checkboxa
	 * @param mixed $val wartosc do sprawdzenia
	 * @uses form_class::valid_range()
	 * @uses form_class::valid_step()
	 */
	private function valid_checkbox($val){
		$this->valid_range($val);
		$this->valid_step($val);
	}
	/**
	 * walidacja inputa typu number
	 * sprawdza jesli wartosci z artybutow pattern, valid_type byly puste
	 * @param float $val wartosc do sprawdzenia
	 * @uses form_class::$current_input
	 * @uses xml_class::getAttribute()
	 * @uses simpleValid_class::valid()
	 * @uses form_class::set_error()
	 * @uses form_class::valid_range()
	 * @uses form_class::valid_step()
	 */
	private function valid_number($val){
		$name = $this->current_input->getAttribute('name');
		if(!$this->current_input->getAttribute('pattern') && !$this->current_input->getAttribute('valid_type')){
			$bool = simpleValid_class::valid($val, 'numeric');
			if(!$bool){
				$this->set_error($name, 'numeric');
			}
		}
		$this->valid_range($val);
		$this->valid_step($val);
	}
	/**
	 * przeprowadza walidacje adresu e-mail
	 * sprawdza jesli wartosci z artybutu pattern jest pusta
	 * @param string $val wartosc do sprawdzenia
	 * @uses form_class::$current_input
	 * @uses xml_class::getAttribute()
	 * @uses simpleValid_class::valid()
	 * @uses form_class::set_error()
	 */
	private function valid_email($val){
		$name = $this->current_input->getAttribute('name');
		if(!$this->current_input->getAttribute('pattern')){
			$bool = simpleValid_class::valid($val, 'mail');
			if(!$bool){
				$this->set_error($name, 'mail');
			}
		}
	}
	/**
	 * przeprowadza walidacje adresu url
	 * sprawdza jesli wartosci z artybutu pattern jest pusta
	 * @param string $val wartosc do sprawdzenia
	 * @uses form_class::$current_input
	 * @uses xml_class::getAttribute()
	 * @uses simpleValid_class::valid()
	 * @uses form_class::set_error()
	 */
	private function valid_url($val){
		$name = $this->current_input->getAttribute('name');
		if(!$this->current_input->getAttribute('pattern') && !$this->current_input->getAttribute('valid_type')){
			$bool = simpleValid_class::valid($val, 'url_full');
			if(!$bool){
				$this->set_error($name, 'url_full');
			}
		}
	}
	/**
	 * przeprowadza walidacje koloru
	 * sprawdza jesli wartosci z artybutu pattern jest pusta
	 * @param mixed $val wartosc do sprawdzenia
	 * @uses form_class::$current_input
	 * @uses xml_class::getAttribute()
	 * @uses simpleValid_class::valid()
	 * @uses form_class::set_error()
	 * @uses form_class::valid_range()
	 */
	private function valid_color($val){
		$name = $this->current_input->getAttribute('name');
		if(!$this->current_input->getAttribute('pattern')){
			$bool = simpleValid_class::valid($val, 'hex_color');
			if(!$bool){
				$this->set_error($name, 'hex_color');
			}
		}
		$this->valid_range($val);
	}
	/**
	 * przeprowadza walidacje daty
	 * sprawdza jesli wartosci z artybutu pattern i valid_type jest pusta
	 * @param string $val wartosc do sprawdzenia
	 * @uses form_class::$current_input
	 * @uses xml_class::getAttribute()
	 * @uses simpleValid_class::valid()
	 * @uses form_class::set_error()
	 * @uses form_class::valid_date_range()
	 */
	private function valid_date($val){
		$name = $this->current_input->getAttribute('name');
		if(!$this->current_input->getAttribute('pattern') && !$this->current_input->getAttribute('valid_type')){
			$bool = simpleValid_class::valid($val, 'date');
			if(!$bool){
				$this->set_error($name, 'date');
			}
		}
		$this->valid_date_range($val);
	}
	/**
	 * przeprowadza walidacje daty wraz numerem tygodnia
	 * sprawdza jesli wartosci z artybutu pattern i valid_type jest pusta
	 * @param string $val wartosc do sprawdzenia
	 * @uses form_class::$current_input
	 * @uses xml_class::getAttribute()
	 * @uses simpleValid_class::valid()
	 * @uses form_class::set_error()
	 * @uses simpleValid_class::range()
	 */
	private function valid_week($val){
		$val = str_replace('W', '', $val);
		$list = explode('-', $val);
		$name = $this->current_input->getAttribute('name');
		if(!$this->current_input->getAttribute('pattern') && !$this->current_input->getAttribute('valid_type')){
			$bool = simpleValid_class::valid($val, 'week');
			if(!$bool){
				$this->set_error($name, 'week');
			}
		}
		$min = $this->current_input->getAttribute('min');
		$max = $this->current_input->getAttribute('max');
		if($min){
			$min = explode('-', $min);
			$bool = simpleValid_class::range($list[0], $min[0], NULL);
			if(!$bool){
				$this->set_error($name, 'range_min_year');
			}
			$bool = simpleValid_class::range($list[1], $min[1], NULL);
			if(!$bool){
				$this->set_error($name, 'range_min_week');
			}
		}
		if($max){
			$max = explode('-', $max);
			$bool = simpleValid_class::range($list[0], NULL, $max[0]);
			if(!$bool){
				$this->set_error($name, 'range_max_year');
			}
			$bool = simpleValid_class::range($list[1], NULL, $max[1]);
			if(!$bool){
				$this->set_error($name, 'range_max_week');
			}
		}
	}
	/**
	 * przeprowadza walidacje czasu
	 * sprawdza jesli wartosci z artybutu pattern i valid_type jest pusta
	 * @param string $val wartosc do sprawdzenia
	 * @uses form_class::$current_input
	 * @uses xml_class::getAttribute()
	 * @uses simpleValid_class::valid()
	 * @uses form_class::set_error()
	 * @uses form_class::valid_date_range()
	 */
	private function valid_time($val){
		$name = $this->current_input->getAttribute('name');
		if(!$this->current_input->getAttribute('pattern') && !$this->current_input->getAttribute('valid_type')){
			$bool = simpleValid_class::valid($val, 'time');
			if(!$bool){
				$this->set_error($name, 'time');
			}
		}
		$this->valid_date_range($val);
	}
	/**
	 * przeprowadza walidacje miesiaca z rokiem
	 * sprawdza jesli wartosci z artybutu pattern i valid_type jest pusta
	 * @param string $val wartosc do sprawdzenia
	 * @uses form_class::$current_input
	 * @uses xml_class::getAttribute()
	 * @uses simpleValid_class::valid()
	 * @uses form_class::set_error()
	 * @uses form_class::valid_date_range()
	 */
	private function valid_month($val){
		$name = $this->current_input->getAttribute('name');
		if(!$this->current_input->getAttribute('pattern') && !$this->current_input->getAttribute('valid_type')){
			$bool = simpleValid_class::valid($val, 'month');
			if(!$bool){
				$this->set_error($name, 'month');
			}
		}
		$this->valid_date_range($val);
	}
	/**
	 * sprawdza zakres wartosci dla daty i czasu
	 * @param string $val czas do sprawdzenia
	 * @uses form_class::$current_input
	 * @uses form_class::set_error()
	 * @uses xml_class::getAttribute()
	 * @uses simpleValid_class::range()
	 */
	private function valid_date_range($val){
		$val = strtotime($val);
		$name = $this->current_input->getAttribute('name');
		if(!$val){
			$this->set_error($name, 'date_range_conversion');
		}
		$max = $this->current_input->getAttribute('max');
		$max = strtotime($max);
		if($max){
			$bool = simpleValid_class::range($val, NULL, $max);
			if(!$bool){
				$this->set_error($name, 'range_max');
			}
		}
		$min = $this->current_input->getAttribute('min');
		$min = strtotime($min);
		if($min){
			$bool = simpleValid_class::range($val, $min, NULL);
			if(!$bool){
				$this->set_error($name, 'range_min');
			}
		}
	}
	/**
	 * przeprowadza walidacje numeru telefonu
	 * sprawdza jesli wartosci z artybutu pattern i valid_type jest pusta
	 * @param mixed $val wartosc do sprawdzenia
	 * @uses form_class::$current_input
	 * @uses xml_class::getAttribute()
	 * @uses simpleValid_class::valid()
	 * @uses form_class::set_error()
	 */
	private function valid_tel($val){
		$name = $this->current_input->getAttribute('name');
		if(!$this->current_input->getAttribute('pattern') && !$this->current_input->getAttribute('valid_type')){
			$bool = simpleValid_class::valid($val, 'phone');
			if(!$bool){
				$this->set_error($name, 'phone');
			}
		}
	}
	/**
	 * sprawdza zaleznosc miedzy inputami
	 * jesli input wypelniony wymusza sprawdzanie innych inputow z listy
	 * @param object $list obiekt danych post do sprawdzenia
	 * @return boolean jesli true pole puste i nie wymaga sprawdzania innych, jesli false kontynuuje sprawdzanie 
	 * @uses form_class::$current_input
	 * @uses xml_class::getAttribute()
	 * @uses core_class::options()
	 */
	private function valid_dependency(post $list){
		
		//i co dalej z tym pierdolonym radio
		//sprawdza czy radio, jesli tak i jesli $radio_index_number = 0 pobiera wartosc atrybutu valid_dependency
		//zapisuje wartosc w specjalnej zmiennej, tak aby mozna bylo go wiazac z innymi elementami radio
		//jesli $radio_index_number = 0 czysci wartosc zmiennej
		
		$dependency = $this->current_input->getAttribute('valid_dependency');
		if($dependency){
			$sep = core_class::options('param_sep');
			$dependency = explode($sep, $dependency);
			foreach($dependency as $element){
				if(!isset($list->$element) || !$list->$element){
					return TRUE;
				}
			}
		}
		return FALSE;
	}
	/**
	 * sprawdza krok dla pol numerycznych
	 * @param float $val wartosc do sprawdzenia
	 * @uses form_class::$current_input
	 * @uses form_class::set_error()
	 * @uses xml_class::getAttribute()
	 * @uses simpleValid_calss::step()
	 */
	private function valid_step($val){
		$step = $this->current_input->getAttribute('step');
		if(($step || $step == 0) && $step != ''){
			$name = $this->current_input->getAttribute('name');
			$default = $this->current_input->getAttribute('value');
			$check = simpleValid_class::step($step, $val, $default);
			if(!$check){
				$this->set_error($name, 'step');
			}
		}
	}
	/**
	 * sprawdza czy input jest wymagany
	 * @param mixed $val wartosc do sprawdzenia
	 * @return boolean jesli true nie wymagany jest pusty i nie sprawdza go dalej, jesli false wymusza dalsze sprawdzanie, badz jest pusty i zglasza blad
	 * @uses form_class::$current_input
	 * @uses form_class::set_error()
	 * @uses xml_class::getAttribute()
	 */
	private function valid_require($val){
		
		//sprawdzenie czy jeden z radio wymagany, oraz sprawdzenie dynamicznych checkboxow
		
		$name = $this->current_input->getAttribute('name');
		$required = $this->current_input->getAttribute('required');
		if($required == 'required'){
			if($val == ''){
				$this->set_error($name, 'required');
				return FALSE;
			}
		}elseif($val == ''){
			return TRUE;
		}
		return FALSE;
	}
	/**
	 * sprawdza wartosc wedlug podanego wzorca
	 * @param mixed $val wartosc do sprawdzenia
	 * @uses form_class::$current_input
	 * @uses form_class::set_error()
	 * @uses xml_class::getAttribute()
	 */
	private function valid_pattern($val){
		$name = $this->current_input->getAttribute('name');
		$pattern = $this->current_input->getAttribute('pattern');
		if($pattern){
			$bool = preg_match($pattern, $val);
			if(!$bool){
				$this->set_error($name, 'pattern');
			}
		}
	}
	/**
	 * sprawdza maksymalna ilosc znakow inputa, z lub bez encji i sekwencji ucieczki
	 * @param mixed $val wartosc do sprawdzenia
	 * @uses form_class::$current_input
	 * @uses form_class::set_error()
	 * @uses xml_class::getAttribute()
	 * @uses simpleValid_class::char_lenght()
	 */
	private function valid_length($val){
		$escape = $this->current_input->getAttribute('escape');
		$entities = $this->current_input->getAttribute('entities');
		$name = $this->current_input->getAttribute('name');
		$min = $this->current_input->getAttribute('minlength');
		$max = $this->current_input->getAttribute('maxlength');
		if($escape){
			$val = mysqli_escape_string($val);
		}
		if($entities){
			$val = htmlentities($val);
		}
		$min_bool = simpleValid_class::char_lenght($val, $min, NULL);
		$max_bool = simpleValid_class::char_lenght($val, NULL, $max);
		if(!$min_bool){
			$this->set_error($name, 'minlength');
		}
		if(!$max_bool){
			$this->set_error($name, 'maxlength');
		}
	}
	/**
	 * sprawdza typ walidacji inputa
	 * seria wyrazen regularnych z simpleValid_class
	 * @param mixes $val wartosc do sprawdzenia
	 * @uses form_class::$current_input
	 * @uses form_class::set_error()
	 * @uses xml_class::getAttribute()
	 * @uses simpleValid_class::valid()
	 * 
	 * @todo w zaleznosci od typu sprawdzania sprawdza dodatkowe atrybuty (np jesli sprawdza date, uruchamia sprawdzenie zakresu dat) zabezpieczenie dla zwyklych inputow
	 * @todo zabezpieczenie gdy mozliwe podwojne sprawdzenie np zakresu
	 * 
	 */
	private function valid_type($val){
		$name = $this->current_input->getAttribute('name');
		$validtype = $this->current_input->getAttribute('valid_type');
		if($validtype){
			$bool = simpleValid_class::valid($val, $validtype);
			if(!$bool){
				$this->set_error($name, 'valid_type');
			}
		}
	}
	/**
	 * sprawdza czy wartosci pol sa identyczne
	 * poszukuje podanego pola i sprawdza jego wartosc, w zaleznosci od parametru zwraca true dla identycznych lub roznych
	 * @uses form_class::$current_input
	 * @uses form_class::$xml
	 * @uses form_class::set_error()
	 * @uses xml_class::getElementsByTagName()
	 * @uses xml_class::getAttribute()
	 * @uses core_class::options()
	 * 
	 * @todo sprawdzanie czy wartosc pola jest wieksza, mniejsza, itp
	 * 
	 */
	private function valid_field(){
		$name = $this->current_input->getAttribute('name');
		$check_field = $this->current_input->getAttribute('check_field');
		if($check_field){
			$sep = core_class::options('param_sep');
			$type = explode($sep, $check_field);
			$inputs = $this->xml->getElementsByTagName('input');
			foreach($inputs as $index => $element){
				if($element->getAttribute('name') == $type[0]){
					if((bool)$type[1]){
						if($element->getAttribute('value') == $this->current_input->getAttribute('value')){
							$this->set_error($name, 'check_field_true');
						}
					}elseif(!(bool)$type[1]){
						if($element->getAttribute('value') != $this->current_input->getAttribute('value')){
							$this->set_error($name, 'check_field_false');
						}
					}
				}
			}
		}
	}
	/**
	 * sprawdza zakres wartosci numerycznej (jesli z , zamienia na .)
	 * @param float $val wartosc numeryczna do sprawdzenia
	 * @uses form_class::$current_input
	 * @uses form_class::set_error()
	 * @uses xml_class::getAttribute()
	 * @uses simpleValid_class::range()
	 */
	private function valid_range($val){
		$val = str_replace(',', '.', $val);
		$name = $this->current_input->getAttribute('name');
		$max = $this->current_input->getAttribute('max');
		if($max){
			$bool = simpleValid_class::range($val, NULL, $max);
			if(!$bool){
				$this->set_error($name, 'range_max');
			}
		}
		$min = $this->current_input->getAttribute('min');
		if($min){
			$bool = simpleValid_class::range($val, $min, NULL);
			if(!$bool){
				$this->set_error($name, 'range_min');
			}
		}
	}
	/**
	 * dokonuje podstawowej transformacji inputa przed wyswietleniem go
	 * usuwa zbedne atrybuty, puste atrybuty i wezly/kontenery dla bledow
	 * @uses form_class::$current_input
	 * @uses form_class::$attributes
	 * @uses xml_class::$value
	 * @uses xml_class::$attributes
	 * @uses xml_class::$name
	 * @uses xml_class::$parentNode
	 * @uses form_class::remove_attribute()
	 * @uses form_class::search_error_tag()
	 * @uses xml_class::removeChild()
	 */
	private function base_transform(){
		$this->remove_attribute($this->attributes);
		$to_delete = array();
		foreach($this->current_input->attributes as $attribute){
			if(trim($attribute->value) == ''){
				$to_delete[] = $attribute->name;
			}
		}
		$error_node = $this->search_error_tag(1);
		if($error_node){
			$parent = $error_node->parentNode;
			$parent->removeChild($error_node);
		}
		$this->remove_attribute($to_delete);
	}
	/**
	 * transformuje pola tekstowe
	 * daje do podstawowej transformacji i usuwa zbedne znaczniki
	 * @uses form_class::base_transform()
	 * @uses form_class::remove_attribute()
	 */
	private function transform_text(){
		$this->base_transform();
		$wrong_attributes = array(
			'step', 'max', 'min'
		);
		$this->remove_attribute($wrong_attributes);
	}
	/**
	 * transformuje pole numeryczne
	 * @uses form_class::base_transform()
	 */
	private function transform_number(){
		$this->base_transform();
	}
	/**
	 * transformuje pole e-mail
	 * @uses form_class::base_transform()
	 * @uses form_class::remove_attributes()
	 */
	private function transform_email(){
		$this->base_transform();
		$wrong_attributes = array(
			'step', 'max', 'min'
		);
		$this->remove_attribute($wrong_attributes);
	}
	/**
	 * transformuje pole koloru
	 * @uses form_class::base_transform()
	 * @uses form_class::remove_attributes()
	 */
	private function transform_color(){
		$this->base_transform();
		$wrong_attributes = array(
			'step'
		);
		$this->remove_attribute($wrong_attributes);
	}
	/**
	 * transformuje pola checkbox
	 * @uses form_class::base_transform()
	 * @uses form_class::remove_attributes()
	 */
	private function transform_checkbox(){
		$this->base_transform();
		$wrong_attributes = array(
			'placeholder', 'autocomplete', 'list'
		);
		$this->remove_attribute($wrong_attributes);
	}
	/**
	 * aktualizuje atrybuty inputa zgodnie z przekazana lista definicji
	 * oraz uzupelnia o odpowiednia tresc gdy formularz jest blednie wypelniony
	 * badz zaznacza zaznaczone wczesniej checkboxy i radiobutony, oraz selekty
	 * @param integer $index index przetwarzanego elementu (dla elementow o takiej samej nazwie)
	 * @uses form_class::$current_input
	 * @uses form_class::$all_inputs
	 * @uses form_class::$list_definition
	 * @uses form_class::remove_attribute()
	 * @uses xml_class::getAttribute()
	 * @uses xml_class::setAttribute()
	 */
	private function update_attr($index){
		$name = $this->current_input->getAttribute('name');
//		$dynamic = $this->chk_dynamic();
		if(isset($this->list_definition[$name])){
			if($this->current_input->getAttribute('type') == 'checkbox'){
				if(isset($this->list_definition[$name]['value'])){
					if($this->list_definition[$name]['value']){
						$this->current_input->setAttribute('checked', 'checked');
					}elseif($this->current_input->getAttribute('checked')){
						$this->remove_attribute(array('checked'));
					}
				}elseif($this->current_input->getAttribute('checked')){
					$this->remove_attribute(array('checked'));
				}
			}
			if($this->current_input->getAttribute('type') == 'radio'){
				$input = $this->list_definition[$name][$this->radio_index_number];
				$this->radio_index_number++;
			}else{
				$this->radio_index_number = 0;
				$input = $this->list_definition[$name];
			}
			foreach($input as $key => $val){
				$this->current_input->setAttribute($key, $val);
			}
		}
	}
	/**
	 * szuka w forlmularzu elementow dla przechowywania bledow
	 * (true przetwarza definicje do wyswietlenia. false przetwarza glowna definicje)
	 * @param boolean $display typ obiektu xml do przetworzenia
	 * @return mixed zwraca wezel xml, lub NULL jesli nie znaleziono 
	 * @uses form_class::$current_input
	 * @uses form_class::$display
	 * @uses form_class::$xml
	 * @uses xml_class::getAttribute()
	 * @uses xml_class::getElementsByTagName()
	 * @uses xml_class::item()
	 * 
	 * @todo przerobic tagi errorow, rozpoznawanie po atrybucie a nie nazwie wezla
	 * 
	 */
	private function search_error_tag($display = FALSE){
		if($this->chk_dynamic()){
			$name = preg_replace('#[\[\]]#', '', $this->current_input->getAttribute('name'));
		}else{
			$name = $this->current_input->getAttribute('name');
		}
		$name = $name.'_error';
		if($display){
			$node = $this->display->getElementsByTagName($name);
		}else{
			$node = $this->xml->getElementsByTagName($name);
		}
		if($node->item(0)){
			return $node->item(0);
		}
		return NULL;
	}
	/**
	 * usuwa niepotrzebne atrybuty podane w liscie
	 * @param array $array tablica atrybutow do usuniecia z wezla
	 * @uses form_class::$current_input
	 * @uses xml_class::removeAttribute()
	 * @access private
	 */
	private function remove_attribute($array){
		foreach($array as $name){
			$this->current_input->removeAttribute($name);
		}
	}
	/**
	 * przetwarza element datalist
	 */
	private function check_list(){
		$datalist = $this->xml->getElementsByTagName('datalist');
		foreach($datalist as $index => $list){
			
		}
	}
	/**
	 * ustawia error w tablicy bledow dla podanego inputa i znacznik ze wystapil blad w inpucie
	 * @param string $name nazwa inputa
	 * @param string $error_type kod bledu
	 * @uses form_class::$input_error
	 * @uses form_class::$error_list
	 */
	private function set_error($name, $error_type){
		$this->input_error = TRUE;
		$this->error_list[$name][] = $error_type;
	}
	/**
	 * dodaje klase do atrybutu class podanego elementu
	 * @param xml_object $element wezel xml do ktorego ma dodac klase
	 * @param string $class_new nowa klasa
	 * @uses xml_class::getAttribute()
	 * @uses xml_class::setAttribute()
	 */
	private function add_class($element, $class_new){
		$class = $element->getAttribute('class');
		$class .= ' '.$class_new;
		$element->setAttribute('class', $class);
	}
	/**
	 * wyszukuje wezel w ktorym ma byc przechowywane info o bledzie dla konkretnego inputa i prztwarza go
	 * uzupelnia tresc formularza o kody do przetwarzania przez funkcje generate
	 * @param string $name nazwa inputa
	 * @param object $list obiekt danych post
	 * @uses form_class::$list_definition
	 * @uses form_class::$current_input
	 * @uses form_class::$error_list
	 * @uses form_class::$xml
	 * @uses form_class::$input_error
	 * @uses xml_class::$parentNode
	 * @uses xml_class::$nodeValue
	 * @uses xml_class::$attributes
	 * @uses xml_class::$nodeType
	 * @uses xml_class::$name
	 * @uses xml_class::$value
	 * @uses form_class::add_class()
	 * @uses form_class::search_error_tag()
	 * @uses xml_class::getAttribute()
	 * @uses xml_class::createElement()
	 * @uses xml_class::appendChild()
	 * @uses xml_class::setAttribute()
	 */
	private function update_error_node($name, post $list){
		$this->list_definition[$name]['value'] = $list->$name;
		$key = key_exists($this->current_input->getAttribute('name'), $this->error_list);
		if($this->input_error && $key){
			$parent = $this->current_input->parentNode;
			$this->add_class($parent, 'input_error');
			$error_node = $this->search_error_tag();
			if($error_node){
				$convert_type = $error_node->getAttribute('convert');
				if(!$convert_type){
					continue;
				}
				$err_code = '';
				foreach($this->error_list[$name] as $code){
					$err_code .= " {;$code;} ";
				}
				$inner_html = $error_node->nodeValue;
				$nodes = $error_node->childNodes;
				$attr_list = $error_node->attributes;
				$parent = $error_node->parentNode;
				$new = $this->xml->createElement($convert_type, $inner_html.$err_code);
				foreach($nodes as $nod){
					if($nod->nodeType == 3){
						continue;
					}
					$new->appendChild($nod);
				}
				foreach($attr_list as $attr){
					if($attr->name == 'convert'){
						continue;
					}
					$new->setAttribute($attr->name, $attr->value);
				}
				$parent->appendChild($new);
			}
		}
	}
	/**
	 * sprawdza czy przetwarzany input jest dynamiczny
	 * @return boolean true jesli dynamiczny inaczj false
	 * @uses form_class::$current_input
	 * @uses xml_class::getAttribute()
	 */
	private function chk_dynamic(){
		if(preg_match('#^[\p{L}\\d_-]+\[\]$#iu', $this->current_input->getAttribute('name'))){
			return TRUE;
		}
		return FALSE;
	}
}
?>