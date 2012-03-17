<?PHP
class kontrola_parametru extends db{
	//sprawdzanie poprawnosci parametrow 
	//np, id w bazie danych, adres e-mail, numer telefonu, kod pocztowy itp


	private $niedozwolone_znaki = '';

	//sprawdza istnienie danego id w tabeli (oraz sama poprawnosc parametru liczbowego jesli $liczba = TRUE)
	public function table_id ($id, $kolumna = 0, $tabela = 0, $liczba = FALSE){
		//sprawdza poprawnosc parametru (czy liczba)
		$sprawdzenie = ereg ('^([0-9]{1,})$', $id);
		if (!$sprawdzenie){
			throw new Exception ("Nieprawidłowy typ parametru");
		}
		if (!$liczba){
			//sprawdza czy wpis w tabeli istnieje
			$pytanie = "SELECT $kolumna FROM $tabela WHERE $kolumna = $id";
			$wynik = $this->pytanie_sprawdzajace ($pytanie);
			if (!$wynik){
				throw new Exception ("Podany produkt nie występuje w bazie danych");
			}
		}
	}
	
	
	//parametry kont
	//login (istnieje(true) - sprawdza czy istnieje w baze, inaczej tylko poprawnosc)
	public function login ($login, $istnieje = FALSE){
		$login = trim($login);
		if ($login == '' || mb_strlen($login) > 15){
			throw new Exception ("Login za długi, lub jego brak");
		}
		if (ereg ('[`!@#$%^&*()=+\|;\'",.<>?/: ]', $login)){
			throw new Exception ("Login zawiera nieprawidłowe znaki");
		}
		//sprawdza czy uzytkownik istnieje w bazie danych
		if ($istnieje){
			$pyt = "SELECT login FROM uzytkownicy WHERE login = '$login';"; 
			$pyt = $this->pytanie_sprawdzajace ($pyt);
			if ($pyt){
				throw new Exception ("Podany użytkownik juz istnieje");
			}
		}
	}
		
	//hasla
	public function hasla ($haslo, $haslo2){
		$haslo = trim($haslo);
		$haslo2 = trim($haslo2);
		if ($haslo != $haslo2){
			throw new Exception ("Podane hasła sa różne");
		}
		if ($haslo == '' || mb_strlen($haslo) > 10 || mb_strlen($haslo) < 5){
			throw new Exception ("Brak hasła, lub niepoprawna długość");
		}
		/*if (ereg ('[[:alnum:]easlzzcnEASLZZCN_-]{5,10}', $haslo) || ereg ('[[:alnum:]easlzzcnEASLZZCN_-]{5,10}', $haslo2)){
			throw new Exception ("Haslo zawiera nieprawidlowe znaki");
		}*/
		if (ereg ('[`!@#$%^&*()=+\|;\'",.<>?/: ]', $haslo)){
			throw new Exception ("Login zawiera nieprawidłowe znaki");
		}
	}
	//imie
	public function imie ($imie, $wymagany = FALSE){
		$imie = trim($imie);
		if ($wymagany && ($imie == '' || $imie == 'Imię')){
			throw new Exception ('Brak podanego imienia');
		}
		if ($imie != ''){
			if (mb_strlen($imie) >20 || mb_strlen($imie) < 2){
				throw new Exception ("Niepoprawna długość imienia (max 20 znakw)");
			}
			if (ereg ('[`!@#$%^&*()=+\|;\'",.<>?/:]', $imie)){
				throw new Exceprion ("Imię zawiera nieprawidłowe znaki");
			}
		}
	}
		
	//nazwisko
	public function nazwisko ($nazwisko, $wymagany = FALSE){
		$nazwisko = trim($nazwisko);
		if ($wymagany && ($nazwisko == '' || $nazwisko == 'Nazwisko')){
			throw new Exception ('Brak podanego nazwiska');
		}
		if ($nazwisko != ''){
			if (mb_strlen($nazwisko) >20 || mb_strlen($nazwisko) < 2){
				throw new Exception ("Niepoprawna długość nazwiska (max 20 znaków)");
			}
			if (ereg ('[`!@#$%^&*()_=+\|;\'",.<>?/:]', $nazwisko)){
				throw new Exceprion ("Nazwisko zawiera nieprawidłowe znaki");
			}
		}
	}
		
