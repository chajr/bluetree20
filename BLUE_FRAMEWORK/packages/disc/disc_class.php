<?PHP
/**
 * podstawowe operacje na plikach i folderach
 * @author chajr <chajr@bluetree.pl>
 * @package disc
 * @version 1.0.1
 * @copyright chajr/bluetree
 */
class disc_class {
	/**
	 * wyrazenie regularne z zastrzezonymi znakami dla plikow i folderow
	 * @var string
	 * @staticvar
	 */
	const restrict_symbol = '#[:?*<>"|\\]#';
	/**
	 * usuwanie pliku/katalogu wraz z cala zawartoscia
	 * @param string $path scierzka dla pliku lub katalogu
	 * @return boolean informacja o powodzeniu operacji, lub NULL jesli scierzka niepoprawna
	 * @static
	 */
	static function delete($path){
		if(!file_exists($path)){
			return NULL;
		}
		@chmod($path, 0777);
		if(is_dir($path)){
			$list = self::read_dir($path);
			$paths = self::return_paths($list, $path, TRUE);
			if(isset($paths['file'])){
				foreach($paths['file'] as $val){
					unlink($val);
				}
			}
			if(isset($paths['dir'])){
				foreach($paths['dir'] as $val){
					rmdir($val);
				}
			}
			rmdir($path);
		}else{
           	$bool = @unlink($path);
		}
		return $bool;
   	}

	static function copy($path, $target){
		if(!file_exists($path)){
			return NULL;
		}
		if(is_dir($path)){
			if(!file_exists($target)){
				mkdir($target);
           	}
			$elements = self::read_dir($path);
			$paths = self::return_paths($elements, '');
			foreach($paths['dir'] as $dir){
				mkdir($dir);
           	}
			foreach($paths['file'] as $file){
				copy($path."/$file", $target."/$file");
			}
       	}else{
			if(!$target){
				$filename = explode('/', $path);
				$target = end($filename);
			}else{
				//sprawdzenie zabronionych symboli
			}
			$bool = copy($path, $target);
		}
		return $bool;
   	}
	/**
	 * tworzy nowy folder w podanej lokalizacji
	 * @param string $path scierzka do lokacji i nazwa folderu do utworzenia
	 * @return informacja o powodzeniu operacji
	 * @static
	 */
	static function mkdir($path){
		$bool = preg_match(self::restrict_symbol, $path);
		if(!$bool){
			$bool = mkdir ($path);
			return $bool;
		}else{
			return FALSE;
		}
	}
	/**
	 * tworzy pusty plik, opcjonalnie umieszcza w nim dane
	 * @example mkfile('folder/inn', 'pliczek.txt')
	 * @example mkfile('folder/inn', 'pliczek.txt', 'sdfklsmndflksmnflksdnflksnf')
	 * @param string $path scierzka w ktorej ma zostac utworzony plik
	 * @param string $file nazwa pliku
	 * @param mixed $data opcjonalnie dane do zapisania w pliku
	 * @return boolean informacja o powoidzeniu operacji, lub NULL jesli scierzka niepoprawna
	 * @static
	 */
	static function mkfile($path, $file, $data = NULL){
		if(!file_exists($path)){
			return NULL;
		}
		$bool = preg_match(self::restrict_symbol, $file);
		if(!$bool){
			$f = @fopen("$path/$file", 'r');
			fclose($f);
			if($data){
				$bool = file_put_contents("$path/$file", $data);
				return $bool;
			}
		}else{
			return FALSE;
		}
	}
	static function uploaded(){

	}
	/**
	 * zmiania nazwe pliku lub katalogu, moze sluzyc tez do kopiowania pliku
	 * @param string $path orginalna scierzka lub nazwa
	 * @param string $target nowa nazwa
	 * @return boolean informacja o powoidzeniu operacji, lub NULL jesli scierzka niepoprawna
	 * @static
	 */
	static function rename($path, $target){
		if(!file_exists($path)){
			return NULL;
		}
		$bool = preg_match(self::restrict_symbol, $target);
		if(!$bool){
			$bool = rename($path, $target);
			return $bool;
		}else{
			return FALSE;
		}
//		if(!file_exists($path1.$orgin.'.'. $lista['rozsz'])){
//			echo $path1.$orgin.'.'. $lista['rozsz'].' ble<br/>';
//		}
//		$data = file_get_contents($path1.$orgin.'.'. $lista['rozsz']);
//		if($data){
//			$bool = file_put_contents($path1.$orgin.'_en.'. $lista['rozsz'], $data);
//			var_dump($bool);
//			echo $path1.$orgin.'_en.'. $lista['rozsz'].' ok<br/>';
//		}
	}
	static function move($path, $target){

	}
	/**
	 * odczytuje zawartosc danego katalogu i zwraca jego katalogi (wraz z ich zawartoscia) i pliki
	 * posortowane za pomoca funkcji array_multisort
	 * @example read_dir('folder/jakis_folder') - odczytuje zawartosc jakiegos_folderu
	 * @example read_dir(); - odczytuje zawartosc folderu w ktorym znajduje sie skrypt (__FILE__)
	 * @param string $path scierzka do katalogu do przetworzenia
	 * @param integer $level poziom zagniezdzenia, tylko do uzycia rekurencyjnego
	 * @return array tablica ze struktura podanego folderu, lub NULL jesli scierzka niepoprawna
	 * @uses disc_class::read_dir()
	 * @static
	 */
	static function read_dir($path = NULL){
		if(!$path){
           	$path = dirname(__FILE__);
		}
		if(!file_exists($path)){
			return NULL;
		}
		$uchwyt = opendir($path);
		if($uchwyt){
			$tab = array();
			while($element = readdir($uchwyt)){
				if($element == '..' || $element == '.'){
					continue;
				}
				if(is_dir("$path/$element")){
					$tab[$element] = self::read_dir("$path/$element");
               	}else{
					$tab[] = $element;
				}
			}
			closedir($uchwyt);
		}
		array_multisort($tab);
       	return $tab;
	}
	/**
	 * transformuje tablice z drzewem katalogow/plikow na liste scierzek pogrupowane na pliki i foldery
	 * jesli brak defaultowej scierzki
	 * @example return_paths($array, '')
	 * @example return_paths($array, '', 1)
	 * @example return_paths($array, 'jakis_folder/inny', 1)
	 * @param array $array tablica do przetworzenia
	 * @param string $path bazowa scierzka dla elementow, jesli brak zwraca scierzki wedlug struktury przetwarzanego katalogu
	 * @param boolean $reverse inf czy ma odwracac tablice (dla usuwania konieczne)
	 * @return array tablica z lista scierzek dla plikow (file) i katalogow (dir)
	 * @uses disc_class::return_paths()
	 * @static
	 */
	static function return_paths($array, $path = '', $reverse = FALSE){
		if($reverse){
			$array = array_reverse($array);
		}
		$tab = array();
		foreach($array as $key => $val){
			if(is_dir($path."/$key")){
				$arr = self::return_paths($val, $path."/$key");
				foreach($arr as $element => $value){
                   	if($element == 'file'){
						foreach($value as $file){
							$tab['file'][] = "$file";
						}
					}
					if($element == 'dir'){
						foreach($value as $dir){
							$tab['dir'][] = "$dir";
						}
                   	}
               	}
				$tab['dir'][] = $path."/$key";
           	}else{
				$tab['file'][] = $path."/$val";
			}
		}
		return $tab;
	}
}
?>