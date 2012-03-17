<?php
/**
 * @author chajr <chajr@bluetree.pl>
 * @version 1.0
 * @access private
 * @copyright chajr/bluetree
*/
/**
 * zwraca informacje na temat uzytkownika odwiedzajacego strone
 * @package clientData
 */
class clientData_class{
	/**
	 * zwraca ip komputera
	 * @static
	 * @return string zwraca ip komputera
    */
	static function ip(){
		return $_SERVER['REMOTE_ADDR'];
	}
	/**
	 * zwraca nazwe i wersje przegladarki uzytkownika
	 * @static
	 * @return array nazwa przegladarki i wersja
    */
	static function browser(){
		$agent = $_SERVER['HTTP_USER_AGENT'];
		if(strstr($agent, 'MSIE 8.0')){
			return 'IE8';
		}
		if(strstr($agent, 'MSIE 7.0')){
			return 'IE7';
		}
		if(strstr($agent, 'MSIE 6.0')){
			return 'IE6';
		}
		$b = preg_match('#(Safari|Opera|Firefox|Chrome)/[\\d\.]*#', $agent, $array);
		if($b){
			return $array[0];
		}else{
			return $agent;
		}
		//"Mozilla/5.0 (Windows; U; Windows NT 5.1; pl-PL) AppleWebKit/525.27.1 (KHTML, like Gecko) Version/3.2.1 Safari/525.27.1"
		//"Opera/9.64 (Windows NT 5.1; U; pl) Presto/2.1.1"
		//"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0)"
		//"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)"
		//"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)"
		//"Mozilla/5.0 (Windows; U; Windows NT 5.1; pl; rv:1.9.2) Gecko/20100115 Firefox/3.6 (.NET CLR 3.5.30729)"
		//"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/532.5 (KHTML, like Gecko) Chrome/4.0.249.89 Safari/532.5"
	}
}
?>
