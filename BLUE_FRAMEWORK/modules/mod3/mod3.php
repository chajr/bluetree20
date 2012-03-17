<?php
/**
 * @author 
 * @version 
 * @access 
 * @copyright 
 * @package 
 */
class mod3 extends module_class {
	static $version = '0.1';
	static $name = 'modul numer 3';
	public $require_libs = array();
	public $require_modules = array('modul1');
	public function run(){
		//ladowanie szablonow
		$this->layout('layout1');
		
		//tresc do layoutu
		$this->generate('znacznik', 'jakas tresc do zastaapienia');
		
	
	}
	public function error_mode(){
		$this->generate('znacznik', 'to jest tryb bledu modulu 3');
	}
	public function install(){}
	public function uninstall(){}
}
?>
