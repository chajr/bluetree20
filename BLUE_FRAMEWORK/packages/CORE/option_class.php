<?PHP
/**
 * odczyt opcji frameworka
 * @author chajr <chajr@bluetree.pl>
 * @package core
 * @version 1.4.1
 * @copyright chajr/bluetree
 * @final
 */
final class option_class {
	/**
	 * wczytuje plik opcji i zwraca pelna liste opcji frameworka, lub modulu
     * @static
	 * @return array zwraca tablice opcji, lub FALSE jesli blad podczas ladowania pliku opcji dla modulu
	 * @uses xml_class::$documentElement
	 * @uses xml_class::$childNodes
	 * @uses xml_class::$nodeType
	 * @uses xml_class::$nodeName
	 * @uses xml_class::wczytaj()
	 * @uses xml_class::__construct()
	 * @uses xml_class::getAttribute()
	 * @uses starter_class::path()
    */
	public static function load($mod = NULL){
		$opcje = array();
		$xml = new xml_class();
		if($mod){
			$bool = $xml->wczytaj(starter_class::path('modules/'.$mod.'/elements').'config.xml', TRUE);
			if(!$bool){
				return FALSE;
			}
		}else{
			$bool = $xml->wczytaj(starter_class::path('cfg').'config.xml', TRUE);
			if(!$bool){
				echo 'Main configuration load error<br/>'.starter_class::path('cfg').'config.xml';
				exit;
			}
		}
		$lista = $xml->documentElement;
		foreach($lista->childNodes as $nod){
			if($nod->nodeType != 1){
				continue;
			}
			if($nod->nodeName == 'langs'){
				$val = array();
				foreach($nod->childNodes as $lang){
					if((bool)$lang->getAttribute('on')){
						$val[] = $lang->getAttribute('id');
					}
				}
			}else{
				$val = $nod->getAttribute('value');
			}
			$id = $nod->getAttribute('id');
			$opcje[$id] = $val;
		}
		return $opcje;
	}
	/**
	 * pobiera pojedyncza opcje z pliku opcji frameworka
	 * @example show ('debug') - pobiera pojedyncza opcje debug
     * @static
	 * @param string nazwa opcji do pobrania
	 * @return mixed zwraca wartosc pojedynczej opcji
	 * @uses xml_class::wczytaj()
	 * @uses xml_class::__construct()
	 * @uses xml_class::get_id()
	 * @uses xml_class::getAttribute()
	 * @uses starter_class::path()
    */
	public static function show($opcja, $mod = NULL){
		$xml = new xml_class();
		$xml->wczytaj(starter_class::path('cfg').'config.xml', TRUE);
		$val = $xml->get_id($opcja)->getAttribute('value');
		return $val;
	}
}
?>