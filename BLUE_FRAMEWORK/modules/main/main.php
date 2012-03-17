<?php
/**
 * glowny modul, uruchamia w zaleznosci od parametrow rozne przydatne funkcje
 * @author chajr <chajr@bluetree.pl>
 * @package main
 * @version 1.0
 * @copyright chajr/bluetree
 */
class main extends module_class {
	static $version = '1.0';
	static $name = 'Główny moduł';
	public $require_libs = array();
	public $require_modules = array();
	public function run(){
		switch($this->params[0]){
			case'sitemap':
				$this->generate('empty', $this->sitemap(), 1);
				break;
		}
	}
	public function error_mode(){
		
	}
	public function install(){

	}
	public function uninstall(){
		
	}
}
?>
