<?PHP
//v1.1.1
class file_class {
	public $err;
	//jesli typ == TRUE zwraca liste plikow zgodna z podanymi rozszerzeniami, jesli == FALSE, nie zwraca plikow z rozszerzeniami
    public static function lista($scierzka, $foldery = FALSE, $rozszerzenia = FALSE, $typ = TRUE){
		$bool = is_dir($scierzka);
		if(!$bool){
			return FALSE;																//zwraca blad jesli nie jest folderem
		}
		$uchwyt = opendir($scierzka);													//otwiera uchwyt do folderu
		if($uchwyt){
			$tab = array();																//jesli ok, tworzy tablice na zawartosc
			if($rozszerzenia){															//jesli podano rozszerzenia
				if(!is_array($rozszerzenia)){											//sprawdza czy tablica z rozszerzeniami
					$rozszerzenia = explode(',', trim($rozszerzenia));					//jesli string, rozdziala na tablice
				}
			}
			while ($zawartosc = readdir($uchwyt)){										//przetwarza zawartosc folderu
				if($zawartosc == '.' || $zawartosc == '..'){							//jesli natrafil na kropki, pomija
					continue;
				}
				if(!$foldery && is_dir($scierzka.'/'.$zawartosc)){							//jesli wlaczono pomijanie folderow kontynuuje
					continue;
				}
				if($rozszerzenia && is_file($scierzka.'/'.$zawartosc)){						//jesli podano jakies rozszerzenia i zawartosc jest plikiem
					$ext = pathinfo($zawartosc);										//pobiera inf o pliku
					$bool = in_array($ext['extension'], $rozszerzenia);					//przeszukuje tablice w poszukiwaniu pliku
					if($typ && $bool){													//jesli typ == true i znaleziono rozszerzenie dodajo do listy
						$tab[] = $zawartosc;
					}elseif(!$typ && !$bool){											//jesli typ == false i nie znaleziono rozszerzenia dodaje plik do listy
						$tab[] = $zawartosc;
					}
				}else{
					$tab[] = $zawartosc;												//jesli brak rozszerzen dodaje plik
				}
			}
		}else{
			return NULL;																//blad jesli nie udalo sie otworzyc folderu
		}
		closedir($uchwyt);																//zamykanie
		return $tab;
	}
	public static function new_f($path, $start_val = FALSE){							//tworzy nowy plik
		 $bool = @fopen($path, 'w');													//tworzy lik
		 if(!$bool){
			 return FALSE;																//jesli blad false
		 }
		 if($start_val){																//jesli ustawiono wprowadza wartosc poczatkowa
			 $bool = @fwrite($bool, $start_val);
			 if(!$bool){
				return FALSE;
			}
		 }
		 @fclose($uchwyt);																//zamyka plik
		 chmod($path, 0777);															//zmienia prawa
		 return TRUE;
	}
	public static function del($path){													//usuwa plik
		$bool = @unlink($path);
		if(!$bool){
			return FALSE;
		}
		return TRUE;
	}
	public static function move($loc_1, $loc_2, $rename = FALSE){						//przenoszenie pliku

	}
}
?>
