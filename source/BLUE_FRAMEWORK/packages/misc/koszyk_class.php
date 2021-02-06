<?PHP
class koszyk extends db{
	
	//dodac mozliwosc przegladu stanu zamowienia, oraz data zmiany stanu
	
	
	private $user_id;
	public $cena = 0;
	public $ilosc = 0;
	public $p_id = NULL;
	private $prod_id = '';
	
	//konstruktor. odpowiada za wyswietlenie zawartosci koszyka
	public function konstruktor(){
	  	if (isset($_SESSION['wuzek'])){
	  		foreach($_SESSION['wuzek'] as $id ) {
	  			$this->cena += $id['ilosc'] * $id['cena'];
	  			$this->ilosc += $id['ilosc'];
				//zwraca liste id produktow
				$this->p_id .= $id['id'];
				$this->p_id .= '::';
				//przygotowanie danych do umieszczenia w bazie zamowien
				$this->prod_id .= $id['id'].'::'.$id['ilosc'].'::'.$id['cena'].':|:';
			}
			//echo $this->p_id;
	  	}
		$this->user_id = $_SESSION['uid'];

	}
	
	
	public function kasa(){
		//tablica z produktami zwracana przez konstruktor
		//jesli sesja istnieje zwruci dane usera
		if (isset($_SESSION['wuzek'])){
			//zais do zmiennej aktualnych danych do wysylki
			$pyt = "SELECT imie, nazwisko, ulica, dom, kod, miasto, telefon FROM uzytkownicy WHERE user_id = '$this->user_id'";
			$wynik = $this->zapytanie($pyt);
			$dane_usera = $this->tablica($wynik);
			return $dane_usera;
			
		//jesli zwraca false
		}else{
			return FALSE;
		}
	}
	
	public function przeglad($id){
		//zwraca nazwy, ilosc danego produktu oraz ceny
		$pyt = "SELECT nazwa FROM produkty WHERE produkt_id = '$id'";
		$wynik = $this->zapytanie($pyt);
		
		//sprawdza obecnosc przekazanego id, jesli nie ma przerywa dzialanie skryptu
		$dane_produktu = $this->tablica($wynik);
		if ($dane_produktu == NULL){
			return FALSE;
		}
		$ilosc = $_SESSION['wuzek'][$id]['ilosc'];
		$dane_produktu['ilosc'] = $ilosc;
		$dane_produktu['laczna_cena'] = $ilosc * $_SESSION['wuzek'][$id]['cena'];
		return $dane_produktu;
		
	}
	
	public function koszyk_dodaj ($id, $ilosc = FALSE){
		//najperw sprawdza czy wpis istnieje w koszyku
		if (!isset ($_SESSION['wuzek'][$id])){
			$pyt = "SELECT cena, cena_prom, dostepnosc FROM produkty WHERE produkt_id = '$id'";
			$wynik = $this->zapytanie($pyt);
			$dane_produktu = $this->tablica($wynik);
			//sprawdza czy dostepny, jesli nie zwraca false
			if (!$dane_produktu['dostepnosc']){
				return FALSE;
			}
			//sprawdza ustawione ceny i ustawia odpowiednia w koszyku
			if ($dane_produktu['cena_prom'] == NULL){
				$cena = $dane_produktu['cena'];
			}else{
				$cena = $dane_produktu['cena_prom'];
			}
			
			$_SESSION['wuzek'][$id]['cena'] = $cena;
			$_SESSION['wuzek'][$id]['ilosc'] = 1;
			$_SESSION['wuzek'][$id]['id'] = $id;
		}else{
			//sprawdzanie ilosci
			$pyt = "SELECT dostepnosc FROM produkty WHERE produkt_id = '$id';";
			$wynik = $this->zapytanie($pyt);
			$tab = $this->tablica($wynik);
			$ilosc_baza = $tab['dostepnosc'];
			//jesli juz istnieje w koszyku sprawdza ilosc
			//jesli ilosc daje jakas wartosc, zwiekasza ilosc w koszyku o podana wartosc, inaczej zwieksza o jeden
			if ($ilosc){
				if ($ilosc > $ilosc_baza){
					return FALSE;
				}else{
					$_SESSION['wuzek'][$id]['ilosc'] = $ilosc;
				}
			}else{
				$ilosc = $_SESSION['wuzek'][$id]['ilosc'];
				$ilosc++;
				if ($ilosc > $ilosc_baza){
					return FALSE;
				}else{
					$_SESSION['wuzek'][$id]['ilosc'] = $ilosc;
				}
			}
			
		}
		return TRUE;
	}
	
	public function koszyk_usun($id){
		//sprawdza czy element istnieje w sesji
		if (isset($_SESSION['wuzek'][$id])){
			unset ($_SESSION['wuzek'][$id]);
		}else{
			throw new Exception ("Brak podanego produktu w koszyku");
		}
		
	}
	
	public function koszyk_oproznij (){
		unset ($_SESSION['wuzek']);
		$this->p_id = NULL;
		$this->prod_id = '';
	}
	