	//ulica
	public function ulica ($ulica, $wymagany = FALSE){
		$ulica = trim($ulica);
		if ($wymagany && ($ulica == '' || $ulica == 'Ulica')){
			throw new Exception ('Brak podanej ulicy');
		}
			if ($ulica != ''){
				if (mb_strlen($ulica) >20 || mb_strlen($ulica) < 2){
					throw new Exception ("Niepoprawna długość nazwy ulicy (max 20 znakw)");
				}
				if (ereg ('[`!@#$%^&*()_=+|;\'<>?:]', $ulica)){
					throw new Exceprion ("Ulica zawiera nieprawidłowe znaki");
				}
			}
	}
		
	//dom
	public function numer_domu ($dom, $wymagany = FALSE){
		$dom = trim($dom);
		if ($wymagany && ($dom == '' || $dom == 'Numer domu/mieszkania')){
			throw new Exception ('Brak podanego numeru domu/mieszkania');
		}
			if ($dom != ''){
				if (mb_strlen($dom) >10){
					throw new Exception ("Niepoprawna długość numeru domu/mieszkania (max 10 znakw)");
				}
				if (ereg ('[`!@#$%^&*()_=+|;\'",.<>?:]', $ulica)){
					throw new Exceprion ("Numer domu/mieszkania zawiera nieprawidłowe znaki");
				}
			}
	}
		
	//kod
	public function kod_pocztowy ($kod, $wymagany = FALSE){
		if ($wymagany && ($kod == '' || $kod == 'Kod pocztowy')){
			throw new Exception ('Brak podanego kodu pocztowego');
		}
			if ($kod != ''){
				if (!ereg ('^[0-9]{2}-[0-9]{3}$', $kod)){
					throw new Exception ("Kod pocztowy ma nieprawidłowy format");
				}
			}
	}
		
	//miasto
	public function miasto ($miasto, $wymagany = FALSE){
		$miasto = trim($miasto);
		if ($wymagany && ($miasto == '' || $miasto == 'Miasto')){
			throw new Exception ('Brak podanego miasta');
		}
			if ($miasto != ''){
				if (mb_strlen($miasto) >20 || mb_strlen($miasto) < 2){
					throw new Exception ("Niepoprawna długość nazwy miasta (max 20 znakw)");
				}
				if (ereg ('[`!@#$%^&*()_=+\|;\'",.<>?/:]', $miasto)){
					throw new Exceprion ("Nazwa miasta zawiera nieprawidłowe znaki");
				}
			}
	}
		
	//telefon
	public function telefon ($telefon, $wymagany = FALSE){
		$telefon = trim($telefon);
		if ($telefon == 'Telefon (opcjonalne)'){
			return 'ok';
		}
		if ($wymagany && $telefon == ''){
			throw new Exception ('Brak podanego nazwiska');
		}
			if ($telefon != ''){
				if (mb_strlen($telefon) >20 || mb_strlen($telefon) < 5){
					throw new Exception ("Niepoprawna długość numeru telefonu (max 20 znakw)");
				}
				if (ereg ('[`!@#$%^&*_=\|;\'",.<>?/a-zA-Z]', $telefon)){
					throw new Exceprion ("Numer telefonu zawiera nieprawidłowe znaki");
				}
			}
	}
		
		
	//mail (istnieje(true) - sprawdza czy istnieje w baze, inaczej tylko poprawnosc)
	public function e_mail($mail, $istnieje = FALSE){
		if ($mail == ''){
			throw new Exception ("Brak podanego adresu e-mail");
		}
		if (!preg_match ('|^[_a-z0-9.-]*[a-z0-9]@[_a-z0-9.-]*[a-z0-9.[a-z]{2,3}$|e',$mail)){
			throw new Exception ("Adres e-mail posiada nieprawidłowy format");
		}
		if ($istnieje){
			//sprawdzanie istnienia maila
			$pyt = "SELECT mail FROM uzytkownicy WHERE mail = '$mail';"; 
			$pyt = $this->pytanie_sprawdzajace ($pyt);
			if ($pyt){
				throw new Exception ("Podany adres E-mail już istnieje");
			}
		}
	}
	
	public function tekst($param){
		$bool = preg_match('|[@#$%^*~\'`=\|]|', $param);
		if (!$bool){
			return TRUE;
		}else{
			return FALSE;
		}
	}
	
	public function cena($param){
		$bool = preg_match('|^[0-9]{1,5}(\.[0-9]{1,2})?$|', $param);
		if ($bool){
			return TRUE;
		}else{
			return FALSE;
		}
	}
}
?>