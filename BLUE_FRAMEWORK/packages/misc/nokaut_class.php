<?PHP
class nokaut_class extends xml_class{
	private $nokaut_xml = '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE nokaut SYSTEM "http://www.nokaut.pl/integracja/nokaut.dtd">
<nokaut>
	
</nokaut>';
	public function __construct(){
		$bool = file_exists('nokaut.xml');
		//$bool = FALSE;
		if($bool){
			$bool = $this->wczytaj('nokaut.xml', TRUE);
			//obsluga bledu przeniesiona do modelu (sprawdza zawartosc zmiennej $obiekt->err)
		}else{
			//budowa nowego pliku
			$uchwyt = @fopen('nokaut.xml', 'w');
			if(!$uchwyt){
				$this->err = 'Bład podczas tworzenia pliku xml';
				return FALSE;
			}
			@fwrite($uchwyt, $this->nokaut_xml);
			@fclose($uchwyt);
			chmod('nokaut.xml', 0777);
			$db = new db();
			$pyt = "SELECT produkt_id, nazwa, producent, opis, cena, cena_prom, kategoria FROM kategorie, produkty WHERE kategorie_id = kategoria_id;";
			$dane = $db->zapytanie($pyt);
			$a = $this->wczytaj('nokaut.xml');
			if(!$a){
				return FALSE;
			}
			$offers = $this->createElement('offers');
			$main = $this->documentElement->appendChild($offers);
			while($tab = $db->tablica($dane)){
				if($tab['cena_prom']){
					$tab['cena'] = $tab['cena_prom'];
				}
				$node = $this->createElement('offer');
				$oferta = $main->appendChild($node);
				$id = $this->createElement('id', $tab['produkt_id']);
				$oferta->appendChild($id);
				//nazwa
				$cdata = $this->createCDATASection($tab['nazwa']);
				//var_dump ($tab['nazwa']);
				$name = $this->createElement('name');
				//var_dump ($name);
				$name = $oferta->appendChild($name);
				//var_dump ($name);
				$name->appendChild($cdata);
				//var_dump ($name);
				//exit;
				//opis
				$cdata = $this->createCDATASection($tab['opis']);
				$description = $this->createElement('description');
				$description = $oferta->appendChild($description);
				$description->appendChild($cdata);
				//url
				$n = urlencode($tab['nazwa']);
				$url = 'http://www.kingkomp.pl/index.php?s=szczegoly&amp;p='.$tab['produkt_id'].'&amp;nazwa='.$n;
				$url = $this->createElement('url', $url);
				$oferta->appendChild($url);
				//obraz
				$img = 'http://www.kingkomp.pl/img.php?img='.$tab['produkt_id'].'&amp;typ=1';
				$img = $this->createElement('image', $img);
				$oferta->appendChild($img);
				//cena
				$cena = $this->createElement('price', $tab['cena']);
				$oferta->appendChild($cena);
				//kategoria
				$cdata = $this->createCDATASection($tab['kategoria']);
				$cat = $this->createElement('category');
				$cat = $oferta->appendChild($cat);
				$cat->appendChild($cdata);
				//producent
				$cdata = $this->createCDATASection($tab['producent']);
				$prod = $this->createElement('producer');
				$prod = $oferta->appendChild($prod);
				$prod->appendChild($cdata);
			}
			$this->zapisz('nokaut.xml');
		}
	}
	
	public final function insert($id, $nazwa, $opis, $cena, $kategoria, $producrnt){
		$a = $this->wczytaj('nokaut.xml', TRUE);
		if(!$a){
			return FALSE;
		}
		$main = $this->documentElement->firstChild;
		$node = $this->createElement('offer');
		$oferta = $main->appendChild($node);
		$id = $this->createElement('id', $id);
		$oferta->appendChild($id);
		//nazwa
		$cdata = $this->createCDATASection($nazwa);
		$name = $this->createElement('name');
		$name = $oferta->appendChild($name);
		$name->appendChild($cdata);
		//opis
		$cdata = $this->createCDATASection($opis);
		$description = $this->createElement('description');
		$description = $oferta->appendChild($description);
		$description->appendChild($cdata);
		//url
		$n = urlencode($nazwa);
		$url = 'http://www.kingkomp.pl/index.php?s=szczegoly&amp;p='.$id.'&amp;nazwa='.$n;
		$url = $this->createElement('url', $url);
		$oferta->appendChild($url);
		//obraz
		$img = 'http://www.kingkomp.pl/img.php?img='.$id.'&amp;typ=1';
		$img = $this->createElement('image', $img);
		$oferta->appendChild($img);
		//cena
		$cena = $this->createElement('price', $cena);
		$oferta->appendChild($cena);
		//kategoria
		$cdata = $this->createCDATASection($kategoria);
		$cat = $this->createElement('category');
		$cat = $oferta->appendChild($cat);
		$cat->appendChild($cdata);
		//producent
		$cdata = $this->createCDATASection($producent);
		$prod = $this->createElement('producer');
		$prod = $oferta->appendChild($prod);
		$prod->appendChild($cdata);
		$b = $this->zapisz('nokaut.xml');
		
		
	}
	
	public function delete($id){
		$a = $this->wczytaj('nokaut.xml', TRUE);
		if(!$a){
			return FALSE;
		}
		$list = $this->getElementsByTagName('id');
		foreach($list as $element){
			//var_dump($element->nodeValue);;echo '<br />';
			if($id == $element->nodeValue){
				$parent = $element->parentNode;
				break;
			}
		}
		//var_dump($parent);
		$master = $parent->parentNode;
		$master->removeChild($parent);
		
		$this->zapisz('nokaut.xml');
	}
	
	public function change($id, $nazwa, $opis, $cena, $kategoria, $producrnt){
		$a = $this->wczytaj('nokaut.xml', TRUE);
		if(!$a){
			return FALSE;
		}
		$main = $this->documentElement->firstChild;
		
		$oferta = $main->appendChild($node);
		$id = $this->createElement('id', $tab['produkt_id']);
		$oferta->appendChild($id);
		//nazwa
		$cdata = $this->createCDATASection($tab['nazwa']);
		$name = $this->createElement('name');
		$name = $oferta->appendChild($name);
		$name->appendChild($cdata);
		//opis
		$cdata = $this->createCDATASection($tab['opis']);
		$description = $this->createElement('description');
		$description = $oferta->appendChild($description);
		$description->appendChild($cdata);
		//url
		$n = urlencode($tab['nazwa']);
		$url = 'http://www.kingkomp.pl/index.php?s=szczegoly&amp;p='.$tab['produkt_id'].'&amp;nazwa='.$n;
		$url = $this->createElement('url', $url);
		$oferta->appendChild($url);
		//obraz
		$img = 'http://www.kingkomp.pl/img.php?img='.$tab['product_id'].'&amp;typ=1';
		$img = $this->createElement('image', $img);
		$oferta->appendChild($img);
		//cena
		$cena = $this->createElement('price', $tab['cena']);
		$oferta->appendChild($cena);
		//kategoria
		$cdata = $this->createCDATASection($tab['kategoria']);
		$cat = $this->createElement('category');
		$cat = $oferta->appendChild($cat);
		$cat->appendChild($cdata);
		//producent
		$cdata = $this->createCDATASection($tab['producent']);
		$prod = $this->createElement('producer');
		$prod = $oferta->appendChild($prod);
		$prod->appendChild($cdata);
		
		$this->zapisz('nokaut.xml');
	}
}
?>