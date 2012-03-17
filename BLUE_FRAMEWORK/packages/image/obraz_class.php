<?PHP
/**
 * obiekt do przeksztalcen na pojedynczym obrazie
 * @author chajr <chajr@bluetree.pl>
 * @version 1.1
 * @access private
 * @copyright chajr/bluetree
*/
class obraz_class{
	/**
	 * jakosc wynikowego pliku jpg 0-100
	 * @var integer
	 */
	public $jpg_quality = 80;
	/**
	 * kompresja wynikowego pliku png 0-9
	 * @var type 
	 */
	public $png_quality = 9;
	/**
	 * lokalizacja folderu tymaczowego
	 * @var string
	 */
	public $tmp_location = 'TMP';
	/**
	 * filtry dla png
	 * PNG_ALL_FILTERS 
	 * PNG_NO_FILTER 
	 * @var string 
	 */
	public $png_filter = '';
	/**
	 * tablica informacji o pliku z konstruktora
	 * @var array
	 */
	private $file_reference;
	/**
	 * uchwyt otwartego pliku do wykozystania
	 * @var resource
	 */
	private $img;
	/**
	 * szerokosc zaladowanego obrazu
	 * @var integer
	 */
	private $width;
	/**
	 * wysokosc zaladowanego obrazu
	 * @var integer 
	 */
	private $height;
	/**
	 * pobiera informacje o pliku i tworzy z niego uchwyt, lub tworzy zupelnie nowy plik o wymiarach i odpowiednim tle
	 * @param array $file tablica informacji o pliku (nazwa, rozszerzenie, scierzka dostepu lub wymiary oraz nazwa pliku do zapisania)
	 * @param boolean $chk jesli na true sprawdza czy plik uploudowany byl z bledem
	 * @example new obraz_class(array('name' => 'nazwa_pliku.jpg' 'tmp_name' => 'tmp/plik_tmp'));
	 * @example new obraz_class(array('name' => 'nazwa_pliku.string' 'tmp_name' => 'binary'));
	 * @example new obraz_class(array('x' => 100, 'y' => 100, 'name => 'jakis_plik.jpg', 'color' => '#000000', 'alpha' => 5)); alpha 0-127
	 * @uses obraz_class::$file_reference
	 * @uses obraz_class::create_image()
	 */
	public function __construct($file, $chk = FALSE){
		$this->file_reference = $file;
		if(isset($file['x']) && isset($file['y'])){
			$this->img = imagecreatetruecolor($file['x'], $file['y']);
			$this->height = $file['y'];
			$this->width = $file['x'];
			$colors = self::html2rgb($this->file_reference['color']);
			if(isset($this->file_reference['alpha'])){
				imagealphablending($this->img, 0);
				$color = imagecolorallocatealpha($this->img, $colors[0], $colors[1], $colors[2], $this->file_reference['alpha']);
			}else{
				$color = imagecolorallocate($this->img, $colors[0], $colors[1], $colors[2]);
			}
			@imagefilledrectangle($this->img, 0, 0, $this->width, $this->height, $color);
		}else{
			if($chk && $this->file_reference['error'] != UPLOAD_ERR_OK){
				throw new package_exception('upload_error', $this->file_reference['error']);
			}
			$typ = pathinfo($this->file_reference['name']);
			$this->file_reference['extension'] = strtolower($typ['extension']);
			$this->file_reference['name'] = $typ['filename'];
			if ($this->file_reference['extension'] != 'jpg' && $this->file_reference['extension'] != 'jpeg' && 
					$this->file_reference['extension'] != 'gif' && $this->file_reference['extension'] != 'png'
					&& $this->file_reference['extension'] != 'bmp'){
				throw new package_exception('invalid_extension', $this->file_reference['extension']);
			}
			$this->create_image();
		}
	}
	/**
	 * zmienia rozmiary obrazu z lub bez zachowania aspect ratio
	 * @param integer $x docelowa szerokosc obrazu
	 * @param integer $y docelowa wysokosc obrazu
	 * @param boolean $ratio jesli true, obraz ma zachowac aspect ratio
	 * @example resize(100, 200, 1)
	 * @example resize(100, 200)
	 * @uses obraz_class::$height
	 * @uses obraz_class::$width
	 * @uses obraz_class::$img
	 */
	public function resize($x, $y, $ratio = FALSE){
		if($ratio){
			if($this->height < $this->width){
				$nowy = imagecreatetruecolor($x,($x/$this->width) * $this->height);
			}else{
				$nowy = imagecreatetruecolor(($y/$this->height) * $this->width, $y);
			}
		}else{
			$nowy = imagecreatetruecolor($x, $y);
		}
		imagealphablending($nowy, 0);
		$nowy_x = imagesx($nowy);
		$nowy_y = imagesy($nowy);
		$bool = @imagecopyresampled($nowy, $this->img, 0, 0, 0, 0, 
				$nowy_x, $nowy_y, $this->width, $this->height);
		if(!$bool){
			throw new package_exception('resample_error');
		}
		$this->height = $nowy_y;
		$this->width = $nowy_x;
		$this->img = $nowy;
	}
	/**
	 * zapisuje plik w podanej lokalizacji i odpowiedim formacie, lub w postaci do wyswietlenia
	 * @param string $location docelowa lokalizacja pliku, jesli NULL zwraca obraz do wyswietlenia
	 * @param string $type typ pliku (jpg/jpeg, gif, png, bmp)
	 * @param string $name opcjonalnie nowa nazwa pliku (bez rozszerzenia)
	 * @example save('folder/podfolder', 'jpg', 'nowa_nazwa')
	 * @example save('folder/podfolder', 'jpg')
	 */
	public function save($location, $type, $name = FALSE){
		if($location){
			if(!$name){
				$name = $this->file_reference['name'];
			}
			$location = $location.'/'.$name.'.'.$type;
		}else{
			$location = NULL;
		}
		switch($type){
			case "jpg":case "jpeg":
				$bool = imagejpeg($this->img, $location, $this->jpg_quality);
				break;
			case "gif":
				$bool = imagegif($this->img, $location);
				break;
			case "png":
				if($this->png_filter){
					$bool = imagepng($this->img, $location, $this->png_quality, $this->png_filter);
				}else{
					$bool = imagepng($this->img, $location, $this->png_quality);
				}
				break;
			case "bmp":
				$bool = imagewbmp($this->img, $location);
				break;
			default:
				throw new package_exception('unknow_file_type', $type);
				break;
		}
		if(!$bool){
			throw new package_exception('save_error', $location);
		}
	}
	/**
	 * zwraca rozmiar pliku i jego reprezentacje binarna
	 * z mozliwoscia dodanie sekwencji ucieczki np. dla bazy danych
	 * @param string $type typ pliku do zapisu i sprawdzenia
	 * @param boolean $slashes jesli na true dodaje sekwencje ucieczki
	 * @return array() tablica rozmiaru pliku (size) i reprezentacji binarnej (bin) 
	 * @uses obraz_class::chk_tmp()
	 * @uses obraz_class::save()
	 * @uses obraz_class::$tmp_location
	 */
	public function binary($type, $slashes = FALSE){
		$this->chk_tmp();
		$arr = array();
		$this->save($this->tmp_location, $type, 'temp_image');
		$f = @fopen($this->tmp_location.'/temp_image.'.$type, 'r');
		if(!$f){
			throw new package_exception('tmp_file_open_error', $this->tmp_location.'/temp_image.'.$type);
		}
		$arr['size'] = @filesize($this->tmp_location.'/temp_image.'.$type);
		$arr['bin'] = fread($f, $arr['size']);
		if($slashes){
			$arr['bin'] = addslashes($arr['bin']);
		}
		fclose($f);
		return $arr;
	}
	/**
	 * tworzy uchwyt pliku do przetwarzania
	 * umozliwia kilka operacji na tym samym pliku (np otrzymac kilka plikow z jednego o roznych wymiarach)
	 * @uses obraz_class::$file_reference
	 * @uses obraz_class::$img
	 * @uses obraz_class::$width
	 * @uses obraz_class::$height
	 */
	public function create_image(){
		if($this->img){
			@imagedestroy($this->img);
		}
		switch($this->file_reference['extension']){
			case "jpg":case "jpeg":
				$this->img = @imagecreatefromjpeg($this->file_reference['tmp_name']);
				break;
			case "gif":
				$this->img = @imagecreatefromgif($this->file_reference['tmp_name']);
				break;
			case "png":
				$this->img = @imagecreatefrompng($this->file_reference['tmp_name']);
				break;
			case "bmp":
				$this->img = @imagecreatefromwbmp($this->file_reference['tmp_name']);
				break;
			case "string":
				$this->img = @imagecreatefromstring($this->file_reference['tmp_name']);
				break;
			default:
				$this->img = FALSE;
				break;
		}
		if (!$this->img){
			throw new package_exception('create_error', $this->file_reference['extension']);
		}
		$this->width = imagesx($this->img);
		$this->height = imagesy($this->img);
	}
	/**
	 * zwraca wymiary aktualne obrazu, badz jego uchwyt do wykozystania przez inne funkcje
	 * @param string $type typ danych do zwrucenia (img, width, height)
	 * @return mixed informacje o wymiarach, badz uchwyt pliku
	 * @uses obraz_class::$img
	 * @uses obraz_class::$width
	 * @uses obraz_class::$height
	 */
	public function returns($type){
		switch($type){
			case'img':
				return $this->img;
				break;
			case'width':
				return $this->width;
				break;
			case'height':
				return $this->height;
				break;
		}
			
	}
	public function rotate($degree, $bg_color, $transparent = 0){
		$this->img = imagerotate($this->img, $degree, $bg_color, $transparent);
	}
	public function crop($x, $y, $width, $height){
		
	}
	/**
	 * umozliwia umieszczanie w obrazie innych obrazow
	 * @param resource $handler uchwyt do obrazu ktory ma byc umieszczony
	 * @param integer $x_poz pozycja x dla obrazu
	 * @param integer $y_poz poxycja y dla obrazu
	 * @uses obraz_class::$img
	 * 
	 */
	public function place_image($handler, $x_poz, $y_poz){
		$bool = imagecopy($this->img, $handler, $x_poz, $y_poz, 0, 0, imagesx($handler), imagesy($handler));
		if(!$bool){
			throw new package_exception('merge_error', $x_poz.'-'.$y_poz.'-'.imagesx($handler).'-'.imagesy($handler));
		}
	}
	/**
	 * zamyka uchwyt i ksuje plik tymczasowy
	 * @uses obraz_class::$img
	 * @uses obraz_class::$tmp_location
	 */
	public function __destruct() {
		@imagedestroy($this->img);
		//@unlink($this->tmp_location.'/temp_image');
	}
	/**
	 * sprawdza czy istnieje i jesli nie tworzy folder dla plikow tymczasowych
	 * @uses obraz_class::$tmp_location
	 */
	private function chk_tmp(){
		if(!file_exists($this->tmp_location)){
			$bool = mkdir($this->tmp_location, 0777);
			if(!$bool){
				throw new package_exception('tmp_create_error', $this->tmp_location);
			}
		}
	}
	/**
	 * konwertuje wartosci rgb na hex
	 * @param integer $r kolor czerwony
	 * @param integer $g kolor zielony
	 * @param rgb $b kolor niebieski
	 * @return string zwraca wartosc hex kolory w postaci #xxxxxx
	 */
	static function rgb2html($r, $g=-1, $b=-1){
		if (is_array($r) && sizeof($r) == 3){
			list($r, $g, $b) = $r;
		}
		$r = intval($r); $g = intval($g);
		$b = intval($b);
		$r = dechex($r<0?0:($r>255?255:$r));
		$g = dechex($g<0?0:($g>255?255:$g));
		$b = dechex($b<0?0:($b>255?255:$b));
		$color = (strlen($r) < 2?'0':'').$r;
		$color .= (strlen($g) < 2?'0':'').$g;
		$color .= (strlen($b) < 2?'0':'').$b;
		return '#'.$color;
	}
	/**
	 * konwertuje wartosci hex na tablice rgb
	 * @param string $color kolor hex w postaci #xxxxxx
	 * @return array tablica kolorow rgb
	 */
	static function html2rgb($color){
		if ($color[0] == '#'){
			$color = substr($color, 1);
		}
		if (strlen($color) == 6){
			list($r, $g, $b) = array($color[0].$color[1],
									 $color[2].$color[3],
									 $color[4].$color[5]);
		}elseif (strlen($color) == 3){
			list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
		}else{
			return false;
		}
		$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
		return array($r, $g, $b);
	}
}
?>