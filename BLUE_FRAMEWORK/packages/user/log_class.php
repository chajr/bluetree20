<?PHP
class log_class {
	public static function zaloguj($uid, $opcje, $grupa){
		$kod = self::kod();														//wygenerowanie kodu
		$_SESSION['log_class']['log'] = TRUE;									//zapis danych do sesji
		$_SESSION['log_class']['uid'] = $uid;
		$_SESSION['log_class']['kod'] = $kod;
		$_SESSION['log_class']['opcje'] = $opcje;
		$_SESSION['log_class']['grupa'] = $grupa;
		$_SESSION['log_class']['czas'] = time() + 60*60;
	}
	public static function wyloguj(){
		$_SESSION['log_class'] = array();										//niszczenie sesji
		unset($_SESSION['log_class']);
	}
	public static function weryfikacja(){
		if (!isset($_SESSION['log_class']['log']) ||							//sprawdza istnienie danych logowania
			!isset($_SESSION['log_class']['uid']) ||
			!isset($_SESSION['log_class']['kod']) ||
			!isset($_SESSION['log_class']['opcje']) ||
			!isset($_SESSION['log_class']['grupa']) ||
			!isset($_SESSION['log_class']['czas']) ||
			!$_SESSION['log_class']['log']){
			return FALSE;														//zwraca false jesli niezalogowany
		}else{
			if ($_SESSION['log_class']['kod'] == self::kod()){					//sprawdzenie kodu
				if ($_SESSION['log_class']['opcje']{0} == '0'){					//sprawdza czy zarejestrowany
					throw new LibraryException('no_reg');
				}
				if ($_SESSION['log_class']['opcje']{1} == '0'){					//sprawdza czy zablokowany
					throw new LibraryException('blocked');
				}
				if ($_SESSION['log_class']['czas'] < time()){			//sprawdza czy nie przekroczono czasu
					return FALSE;
				}
				$_SESSION['log_class']['czas'] = time() + 60*60;
				@session_regenerate_id();										//generuje nowe id sesji
				$_SESSION['log_class']['kod'] = self::kod();					//ponowne wygenerowanie kodu
				return TRUE;
			}else{
				return FALSE;													//zwraca niezalogowanego (w przyszlosci wyjatek?)
			}
		}
	}
	private static function kod(){
		$id_sesji = session_id();									//id sesji
		$client = $_SERVER['HTTP_USER_AGENT'];						//przegladarka
		$ip = $_SERVER['REMOTE_ADDR'];								//ip
		$kod = hash('sha256', $id_sesji.$client.$ip);				//tworzy kod dla uzytkownika z powyzszych wartosci
		return $kod;
	}
}
?>