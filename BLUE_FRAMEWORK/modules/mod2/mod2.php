<?php
/**
 * 
 * 
 * @author 
 * @version 
 * @access 
 * @copyright 
 * @package 
 */
class mod2 extends module_class {
	static $version = '';
	static $name = '';
	public $require_libs = array('mysql', 'simpleData', 'data');
	public $require_modules = array();
	public function run(){
		$this->layout('lay');
		//odczyt tresci z innego modulu
		if(isset($this->mod['modul1'])){
			if(isset($this->mod['modul1']->zmienna_modulu)){
				$this->generate('znacznik', $this->mod['modul1']->zmienna_modulu);
			}
		}else{
			$this->generate('znacznik', 'modul 1 sie wysypał i nie bedzie z niego informacji :(');
		}
		$this->connection_test();
		//$img = new image_class(array('name'=>'right.png', 'tmp_name'=>'elementy/Right.png'));
		//$this->generate('data', simpleData_class::miesiac(0, 1));
		$this->date_test();
		$this->generate('form', $this->form_test());
	}
	/**
	 * testowe polaczenie z baza danych
	 * zapis, odczyt informacji z bazy, oraz ostatnio dodane id
	 */
	public function connection_test(){
		$conn = new mysql_connection_class(array(
			'zmp.nazwa.pl', 'zmp_5', 'WE244ef43%$', 'zmp_5'
		));
		if($conn->err){
			throw new modException('db_conn_error', $conn->err);
		}else{
			$this->generate('connection', 'Połączono');
		}
		$query = new mysql_class('SELECT * FROM test');				//z bledem dziala
		if($query->err){
			$this->generate('select', $query->err);
		}else{
			$this->generate('select', $query->rows);
		}
		$query = new mysql_class("INSERT INTO test (string) VALUES ('nowy test $query->rows')");
		if($query->err){
			$this->generate('insert', $query->err);
		}else{
			$this->generate('insert', $query->id);
		}
	}
	/**
	 * testowanie klasy z datami
	 * zwykle daty, zformatowane
	 * roznice miedzy datami
	 */
	public function date_test(){
		$data = new data_class();
		$this->generate('znacznik1', $data);
		$this->generate('znacznik2', $data->czas());
		$this->generate('znacznik3', $data->dzien(1));
		$this->generate('znacznik4', $data->full_time());
		$this->generate('znacznik5', $data->miesiac_nazwa());
		$this->generate('znacznik6', $data->tydzien());
		$data->use_conversion = 1;
		$this->generate('znacznik7', $data->other_formats("%A - nazwa tygodnia zgodna z lokalizacją 
			(przy wyłączonej opcji poprawy polskich znaków, dla błędnego ustawiania setlocale<br/>
			dziuała tylko gdy ma konwertowaćz iso na utf)"));
		$data2 = new data_class(1214257275);
		$diff = $data->diff($data2, 'seconds');
		$this->generate('znacznik8', $diff);
	}
	/**
	 * testowy formularz
	 * walidacja, komunikaty o bledach
	 * @return string formularz do wyswietlenia
	 */
	public function form_test(){
		$this->set('form', 'css');
		$this->set('form', 'js');
		$default_array = array(
//			'inputę1' => array(
//				array('value' => 'asdfasdas'),
//				array('value' => 'asdfasdas 2')
//			),
			'input1' => array('value' => 'asdfasdas'),
			'input2' => array('value' => 'ddddddddddddd'),
			'input5' => array('value' => 34),
			'chka' => array('checked' => 'checked'),
			'rada' => array(
				array('class' => 'first'),
				array('class' => 'second', 'checked' => 'checked'),
				array('class' => 'last')
			)
		);
		$form = new form_class('mod2', 'form_definition', $default_array);
		if($this->post->incoming_form){
			$bool = $form->valid($this->post);
			if(!$bool){
				echo '<pre>';
				var_dump($form->error_list);
				echo '</pre>';
			}
			//przetworzenie danych
			//uruchomienie metody add lub edit lub jakiejs innej
			//metody zwracaja kompletne zapytania do uzycia w obiektach odpowiednich baz danych
			//lub jako parametr jakiej ma uzyc metody bazy, bazy itp
		}
		$this->generate('post_serialize', $this->post);
		return $form->display_form();
	}
	public function error_mode(){
		
	}
	public function install(){

	}
	public function uninstall(){
		
	}
}
?>
