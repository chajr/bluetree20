<?PHP

//pokazuje obraz przekazany jako dane binarne, inaczej tworzy wpis brak zdjecia

class pokaz_obraz{
	private $id;
	public $rozmiar= '2515';				//rozmiar ikony brak obrazu
	public $nazwa = 'brak_obrazu.jpg';		//scierzka do brak obrazu
	
	public function pokaz($id){
		$this->id = intval($id);
		require_once ("mysql_class.php");				//powoluje klase bazy danych
		$param[0] = 'sql.zmp.nazwa.pl';
		$param[1] = 'zmp_5';
		$param[2] = 'WE244ef43%$';
		$param[3] = 'zmp_5';
		$db = new mysql_class($param);	//uruchamia baze
		if(!$db){
			return FALSE;
		}
		
		//walidacja id
		
		$obraz = $db->zapytanie("SELECT plik, rozmiar, bin FROM zdj WHERE z_id = $this->id");
		$obraz = $db->tablica($obraz);
		//zapis obrazu do tablicy
		if ($obraz != NULL){
			$tab = array();
			$tab['bin'] = $obraz['bin'];
			$tab['plik'] = $obraz['plik'];
			$tab['rozmiar'] = $obraz['rozmiar'];
			return $tab;
		}else{
			return FALSE;
		}
	}
}
?>