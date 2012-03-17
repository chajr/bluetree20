<?php
/**
 * @author 
 * @version 
 * @access 
 * @copyright 
 * @package 
 */
class modul1 extends module_class {
	static $version = '0.1';
	static $name = 'modul numer 1';
	public $require_libs = array();
	public $require_modules = array();
	public function run(){
		
		//umozliwia tulmaczenie modulu
		$this->translate();
		
		//ladowanie szablonow
		$this->layout('layout1');//layout1
		
		//tresc do layoutu
		$this->generate('znacznik', 'jakas treść do zastąpienia');
		
		//tresc do glownego layoutu
		$this->generate('znacznik', 'jakas tresc do zastaapienia w glownym szablonie', 1);
		
		//generowanie tresci dla gropy znacznikow
		$tresci = array('znacznik-a' => 'tresc a', 'znacznik-b' => 'tresc b');
		$this->generate($tresci);
		
		//uruchamianie zaladowanych bibliotek
		$valid = new valid_class();
		$this->generate('lib', $valid->ret());
		
		//dodawanie js i css
		$this->set('https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.js', 'js', 'external');
		$this->set('jakis_skrypt', 'js');
		$this->set('base', 'css', 'internal', 'print');//?
		$this->set('base2', 'css');
		
		//generowanie tresci (zwykle, petla, opcjonalne, z sesji)
		$tab = array(array('item1' => 'val a1', 'item2' => 'val a2'), 
			array('item1' => 'val b1', 'item2' => 'val b2'));
		$this->loop('petla1', $tab);
		$arr = array(array('aaa' => 'val aaa1', 'bbb' => 'val bbb2', 'oo' => 'ooo'), 
			array('aaa' => 'val ccc1', 'bbb' => 'val ddd2', 'oo2' => 'op2'));
		$this->loop('petla2', $arr);
		
		//dane z sesji
		$this->generate(array(
			'public' => $this->session->val, 
			'user' => $this->return_session('val_user', 'user')));
		if($this->session->val){
			$this->session->val += 1;
		}else{
			$this->session->val = 1;
		}
		if($this->return_session('val_user', 'user')){
			$v_u = $this->return_session('val_user', 'user') +1;
		}else{
			$v_u = 1;
		}
		$this->set_session('val_user', $v_u, 'user');
		$this->generate(array('public2' => $this->session->val, 
			'user2' => $this->return_session('val_user', 'user')));
		
		//oczyszczanie sesji
		//$this->clear_session('val');					//!!DZIALA
		
		//generowanie breadcrumbs
		$bread = var_export($this->breadcrumbs(), 1);
		$this->generate('breadcrumbs', $bread);
		
		//ustawienie danych w cookie
		$this->generate('cookie', $this->cookie->jakaszmienna);
		if($this->cookie->jakaszmienna){
			$this->cookie->jakaszmienna += 1;
		}else{
			$this->cookie->jakaszmienna = 1;
		}
		$this->generate('cookie2', $this->cookie->jakaszmienna);
		
		//dostep do danych z get, post, files
		$arr = array('val1' => $this->get->val1, 'val2' => $this->get->val2, 'val3' => $this->get->val3);
		$this->generate($arr);
		
		//dostep do obiektu get
		//dorobic metoduy zwacajace nowe wartosci w module_class i sprawdzic to samo przy innych obiektach
		$gettab = array(
			'path' => get::real_path(),
			'rget' => var_export(get::rewrite_get('', 'val/val2/val3'), 1),
			'lang' => $this->get->get_lang(),
			'current_page' => $this->get->get_current_page(),
			'parrent' => $this->get->get_parrent(),
			'master' => $this->get->get_master(),
			'full' => var_export( $this->get->full_get(), 1),
			'full2' => var_export( $this->get->full_get(1), 1),
			'type' => $this->get->typ(),
			'path_domain' => $this->get->path(),
			'path' => $this->get->path(1),
		);
		$this->generate($gettab);
		
		//generowanie scierzek url
		$this->generate('jakas_scierzka', '{;core;domain;}{;core;lang;}{;path;/strona2;}');
		
		//zglaszanie dodatkowych metatagow
		$this->add_meta('<meta name="jakismeta" content="cos"/>');
		$this->add_to_meta('keywords', 'slowo 1, slowo 2');
		
		//odczyt danych z innego modulu
		$this->zmienna_modulu = 'aaaaaaaaaaa';
		
		//mapa strony
		$map = '<pre>'.var_export($this->map(), 1).'</pre>';
		$this->generate('mapa', $map);
		
		//ladowanie modulu do elementu blokowego
		
		//odczyt parametrow dla modulu
		$param = '<pre>'.var_export($this->params, 1).'</pre>';
		$this->generate('param', $param);
		
		//odczyt konfiguracji modulu
		$list = '<pre>'.var_export($this->load_options(), 1).'</pre>';
		$this->generate('tablica_cfg', $list);
				
		//sprawdzanie wymaganych modulow
		//w mod 3						//!!DZIALA
		
		//pomijanie wybranych modulow
		//$this->dissemble('mod2');		//!!DZIALA
		
		//zatrzymywanie frameworka
		//$this->stop();				//!!DZIALA

		//jakis standardowy blad
		//echo $adfdsf;					//!!DZIALA

		//zglaszanie bledow poprzez renderig, throw i do konkretnego znacznika
		
		//blad zatrzymujacy frameworka
		//throw new coreException('core_error_20', 'jakieś dodatkowe informacje {;lang;tekst_do_tulmaczenia;}');			//!!DZIALA
		
		//blad zatrzymujacy modul
		//throw new modException('blad_z_modulu', 'jakieś dodatkowe informacje {;lang;tekst_do_tulmaczenia;}');			//!!DZIALA
		$this->error('critic', 'blad_z_modulu', '<b>tulmaczenie z modulu: {;lang;tekst_do_tulmaczenia_error;}</b>');	//!!DZIALA
		
		//warning
		//throw new warningException('kod_ostrzezenia', 'dodatkowe info z warninga');									//!!DZIALA
		$this->error('warning', 'kod_ostrzezenia', 'jakieś dodatkowe informacje1');										//!!DZIALA
		
		//info
		//throw new infoException('kod_info', 'dodatkowe info z info');													//!!DZIALA
		$this->error('info', 'kod_info', '<b>tulmaczenie z core: {;lang;core;tekst_do_tulmaczenia;}</b>');				//!!DZIALA
		
		//ok
		//throw new okException('kod_ok', 'dodatkowe info z ok');														//!!DZIALA
		$this->error('ok', 'kod_ok', 'jakieś dodatkowe informacje3');													//!!DZIALA
		
		//blad do konkretnego znacznika
		//sprawdzic dzialanie w poprzedniej wersji frameworka
		$this->error('znacznik_bledu', 'jakis_kod', 'jakieś dodatkowe informacje4 {;lang;tekst_do_tulmaczenia_error;}');//!!DZIALA
		$this->error('znacznik_bledu1', 'jakis_kod', 'inf dodatkowe');													//!!DZIALA
				
		//zwykly wyjatek
		//throw new Exception('jakiś zwykły wyjątek');																	//!!DZIALA
		
		//tablica zgloszen o bledach, informacjach
		$errors = '<pre>'.var_export($this->error, 1).'</pre>';
		$this->generate('tablica_bledow', $errors);
		
		
		
		//konwersja scierzek wymuszona
		
		
		
		//dodac metoda umozliwiajaca wczesniejsze tulmaczenie
		//dla np zglaszania bledu do znacznika bledu
		
		//dodatkowa tablica do tulmaczen
		$this->set_translate(array('kod_dodatkowy' => 'fsdfsdfsdfd'));			//do dorobienia !!!!!!!!!!!!!!!!!!!!!!!!!!!!
		

	}
	public function error_mode(){
		$this->generate('znacznik', 'to jest tryb bledu modulu 1');
	}
	public function install(){}
	public function uninstall(){}
}
?>