	public function koszyk_wyslij ($forma_wysylki, $adres, $dodatkowe_info){
		//wyslanie zawartosci koszyka do bazy danych
		//zapisuje tylko, uid, id produktow, laczna cena, laczna ilosc, data + jesli true nowy adres wysylki, dodatkowe info o wysylce
		
		//tablica zawierajaca id produktow, oraz i che cene i ilosc, w konstruktorze
		if (trim($dodatkowe_info) != ''){
			$dodatkowe_info = strip_tags($dodatkowe_info);
		}else{
			$dodatkowe_info = NULL;
		}
		//ustawia ilosci produktow w sklepie
		foreach($_SESSION['wuzek'] as $id ) {
			$ilosc = $id['ilosc'];
			$p_id = $id['id'];
			//wykonanie zapytania pobierajacego ilosc
			$pyt = "SELECT dostepnosc FROM produkty WHERE produkt_id = '$p_id';";
			$wynik = $this->zapytanie($pyt);
			$tab = $this->tablica($wynik);
			$nowa_ilosc = $tab['dostepnosc'] - $ilosc;
			if ($nowa_ilosc < 0){
				throw new Exception ("Błąd ustawiania ilości produktu o id - $p_id<br />Sprawdź dostępność produktu, bądź zmień ilość w koszyku");
			}
			
			//zmiana ilosci
			$pyt = "UPDATE produkty SET dostepnosc = '$nowa_ilosc' WHERE produkt_id = '$p_id';";
			$this->zapytanie($pyt);
		}
		//zapis czasu dodania do bazy YYYY-MM-DD HH:MM:SS
		$czas = strftime ("%Y-%m-%d %H:%M:%S");
		$pyt = "INSERT INTO zamowienia (user_id, prod_id, nowy_adres, dodatkowe_info, data, laczna_cena, laczna_ilosc, forma_wysylki) VALUES ('".$this->user_id."', '".$this->prod_id."', '$adres', '$dodatkowe_info', '$czas', '".$this->cena."', '".$this->ilosc."', '$forma_wysylki')";
		$wynik = $this->zapytanie($pyt);
		//zwraca id, ostatniego zamowienia
		$id_zamowienia = $this->id();
		//czyszczenie koszyka
		$this->koszyk_oproznij();
		//zwracanie id zamowienia, dla maila
		return $id_zamowienia;
	}
	
	
	//pobieranie zamowienia z bazy danych, np do maila, lub do wyswietlenia dla usera/admina
	public function pokaz_zamowienie ($id, $u_id = FALSE){
		//pobranie zamowienia
		$pyt = "SELECT user_id, prod_id, nowy_adres, dodatkowe_info, data, laczna_cena, laczna_ilosc, forma_wysylki, status FROM zamowienia WHERE id = '$id'";
		$wynik = $this->zapytanie($pyt);
		$dane = $this->tablica($wynik);
		$tresc = '<table><tr>
        <td>Nazwa produktu</td>
        <td>Identyfikator</td>
        <td>Ilość</td>
        <td>Łączna cena</td>
		<td>Cena za sztukę</td>
    	</tr>';
		//gorna czesc maila
		//w kontrolerze
		
		//petla przeksztalcajaca id w tresc wiadomosci do pokazania
		$zamowienie = $dane['prod_id'];
		$zamowienie = explode (':|:', $zamowienie);
		foreach ($zamowienie as $element){
			$prod = explode ('::', $element);
			$id = $prod[0];
			$ilosc = $prod[1];
			$cena = $prod[2];
			$pyt = "SELECT nazwa, producent FROM produkty WHERE produkt_id = '$id'";
			$wynik = $this->zapytanie($pyt);
			$dane = $this->tablica($wynik);
			//generacja wyniku
			$tresc .= '<tr>
        				<td>'.$dane['nazwa'].'<br />'.$dane['producent'].'</td>
						<td>'.$id.'</td>
						<td>'.$ilosc.'</td>
						<td>'.$ilosc * $cena.'</td>
						<td>'.$cena.'</td>
					</tr>';
			
		}
		$tresc .= '</table>';
		//dolna czesc maila
		//w kontrolerze
		
		return $tresc;
	}
	
	
	//sprawdzanie czy koszyk jest ustawiony, i czy sa w nim jakies dane
	public function koszyk_sprawdzenie(){
		if (!isset ($_SESSION['wuzek']) || $this->p_id == NULL || $this->prod_id == ''){
			return FALSE;
		}else{
			return TRUE;
		}
	}
	
	//zmiana ilosci towaru w koszyku
	public function ilosc($id, $ilosc){
		//sprawdza czy id istnieje w sesji
		if (isset($_SESSION['wuzek'][$id])){
			//sprawdza poprawna ilosc (walidacja typu danych w kontrolerze)
			if ((int)$ilosc > 99){
				throw new Exception ("Zbyt duża ilość wybranych produktów. Skontaktuj się z administracją");
			}
			if ($ilosc == '0'){
				unset ($_SESSION['wuzek'][$id]);
			}else{
				$_SESSION['wuzek'][$id]['ilosc'] = $ilosc;
			}
			
		}else{
			throw new Exception ("Podanego produktu nie ma w koszyku");
		}
	}
	
	/*konstrukcja wuzka
	array wuzek
			[id] - id towaru
				[ilosc] - ilosc w wuzku
				[cena] - cena za sztuke
			[id]
				[ilosc]
				[cena]
	*/
}
?>